<?php

namespace App\Models\Odoo;
use Edujugon\Laradoo\Exceptions\OdooException;

class Order extends Base
{
    public $resource = 'sale.order';
    public $fields = [
        'id',
        "name",
        'activity_state',
        'date_order',
        'user_id',
        'partner_id',
        'partner_invoice_id', // invoice address
        'partner_shipping_id', // delivery address
        'pricelist_id',
        'payment_term_id',
        'order_line',
        'amount_untaxed',
        'amount_tax',
        'amount_total',
        'company_id', //customer
        'warehouse_id',
        'is_urgent_delivery',
        'validity_date'
    ];

    public function __construct($data=null)
    {
        $this->data = $data;
    }

    public function all(){
        return $this->cache('locations', function(){
            return $this->connect()->where('usage', 'internal')->fields($this->fields)->get($this->resource);
        });
    }

    public function getById($id, $clearCache = false){
        $this->data = $this->connect()->where('id','=',$id)->fields($this->fields)->get($this->resource);
        return $this;
    }

    public function getAll(){
        $this->data = $this->connect()
        // ->fields($this->fields)
        // ->where("id","=",14131)
        // ->where("id","=",14147)
        // ->where("id","=",14157)
        ->where("id","=",14168)
        // ->where("id","=",108760)
        // ->limit(10)type_name
        ->get($this->resource);
        // ->get("product.template");
        return $this;
    }
    public function createSaleOrder($data){
        try {
            // \Log::info($data);
            $resultQuo = $this->connect()->create($this->resource,$data);
            // \Log::info("order confirm ==> ".$resultQuo);
            return $this->connect()->call($this->resource,'action_confirm',[$resultQuo]);
        } catch (OdooException $e) {
            // \Log::info($e->getMessage());
            throw $e;
        }
    }
    public function saleOrderConfirm($product_id){
        try {
            return $this->connect()->call($this->resource,'action_confirm',[$product_id]);
        } catch (OdooException $e) {
            // \Log::info($e->getMessage());
            throw $e;
        }
    }
    public function manufacturingOrder($product_id){
        try {
            // return $this->connect()->where('id','=',$product_id)->get('mrp.production');
            return $this->connect()->where('id','=',$product_id)->get('stock.picking');
            // return $this->connect()->call('stock.picking','replenish_products',[$product_id]);
        } catch (OdooException $e) {
            // \Log::info($e->getMessage());
            throw $e;
        }
    }
    public function createOrder(){
        $data = [
            'partner_id' => 109193,
            'validity_date' => '2023-10-12',
            'partner_invoice_id' => 109193,
            'partner_shipping_id' => 109193,
            // 'date_order' =>  '2023-08-01 16:00:00',
            'date_order' =>  date('Y-m-d H:i:s'),
            'payment_term_id'=> 3,
            'pricelist_id' => 125,
            'order_line' => [
                [0, 0, [
                    'route_id' => 47,
                    'product_id' => 9060, // Replace with the ID of the product you want to add to the order
                    'custom_description' => 'nirmaljeet singh testing for RND',
                    'product_uom_qty' => 2, // Quantity of the product
                    'price_unit' => 7700, // Unit price of the product
                ]],
            ],
            ];
            try {
                //code...
                echo "<pre>";
                $resultQuo = $this->connect()->create($this->resource,[$data]);
                print_r($resultQuo);
                $result = $this->connect()->call($this->resource,'action_confirm',[$resultQuo]);
                print_r($result);
                die();
            } catch (OdooException $e) {
                throw $e;
                // f (is_string($ids))
                // throw new OdooException($th);
            }
    }
    public function Address(){
        // return $this->connect()->get('delivery.carrier');
        return $this->connect()
        // ->where('model', 'delivery.carrier')
        ->fields(['id', 'name', 'product_id'])
        ->get('delivery.carrier');
    }
    public function addressCustomer(){
        return $this->connect()
        // ->where('model', 'delivery.carrier')
        // ->where('parent_id', 63643)
        ->where('id', 64561)
        // ->fields(['id', 'name','display_name','x_studio_many2one_field_owPlU'])
        // ->where('type','delivery')
        ->get('res.partner');
        // ->get('delivery.carrier');
        
    }
    public function createDelivery($saleOrderId = 14168){
        $deliveryRequestData = [
            'origin' => 'V42479', // Reference to the refill order (name of order)
            'move_type' => 'direct', // Set the move type (e.g., direct, one, etc.)
            'picking_type_id' => 1, // ID of the picking type (e.g., delivery order, receipt, etc.)
            'location_id' => 48, // get from order [picking_ids]
            'location_dest_id' => 5, // this get from getLocation but that order is not print the value
            // Add other relevant data as needed for the specific picking type
        ];
        // $deliveryRequestData = [
        //     'origin' => 'SO' . $saleOrderId, // Reference to the sale order
        //     'move_type' => 'direct', // Set the move type (e.g., direct, one, etc.)
        //     'picking_type_id' => 1, // ID of the picking type (e.g., delivery order, receipt, etc.)
        //     // Add other relevant data as needed for the specific picking type
        // ];
        return $this->connect()->create('stock.picking', $deliveryRequestData);
        // return $this->connect()->call('stock.picking','action_assign', $deliveryRequestData);
    }
    public function getLocation(){
        return $this->connect()
                    ->where('origin', 'like', 'V42479') // Replace 'SO' with the correct prefix for sale orders
                    // ->fields(['id', 'location_dest_id'])
                    ->get('stock.picking');
    }
    public function checkPickingOrder(){
        return $this->connect()
                    ->where('id', 24794) // Replace 'SO' with the correct prefix for sale orders
                    // ->fields(['id', 'location_dest_id'])
                    ->search('stock.picking');
    }
    public function getCustomerAddress()
    {
        // $odoo = $this->connectToOdoo();

        // Search for the customer's address using the customer ID
        return $customerAddress = $this->connect()
                            // ->where('res.partner')
                                ->where('id', 64415)
                                // ->where('id', 60921)
                                // ->fields(['id', 'street', 'city', 'zip', 'state_id', 'country_id']) // Customize fields as needed
                                ->get('res.partner');

        return $customerAddress->first();
    }
    public function finalAction($saleOrderId){
        // $saleOrderId = 14205;

        // return $this->connect()->call('mrp.production', 'replenish_products', [$saleOrderId]);

        $newManufacturingOrder = [
            'product_id' => 7244, // Product ID
            'product_qty' => 1, // Quantity to produce
            'product_uom_id' => 1,
            'order_line_id' => 25643,
            'deceased_name' => 'no',
            'case_number' => 'cn',
            'origin' => 14244,
            'bom_id' => 4024, // Bill of Materials ID (replace with actual BoM ID)
            // Add any other required fields for the manufacturing order
        ];
    
        // Create the new manufacturing order
        $createdManufacturingOrderID = $this->connect()->create('mrp.production', $newManufacturingOrder);
        // \Log::info("<<< production >>> ==================> ".$createdManufacturingOrderID);
        // return $this->connect()->where('id','=',$newManufacturingOrder)->update('mrp.production',['state' => 'done']);

        // return $this->connect()->call('mrp.production','action_confirm',[29066]);
        return $this->connect()->call('mrp.production','action_confirm',[$createdManufacturingOrderID]);
        // Call the action_replenish method on the sale.order object
        // return $this->connect()->call('sale.order', 'action_replenish', [$saleOrderId]);
    }
    public function confirm($picking_id){
        // manufacturing update
        $move_lines_id = 349764;
        $picking_id = 24836;
        $manufacturing_id = 29070;
        return $this->connect()->call('stock.picking', 'replenish_products', [24838]);
        return $this->connect()->where('id', '=', $manufacturing_id)
        ->update('mrp.production',['move_raw_ids' => $picking_id]);
        // return $picking = $this->connect()->where('id','=',$picking_id)->get('stock.picking');
        // return $picking = $this->connect()->where('id','=',$picking_id)->update('stock.picking',['manufacturing_order_ids' => 29069]);
        $production = $this->connect()->where('id','=',$product_id)->get('mrp.production');
        $production_id = 29066;
        $this->connect()->call('mrp.production','action_confirm',[$production_id]);
    }
    public function connectClient(){
        return $this->connect();
    }
}
