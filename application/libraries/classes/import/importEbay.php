<?php

include_once 'importClass.php';

class importEbayClass extends importClass{
    
    function __construct() {
        parent::__construct();
    }
    
    function sales_import($file){
        $return = array("status"=>"0","message"=>"");
        
        $cols = array('buyer_id'=>'User Id','buyer_name'=>'Buyer Fullname','buyer_contact'=>'Buyer Phone Number','buyer_email'=>'Buyer Email','buyer_addr1'=>'Buyer Address 1','buyer_addr2'=>'Buyer Address 2','buyer_city'=>'Buyer City','buyer_state'=>'Buyer State','buyer_postcode'=>'Buyer Zip','buyer_country'=>'Buyer Country','quantity'=>'Quantity','item_id'=>'Item ID','sales_id'=>'Transaction ID','selling_price'=>'Sale Price','shipping_price'=>'Shipping And Handling','paypal_trans_id'=>'PayPal Transaction ID','paid_date'=>'Paid on Date','buyer_reference'=>'Notes to Yourself','priv_notes'=>'Private Notes','item_sku'=>'Custom Label','variation_order'=>'Variation Details','sales_record_number'=>'Sales Record Number');
        
        if(($temp_records = $this->excel_read($file, $cols))){
            $temp_list = array();
            $missing = array();
            $parent_record = array();
            for($row_count=1; $row_count<sizeof($temp_records); $row_count++){
                if($row_count==0 || trim(implode("",$this->excel_get($row_count)))==""){
                    continue;
                }
                $temp = $this->excel_get($row_count, 'sales_record_number');
                if($this->excel_get($row_count, 'quantity')>1 && $this->excel_get($row_count, 'item_id')==""){
                    $parent_record[$temp] = $row_count;
                    continue;
                }else if(isset($parent_record[$temp]) && $this->excel_get($row_count, 'buyer_id')==""){
                    foreach($this->excel_get($parent_record[$temp]) as $key => $value){
                        if(strlen($value)>0 && $this->excel_get($row_count, $key)==""){
                            $this->excel_set($row_count, $key, $value);
                        }
                    }
                }
                $cur_siteid = "";
                if(!empty($this->excel_get($row_count, 'selling_price'))){
                    switch (substr(strtoupper($this->excel_get($row_count, 'selling_price')),0,1)){
                        case '$':
                            $cur_siteid = "USD";
                            break;
                        case 'A':
                            $cur_siteid = "AUD";
                            break;
                        case 'G':
                            $cur_siteid = "GBP";
                            break;
                    }
                }
                
                if(($row = $this->search_store_item($this->account_id, "ebay", 0, $cur_siteid, $this->excel_get($row_count, 'item_sku'), $this->excel_get($row_count, 'item_id'), str_ireplace(array('[',']',':'), array('','','='), $this->excel_get($row_count, 'variation_order'))))){
                    $selling_price = preg_replace('#[^0-9\.]#iu', '', $this->excel_get($row_count, 'selling_price'));
                    $shipping_charges = preg_replace('#[^0-9\.]#iu', '', $this->excel_get($row_count, 'shipping_price'));
                    $temp3 = array('account_id'=>$row['account_id'],'store_item_id'=>$row['store_item_id'],'buyer_reference'=>$this->excel_get($row_count, 'buyer_reference').$this->excel_get($row_count, 'priv_notes'),'buyer_id'=>$this->excel_get($row_count, 'buyer_id'),'buyer_name'=>$this->excel_get($row_count, 'buyer_name'),'buyer_contact'=>$this->excel_get($row_count, 'buyer_contact'),'buyer_email'=>$this->excel_get($row_count, 'buyer_email'),'buyer_address'=>$this->excel_get($row_count, 'buyer_addr1'),'buyer_address2'=>$this->excel_get($row_count, 'buyer_addr2'),'buyer_city'=>$this->excel_get($row_count, 'buyer_city'),'buyer_state'=>$this->excel_get($row_count, 'buyer_state'),'buyer_postcode'=>$this->excel_get($row_count, 'buyer_postcode'),'buyer_country'=>$this->excel_get($row_count, 'buyer_country'),'tracking_number'=>$this->excel_get($row_count, 'tracking_number'),'quantity'=>$this->excel_get($row_count, 'quantity'),'selling_currency'=>$cur_siteid,'selling_price'=>$selling_price,'shipping_charges_received'=>$shipping_charges,'payment_date'=>date("Y-m-d H:i:s",strtotime($this->excel_get($row_count, 'paid_date'))),'shipment_date'=>$this->excel_get($row_count, 'shipment_date'),'courier_id'=>$this->excel_get($row_count, 'courier_id'),'shipping_charges_paid'=>'','sales_id'=>$this->excel_get($row_count, 'sales_id'),'sales_fees_pect'=>$row['sales_fees_pect'],'sales_fees_fixed'=>$row['sales_fees_fixed'],'paypal_trans_id'=>$this->excel_get($row_count, 'paypal_trans_id'),'paypal_fees_pect'=>$row['paypal_fees_pect'],'paypal_fees_fixed'=>$row['paypal_fees_fixed'],'cost_price'=>$row['cost_price']);
                    $temp_id = $row['store_item_id']."_".$this->excel_get($row_count, 'sales_id');
                    $temp_list[$temp_id] = $temp3;
                }else{
                    $missing[] = "row no. ".($row_count + 1).": SKU no found. Transaction ID:".$this->excel_get($row_count, 'sales_id');
                }
            }
            return $this->get_return($temp_list, $missing, 'sales');
        }
        return $return;
    }
    
