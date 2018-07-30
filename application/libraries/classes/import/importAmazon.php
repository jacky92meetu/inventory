<?php

include_once 'importClass.php';

class importAmazonClass extends importClass{
    
    function __construct() {
        parent::__construct();
    }
    
    function sales_import($file){
        $return = array("status"=>"0","message"=>"");
        
        $cols_list = ["default" => array('buyer_name'=>'recipient-name','buyer_contact'=>'buyer-phone-number','buyer_email'=>'buyer-email','buyer_addr1'=>'ship-address-1','buyer_addr2'=>'ship-address-2','buyer_addr3'=>'ship-address-3','buyer_city'=>'ship-city','buyer_state'=>'ship-state','buyer_postcode'=>'ship-postal-code','buyer_country'=>'ship-country','quantity'=>'quantity-purchased','item_id'=>'order-item-id','sales_id'=>'order-id','selling_price'=>'item-price','shipping_charges_paid'=>'shipping-price','paid_date'=>'payments-date','buyer_reference'=>'delivery-Instructions','item_sku'=>'sku','amazon-order-id'=>'amazon-order-id','amazon-order-item-id'=>'amazon-order-item-id','tracking_number'=>'tracking-number','shipment_date'=>'shipment-date','courier_id'=>'carrier','ship-promotion-discount'=>'ship-promotion-discount','currency'=>'currency','item_name'=>'product-name','quantity-shipped'=>'quantity-shipped')
        ,"cols_20180730" => array('buyer_name'=>'Buyer Name','buyer_contact'=>'Buyer Phone Number','buyer_email'=>'Buyer Email','buyer_addr1'=>'Shipping Address 1','buyer_addr2'=>'Shipping Address 2','buyer_addr3'=>'Shipping Address 3','buyer_city'=>'Shipping City','buyer_state'=>'Shipping State','buyer_postcode'=>'Shipping Postal Code','buyer_country'=>'Shipping Country Code','quantity'=>'quantity-purchased','item_id'=>'Merchant Order Item Id','sales_id'=>'Merchant Order Id','selling_price'=>'Item Price','shipping_charges_paid'=>'Shipping Price','paid_date'=>'Payments Date','buyer_reference'=>'delivery-Instructions','item_sku'=>'Merchant SKU','amazon-order-id'=>'Amazon Order Id','amazon-order-item-id'=>'Amazon Order Item Id','tracking_number'=>'Tracking Number','shipment_date'=>'Shipment Date','courier_id'=>'Carrier','ship-promotion-discount'=>'Shipment Promo Discount','currency'=>'Currency','item_name'=>'Title','quantity-shipped'=>'Shipped Quantity')];
        
        foreach($cols_list as $cols){
            $temp_records = $this->excel_read($file, $cols);
            if(empty($this->excel_cols) || (sizeof($cols) - sizeof($this->excel_cols))>5){
                continue;
            }
            $temp_list = array();
            $missing = array();
            
            $courier_sys_id = "3";
            if(($result = $this->CI->db->query('SELECT id FROM couriers WHERE name="FBA"')) && ($row = $result->row_array())){
                $courier_sys_id = $row['id'];
            }
            
            for($row_count=1; $row_count<sizeof($temp_records); $row_count++){
                if($row_count==0 || trim(implode("",$this->excel_get($row_count)))==""){
                    continue;
                }
                $cur_siteid = strtoupper($this->excel_get($row_count, 'currency'));
                $is_fba = 0;
                $selling_price = preg_replace('#[^0-9\.]#iu', '', $this->excel_get($row_count, 'selling_price'));
                $shipping_charges = 0;
                $tracking_number = $this->excel_get($row_count, 'tracking_number');
                $courier_id = $this->excel_get($row_count, 'courier_id');
                $sales_id = $this->excel_get($row_count, 'sales_id');
                $temp_sku = $this->excel_get($row_count,'item_sku');
                $quantity = $this->excel_get($row_count, 'quantity');
                if($this->excel_get($row_count, 'amazon-order-id')!=""){
                    $is_fba = 1;
                    //$tracking_number = $this->excel_get($row_count, 'courier_id')." ".$this->excel_get($row_count, 'tracking_number');
                    $courier_id = ((strlen($courier_id)!="")?$courier_id:$courier_sys_id);
                    $sales_id = $this->excel_get($row_count, 'amazon-order-id');
                    $temp_sku = str_ireplace('.fba', '', $this->excel_get($row_count,'item_sku'));
                    $quantity = $this->excel_get($row_count, 'quantity-shipped');
                    /*
                    $shipping_charges_discount = preg_replace('#[^0-9\.]#iu', '', $this->excel_get($row_count, 'ship-promotion-discount'));
                    if(is_numeric($shipping_charges_discount) && $shipping_charges_discount>0){
                        $shipping_charges = $shipping_charges - $shipping_charges_discount;
                    }
                    */
                }
                
                if(($row = $this->search_store_item($this->account_id, "amazon_".$cur_siteid, $is_fba, $cur_siteid, $this->excel_get($row_count, 'item_sku')))){
                    $temp3 = array('account_id'=>$row['account_id'],'store_item_id'=>$row['store_item_id'],'buyer_reference'=>$this->excel_get($row_count, 'buyer_reference'),'buyer_id'=>$row['store_name'],'buyer_name'=>$this->excel_get($row_count, 'buyer_name'),'buyer_contact'=>$this->excel_get($row_count, 'buyer_contact'),'buyer_email'=>$this->excel_get($row_count, 'buyer_email'),'buyer_address'=>$this->excel_get($row_count, 'buyer_addr1'),'buyer_address2'=>$this->excel_get($row_count, 'buyer_addr2'),'buyer_address3'=>$this->excel_get($row_count, 'buyer_addr3'),'buyer_city'=>$this->excel_get($row_count, 'buyer_city'),'buyer_state'=>$this->excel_get($row_count, 'buyer_state'),'buyer_postcode'=>$this->excel_get($row_count, 'buyer_postcode'),'buyer_country'=>$this->excel_get($row_count, 'buyer_country'),'tracking_number'=>$tracking_number,'quantity'=>$quantity,'selling_currency'=>$cur_siteid,'selling_price'=>$selling_price,'shipping_charges_received'=>'','payment_date'=>date("Y-m-d H:i:s",strtotime($this->excel_get($row_count, 'paid_date'))),'shipment_date'=>$this->excel_get($row_count, 'shipment_date'),'courier_id'=>$courier_id,'shipping_charges_paid'=>$shipping_charges,'sales_id'=>$sales_id,'sales_fees_pect'=>$row['sales_fees_pect'],'sales_fees_fixed'=>$row['sales_fees_fixed'],'paypal_trans_id'=>$this->excel_get($row_count, 'paypal_trans_id'),'paypal_fees_pect'=>$row['paypal_fees_pect'],'paypal_fees_fixed'=>$row['paypal_fees_fixed'],'cost_price'=>$row['cost_price'],'store_skucode'=>$this->excel_get($row_count, 'item_sku', $row['store_skucode']));
                    $temp_id = $row['store_item_id']."_".$sales_id;
                    $temp_list[$temp_id] = $temp3;
                }else{
                    $missing[] = "row no. ".($row_count + 1).": SKU no found. order-id:".$sales_id;
                }
            }
            return $this->get_return($temp_list, $missing, 'sales');
        }
        return $return;
    }
    
