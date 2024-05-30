<?php

namespace App\Services;

use App\Enums\Languages;
use App\Models\DepotReturn;
use App\Models\Deregister;
use App\Models\Client;
use App\Services\ProductService;
use App\Models\{Extension, User};
use App\Enums\RequestType;
use App\Models\Request as VanWijkRequest;
use Illuminate\Http\Client\Request;
use  App\Models\Odoo\Attributes\Interior;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use App\Models\Odoo\{Attribute, Partner, Product, Tax, Order, OrderLine, PickingLine, PricelistItem, Location, ProductVariant, Stock, Translation};
use App\Services\StockService;
use Edujugon\Laradoo\Exceptions\OdooException;

class RequestService
{
    public $deregister;
    public $stockDataQuant;

    public function deregister()
    {

        $routeName = Route::currentRouteName();
        if ($routeName == 'deregisterFromApp') {
            request()->validate([
                'code' => 'required',
                'deceased' => 'required',
                'file_number' => 'required',
            ]);
            $location = $this->getlocation()->first();
            $stock = (new StockService())->getStockByLotId(request()->input('code'), $location['id']);
            $product_data = [];
            if (!empty($stock)) {
                $price = new Product();
                $product = $price->getById($stock[0]['product_id'][0])->data;
                $variant = (new ProductVariant())->find($stock[0]['product_id'][0]);
                $attributes = (new Attribute())->all();
                $attributes = $attributes->whereIn('id',$variant['product_template_attribute_value_ids'])->values();
                $translations = (new Translation())->productTemplate('name', [$product[0]['id']]);
                $translations = $translations->where('lang', Languages::NL)->groupBy('res_id')->map(function ($translation) {
                    return $translation->pluck('value', 'name');
                });
                $product_template= $stock[0]['product_tmpl_id'];
                $product_template[1] = $translations[$product[0]['id']]['product.template,name'];
                $product_data = [
                    'id' => isset($stock[0]['id']) ? $stock[0]['id'] : '',
                    'product_id' => isset($stock[0]['product_id']) ? $stock[0]['product_id'] : '',
                    'product_tmpl_id' => $product_template ?? '',
                    'location_id' => isset($stock[0]['location_id']) ? $stock[0]['location_id'] : '',
                    'lot_id' => isset($stock[0]['lot_id']) ? $stock[0]['lot_id'] : '',
                    'reserved_quantity' => $stock[0]['reserved_quantity'] > 0 ? $stock[0]['reserved_quantity'] : 0,
                    'available_quantity' => isset($stock[0]['available_quantity']) ? $stock[0]['available_quantity'] : 0,
                    'quantity' => $stock[0]['quantity'] > 0 ? $stock[0]['quantity'] : 0,
                    'selectedLength' => count($attributes) > 0 ? $attributes[0] : '',
                    'selectedWidth' => count($attributes) > 0 ? $attributes[1] : '',
                    'selectedInterior' => count($attributes) > 0 ? $attributes[2] : '',
                ];
                //create the new client here if undertaker_id is empty
                if (!request()->input('undertaker_id') && request()->input('undertaker_name')) {
                    $Client = (new ClientService)->createRequestWithUnderTakerName([
                        'name' => request()->input('undertaker_name'),
                        'user_id' => auth('api')->user()->id
                    ]);
                } else {
                    $Client = Client::where('id', request()->input('undertaker_id'))->first();
                }
                $data = [
                    'user_id' => auth()->user()->id,
                    'undertaker' => $Client->name,
                    'undertaker_id' => $Client->id,
                    'quantity' => 1,
                    'product_id' => $stock[0]['product_id'][0],
                    'product_data' => json_encode($product_data),
                    'deceased' => request()->input('deceased'),
                    'reason' => request()->input('reason'),
                    'lot_id' => request()->input('code'),
                    'file_number' => request()->input('file_number'),
                ];
            }
        } else {
            request()->validate([
                'deceased' => 'required',
                'product' => 'required',
                'quantity' => 'required',
            ]);
            $product = request()->input('product');
            if (empty($product['lot_id'][0])) {
                return response()->json(self::errorMessage('Uniek nummer is vereist'), 400);
            }
            $data = [
                'user_id' => auth()->user()->id,
                'undertaker' => request()->input('undertaker')['name'],
                'undertaker_id' => request()->input('undertaker')['id'],
                'quantity' => 1,
                'product_id' => $product['product_id'][0],
                'product_data' => json_encode($product),
                'deceased' => request()->input('deceased'),
                'reason' => request()->input('reason'),
                'lot_id' => $product['lot_id'][1],
                'file_number' => request()->input('file_number'),
            ];
        }
        $deregister = Deregister::create($data);

        $request = $deregister->request()->create([
            'status' => RequestType::OPEN,
            'requested_by' => auth('api')->user()->id,
        ]);
        return $request;
    }

