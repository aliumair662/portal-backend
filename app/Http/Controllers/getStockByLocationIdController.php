<?php

namespace App\Http\Controllers;

use App\Models\Odoo\PickingLine;
use App\Models\Odoo\Production;
use App\Services\ProductService;
use App\Services\StockService;
use Illuminate\Http\Request;
use App\Models\{User, Deregister, UserLocation};
use App\Enums\OrganisationType;
use App\Enums\RequestType;
use App\Models\Odoo\Picking;
use App\Models\Odoo\Product;

class getStockByLocationIdController extends Controller
{
    public function index($id)
    {
        $pickings = (new Picking())->getAssignedFromLocation($id);

        $manufactoringOrders = (new Production())->fromLocation($id);
        // loop through the MO
        $productModel = new Product();
        $ids = collect($manufactoringOrders)->pluck('product_id.0');
        $products = count($ids) > 0 ? collect($productModel->getByProductVariantIds($ids)) : [];

        $tmpMO = collect($manufactoringOrders)->map(function ($manufactoringOrder, $key) use ($products) {

            $productTemplate = $products->filter(function ($item) use ($manufactoringOrder) {
                return in_array($manufactoringOrder['product_id'][0], $item['product_variant_ids']);
            })->values();
            $arr = collect($productTemplate)->map(function ($product) {
                return [
                    $product['id'], $product['name']
                ];
            });

            return [
                'id' => $manufactoringOrder['id'],
                'product_id' => $manufactoringOrder['product_id'],
                'product_tmpl_id' => $arr[0],
                'location_id' => array(),
                'lot_id' => array(),
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'quantity' => 0
            ];
        });

        $ids = collect($pickings)->pluck('product_id.0');
        $products = count($ids) > 0 ?  collect($productModel->getByProductVariantIds($ids)) : [];
        // loop through the pickings
        $tmpPickings = collect($pickings)->map(function ($picking) use ($products) {

            $productTemplate = $products->filter(function ($item) use ($picking) {
                return in_array($picking['product_id'][0], $item['product_variant_ids']);
            })->values();
            $arr = collect($productTemplate)->map(function ($product) {
                return [
                    $product['id'], $product['name']
                ];
            });

            if (empty($arr) || !isset($arr[0])) {
                return;
            }

            return [
                'id' => $picking['id'],
                'product_id' => $picking['product_id'],
                'product_tmpl_id' => $arr[0],
                'location_id' => $picking['location_id'],
                'lot_id' => array(),
                'reserved_quantity' => 0,
                'available_quantity' => 0,
                'quantity' => 0
            ];
        });


        $stock_items = (new StockService())->fromLocation($id);



        foreach ($tmpPickings as $tmpPicking) {
            if (!$tmpPicking || !$tmpPicking['product_id'] || empty($tmpPicking)) {
                continue;
            }
            $exists = $stock_items->where('product_id.0', $tmpPicking['product_id'][0])->first();
            if ($exists) {
                continue;
            }
            $stock_items->push($tmpPicking);
        }

        foreach ($tmpMO as $manufactoringOrder) {
            if (!$manufactoringOrder || !$manufactoringOrder['product_id'] || empty($manufactoringOrder)) {
                continue;
            }
            $exists = $stock_items->where('product_id.0', $manufactoringOrder['product_id'])->first();
            if ($exists) {
                continue;
            }
            $stock_items->push($manufactoringOrder);
        }

        $stock_moving = (new PickingLine())->toLocation($id);

        return [
            'stock' => $stock_items,
            'stock_list' => $stock_items->groupBy('product_id.0')->transform(function ($data, $key) use ($stock_moving, $manufactoringOrders) {

                $incoming_stock = 0;
                $deregister = 0;
                $unique_stock_id = [];
                $deregister_lot_ids = [];

                foreach ($data as $item) {
                    if ($item && $item['product_id'] && in_array($item['product_id'][0], $unique_stock_id)) {
                        continue;
                    }
                    if ($item && $item['product_id'] && isset($stock_moving[$item['product_id'][0]])) {
                        $incoming_stock += $stock_moving[$item['product_id'][0]];
                        $unique_stock_id[] = $item['product_id'][0];
                    }

                    if (!empty($manufactoringOrders) && isset($manufactoringOrders[$item['product_id'][0]])) {
                        $incoming_stock += $manufactoringOrders[$item['product_id'][0]]['in_production'];
                        $unique_stock_id[] = $item['product_id'][0];
                    }

                    if ($item && $item['location_id'] && isset($item['location_id'][0])) {
                        $users_location = UserLocation::where('odoo_location_id', $item['location_id'][0])->first();
                        if (!empty($users_location)) {
                            $depot_user = User::where('odoo_organisation_type_id', OrganisationType::DEPOT)
                                ->where('id', $users_location->user_id)
                                ->first();
                            if (!empty($depot_user)) {
                                $deregister = Deregister::where("product_id", $item['product_id'][0])->whereHas('request', function ($qr) {
                                    $qr->where('status', 'open');
                                })->count();
                            }
                        }
                    }

                    if ($item && !empty($item['lot_id']) && isset($item['lot_id'][1])) {
                        $deregisterproduct = Deregister::where("product_id", $item['product_id'][0])->where("lot_id", $item['lot_id'][1])->first();
                        if (!empty($deregisterproduct)) {
                            if (!in_array($item['lot_id'][1], $deregister_lot_ids)) {
                                $deregister_lot_ids[] = $item['lot_id'][1];
                            }
                        }
                    }
                }
                return [
                    'stock' => $data,
                    'quantity' => $data->sum(function ($item) {
                        return $item['quantity'] > 0 ? $item['quantity'] : 0;
                    }) - $deregister,
                    'reserved_quantity' => $data->sum(function ($item) {
                        return $item['reserved_quantity'] > 0 ? $item['reserved_quantity'] : 0;
                    }),
                    'incoming_stock' => $incoming_stock,
                    'deregister' => $deregister,
                    'deregister_lot_ids' => $deregister_lot_ids,
                ];
            }),
            'stock_list_by_template' => $stock_items->groupBy('product_tmpl_id.0')->transform(function ($data, $key) use ($stock_moving, $pickings, $manufactoringOrders) {

                $tmpArrUniqueNumbers = [];
                $arrMODone = [];

                $incoming_stock = 0;
                $deregister = 0;
                $unique_stock_id = [];
                $deregister_lot_ids = [];
                $pickingsCount = 0;
                $moCount = 0;
                $arr = array();
                foreach ($data as $item) {
                    if (in_array($item['product_id'][0], $unique_stock_id)) {
                        continue;
                    }

                    if ($item && $item['product_id'] && !array_key_exists($item['product_id'][0], $tmpArrUniqueNumbers)) {
                        $pickingsCount += $pickings->where('product_id.0', $item['product_id'][0])->count();
                        $tmpArrUniqueNumbers[$item['product_id'][0]] = $pickingsCount;
                    }

                    if ($item && $item['product_id'] && !array_key_exists($item['product_id'][0], $arrMODone)) {
                        if (isset($manufactoringOrders[$item['product_id'][0]])) {
                            $moCount += $manufactoringOrders[$item['product_id'][0]]['in_production'];
                        }
                        $arrMODone[$item['product_id'][0]] = $moCount;
                    }

                    if ($item && $item['product_id'] && isset($stock_moving[$item['product_id'][0]])) {
                        $incoming_stock += $stock_moving[$item['product_id'][0]];
                        $unique_stock_id[] = $item['product_id'][0];
                    }

                    if (!empty($manufactoringOrders) && isset($manufactoringOrders[$item['product_id'][0]])) {
                        $incoming_stock += $manufactoringOrders[$item['product_id'][0]]['in_production'];
                        $unique_stock_id[] = $item['product_id'][0];
                    }

                    if ($item && $item['location_id'] && isset($item['location_id'][0])) {
                        $users_location = UserLocation::where('odoo_location_id', $item['location_id'][0])->first();
                        if (!empty($users_location)) {
                            $depot_user = User::where('odoo_organisation_type_id', OrganisationType::DEPOT)
                                ->where('id', $users_location->user_id)
                                ->first();
                            if (!empty($depot_user)) {
                                $deregister = Deregister::where("product_id", $item['product_id'][0])->whereHas('request', function ($qr) {
                                    $qr->where('status', 'open');
                                })->count();
                            }
                        }
                    }
                    if ($item && !empty($item['lot_id']) && isset($item['lot_id'][1])) {
                        $deregisterproduct = Deregister::where("product_id", $item['product_id'][0])->where("lot_id", $item['lot_id'][1])->first();
                        if (!empty($deregisterproduct)) {
                            if (!in_array($item['lot_id'][1], $deregister_lot_ids)) {
                                $deregister_lot_ids[] = $item['lot_id'][1];
                            }
                        }
                    }
                }

                return [
                    'stock' => $data,
                    'quantity' => $data->sum(function ($item) {
                        return $item['quantity'] > 0 ? $item['quantity'] : 0;
                    }) - $deregister,
                    'quantity_count' => $data->sum(function ($item) {
                        return $item['quantity'] > 0 ? $item['quantity'] : 0;
                    }),
                    'reserved_quantity' => $data->sum(function ($item) {
                        return $item['reserved_quantity'] > 0 ? $item['reserved_quantity'] : 0;
                    }),
                    'incoming_stock' => $incoming_stock,
                    // 'deregister' => $pickingsCount + $deregister,
                    'deregister' => $pickingsCount + $moCount + $deregister,
                    'pickingsCount' => $pickingsCount,
                    'moCount' => $moCount,
                    'deregister_lot_ids' => $deregister_lot_ids,
                ];
            }),
            'attributes' => (new ProductService())->attributePerVariant($stock_items->pluck('product_id.0')),
            'incoming_stock' => $stock_moving,
            'incoming_stock_production' => $manufactoringOrders,
        ];
    }
}