    function item_import($file,$cur_siteid = ""){
        $return = array("status"=>"0","message"=>"");
        if($cur_siteid==""){
            return $return;
        }
        $cur_siteid = strtoupper($cur_siteid);
        
        $cols = array('item_sku'=>'seller-sku','item_product'=>'Frame Model','item_option'=>'Color / combo');
        
        if(($temp_records = $this->excel_read($file, $cols))){
            $temp_list = array();
            $missing = array();
            for($row_count=1; $row_count<sizeof($temp_records); $row_count++){
                if($row_count==0 || trim(implode("",$this->excel_get($row_count)))==""){
                    continue;
                }
                $temp_sku = $this->excel_get($row_count,'item_sku');
                $is_fba = 0;
                if(stristr($this->excel_get($row_count, 'item_sku'),'.fba')!==FALSE){
                    $temp_sku = str_ireplace('.fba', '', $this->excel_get($row_count,'item_sku'));
                    $is_fba = 1;
                }
                $temp3 = array('item_sku'=>$this->excel_get($row_count,'item_sku'),'variation_order'=>$this->excel_get($row_count,'item_sku'),'price'=>0,'quantity'=>0,'store_item_id'=>'0');
                if(($row = $this->search_store_item($this->account_id, "amazon_".$cur_siteid, $is_fba, $cur_siteid, array($this->excel_get($row_count,'item_product')."-".$this->excel_get($row_count,'item_option'),$temp_sku)))){
                    $temp3['store_item_id'] = $row['store_item_id'];
                    if(empty($temp_list[$cur_siteid][$this->excel_get($row_count,'item_product')])){
                        $temp_list[$cur_siteid][$this->excel_get($row_count,'item_product')] = array('item_id'=>$this->excel_get($row_count,'item_product'),'item_name'=>$this->excel_get($row_count,'item_product')."-".$this->excel_get($row_count,'item_option'),'item_sku'=>$this->excel_get($row_count,'item_product'),'variation_order'=>'','currency'=>$cur_siteid,'price'=>0,'quantity'=>0,'variation'=>array());
                    }
                    $temp_list[$cur_siteid][$this->excel_get($row_count,'item_product')]['variation'][$this->excel_get($row_count,'item_sku')] = $temp3;
                }else{
                    $missing[] = "row no. ".($row_count + 1).": SKU no found. seller-sku:".$this->excel_get($row_count,'item_sku');
                }
            }
            return $this->get_return($temp_list, $missing, 'item');
        }
        return $return;
    }
    