    public function deregisterDecline()
    {
        $request = VanWijkRequest::findOrFail(request()->input('id'));
        $request->status = RequestType::REFUSED;
        return $request->save();
    }

    public function deregisterAccept()
    {
        // request()->validate([
        //     'custom_message_error' => 'required',
        // ],['custom_message_error.required' => "Doodskist is niet beschikbaar in Odoo-voorraad"]);
        // return request()->all();
        $request = VanWijkRequest::findOrFail(request()->input('id'));
        // return $request;
        $orderContactDetail = User::find($request->requestable->client->user_id);
        // echo "<pre>";
        // dd($orderContactDetail);
        // die();
        $depotAddress = 0;
        $orderContactId = 0;
        if (strtolower($orderContactDetail->odoo_invoice_rules) == 'no' && $orderContactDetail->odoo) {
            if (is_array($orderContactDetail->odoo[0]['parent_id'])) {
                $orderContactId = $orderContactDetail->odoo[0]['parent_id'][0];
            } else {
                $orderContactId = $request->requestable->client->odoo_id;
            }
        } else {
            $orderContactId = $request->requestable->client->odoo_id;
        }

        $deregister_id = $request->requestable->id;
        $this->deregister = Deregister::find($deregister_id);
        // return $request;
        // return (new Tax())->all($request->requestable->product_id);
        // \Log::info("==========================  Request Detail  =============================\n");
        // \Log::info($request);
        // \Log::info("==========================  Request Detail end  =============================\n");
        if (!$request->requestable->client->odoo_id) {
            return response()->json(self::errorMessage('ODOO-contact bestaat niet of is verwijderd'), 422);
        }
        $customerDetail = $this->getCustomer($request->requestable->client->odoo_id);
        $pricelist = 0;
        $property_payment_term_id = 0;
        $route_id = 0;

        if ($customerDetail) {
            try {
                $pricelist = $customerDetail['property_product_pricelist'][0];
                $property_payment_term_id = $customerDetail['property_payment_term_id'][0];
                // if(in_array('x_studio_many2one_field_CmfIR',$customerDetail) && is_array($customerDetail['x_studio_many2one_field_CmfIR']) && count($customerDetail['x_studio_many2one_field_CmfIR']) != 0){
                //     $route_id = $customerDetail['x_studio_many2one_field_CmfIR'][0];
                // }
            } catch (\Throwable $th) {
                // throw $th; // it should be comment when code run successfully
                // \Log::info("================== Error in ODOO Contact detail get");
                // \Log::info($th);
                return response()->json(self::errorMessage('ODOO-contact bestaat niet of is verwijderd'), 422);
            }
            $locationOBJ = new Location();
            $locationGet = $locationOBJ->getByIdCustom($request->requestable->product_data_array['location_id'][0])->first(); // getting location
            if (in_array('partner_id', $locationGet) && is_array($locationGet['partner_id']) && count($locationGet['partner_id']) != 0) {
                $routeContact = $locationGet['partner_id'][0]; // getting route contact id
                $routeContactDetail = $this->getCustomer($routeContact); // getting contact detail for route id

                if (in_array('x_studio_many2one_field_CmfIR', $routeContactDetail) && is_array($routeContactDetail['x_studio_many2one_field_CmfIR']) && count($routeContactDetail['x_studio_many2one_field_CmfIR']) != 0) {
                    $route_id = $routeContactDetail['x_studio_many2one_field_CmfIR'][0];
                    $depotDeliveryContact = (new Partner())->getDeliveryContact($routeContactDetail['id']);
                    $depotAddress = $depotDeliveryContact['id'] ?? $routeContactDetail['id'];
                } else {
                    if (is_array($routeContactDetail['parent_id'])) {
                        $routeContactDetailParent = $this->getCustomer($routeContactDetail['parent_id'][0]);

                        if (in_array('x_studio_many2one_field_CmfIR', $routeContactDetailParent) && is_array($routeContactDetailParent['x_studio_many2one_field_CmfIR']) && count($routeContactDetailParent['x_studio_many2one_field_CmfIR']) != 0) {
                            $route_id = $routeContactDetailParent['x_studio_many2one_field_CmfIR'][0];
                            // $depotAddress = $routeContactDetailParent['id'];
                            $depotDeliveryContact = (new Partner())->getDeliveryContact($routeContactDetailParent['id']);
                            $depotAddress = $depotDeliveryContact['id'] ?? $routeContactDetailParent['id'];
                        }
                    }
                }
            } else {
                return response()->json(self::errorMessage('Odoo-contact is niet gekoppeld aan de Locatie'), 422);
            }

            if ($route_id == 0) {
                return response()->json(self::errorMessage('ODOO-contact heeft geen route'), 422);
            }
            $catId = $request->requestable->product_attributes['categ_id'][0];
            $productTemplateId = $request->requestable->product_attributes['product_tmpl_id'][0];
            $productId = $request->requestable->product_id;

            $productPrice = $request->requestable->product_attributes['_price']; // coffin price

            // /** getting price according to the price rule */
            $productPrice = self::findPriceAccurate($productPrice, $pricelist, $productId, $productTemplateId, $catId);
            if (is_array($productPrice)) return response()->json($productPrice, 422); // if price not found
        } else {
            // \Log::info("================== ODOO contact not found");
            return response()->json(self::errorMessage('ODOO-contact bestaat niet of is verwijderd'), 422);
        }
        \Log::debug("===> route_id " . $route_id);
        $this->stockDataQuant =  self::quantStock($request->requestable->product_id, $request->requestable->product_data_array['location_id'][0], $request->requestable->product_data_array['lot_id'][0]);
        if (!$this->stockDataQuant) return response()->json(self::errorMessage('Voorraad niet gevonden.'), 422);
        \Log::debug("<< ================== ODOO stock quantity check ============== >>");
        \Log::debug($this->stockDataQuant);
        \Log::debug(">> ================== ODOO stock quantity check ============== <<");

        $orderData = $this->makeJson(
            $pricelist, // ODOO contact detail
            $property_payment_term_id, // ODOO payment term id
            $orderContactId, //$request->requestable->client->odoo_id, // partner id
            $request->requestable->product_id, //product id
            $route_id, //$request->requestable->product_data_array['location_id'][0], //route id
            $depotAddress, //delivery address
            $request->requestable->product_data_array['product_id'][0], // product variant id
            $request->requestable->product_data_array['product_id'][1], // product variant name
            $request->requestable->deceased, // deceased_name
            $request->requestable->file_number, // case_number
            $request->requestable->product_attributes['product_tmpl_id'][0], // product package id
            $request->requestable->product_attributes['taxes_id'][0], // product tax id
            $productPrice, //$request->requestable->product_attributes['price'], // product price
            $request->requestable->product_data_array['product_tmpl_id'][0] // product template id
        );

        // return $orderData;
        $request->status = RequestType::CLOSED;

        // Push deregister as an order into Odoo
        // $getOdooOrderData = (new Order())->createSaleOrder($orderData);
        // \Log::info("================= sale.order ====================\n");
        // \Log::info("order  ==> ".$getOdooOrderData);
        // \Log::info("=====================================\n");
        $orderLineData = $orderData['order_line'][2];
        unset($orderData['order_line']);
        $this->deregister->update(['status' => 'pending']);
        if ($this->deregister->order_id == '') {
            $new_order = (new \App\Models\Odoo\Order($orderData))->create();
            $this->deregister->update(['order_id' => $new_order]);
        }
        $order_id = (int) $this->deregister->order_id;
        // $new_order = (new \App\Models\Odoo\Order([
        //     'partner_id' => $request->user->odoo_user_id,
        //     'payment_term_id' => 3, // @TODO: To connect to the right payment term
        // ]) )->create();
        if ($this->deregister->order_line_id == '') {
            $orderLineData['order_id'] = $order_id;
            // \Log::info('=== ORDER CREATED '.$order_id);
            // @TODO How to connect the order to the right 'stock' / 'lot' ?
            $order_line = (new \App\Models\Odoo\OrderLine($orderLineData))->create();
            (new Order())->saleOrderConfirm($order_id);
            $this->deregister->update(['order_line_id' => $order_line]);

            // $__qty = $this->stockDataQuant->first()['reserved_quantity'];
            // $__qty++;
            // \Log::debug("<< ================== ODOO stock quantity check update ============== >>");
            // \Log::debug($this->stockDataQuant);
            // \Log::debug($__qty);
            // $qty_update_check = (new \App\Models\Odoo\Order)->connectClient()->where('id','=',$this->stockDataQuant->first()['id'])->update('stock.quant',[
            //     'reserved_quantity' => $__qty
            // ]);
            // \Log::debug($qty_update_check);
            // \Log::debug(">> ================== ODOO stock quantity check ============== <<");
        }
        // \Log::info('=== ORDER LINE CREATED '.$this->deregister->order_line_id);
        $pickingLines = $this->deliveryLines($order_id, ($request->requestable->product_data_array['lot_id'][0] ?? ''));
        // \Log::info('=== PIKING LINE CREATED ');
        // \Log::info($pickingLines);
        if ($pickingLines['status'] == 1) {
            (new Order())->manufacturingOrder($order_id);
            $this->deregister->update(['status' => 'done']);
            return $request->save();
        } else {
            return response()->json($pickingLines, 422);
        }
    }

