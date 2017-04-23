<?php

include_once 'extra/PHPExcel_1.8.0/Classes/PHPExcel.php';

class importClass{
    
    var $account_id = 0;
    
    function __construct() {
        $this->CI = get_instance();
        $this->CI->load->database();
    }
    
    function sales_import($account_id,$type,$file){
        $this->account_id = $account_id;
        $temp = explode("_",$type);
        $return = array("status"=>"0","message"=>"");
        switch ($temp[0]){
            case 'ebay':
                $return = $this->sales_import_ebay($file);
                break;
            case 'amazon':
                $return = $this->sales_import_amazon($file);
                break;
        }
        
        return $return;
    }
    
    function item_import($account_id,$type,$file){
        $this->account_id = $account_id;
        $temp = explode("_",$type);
        $return = array("status"=>"0","message"=>"");
        switch ($temp[0]){
            case 'ebay':
                $return = $this->item_import_ebay($file);
                break;
            case 'amazon':
                $return = $this->item_import_amazon($file,$temp[1]);
                break;
        }
        
        return $return;
    }
    
    function get_warehouse_item($search_array = array()){
        static $instances = array();
        if(!isset($instances['product_list'])){
            $temp = array();
            if(($result = $this->CI->db->query('SELECT id,name,code,option_id FROM products ORDER BY length(code),code'))){
                foreach($result->result_array() as $value){
                    $temp[$value['id']] = $value;
                }
            }
            $instances['product_list'] = $temp;
        }
        if(!isset($instances['option_list'])){
            $temp = array();
            if(($result = $this->CI->db->query('SELECT id,name,if(type=1,code,code2) code,option_id FROM option_item ORDER BY type,length(code),code'))){
                foreach($result->result_array() as $value){
                    if(!isset($temp[$value['option_id']])){
                        $temp[$value['option_id']] = array();
                    }
                    $temp[$value['option_id']][$value['id']] = $value;
                }
            }
            $instances['option_list'] = $temp;
        }
        
        //check product id
        $selected_product = false;
        foreach($search_array as $str){
            $temp = preg_replace('#[^0-9a-z\s][^0-9a-z]+[^0-9a-z\s]?#iu', '', $str);
            $temp_str2 = trim(preg_replace('#[\s]+#iu', "", $temp));
            $temp = "-".trim(preg_replace('#[\s]+#iu', "-", $temp),"-")."-";
            foreach(array_reverse($instances['product_list']) as $p){
                $temp2 = explode(" ",trim($p['name']));
                $count = 0;
                foreach($temp2 as $c){
                    if(stristr($temp,"-".$c."-")!==FALSE || stristr($temp_str2,$c)!==FALSE){
                        $count++;
                        continue;
                    }
                }
                if(sizeof($temp2)==$count){
                    $selected_product = $p;
                    break(2);
                }
            }
            if(!$selected_product){
                $temp = preg_replace('#[^0-9a-z]#iu', '', $str);
                foreach($instances['product_list'] as $p){
                    if(stristr($temp,preg_replace('#[^0-9a-z]#iu', '', $p['name']))!==FALSE || stristr($temp,preg_replace('#[^0-9a-z]#iu', '', $p['code']))!==FALSE){
                        $selected_product = $p;
                        break(2);
                    }
                }
            }
        }
        
        //check option if exists
        $selected_option = false;
        if($selected_product && !empty($instances['option_list'][$selected_product['option_id']])){
            $temp_ops = $instances['option_list'][$selected_product['option_id']];
            foreach($search_array as $str){
                $temp = $str;
                foreach(array_reverse($temp_ops) as $p){
                    $temp2 = explode(",",$p['code']);
                    $count = 0;
                    foreach($temp2 as $c){
                        if(stristr($temp,"-".$c)!==FALSE){
                            $count++;
                            continue;
                        }
                    }
                    if(sizeof($temp2)==$count){
                        $selected_option = $p;
                        break(2);
                    }
                }
            }
            if(!$selected_option){
                foreach($search_array as $str){
                    $temp = $str;
                    foreach($temp_ops as $p){
                        if(stristr($temp,$p['name'])!==FALSE){
                            $selected_option = $p;
                            break(2);
                        }
                    }
                }
            }
        }
        if($selected_product && $selected_option){
            return array($selected_product['id'],$selected_option['id']);
        }
        
        return false;
    }
    
