<?php

include_once APPPATH.'libraries/classes/extra/PHPExcel_1.8.0/Classes/PHPExcel.php';

class importClass{
    
    var $account_id = 0;
    var $default_cols = array('system_tracking_number'=>'shipping_code','system_shipment_date'=>'shipping_date','system_courier_id'=>'shipping_comp');
    
    function __construct() {
        $this->CI = get_instance();
        $this->CI->load->database();
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
        $temp5 = 0;
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
                    if(sizeof($temp2) > $temp5){
                        $temp5 = sizeof($temp2);
                        $selected_product = $p;
                    }
                    //break(2);
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
            $temp5 = 0;
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
                        if(sizeof($temp2) > $temp5){
                            $temp5 = sizeof($temp2);
                            $selected_option = $p;
                        }
                        //break(2);
                    }
                }
            }
            if(!$selected_option){
                foreach($search_array as $str){
                    $temp = preg_replace('#(\sand\s)|[,\+]#iu', '+', $str);
                    $temp2 = explode('+', $temp);
                    $count = array();
                    foreach($temp2 as $c){
                        foreach($temp_ops as $p){
                            if(stristr($c,$p['name'])!==FALSE){
                                $count[$p['code']] = $p['code'];
                                continue(2);
                            }
                        }
                        foreach($temp_ops as $p){
                            foreach(explode(' ',$p['name']) as $d){
                                if(stristr($c,$d)!==FALSE && !isset($count[$p['code']])){
                                    $count[$p['code']] = $p['code'];
                                    continue(3);
                                }
                            }
                        }
                    }
                    if(sizeof($temp2)==sizeof($count)){
                        foreach(array_reverse($temp_ops) as $p){
                            $temp3 = explode(',',$p['code']);
                            if(sizeof($count)==sizeof($temp3) && ($temp4 = array_intersect($count, $temp3)) && sizeof($temp3)==sizeof($temp4)){
                                if(sizeof($temp2) > $temp5){
                                    $temp5 = sizeof($temp2);
                                    $selected_option = $p;
                                }
                                //break(2);
                            }
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
                $sql = 'select b.account_id,a.id store_item_id,b.sales_fees_pect,b.sales_fees_fixed,b.paypal_fees_pect,b.paypal_fees_fixed,b.name store_name
                        ,d.cost_price,a.store_skucode
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
            $sql = 'select b.account_id,a.id store_item_id,b.sales_fees_pect,b.sales_fees_fixed,b.paypal_fees_pect,b.paypal_fees_fixed,b.name store_name
                    ,d.cost_price,a.store_skucode
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
        static $courier_list = false;
        static $courier_pattern = false;
        if(!$courier_list){
            $courier_list = array();
            if(($result = $this->CI->db->query('SELECT id,name,pattern FROM couriers ORDER BY name'))){
                $courier_list[0] = "";
                foreach($result->result_array() as $value){
                    $courier_list[$value['id']] = $value['name'];
                    $courier_pattern[$value['id']] = $value['pattern'];
                }
            }
        }
        
        $return = array('exists'=>array(),'success'=>array());
        foreach($data as $value){
            if((strlen($value['courier_id'])==0 || $value['courier_id']==0) && strlen($value['tracking_number'])>0){
                foreach($courier_pattern as $k => $v){
                    if(strlen(trim($v))>0 && preg_match('#^'.trim($v).'$#iu', $value['tracking_number'], $matches)){
                        $value['courier_id'] = $k;
                        break;
                    }
                }
            }
            
            if(($result = $this->CI->db->query('select id from transactions_cache where store_item_id=? AND sales_id=? limit 1',array($value['store_item_id'],$value['sales_id']))) && $result->num_rows()){
                $row = $result->row_array();
                $return['success'][] = $value['sales_id'];
                $value_list = array();
                foreach($this->default_cols as $k => $v){
                    $k = str_replace('system_', '', $k);
                    $value_list[] = '`'.$k.'`=""';
                    if(!empty($value[$k])){
                        $value_list[] = '`'.$k.'`="'.$this->clean_data($value[$k]).'"';
                    }
                }
                $sql = 'UPDATE transactions_cache SET '.implode(",", $value_list).' WHERE id=?';
                $this->CI->db->query($sql,array($row['id']));
                continue;
            }
            if(($result = $this->CI->db->query('select id from transactions where store_item_id=? AND sales_id=? limit 1',array($value['store_item_id'],$value['sales_id']))) && $result->num_rows()){
                $return['exists'][] = $value['sales_id'];
                continue;
            }
            
            $return['success'][] = $value['sales_id'];
            $value_list = array();
            foreach($value as $k => $v){
                $value_list[] = '`'.$k.'`="'.$this->clean_data($v).'"';
            }
            $sql = 'INSERT INTO transactions_cache SET '.implode(",", $value_list);
            $this->CI->db->query($sql);
        }
        return $return;
    }
    
    function store_item_update($data = array()){
        $return = array('success'=>array());
        foreach($data as $a){
            foreach($a as $b){
                foreach($b['variation'] as $c){
                    $return['success'][] = $c['item_sku'];
                    $sql = 'UPDATE store_item SET store_skucode=?,selling_price=?,discount_price=?,expire_date=?,item_status=?,marketplace_item_id=?,marketplace_item_name=?,marketplace_variation=?,marketplace_variation_order=?,marketplace_item_label=? WHERE id=? limit 1';
                    $this->CI->db->query($sql,array($c['item_sku'],$c['price'],0,'0000-00-00',1,$b['item_id'],$b['item_name'],$c['variation_order'],$b['variation_order'],$b['item_sku'],$c['store_item_id']));
                }
            }
        }
        return $return;
    }
    
    function transactions_sales_update($data = array()){
        $return = array('success'=>array());
        foreach($data as $row){
            $return['success'][] = $row['query_id'];
            /*
            if($row['query_table']=="transactions"){
                $trans_id = 0;
                if(($result2 = $this->CI->db->query('select * from transactions where id=? limit 1',array($row['query_id']))) && ($row2 = $result2->row_array())){
                    unset($row2['id']);
                    $value_list = array();
                    foreach($row2 as $k => $v){
                        $value_list[] = '`'.$k.'`="'.$this->clean_data($v).'"';
                    }
                    $sql = 'INSERT INTO transactions_cache SET '.implode(",", $value_list);
                    $this->CI->db->query($sql);
                    $trans_id = $this->CI->db->insert_id();
                }
                $row['query_id'] = $trans_id;
                $row['query_table'] = "transactions_cache";
            }
            */
            $col_list = array();
            $data_list = array();
            foreach($row['data'] as $key => $value){
                if($key=="id"){continue;}
                $col_list[] = "`".$key."`=?";
                $data_list[] = $value;
            }
            $data_list[] = $row['query_id'];
            $sql = 'UPDATE '.$row['query_table'].' SET '.implode(",",$col_list).' WHERE id=?';
            $this->CI->db->query($sql,$data_list);
        }
        return $return;
    }
    
    function get_return($temp_list,$missing = array(),$type = 'item'){
        $return = array("status"=>"0","message"=>"");
        $func = "";
        $call_return = false;
        if(is_array($temp_list) && sizeof($temp_list)>0){
            switch($type){
                case "item":
                    $call_return = $this->store_item_update($temp_list);
                    break;
                case "sales":
                    $call_return = $this->transactions_cache_insert($temp_list);
                    break;
                case "payment":
                    $call_return = $this->transactions_sales_update($temp_list);
                    break;
            }
            $return['status'] = "1";
        }
        if($call_return && isset($call_return['success']) && sizeof($call_return['success'])>0){
            $return['message'] .= "<div class='alert alert-success'><strong>".sizeof($call_return['success'])."</strong> item(s) import successfully.</div>";
        }else if($return['status']=="1"){
            $return['message'] .= "<div class='alert alert-success'><strong>".sizeof($temp_list)."</strong> item(s) import successfully.</div>";
        }
        if($call_return && isset($call_return['exists']) && sizeof($call_return['exists'])>0){
            $return['message'] .= "<div class='alert alert-warning'><strong>".sizeof($call_return['exists'])."</strong> item(s) exists!</div>";
            $temp = implode("<br/>",$call_return['exists']);
            $return['message'] .= "<pre style='text-align:left;height:300px;overflow-y:auto;'>Exists list:\\n".$temp."</pre>";
        }
        if(sizeof($missing)>0){
            $return['message'] .= "<div class='alert alert-danger'><strong>".sizeof($missing)."</strong> item(s) fail to import!</div>";
            $temp = implode("<br/>",$missing);
            $return['message'] .= "<pre style='text-align:left;height:300px;overflow-y:auto;'>Fail list:\\n".$temp."</pre>";
        }else{
            $return['status'] = "1";
        }
        $func .= 'swal({
                title: "Submission Result",
                html: "<pre style=\'text-align:left;\'>'.$return['message'].'</pre>",
                type: ""
            });';
        $return['message'] = "";
        if(sizeof($func)>0){
            $return['func'] = 'function(){'.$func.'}';
        }
        return $return;
    }
    
    public function excel_read($file,$cols = array(),$header_line = 0){
        $return = false;
        $cols = array_merge($this->default_cols,$cols);
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
                $header = $data[$header_line];
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
    
    public function excel_get($row = 0,$col = '',$default = ''){
        $return = null;
        
        static $courier_list = false;
        static $courier_pattern = false;
        if(!$courier_list){
            $courier_list = array();
            if(($result = $this->CI->db->query('SELECT id,name,pattern FROM couriers ORDER BY name'))){
                $courier_list[0] = "";
                foreach($result->result_array() as $value){
                    $courier_list[$value['id']] = $value['name'];
                    $courier_pattern[$value['id']] = $value['pattern'];
                }
            }
        }
        
        if(isset($this->excel_data[$row])){
            if($col==""){
                $temp = array();
                foreach($this->excel_cols as $col => $pos){
                    if(isset($this->excel_data[$row][$this->excel_cols[$col]])){
                        $temp[$col] = trim($this->excel_data[$row][$this->excel_cols[$col]]);
                    }
                }
                return $temp;
            }else if(strlen($col)>0 && isset($this->excel_cols[$col]) && isset($this->excel_data[$row][$this->excel_cols[$col]])){
                $return = trim($this->excel_data[$row][$this->excel_cols[$col]]);
            }
        }
        
        $t = "system_".$col;
        if($return=="" && array_key_exists($t, $this->default_cols)!==false){
            $return = $this->excel_get($row, $t);
        }
        if($t=='system_courier_id'){
            if(($key = array_search($return, $courier_list))!==false){
                $return = $key;
            }else{
                $return = "";
            }
        }else if($t=='system_shipment_date'){
            $return = date("Y-m-d H:i:s",strtotime($return));
        }
        
        if(is_null($return)){
            $return = $default;
        }
        
        return $return;
    }
    
    public function excel_set($row = 0,$col = '',$val = ''){
        $return = "";
        if(isset($this->excel_data[$row]) && strlen($col)>0 && isset($this->excel_cols[$col]) && isset($this->excel_data[$row][$this->excel_cols[$col]])){
            $return = $this->excel_data[$row][$this->excel_cols[$col]] = $val;
        }
        return $return;
    }
    
    public function clean_data($value){
        $value = str_ireplace(array("'","\""), "", $value);
        $value = $this->CI->db->escape_str($value);
        return $value;
    }
    
}

?>