    public function deregisterRequests()
    {
        return VanWijkRequest::where('requestable_type', 'App\Models\Deregister')->whereIn('status', [RequestType::OPEN])->orderBy('created_at', 'DESC')->get();
    }

    public function return()
    {
        $depotReturndata = [];
        $product_data = '';
        $lot_id = '';
        $routeName = Route::currentRouteName();
        if ($routeName == 'returnCompleteFromApp') {
            request()->validate([
                'code' => 'required',
                'reason' => 'required',
                'file.*' => 'mimes:jpg,jpeg,png|max:1000000',
            ]);
            $stock = (new StockService())->getStockByLotIdOnly(request()->input('code'));
            $lotCounts = count($stock);
            if ($lotCounts === 0) {
                return response()->json(['error' => 'Couldn\'t find this id'], 400);
            }
            $record = $stock[$lotCounts - 1];
            if (empty($record['location_id'])) {
                return response()->json(['error' => 'No location found!'], 400);
            }
            $locationId = $record['location_id'][0];
            $user_locations = auth()->user()->locations->pluck('odoo_location_id')->toArray();
            if (in_array($locationId, $user_locations)) {
                // $price = new Product();
                // $product_data = $price->getById($record['product_id'][0])->data;
                $product_data = [];
                $attributes_variants = (new ProductService())->attributesOfOptimized($record['product_id'][0]);
                $product_data = [
                    'id' => isset($record['id']) ? $record['id'] : '',
                    'product_id' => isset($record['product_id']) ? $record['product_id'] : '',
                    'product_tmpl_id' => isset($record['product_tmpl_id']) ? $record['product_tmpl_id'] : '',
                    'location_id' => isset($record['location_id']) ? $record['location_id'] : '',
                    'lot_id' => isset($record['lot_id']) ? $record['lot_id'] : '',
                    'reserved_quantity' => $record['reserved_quantity'] > 0 ? $record['reserved_quantity'] : 0,
                    'available_quantity' => isset($record['available_quantity']) ? $record['available_quantity'] : 0,
                    'quantity' => $record['quantity'] > 0 ? $record['quantity'] : 0,
                    'selectedLength' => isset($attributes_variants['length']) ? $attributes_variants['length'] : '',
                    'selectedWidth' => isset($attributes_variants['width']) ? $attributes_variants['width'] : '',
                    'selectedInterior' => isset($attributes_variants['interior']) ? $attributes_variants['interior'] : '',
                ];
                $lot_id = $record['lot_id'][1];
                $depotReturndata = [
                    'product_id' => $stock[0]['product_id'][0],
                    'product_data' => json_encode($product_data),
                    'reason' => request()->input('reason'),
                    'lot_id' => $lot_id,
                ];
            }
        } else {
            request()->validate([
                'product_id' => 'required',
                'reason' => 'required',
                'file.*' => 'mimes:jpg,jpeg,png|max:1000000',
            ]);

            if (request()->input('product_data')) {
                $product_data = request()->input('product_data');
                $lot_id = json_decode($product_data, true)['lot_id'][1];
            }
            $depotReturndata = [
                'product_id' => request()->input('product_id'),
                'product_data' => $product_data,
                'reason' => request()->input('reason'),
                'lot_id' => $lot_id,
            ];
        }
        $return = DepotReturn::create($depotReturndata);

        if (request()->hasfile('file')) {
            foreach (request()->file('file') as $file) {
                $return
                    ->addMedia($file)
                    ->toMediaCollection();
            }
        }

        if ($return) {
            $reqReturn = $return->request()->create([
                'status' => RequestType::OPEN,
                "requested_by" => auth('api')->user()->id,
            ]);

            if ($reqReturn) {
                $data = ['success' =>  'Return request has been created successfully'];
                return response()->json($data);
            }
        }
        $error = ['error' =>  'Return request failed'];
        return response()->json($error);
    }