    function search_store_item($account_id="",$type="",$is_fba=0,$currency="",$skucode="",$market_item_id="",$variation=""){
        $return = false;
        if(is_array($skucode)){
            if($t = $this->get_warehouse_item($skucode)){
                $sql = 'select b.account_id,a.id store_item_id,b.sales_fees_pect,b.sales_fees_fixed,b.paypal_fees_pect,b.paypal_fees_fixed
                        from store_item a
                        join stores b on a.store_id=b.id
                        join marketplaces c on b.marketplace_id=c.id
                        join warehouse_item d on b.warehouse_id=d.warehouse_id and a.warehouse_item_id=d.id
                        where b.account_id=? and c.import_template=? and is_fba=?
                        and c.currency=? and d.product_id=? and d.item_id=? limit 1';
                $binding = array($account_id,$type,$is_fba,$currency,$t[0],$t[1]);
            }else{
                return false;
            }
        }else{
            $sql = 'select b.account_id,a.id store_item_id,b.sales_fees_pect,b.sales_fees_fixed,b.paypal_fees_pect,b.paypal_fees_fixed
                    from store_item a
                    join stores b on a.store_id=b.id
                    join marketplaces c on b.marketplace_id=c.id
                    join warehouse_item d on b.warehouse_id=d.warehouse_id and a.warehouse_item_id=d.id
                    where b.account_id=? and c.import_template=? and is_fba=?
                    and c.currency=? and (a.store_skucode=? or (a.marketplace_item_id=? and a.marketplace_variation_order=?)) limit 1';
            $binding = array($account_id,$type,$is_fba,$currency,$skucode,$market_item_id,$variation);
        }
        if(($result = $this->CI->db->query($sql,$binding)) && $result->num_rows()){
            $return = $result->row_array();
        }
        return $return;
    }
    
    function transactions_cache_insert($data = array()){
        foreach($data as $value){
            if(($result = $this->CI->db->query('select id from transactions_cache where store_item_id=? AND sales_id=?
                union distinct 
                select id from transactions where store_item_id=? AND sales_id=?
                limit 1',array($value['store_item_id'],$value['sales_id'],$value['store_item_id'],$value['sales_id']))) && ($row = $result->row_array()) && ($row['id']!=$id)){
                continue;
            }
            
            $value_list = array();
            foreach($value as $k => $v){
                $value_list[] = '`'.$k.'`="'.$v.'"';
            }
            $sql = 'INSERT INTO transactions_cache SET '.implode(",", $value_list);
            $this->CI->db->query($sql);
        }
    }
    
    function store_item_update($data = array()){
        foreach($data as $a){
            foreach($a as $b){
                foreach($b['variation'] as $c){
                    $sql = 'UPDATE store_item SET store_skucode=?,selling_price=?,discount_price=?,expire_date=?,item_status=?,marketplace_item_id=?,marketplace_item_name=?,marketplace_variation=?,marketplace_variation_order=?,marketplace_item_label=? WHERE id=? limit 1';
                    $this->CI->db->query($sql,array($c['item_sku'],$c['price'],0,'0000-00-00',1,$b['item_id'],$b['item_name'],$c['variation_order'],$b['variation_order'],$b['item_sku'],$c['store_item_id']));
                }
            }
        }
    }
    
    function get_return($temp_list,$missing = array(),$type = 'item'){
        $return = array("status"=>"0","message"=>"");
        $func = "";
        if(is_array($temp_list) && sizeof($temp_list)>0){
            switch($type){
                case "item":
                    $this->store_item_update($temp_list);
                    break;
                case "sales":
                    $this->transactions_cache_insert($temp_list);
                    break;
            }
            $return['status'] = "1";
            $return['message'] .= "<div class='alert alert-success'><strong>".sizeof($temp_list)."</strong> item(s) import successfully.</div>";
        }
        if(sizeof($missing)>0){
            $return['message'] .= "<div class='alert alert-danger'><strong>".sizeof($missing)."</strong> item(s) fail to import!</div>";
            $temp = implode("<br/>",$missing);
            $return['message'] .= "<pre style='text-align:left;'>Fail list:\\n".$temp."</pre>";
        }else{
            $return['status'] = "1";
        }
        $func .= 'swal({
                title: "Submission Result",
                text: "<pre style=\'text-align:left;\'>'.$return['message'].'</pre>",
                type: "",
                html: true
            });';
        $return['message'] = "";
        if(sizeof($func)>0){
            $return['func'] = 'function(){'.$func.'}';
        }
        return $return;
    }
    