    function item_import($file,$cur_siteid = ""){
        $return = array("status"=>"0","message"=>"");
        
        $cols = array('item_id'=>'ItemID','item_name'=>'Title','currency'=>'Currency','price'=>'StartPrice','quantity'=>'Quantity','relationship'=>'Relationship','variation_order'=>'RelationshipDetails','item_sku'=>'CustomLabel');
        
        if(($temp_records = $this->excel_read($file, $cols))){
            $temp_list = array();
            $cur_siteid = "";
            $cur_product_id = "";
            $missing = array();
            for($row_count=1; $row_count<sizeof($temp_records); $row_count++){
                if($row_count==0 || trim(implode("",$this->excel_get($row_count)))==""){
                    continue;
                }
                if($this->excel_get($row_count,'item_id')!=''){
                    if(!empty($this->excel_get($row_count, 'currency')) && ($cur_siteid=="" || $cur_siteid!=$this->excel_get($row_count, 'currency'))){
                        $cur_siteid = strtoupper($this->excel_get($row_count, 'currency'));
                    }
                    if(!isset($temp_list[$cur_siteid])){
                        $temp_list[$cur_siteid] = array();
                    }
                    $cur_product_id = $this->excel_get($row_count, 'item_id');
                    $temp_list[$cur_siteid][$this->excel_get($row_count,'item_id')] = array('item_id'=>$this->excel_get($row_count,'item_id'),'item_name'=>$this->excel_get($row_count,'item_name'),'item_sku'=>$this->excel_get($row_count,'item_sku'),'variation_order'=>$this->excel_get($row_count,'variation_order'),'currency'=>strtoupper($this->excel_get($row_count,'currency')),'price'=>$this->excel_get($row_count,'price'),'quantity'=>$this->excel_get($row_count,'quantity'),'variation'=>array());
                }
                if($this->excel_get($row_count,'relationship')=='Variation' 
                        || ($this->excel_get($row_count,'variation_order')=='' && $this->excel_get($row_count,'price')!='' && $this->excel_get($row_count,'quantity')!='')){
                    $temp3 = array('item_sku'=>$this->excel_get($row_count,'item_sku'),'variation_order'=>$this->excel_get($row_count,'variation_order'),'price'=>$this->excel_get($row_count,'price'),'quantity'=>$this->excel_get($row_count,'quantity'),'store_item_id'=>'0');
                    if(($row = $this->search_store_item($this->account_id, "ebay", 0, $cur_siteid, array($temp_list[$cur_siteid][$cur_product_id]['item_name'],$this->excel_get($row_count,'variation_order'),$this->excel_get($row_count,'item_sku'))))){
                        $temp3['store_item_id'] = $row['store_item_id'];
                        $temp_list[$cur_siteid][$cur_product_id]['variation'][$this->excel_get($row_count,'variation_order')] = $temp3;
                    }else{
                        $missing[] = "row no. ".($row_count + 1).": SKU no found. CustomLabel:".$this->excel_get($row_count,'item_sku');
                    }
                }
            }
            return $this->get_return($temp_list, $missing, 'item');
        }
        return $return;
    }
    