    public function returnRequests()
    {
        $requests =  VanWijkRequest::where('requestable_type', 'App\Models\DepotReturn')->whereIn('status', [RequestType::OPEN])->orderBy('created_at', 'DESC')->get();
        $locations = (new Location)->all();
        $requests->transform(function ($req) use ($locations) {
            $locationIds = $req->user->locations->pluck('odoo_location_id')->toArray();
            $req->user->locs = $locations->whereIn('id', $locationIds)->values();
            return $req;
        });
        return $requests;
    }

    public function DepotExtension()
    {
        request()->validate([
            'product_id' => 'required',
            'reason' => 'required',
            'quantity' => 'required',
        ]);

        $extension = Extension::create([
            "product_id" => request()->input('product_id'),
            "reason" => request()->input('reason'),
            "quantity" => request()->input('quantity')
        ]);

        if ($extension) {
            $reqExtension = $extension->request()->create([
                'status' => RequestType::OPEN,
                "requested_by" => auth('api')->user()->id,
                "requestable_id" => $extension->id,
                "requestable_type" => 'depot_exention'
            ]);

            if ($reqExtension) {
                $data = ['success' =>  'Depot Extension Request has been created successfully'];
                return response()->json($data);
            }
        }
        $error = ['error' =>  'Depot Extension Request failed'];
        return response()->json($error);
    }