    public function excel_read($file,$cols = array()){
        $return = false;
        if(file_exists($file)){
            $data = false;
            try{
                $inputFileType = PHPExcel_IOFactory::identify($file);
                if(array_search(strtolower($inputFileType), array('txt','csv'))===FALSE){
                    $objPHPExcel = PHPExcel_IOFactory::createReader($inputFileType);
                    $objPHPExcel = $objPHPExcel->load($file);
                    $data = $objPHPExcel->getActiveSheet()->toArray('',true,true,false);
                    $objPHPExcel->disconnectWorksheets();
                }
            } catch (Exception $ex) {
                return false;
            }
            if(!$data || empty($data[0]) || empty($data[0][0]) || empty($data[0][1]) || strlen(trim($data[0][0]))==0){
                try{
                    $step = 1;
                    if (($handle = fopen($file, "r")) !== FALSE) {
                        $data = array();
                        while (($row = fgetcsv($handle, 1024, ",")) !== FALSE) {
                            if(sizeof($row)<5 && strlen(trim($row[0]))>0){
                                $step = 2;
                                break;
                            }
                            $data[] = $row;
                        }
                        fclose($handle);
                    }
                    if ($step==2 && ($handle = fopen($file, "r")) !== FALSE) {
                        $data = array();
                        while (($row = fgetcsv($handle, 1024, "\t")) !== FALSE) {
                            $data[] = $row;
                        }
                        fclose($handle);
                    }
                } catch (Exception $ex) {
                    return false;
                }
            }
            
            $this->excel_cols = array();
            $this->excel_data = array();
            if($data && is_array($data) && sizeof($data)>0){
                $header = $data[0];
                $temp = array();
                $count = 0;
                foreach($header as $c){
                    if(strlen(trim($c))==0){
                        break;
                    }
                    foreach($cols as $k => $v){
                        if(strlen(trim($v))==0){continue;}
                        if((string)$count==(string)$v || strtolower($v)==strtolower($c)){
                            $temp[$k] = $count;
                            unset($cols[$k]);
                            break;
                        }
                    }
                    $count++;
                }
                $this->excel_cols = $temp;
                $this->excel_data = $data;
                $return = $data;
            }
        }
        return $return;
    }
    