    function item_export(){
        $template_path = APPPATH.'libraries/classes/templates/item_export_ebay.csv';
        if(!file_exists($template_path)){
            exit;
        }
        
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=item_export_ebay.csv");
        header("Pragma: no-cache");
        header("Expires: 0");
        
        $out = fopen('php://output', 'w');
        $header = array();
        if (($handle = fopen($template_path, "r")) !== FALSE) {
            $header = fgetcsv($handle, 1000, ",");
            fclose($handle);
        }
        fputcsv($out, $header);
                
        $site_id = array('USD'=>'US','GBP'=>'UK','AUD'=>'Australia');
        $item_list = array();
        $sql = 'select a.*,c.currency,ifnull(d.quantity,0)+ifnull(d.quantity2,0) quantity
            from store_item a
            join stores b on a.store_id=b.id
            join marketplaces c on b.marketplace_id=c.id
            join warehouse_item d on b.warehouse_id=d.warehouse_id and a.warehouse_item_id=d.id
            where a.item_status=1 and b.account_id=? and c.import_template=?
            order by c.currency,d.product_id,d.item_id';
        $binding = array($this->account_id,'ebay');
        if(($result = $this->CI->db->query($sql,$binding)) && $result->num_rows()){
            foreach($result->result_array() as $row){
                if(!isset($item_list[$row['currency']])){
                    $item_list[$row['currency']] = array();
                }
                if(!isset($item_list[$row['currency']][$row['marketplace_item_id']])){
                    $item_list[$row['currency']][$row['marketplace_item_id']] = array('item_id'=>$row['marketplace_item_id'],'item_name'=>$row['marketplace_item_name'],'relationshipDetails'=>$row['marketplace_variation_order'],'data'=>array());
                }
                $item_list[$row['currency']][$row['marketplace_item_id']]['data'][$row['warehouse_item_id']] = array('site_id'=>$site_id[$row['currency']],'currency'=>$row['currency'],'relationshipDetails'=>$row['marketplace_variation'],'price'=>$row['selling_price'],'quantity'=>$row['quantity'],'skucode'=>$row['store_skucode']);
            }
        }
        
        foreach($item_list as $currency){
            foreach($currency as $item_details){
                $count = 0;
                foreach($item_details['data'] as $data_list){
                    $count++;
                    if($count==1){
                        $data = array();
                        $data[] = "Revise";
                        $data[] = $item_details['item_id'];
                        $data[] = $item_details['item_name'];
                        $data[] = $data_list['site_id'];
                        $data[] = $data_list['currency'];
                        $data[] = "";
                        $data[] = "";
                        $data[] = "";
                        $data[] = "";
                        $data[] = $item_details['relationshipDetails'];
                        $data[] = "";
                        fputcsv($out, $data);
                    }
                    $data = array();
                    $data[] = "";
                    $data[] = "";
                    $data[] = "";
                    $data[] = "";
                    $data[] = "";
                    $data[] = $data_list['price'];
                    $data[] = "";
                    $data[] = max(0,$data_list['quantity']);
                    $data[] = "Variation";
                    $data[] = $data_list['relationshipDetails'];
                    $data[] = $data_list['skucode'];
                    fputcsv($out, $data);
                }
            }
        }
        
        fclose($out);
        exit;
    }
}

?>