    public function getDepotExtension()
    {
        $extension = VanWijkRequest::where('requestable_type', 'App\Models\Extension')->where('status', RequestType::OPEN)->orderBy('created_at', 'DESC')->get();
        $locations = (new Location)->all();
        $extension->transform(function ($ex) use ($locations) {
            $locationIds = $ex->user->locations->pluck('odoo_location_id')->toArray();
            $ex->user->locs = $locations->whereIn('id', $locationIds)->values();
            return $ex;
        });
        $data = ['success' =>  'Depost Extension Request has been fetch successfully', 'data' => $extension];
        return response()->json($data);
    }

    public function confirmOrReject()
    {
        request()->validate([
            'id' => 'required',
            'accepted' => 'required',
        ]);
        if (request()->input('accepted')) {
            VanWijkRequest::where('id', request()->input('id'))->update(['status' => RequestType::CLOSED]);
        } else {
            VanWijkRequest::where('id', request()->input('id'))->update(['status' => RequestType::REFUSED]);
        }
        $data = ['success' =>  'Request has been process successfully'];
        return response()->json($data);
    }

    /**
     * Deprecated
     */
    public function getHistory()
    {
        return $this->deregisterHistory();

        $extension = DepotRequest::get();
        foreach ($extension as $req) {
            $req->requestable->product = (new ProductService())->getProductVariant([$req->requestable->product_id]);
            $product_maat = (new ProductService())->attributePerVariant([$req->requestable->product_id]);
            if (count($product_maat) > 0) {
                $req->requestable->product_maat = $product_maat[$req->requestable->product_id]['size'];
            } else {
                $req->requestable->product_maat = 'N/A';
            }
        }
        $data = ['success' =>  'Depost Extension Request history has been fetch successfully', 'data' => $extension];
        return response()->json($data);
    }