    public function excel_get($row = 0,$col = ''){
        $return = "";
        if(isset($this->excel_data[$row])){
            if($col==""){
                return $this->excel_data[$row];
            }else if(strlen($col)>0 && isset($this->excel_cols[$col]) && isset($this->excel_data[$row][$this->excel_cols[$col]])){
                $return = $this->excel_data[$row][$this->excel_cols[$col]];
            }
        }
        return $return;
    }
    
/*ebay section start*/
    function sales_import_ebay($file){
        $return = array("status"=>"0","message"=>"");
        
        $cols = array('buyer_id'=>'User Id','buyer_name'=>'Buyer Fullname','buyer_contact'=>'Buyer Phone Number','buyer_email'=>'Buyer Email','buyer_addr1'=>'Buyer Address 1','buyer_addr2'=>'Buyer Address 2','buyer_city'=>'Buyer City','buyer_state'=>'Buyer State','buyer_postcode'=>'Buyer Zip','buyer_country'=>'Buyer Country','quantity'=>'Quantity','item_id'=>'Item ID','sales_id'=>'Transaction ID','selling_price'=>'Sale Price','shipping_price'=>'Shipping And Handling','total_price'=>'Total Price','paypal_trans_id'=>'PayPal Transaction ID','paid_date'=>'Paid on Date','buyer_reference'=>'Notes to Yourself','priv_notes'=>'Private Notes','item_sku'=>'Custom Label','variation_order'=>'Variation Details');
        
        if(($temp_records = $this->excel_read($file, $cols))){
            $temp_list = array();
            $row_count = 0;
            $missing = array();
            for($row_count=0; $row_count<sizeof($temp_records); $row_count++){
                if($row_count==0 || trim(implode("",$this->excel_get($row_count)))==""){
                    continue;
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
                    $total_price = preg_replace('#[^0-9\.]#iu', '', $this->excel_get($row_count, 'total_price'));
                    $shipping_charges = preg_replace('#[^0-9\.]#iu', '', $this->excel_get($row_count, 'shipping_price'));
                    $temp3 = array('account_id'=>$row['account_id'],'store_item_id'=>$row['store_item_id'],'buyer_reference'=>$this->excel_get($row_count, 'buyer_reference').$this->excel_get($row_count, 'priv_notes'),'buyer_id'=>$this->excel_get($row_count, 'buyer_id'),'buyer_name'=>$this->excel_get($row_count, 'buyer_name'),'buyer_contact'=>$this->excel_get($row_count, 'buyer_contact'),'buyer_email'=>$this->excel_get($row_count, 'buyer_email'),'buyer_address'=>$this->excel_get($row_count, 'buyer_addr1').(strlen($this->excel_get($row_count, 'buyer_addr2'))>0?', '.$this->excel_get($row_count, 'buyer_addr2'):''),'buyer_city'=>$this->excel_get($row_count, 'buyer_city'),'buyer_state'=>$this->excel_get($row_count, 'buyer_state'),'buyer_postcode'=>$this->excel_get($row_count, 'buyer_postcode'),'buyer_country'=>$this->excel_get($row_count, 'buyer_country'),'tracking_number'=>'','quantity'=>$this->excel_get($row_count, 'quantity'),'selling_currency'=>$cur_siteid,'selling_price'=>$selling_price,'shipping_charges_received'=>$shipping_charges,'payment_date'=>date("Y-m-d H:i:s",strtotime($this->excel_get($row_count, 'paid_date'))),'shipment_date'=>'','courier_id'=>'','shipping_charges_paid'=>'','sales_id'=>$this->excel_get($row_count, 'sales_id'),'sales_fees_pect'=>$row['sales_fees_pect'],'sales_fees_fixed'=>$row['sales_fees_fixed'],'paypal_trans_id'=>$this->excel_get($row_count, 'paypal_trans_id'),'paypal_fees_pect'=>$row['paypal_fees_pect'],'paypal_fees_fixed'=>$row['paypal_fees_fixed']);
                    $temp_id = $row['store_item_id']."_".$this->excel_get($row_count, 'sales_id');
                    $temp_list[$temp_id] = $temp3;
                }else{
                    $missing[] = "row no. ".$row_count.": SKU no found. Transaction ID:".$this->excel_get($row_count, 'sales_id');
                }
            }
            return $this->get_return($temp_list, $missing, 'sales');
        }
        return $return;
    }
    