    function payment_import($file){
        $return = array("status"=>"0","message"=>"");
        
        $cols = array('sales_id'=>'Order ID','payment_amount'=>'Amount','item_sku'=>'SKU','transaction_type'=>'Transaction type','payment_type'=>'Payment Type','payment_detail'=>'Payment Detail');
        
        if(($temp_records = $this->excel_read($file, $cols, 3))){
            $temp_list = array();
            $missing = array();
            
            for($row_count=4; $row_count<sizeof($temp_records); $row_count++){
                if($row_count==0 || trim(implode("",$this->excel_get($row_count)))=="" || $this->excel_get($row_count,'sales_id')=="" || $this->excel_get($row_count,'item_sku')==""){
                    continue;
                }
                $temp = $this->excel_get($row_count,'sales_id')."_".$this->excel_get($row_count,'item_sku');
                if(!isset($temp_list[$temp])){
                    if(($result = $this->CI->db->query('select a.id store_item_id from store_item a 
                        join stores b on a.store_id=b.id
                        join marketplaces c on b.marketplace_id=c.id
                        where c.sales_template="amazon" AND b.account_id=? AND a.store_skucode=? LIMIT 1',array($this->account_id,$this->excel_get($row_count,'item_sku')))) && ($row = $result->row_array())){
                        $store_item_id = $row['store_item_id'];
                        foreach(array('transactions_cache','transactions') as $table){
                            if(($result = $this->CI->db->query('SELECT id,0 as shipping_charges_received,0 as shipping_charges_paid,0 as sales_fees_pect,0 as sales_fees_fixed FROM '.$table.' WHERE store_item_id=? AND sales_id=? LIMIT 1',array($store_item_id,$this->excel_get($row_count, 'sales_id')))) && ($row = $result->row_array())){
                                $temp_list[$temp] = array('data'=>$row,'query_table'=>$table,'query_id'=>$row['id']);
                                break;
                            }
                        }
                    }
                }
                if(!isset($temp_list[$temp])){
                    if(!isset($missing[$temp])){
                        $missing[$temp] = "row no. ".($row_count + 1).": SKU no found. Order ID:".$this->excel_get($row_count, 'sales_id');
                    }
                    continue;
                }
                
                if(array_search($this->excel_get($row_count, 'transaction_type'),array('Order Payment','Refund'))!==FALSE && array_search($this->excel_get($row_count, 'payment_type'),array('Amazon fees','Other','Promo rebates'))!==FALSE){
                    if($this->excel_get($row_count, 'transaction_type')=="Refund"){
                        //$temp_list[$temp]['data']['selling_price'] = 0;
                        $temp_list[$temp]['data']['transaction_status'] = '1';
                    }
                    $payment_amount = (float)preg_replace('#[^0-9\.-]#iu', '', $this->excel_get($row_count, 'payment_amount')) * -1;
                    if(stristr($this->excel_get($row_count, 'payment_detail'),"Pick & Pack")!==FALSE){
                        $temp_list[$temp]['data']['shipping_charges_paid'] = (float)$temp_list[$temp]['data']['shipping_charges_paid'] + $payment_amount;
                    }else{
                        $temp_list[$temp]['data']['sales_fees_fixed'] = (float)$temp_list[$temp]['data']['sales_fees_fixed'] + $payment_amount;
                    }
                }
            }
            return $this->get_return($temp_list, $missing, 'payment');
        }
        return $return;
    }
    
    function item_export($file = "", $cur_siteid = ""){
        header("Content-type: text/plain");
        header("Content-Disposition: attachment; filename=item_export_amazon_".$cur_siteid.".txt");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        $out = fopen('php://output', 'w');
        $header = array('seller-sku','quantity','price','product-id');
        fputcsv($out, $header, "\t");
        
        $item_list = array();
        $sql = 'select a.*,c.currency,ifnull(d.quantity,0)+ifnull(d.quantity2,0) quantity
            from store_item a
            join stores b on a.store_id=b.id
            join marketplaces c on b.marketplace_id=c.id
            join warehouse_item d on b.warehouse_id=d.warehouse_id and a.warehouse_item_id=d.id
            where a.item_status=1 and b.account_id=? and c.import_template=?
            order by c.currency,d.product_id,d.item_id';
        $binding = array($this->account_id,"amazon_".$cur_siteid);
        if(($result = $this->CI->db->query($sql,$binding)) && $result->num_rows()){
            foreach($result->result_array() as $row){
                $data = array($row['store_skucode'],max(0,$row['quantity']),'','');
                fputcsv($out, $data, "\t");
            }
        }
        
        fclose($out);
        exit;
    }
}

?>