    public function deregisterHistory()
    {
        if (auth('api')->user()->isAdmin()) {
            return VanWijkRequest::where('requestable_type', 'App\Models\Deregister')->orderBy('created_at', 'DESC')->get();
        }
        $ids = User::where('odoo_organisation_type_id', auth('api')->user()->odoo_organisation_type_id)->pluck('id')->toArray();
        return VanWijkRequest::where('requestable_type', 'App\Models\Deregister')
            ->orderBy('created_at', 'DESC')
            ->get();
    }
    public function makeJson(
        $pricelist,
        $property_payment_term_id,
        $partner_id,
        $product_id,
        $route_id,
        $depotAddress,
        $product_variant_id,
        $product_variant_name,
        $deceased_name,
        $case_number,
        $package_id,
        $taxes_id,
        $price,
        $product_template_id
    ) {
        $now = Carbon::now();
        $oneMonthLater = $now->addMonth();
        // $customerDetail = $this->getCustomer($partner_id);

        // \Log::info("==========================  Customer Detail  =============================\n");
        // \Log::info($customerDetail);
        // \Log::info("==========================  Customer Detail end  =============================\n");
        // dd($customerDetail);
        // $pricelist = 0;
        // $property_payment_term_id = 0;
        // if($customerDetail){
        //     try {
        //         $pricelist = $customerDetail['property_product_pricelist'][0];
        //         $property_payment_term_id = $customerDetail['property_payment_term_id'][0];
        //     } catch (\Throwable $th) {
        //         throw $th; // it should be comment when code run successfully
        //     }
        // }
        // $tax = (new Tax())->productTax($product_id)->toArray();
        // \Log::info($tax);
        return [
            "partner_id" => $partner_id,
            "partner_invoice_id" => $partner_id,
            "partner_shipping_id" => $depotAddress,
            // "quotation_template" => "empty",
            "validity_date" => $oneMonthLater->format("Y-m-d"),
            "pricelist_id" => $pricelist,
            "payment_term_id" => $property_payment_term_id,
            "order_line" => [
                0, 0, [
                    "route_id" => $route_id,
                    "product_id" => $product_id,
                    "custom_description" => $product_variant_name, //" + all of the variants as text", ** need to check with ken is he asking for [ptav_product_variant_ids] variants it is under the request table data get-> product_attribute_get -> width -> ptav_product_variant_ids
                    "product_uom_qty" => 1,
                    "price_unit" => $price,
                    "deceased_name" => $deceased_name,
                    "case_number" => $case_number,
                    // "packaging_type_id" => $package_id, // Folie
                    // "product_tmpl_id" => $package_id,
                    "product_template_id" => $package_id,
                    "price_subtotal" => $price * 1,
                    // "tax_id" => [$taxes_id,0,$tax],
                ]
            ]
        ];
        /** this params are under the order_line */
        // "product_variant_id"=> $product_variant_id,
        // "Product modal" => "", // need to check this later
        // "Ondernemer"=> "If the Depot that made the request has Factuurregels => Nee, we fill this in with the Odoo contact of the Depot that made the request itself",
        // "product_template_id" => $product_template_id,
        // "product_uom" => "The uom_id field of the product.product object for the specific variant chosen for the Deregister"
    }
    public function getCustomer($id)
    {
        return $partners = (new Partner())->singleDataWithAllFields($id);
        // return $partner = $partners->where('id', $id)->first() ?? [];
        // return Partner::find($id);
    }
    public function deliveryLines($order_id, $lot_id = '')
    {
        // $order_id = 14213;
        try {
            //code...
            // \Log::info(" ============== Order get ==========");
            $oder = new Order();
            $orderData = $oder->getById($order_id); // get order
            // echo "<pre>";
            $orderGet = $orderData->data->first();
            // \Log::info(" ============== Order Line get ==========");
            // print_r($orderGet);
            // die();
            $oder_line = new OrderLine();
            $oder_line_data = $oder_line->getById($orderGet['order_line'][0]); // get order lines
            // \Log::info(" ============== Order Line get ==========");
            // \Log::info($oder_line_data);
            $oder_line_data_get = $oder_line_data->data->first();

            // echo "<pre>";
            // \Log::info(" ============== pickingIds ==========");
            $pickingIds = $oder->connectClient()->where('sale_id', $order_id)->get('stock.picking')->toArray();
            // \Log::info($pickingIds);
            if ((count($pickingIds) > 0) && strtolower($pickingIds[0]['products_availability']) != 'available') {
                // \Log::info("================ Product not available =================");
                return self::errorMessage('Product is niet beschikbaar');
            }
            // die();
            // \Log::info("=============================================");
            $lines = [];

            /** this code is for stop enter new line in side the delivery  */
            // $oder = new App\Models\Odoo\PickingLine();
            // // return $orderData = $oder->update(357768,["qty_done" => 1]);
            // return $orderData = $oder->getByPickingId(24894);
            /** */

            foreach ($pickingIds as $moveLine) {
                $lines = [
                    'product_uom_qty' => 1,  // Adjust the quantity
                    'product_id' => $moveLine['product_id'][0],
                    'picking_id' => $moveLine['id'],
                    // 'name' => 'New Stock Move',
                    'qty_done' => 1,
                    'product_uom_id' => $oder_line_data_get['product_uom'][0],
                    // 'product_uom' => $oder_line_data_get['product_uom'][0],
                    'location_id' => $moveLine['location_id'][0],
                    'location_dest_id' => $moveLine['location_dest_id'][0],
                    'lot_id' => $lot_id //21655 // lot id
                    // Other fields you want to update
                ];
            }
            $updatePickingLine = ['state' => 'done'];
            if ($this->deregister->picking_line_id == '') {
                // print_r($lines);
                // echo "======================== <br>";
                // echo " Request hit for stock ==> ";
                // \Log::info('============== Picking Lines ============');
                // \Log::info($lines);
                if (count($pickingIds[0]['move_line_ids']) == 0) {
                    $pickingLine = new PickingLine($lines);
                    $lineCreated = $pickingLine->create();
                } else {
                    $lineCreated = $pickingIds[0]['move_line_ids'][0];
                    $updatePickingLine['qty_done'] = 1;
                }
                // \Log::info('============== Picking Lines created ============');
                // \Log::info($lineCreated);
                // \Log::info('============== Picking Lines created end ============');
                $this->deregister->update(['picking_line_id' => $lineCreated]);
            }

            // print_r($lineCreated);
            $orderData = (new PickingLine)->update($this->deregister->picking_line_id, $updatePickingLine);
            // \Log::info("============= picking lines id => ".$orderData);

            // \Log::info("============= Delivery for last step of order ============ ");

            $leverageConfirm = $oder->connectClient()
                ->where('picking_id', '=', $lines['picking_id']) // Replace 'SO' with the correct prefix for sale orders
                // ->fields(['id', 'location_dest_id'])
                ->update('stock.move', ['state' => 'done']);

            try {
                // \Log::info("=============== replinsh Product here ============");
                $oder->connectClient()->call('stock.picking', 'replenish_products', [$lines['picking_id']]);
            } catch (OdooException $e) {
                //throw $th;
                // \Log::info("=============== replinsh Product error start here ============");
                // \Log::info($e->getMessage());
                // \Log::info("=============== replinsh Product error end here ============");
            }
            // \Log::debug("<< ================== ODOO stock quantity check update reserved_quantity ============== >>");
            // \Log::debug($this->stockDataQuant);
            $__qty = $this->stockDataQuant->first()['reserved_quantity'];
            $__quantity = $this->stockDataQuant->first()['quantity'];
            $__qty--;
            $__quantity--;
            // \Log::debug($__qty);
            $check_inventory_update = (new \App\Models\Odoo\Order)->connectClient()->where('id', '=', $this->stockDataQuant->first()['id'])->update('stock.quant', [
                'quantity' => $__quantity
            ]);
            // \Log::debug($check_inventory_update);
            // \Log::debug(">> ================== ODOO stock quantity check reserved_quantity ============== <<");
            // \Log::info("============= Delivery for last step of order update status =====> ".$leverageConfirm);

            // $newStockMove = [
            //     'name' => 'Stock Transfer', // Name for the stock transfer
            //     'product_id' => $lines['product_id'], // Product ID
            //     'product_uom_qty' => 1, // Quantity to transfer
            //     "product_uom" => $lines['product_uom_id'],
            //     'location_id' => $lines['location_id'], // Source Location ID
            //     'location_dest_id' => $lines['location_dest_id'], // Destination Location ID
            //     // Add any other required fields for the stock move
            // ];

            // // Create the new stock move
            // \Log::info('================== stock.move =========================');
            // $createdStockMoveID = $oder->connectClient()->create('stock.move', $newStockMove);
            // \Log::info($createdStockMoveID);
            // \Log::info('=======================================================');

            // // Update the state of the stock move to "done" to confirm the transfer
            // $updateStatus = $oder->connectClient()->where('id', '=', $createdStockMoveID)
            //      ->update('stock.move',['state' => 'done']);
            // \Log::info('================== stock.move update =========================');
            // \Log::info($updateStatus);
            // \Log::info('=======================================================');

            return ["status" => 1, "data" => $orderData];
        } catch (\Throwable $th) {
            //throw $th;
            // \Log::info("================ error while creating delivery =================");
            // \Log::info($th);
            return self::errorMessage('Doodskist is niet beschikbaar in Odoo-voorraad');
        }
    }
    public function replinshProduct($id)
    {
        try {

            // \Log::info("=============== replinsh Product ============");
            $result =  (new Order)->connectClient()->call('stock.picking', 'replenish_products', [$id]);
            // \Log::info($result);
            return $result;
        } catch (OdooException $e) {
            // \Log::info($e->getMessage());
            // \Log::info("=============== replinsh Product error here ============");
            // throw $e;
        }
    }
    public function errorMessage($message)
    {
        return ["status" => 0, "data" => '', "error" => ['bag' => ['Coffin' => [$message]]]];
    }
    public function findPriceAccurate($productPrice, $pricelist, $productId, $productTemplateId, $catId, $callbackOnce = 0)
    {
        // \Log::info('============= findprice data get ============');
        // \Log::info([$productPrice,$pricelist,$productId,$productTemplateId,$catId,$callbackOnce]);
        // \Log::info("====================================================================");
        // $productPrice = $request->requestable->product_attributes['_price']; // coffin price
        /** getting price according to the price rule */
        $priceListItem = new PricelistItem();
        /** get data according to category id */
        $priceForParticularContact = $priceListItem->byProductCategoryIdCustom($pricelist, $catId); // get data according to $priceList and all product
        /** get data according to product id */
        $priceForParticularContact = (count($priceForParticularContact) == 0) ? $priceListItem->byProductIdCustom($productTemplateId, $pricelist) : $priceForParticularContact; // get data according to $product_id, $priceList
        /** get data according to product variant id */
        $priceForParticularContact = (count($priceForParticularContact) == 0) ? $priceListItem->byProductVariantIdCustom($productId, $pricelist) : $priceForParticularContact; // get data according to $product_variant_id, $priceList
        /** get data according all product */
        $priceForParticularContact = (count($priceForParticularContact) == 0) ? $priceListItem->byAllProductCustom($pricelist) : $priceForParticularContact; // get data according to $priceList and all product
        // \Log::info("=== in the conditions ====");
        if (count($priceForParticularContact) > 0) {
            // return response()->json(self::errorMessage('prijs niet gevonden'),422);
            $pricelistRuleGet = $priceForParticularContact->first();
            // \Log::info("===== >>>  ".$pricelistRuleGet['compute_price']);
            if ($pricelistRuleGet['compute_price'] == 'fixed') {
                $productPrice = $priceForParticularContact->first()['fixed_price'];
            } elseif ($pricelistRuleGet['compute_price'] == 'formula') {
                // [coffin_price] * (1-(discount_value/100)) + [extra_fee_field]
                // \Log::info('==== in formula =====');
                $extraFee = $pricelistRuleGet['price_surcharge'];
                $discountValue = $pricelistRuleGet['price_discount'];
                $price_round = $pricelistRuleGet['price_round'];
                // \Log::info(['extraFee' => $extraFee,'price_round' => $price_round,'discountValue' => $discountValue]);
                // if($pricelistRuleGet['base'] == 'list_price'){
                //     $productPrice = $productPrice * (1 - ($discountValue / 100)) + $extraFee;
                // }
                // elseif($pricelistRuleGet['base'] == 'standard_price'){
                //     $productPrice = $productPrice * (1 - ($discountValue / 100)) + $extraFee;
                // }
                if ($pricelistRuleGet['base'] == 'pricelist') {
                    if ($callbackOnce == 2) return self::errorMessage('Prijs niet gevonden');
                    $callbackOnce++;
                    if (count($pricelistRuleGet['base_pricelist_id']) > 0) {
                        /** this condition is for find nested price according to the list */
                        $productPrice = self::findPriceAccurate($productPrice, $pricelistRuleGet['base_pricelist_id'][0], $productId, $productTemplateId, $catId, $callbackOnce);
                        if (is_array($productPrice)) return $productPrice;
                    }
                }
                $productPrice = ($productPrice * (1 - ($discountValue / 100))) + $extraFee;
            } else {
                $percentage = $pricelistRuleGet['percent_price'];
                $productPrice = ($percentage > 0) ? $productPrice - (($productPrice * $percentage) / 100) : $productPrice;
            }
            $productPrice = ($productPrice < 0) ? 0 : $productPrice;
            // \Log::info("got price after condition ===> ".$productPrice);
        }
        if (count($priceForParticularContact) == 0 && $callbackOnce == 2) return self::errorMessage('Prijs niet gevonden'); // callback again and agin stop condition

        return $productPrice;
    }
    public function quantStock($product_id, $loc_id, $lot_id)
    {
        // return [$product_id,$loc_id,$lot_id];
        return $stockQuant = (new \App\Models\Odoo\Order)
            ->connectClient()
            ->fields(['id', 'reserved_quantity', 'quantity'])
            ->where('product_id', '=', $product_id)
            ->where('location_id', '=', $loc_id)
            ->where('lot_id', '=', $lot_id)
            ->get('stock.quant');
    }

    public function getlocation()
    {

        $locations = (new StockService())->locations();

        if (auth()->user()->isAdmin()) {
            return $locations;
        }
        if (!auth()->user()->locations) {
            return [];
        }

        $user_locations = auth()->user()->locations->pluck('odoo_location_id')->toArray();

        $locations = $locations->filter(function ($location) use ($user_locations) {
            return in_array($location['id'], $user_locations);
        });
        return $locations;
    }
}