    function item_import_ebay($file){
        $return = array("status"=>"0","message"=>"");
        
        $cols = array('item_id'=>'ItemID','item_name'=>'Title','currency'=>'Currency','price'=>'StartPrice','quantity'=>'Quantity','relationship'=>'Relationship','variation_order'=>'RelationshipDetails','item_sku'=>'CustomLabel');
        
        if(($temp_records = $this->excel_read($file, $cols))){
            $temp_list = array();
            $cur_siteid = "";
            $cur_product_id = "";
            $missing = array();
            for($row_count=0; $row_count<sizeof($temp_records); $row_count++){
                if($row_count==0 || trim(implode("",$this->excel_get($row_count)))==""){
                    continue;
                }
                if($this->excel_get($row_count,'relationship')=='Variation'){
                    $temp3 = array('item_sku'=>$this->excel_get($row_count,'item_sku'),'variation_order'=>$this->excel_get($row_count,'variation_order'),'price'=>$this->excel_get($row_count,'price'),'quantity'=>$this->excel_get($row_count,'quantity'),'store_item_id'=>'0');
                    if(($row = $this->search_store_item($this->account_id, "ebay", 0, $cur_siteid, array($temp_list[$cur_siteid][$cur_product_id]['item_name'],$this->excel_get($row_count,'variation_order'),$this->excel_get($row_count,'item_sku'))))){
                        $temp3['store_item_id'] = $row['store_item_id'];
                        $temp_list[$cur_siteid][$cur_product_id]['variation'][$this->excel_get($row_count,'variation_order')] = $temp3;
                    }else{
                        $missing[] = "row no. ".$row_count.": SKU no found. CustomLabel:".$this->excel_get($row_count,'item_sku');
                    }
                }else{
                    if(!empty($this->excel_get($row_count, 'currency')) && ($cur_siteid=="" || $cur_siteid!=$this->excel_get($row_count, 'currency'))){
                        $cur_siteid = strtoupper($this->excel_get($row_count, 'currency'));
                    }
                    if(!isset($temp_list[$cur_siteid])){
                        $temp_list[$cur_siteid] = array();
                    }
                    $cur_product_id = $this->excel_get($row_count, 'item_id');
                    $temp_list[$cur_siteid][$this->excel_get($row_count,'item_id')] = array('item_id'=>$this->excel_get($row_count,'item_id'),'item_name'=>$this->excel_get($row_count,'item_name'),'item_sku'=>$this->excel_get($row_count,'item_sku'),'variation_order'=>$this->excel_get($row_count,'variation_order'),'currency'=>strtoupper($this->excel_get($row_count,'currency')),'price'=>$this->excel_get($row_count,'price'),'quantity'=>$this->excel_get($row_count,'quantity'),'variation'=>array());
                }
            }
            return $this->get_return($temp_list, $missing, 'item');
        }
        return $return;
    }
/*ebay section end*/
    
/*amazon section start*/
    function sales_import_amazon($file){
        $return = array("status"=>"0","message"=>"");
        
        $cols = array('buyer_name'=>'recipient-name','buyer_contact'=>'buyer-phone-number','buyer_email'=>'buyer-email','buyer_addr1'=>'ship-address-1','buyer_addr2'=>'ship-address-2','buyer_addr3'=>'ship-address-3','buyer_city'=>'ship-city','buyer_state'=>'ship-state','buyer_postcode'=>'ship-postal-code','buyer_country'=>'ship-country','quantity'=>'quantity-purchased','item_id'=>'order-item-id','sales_id'=>'order-id','selling_price'=>'item-price','shipping_charges_paid'=>'shipping-price','paid_date'=>'payments-date','buyer_reference'=>'delivery-Instructions','item_sku'=>'sku','amazon-order-id'=>'amazon-order-id','amazon-order-item-id'=>'amazon-order-item-id','tracking_number'=>'tracking-number','shipment_date'=>'shipment-date','courier_id'=>'carrier','ship-promotion-discount'=>'ship-promotion-discount','currency'=>'currency','item_name'=>'product-name','quantity-shipped'=>'quantity-shipped');
        
        if(($temp_records = $this->excel_read($file, $cols))){
            $temp_list = array();
            $row_count = 0;
            $missing = array();
            
            $courier_sys_id = "3";
            if(($result = $this->CI->db->query('SELECT id FROM couriers WHERE name="FBA"')) && ($row = $result->row_array())){
                $courier_sys_id = $row['id'];
            }
            
            for($row_count=0; $row_count<sizeof($temp_records); $row_count++){
                if($row_count==0 || trim(implode("",$this->excel_get($row_count)))==""){
                    continue;
                }
                $cur_siteid = strtoupper($this->excel_get($row_count, 'currency'));
                $is_fba = 0;
                $selling_price = preg_replace('#[^0-9\.]#iu', '', $this->excel_get($row_count, 'selling_price'));
                $shipping_charges = 0;
                $tracking_number = "";
                $courier_id = "";
                $sales_id = $this->excel_get($row_count, 'sales_id');
                $temp_sku = $this->excel_get($row_count,'item_sku');
                $quantity = $this->excel_get($row_count, 'quantity');
                if($this->excel_get($row_count, 'amazon-order-id')!=""){
                    $is_fba = 1;
                    $tracking_number = $this->excel_get($row_count, 'courier_id')." ".$this->excel_get($row_count, 'tracking_number');
                    $courier_id = $courier_sys_id;
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
                    $temp3 = array('account_id'=>$row['account_id'],'store_item_id'=>$row['store_item_id'],'buyer_reference'=>$this->excel_get($row_count, 'buyer_reference'),'buyer_id'=>$this->excel_get($row_count, 'buyer_email'),'buyer_name'=>$this->excel_get($row_count, 'buyer_name'),'buyer_contact'=>$this->excel_get($row_count, 'buyer_contact'),'buyer_email'=>$this->excel_get($row_count, 'buyer_email'),'buyer_address'=>$this->excel_get($row_count, 'buyer_addr1').(strlen($this->excel_get($row_count, 'buyer_addr2'))>0?', '.$this->excel_get($row_count, 'buyer_addr2'):'').(strlen($this->excel_get($row_count, 'buyer_addr3'))>0?', '.$this->excel_get($row_count, 'buyer_addr3'):''),'buyer_city'=>$this->excel_get($row_count, 'buyer_city'),'buyer_state'=>$this->excel_get($row_count, 'buyer_state'),'buyer_postcode'=>$this->excel_get($row_count, 'buyer_postcode'),'buyer_country'=>$this->excel_get($row_count, 'buyer_country'),'tracking_number'=>$tracking_number,'quantity'=>$quantity,'selling_currency'=>$cur_siteid,'selling_price'=>$selling_price,'shipping_charges_received'=>'','payment_date'=>date("Y-m-d H:i:s",strtotime($this->excel_get($row_count, 'paid_date'))),'shipment_date'=>$this->excel_get($row_count, 'shipment_date'),'courier_id'=>$courier_id,'shipping_charges_paid'=>$shipping_charges,'sales_id'=>$sales_id,'sales_fees_pect'=>$row['sales_fees_pect'],'sales_fees_fixed'=>$row['sales_fees_fixed'],'paypal_trans_id'=>$this->excel_get($row_count, 'paypal_trans_id'),'paypal_fees_pect'=>$row['paypal_fees_pect'],'paypal_fees_fixed'=>$row['paypal_fees_fixed']);
                    $temp_id = $row['store_item_id']."_".$sales_id;
                    $temp_list[$temp_id] = $temp3;
                }else{
                    $missing[] = "row no. ".$row_count.": SKU no found. order-id:".$sales_id;
                }
            }
            return $this->get_return($temp_list, $missing, 'sales');
        }
        return $return;
    }
    
    function item_import_amazon($file,$cur_siteid = ""){
        $return = array("status"=>"0","message"=>"");
        if($cur_siteid==""){
            return $return;
        }
        
        $cols = array('item_sku'=>'seller-sku','item_product'=>'Frame Model','item_option'=>'Color / combo');
        
        if(($temp_records = $this->excel_read($file, $cols))){
            $temp_list = array();
            $cur_siteid = strtoupper($cur_siteid);
            $missing = array();
            for($row_count=0; $row_count<sizeof($temp_records); $row_count++){
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
                    $missing[] = "row no. ".$row_count.": SKU no found. seller-sku:".$this->excel_get($row_count,'item_sku');
                }
            }
            return $this->get_return($temp_list, $missing, 'item');
        }
        return $return;
    }
    
    
/*amazon section end*/
    
}

?>