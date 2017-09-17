<?php

require_once('lensesMain.php');

class lensesSalesEntry extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Sales Entry'=>''));
        $this->table = "transactions_cache";
        $this->custom_view_config = "transactions_cache";
        $this->title = "Sales Entry";
        $this->selected_menu = "sales_entry";
        $this->freezePane = 4;
        $this->is_required = false;
        $this->extra_btn = array();
        $this->extra_btn[] = array('name'=>'Sales/Payment Import','custom_form'=>'sales_import');
        $this->extra_btn[] = array('name'=>'Shipping Export','custom_form'=>'shipping_export','require_select'=>'1');
        $this->extra_btn[] = array('name'=>'Update Courier','custom_form'=>'sales_courier','require_select'=>'1');
        $this->extra_btn[] = array('name'=>'Save Transactions','url'=>base_url('ajax/sales_entry?method=save_transactions'));
        $this->custom_form = false;
        $this->ajax_url = base_url('ajax/sales_entry');
        $this->search_query = 'select * from (select a.id
            , b.name account_name
            , g.name store_name
            , c.store_skucode
            , d.name product_name
            , e.code2 option_name
            , a.buyer_id, a.buyer_name
            , ifnull(a.buyer_address,"") buyer_address, ifnull(a.buyer_address2,"") buyer_address2, ifnull(a.buyer_address3,"") buyer_address3
            , a.buyer_city, a.buyer_state, a.buyer_postcode, a.buyer_country, a.buyer_contact, a.buyer_email, a.tracking_number
            , a.selling_currency, a.quantity
            , a.selling_price, a.shipping_charges_received, a.payment_date, a.shipment_date
            , f.name courier_name, a.shipping_charges_paid, a.sales_id
            , a.paypal_trans_id, a.sales_fees_pect, a.sales_fees_fixed, a.paypal_fees_pect, a.paypal_fees_fixed
            ,b.id account_id, a.store_item_id, a.courier_id, g.id store_id, d.id product_id
            from transactions_cache a
            join accounts b on a.account_id=b.id
            join store_item c on a.store_item_id=c.id
            join warehouse_item wi on c.warehouse_item_id=wi.id
            join products d on wi.product_id=d.id
            join option_item e on wi.item_id=e.id
            left join couriers f on a.courier_id=f.id
            join stores g on c.store_id=g.id) a';
        
        $supp_list = array();
        if(($result = $this->CI->db->query('SELECT id,name FROM accounts ORDER BY name'))){
            foreach($result->result_array() as $value){
                $supp_list[$value['id']] = $value['name'];
            }
        }
        
        $courier_list = array();
        if(($result = $this->CI->db->query('SELECT id,name FROM couriers ORDER BY name'))){
            foreach($result->result_array() as $value){
                $courier_list[$value['id']] = $value['name'];
            }
        }
        
        $courier_list2 = array();
        if(($result = $this->CI->db->query('SELECT concat(id,"|",name,"|",export_template) as id,name FROM couriers WHERE export_template<>"" ORDER BY name'))){
            foreach($result->result_array() as $value){
                $courier_list2[$value['id']] = $value['name'];
            }
        }
        
        $quantity_list = array('0'=>'0');
        for($i=1; $i<=100; $i++){
            $quantity_list[$i] = $i;
        }
        
        $currency_list = array();
        if(!empty($temp = $this->get_global_config("support_currency"))){
            $temp = explode(",",$temp);
            foreach($temp as $v){
                $currency_list[$v] = $v;
            }
        }else{
            $this->CI->load->library('cmessage');
            $this->CI->cmessage->set_message_url('Currency not set!','error','/settings');
        }
        
        $this->header = array(
            array('id'=>'id','name'=>'ID','custom_col'=>'adj_frame'),
            array('id'=>'account_id','name'=>'Account'),
            array('id'=>'store_name','name'=>'Store','is_ajax'=>'stores'),
            array('id'=>'store_skucode','name'=>'SKU'),
            array('id'=>'product_name','name'=>'Frame','is_ajax'=>'products'),
            array('id'=>'option_name','name'=>'Color','is_ajax'=>'option_item'),
            array('id'=>'buyer_id','name'=>'Buyer ID','editable'=>true),
            array('id'=>'buyer_name','name'=>'Buyer Name','editable'=>true),
            array('id'=>'buyer_address','name'=>'Buyer Address1','editable'=>true),
            array('id'=>'buyer_address2','name'=>'Buyer Address2','editable'=>true),
            array('id'=>'buyer_address3','name'=>'Buyer Address3','editable'=>true),
            array('id'=>'buyer_city','name'=>'Buyer City','editable'=>true),
            array('id'=>'buyer_state','name'=>'Buyer State','editable'=>true),
            array('id'=>'buyer_postcode','name'=>'Buyer Postcode','editable'=>true),
            array('id'=>'buyer_country','name'=>'Buyer Country','editable'=>true),
            array('id'=>'buyer_contact','name'=>'Buyer Contact','editable'=>true),
            array('id'=>'buyer_email','name'=>'Buyer Email','editable'=>true),
            array('id'=>'tracking_number','name'=>'Tracking','editable'=>true),
            array('id'=>'selling_currency','name'=>'Currency','option_text'=>$currency_list,'editable'=>true),
            array('id'=>'quantity','name'=>'Quantity','option_text'=>$quantity_list,'editable'=>true),
            array('id'=>'selling_price','name'=>'Selling Price','editable'=>true),
            array('id'=>'shipping_charges_received','name'=>'Shipment Charges Received','editable'=>true),
            array('id'=>'payment_date','name'=>'Payment Date','is_date'=>'1','editable'=>true),
            array('id'=>'shipment_date','name'=>'Shipment Date','is_date'=>'1','editable'=>true),
            array('id'=>'courier_id','name'=>'Courier Company','option_text'=>$courier_list,'editable'=>true),
            array('id'=>'shipping_charges_paid','name'=>'Shipment Charges Paid','editable'=>true),
            array('id'=>'sales_id','name'=>'Sales ID','editable'=>true),
            array('id'=>'paypal_trans_id','name'=>'Paypal Transaction ID','editable'=>true),
            array('id'=>'sales_fees_pect','name'=>'Store Fee %','editable'=>true),
            array('id'=>'sales_fees_fixed','name'=>'Store Fee Fixed','editable'=>true),
            array('id'=>'paypal_fees_pect','name'=>'Paypal Fee %','editable'=>true),
            array('id'=>'paypal_fees_fixed','name'=>'Paypal Fee Fixed','editable'=>true),
        );
        
        $this->custom_header = array(
            array('id'=>'id','name'=>'ID','hidden'=>'1'),
            array('id'=>'account_id','name'=>'Account','is_ajax'=>'1','option_text'=>$supp_list,'editable'=>true),
            array('id'=>'store_id','name'=>'Store','is_ajax'=>'1','option_text'=>array(),'editable'=>true),
            array('id'=>'product_id','name'=>'Frame','is_ajax'=>'1','option_text'=>array(),'editable'=>true),
            array('id'=>'store_item_id','name'=>'Color','is_ajax'=>'1','option_text'=>array(),'editable'=>true),
            array('id'=>'store_skucode','name'=>'SKU','editable'=>true),
            array('id'=>'buyer_id','name'=>'Buyer ID','editable'=>true),
            array('id'=>'buyer_name','name'=>'Buyer Name','editable'=>true),
            array('id'=>'buyer_address','name'=>'Buyer Address1','editable'=>true),
            array('id'=>'buyer_address2','name'=>'Buyer Address2','editable'=>true),
            array('id'=>'buyer_address3','name'=>'Buyer Address3','editable'=>true),
            array('id'=>'buyer_city','name'=>'Buyer City','editable'=>true),
            array('id'=>'buyer_state','name'=>'Buyer State','editable'=>true),
            array('id'=>'buyer_postcode','name'=>'Buyer Postcode','editable'=>true),
            array('id'=>'buyer_country','name'=>'Buyer Country','editable'=>true),
            array('id'=>'buyer_contact','name'=>'Buyer Contact','editable'=>true),
            array('id'=>'buyer_email','name'=>'Buyer Email','editable'=>true),
            array('id'=>'tracking_number','name'=>'Tracking','editable'=>true),
            array('id'=>'selling_currency','name'=>'Currency','option_text'=>$currency_list,'editable'=>true),
            array('id'=>'quantity','name'=>'Quantity','option_text'=>$quantity_list,'editable'=>true),
            array('id'=>'selling_price','name'=>'Selling Price','editable'=>true),
            array('id'=>'shipping_charges_received','name'=>'Shipment Charges Received','editable'=>true),
            array('id'=>'payment_date','name'=>'Payment Date','is_date'=>'1','editable'=>true),
            array('id'=>'shipment_date','name'=>'Shipment Date','is_date'=>'1','editable'=>true),
            array('id'=>'courier_id','name'=>'Courier Company','option_text'=>$courier_list,'editable'=>true),
            array('id'=>'shipping_charges_paid','name'=>'Shipment Charges Paid','editable'=>true),
            array('id'=>'sales_id','name'=>'Sales ID','editable'=>true),
            array('id'=>'paypal_trans_id','name'=>'Paypal Transaction ID','editable'=>true),
            array('id'=>'sales_fees_pect','name'=>'Store Fee %','editable'=>true),
            array('id'=>'sales_fees_fixed','name'=>'Store Fee Fixed','editable'=>true),
            array('id'=>'paypal_fees_pect','name'=>'Paypal Fee %','editable'=>true),
            array('id'=>'paypal_fees_fixed','name'=>'Paypal Fee Fixed','editable'=>true),
        );
        
        $this->sales_import_header = array(
            array('id'=>'id','name'=>'ID','hidden'=>'1'),
            array('id'=>'type','name'=>'type','value'=>'sales_import','hidden'=>'1'),
            array('id'=>'account_id','name'=>'Account','is_ajax'=>'1','option_text'=>$supp_list,'editable'=>true),
            array('id'=>'marketplace_template','name'=>'MarketPlace','is_ajax'=>'1','option_text'=>array(),'editable'=>true),
            array('id'=>'import_type','name'=>'Type','option_text'=>array('sales'=>'Sales Report','payment'=>'Payment Report')),
            array('id'=>'file','name'=>'File','is_file'=>'1')
        );
        
        $this->sales_courier_header = array(
            array('id'=>'id','name'=>'ID','hidden'=>'1'),
            array('id'=>'type','name'=>'type','value'=>'sales_courier','hidden'=>'1'),
            array('id'=>'courier_id','name'=>'Courier Company','option_text'=>$courier_list,'editable'=>true),
            array('id'=>'shipment_date','name'=>'Shipment Date','is_date'=>'1','editable'=>true)
        );
        
        $temp = json_decode($_COOKIE['shipping_export'],true);
        $this->shipping_export_header = array(
            array('id'=>'id','name'=>'ID','hidden'=>'1'),
            array('id'=>'type','name'=>'type','value'=>'shipping_export','hidden'=>'1'),
            array('id'=>'courier_id','name'=>'Courier Company','option_text'=>$courier_list2,'editable'=>true,'value'=>((!empty($temp['courier_id']))?$temp['courier_id']:"")),
            array('id'=>'fixed_price_currency','name'=>'Fixed Price Currency','option_text'=>$currency_list,'editable'=>true,'value'=>((!empty($temp['fixed_price_currency']))?$temp['fixed_price_currency']:"")),
            array('id'=>'fixed_price_amount','name'=>'Fixed Price Amount','editable'=>true,'value'=>((!empty($temp['fixed_price_amount']))?$temp['fixed_price_amount']:""))
        );
    }
    
    function ajax_save(){
        return $this->ajax_custom_form_save();
    }
    
    function ajax_custom_form_save(){
        $return = array("status"=>"0","message"=>"");
        $id = $this->CI->input->post('id',true);
        
        $col_list = array();
        $value = $this->CI->input->post('value',true);
        
        if(!empty($value['type']) && $value['type']=='sales_import'){
            $return = array("status"=>"0","message"=>"");
            $import_type = $value['import_type'];
            
            if(!empty($value['file'])){
                include_once(APPPATH.'libraries/classes/ImportHelper.php');
                $class = new ImportHelper;
                $file = tempnam(sys_get_temp_dir(), 'sales_import_');
                $data = $value['file'];
                $data = base64_decode($data);
                $data = iconv(mb_detect_encoding($data, "UTF-8,ISO-8859-1"), "UTF-8", $data);
                file_put_contents($file, $data);
                if($import_type=="sales"){
                    $return = $class->sales_import($value['account_id'],$value['marketplace_template'], $file);
                }else if($import_type=="payment"){
                    $return = $class->payment_import($value['account_id'],$value['marketplace_template'], $file);
                }
                unlink($file);
            }
            return $return;
        }else if(!empty($value['type']) && $value['type']=='shipping_export'){
            $return['message'] = "";
            $action = base_url('ajax/sales_entry?method=shipping_export');
            $selection = $this->CI->input->post('selection',true);
            setcookie("shipping_export", json_encode($value), time()+(60*60*24*30), '/');
            $return['func'] = $this->write_js_form($action, array('courier_id'=>$value['courier_id'],'fp_cur'=>$value['fixed_price_currency'],'fp_amt'=>$value['fixed_price_amount'],'selection'=>$selection));
            return $return;
        }else if(!empty($value['type']) && $value['type']=='sales_courier'){
            $selection = $this->CI->input->post('selection',true);
            if(($temp = explode('/', $value['shipment_date'])) && sizeof($temp)==3){
                $value['shipment_date'] = $temp[2].'-'.$temp[1].'-'.$temp[0];
            }
            if(is_array($selection)){
                if($this->CI->db->query('update transactions_cache set courier_id = ?, shipment_date = ? where id in ('.implode(",", $selection).')',array($value['courier_id'],$value['shipment_date']))){
                    $return['status'] = "1";
                }
            }
            return $return;
        }else{
            //check available store_item_id
            $store_item_id = $this->CI->input->post('value[store_item_id]',true);
            $quantity = $this->CI->input->post('value[quantity]',true);
            $prev_tracking_number = "";
            if($id>0 && ($result = $this->CI->db->query('select quantity,tracking_number from transactions_cache where id=? limit 1',$id)) && ($row = $result->row_array())){
                $quantity -= $row['quantity'];
                $prev_tracking_number = $row['tracking_number'];
            }
            /*
            $temp = $this->get_available_quantity($store_item_id);
            if($quantity>$temp){
                $return['message'] = 'Insufficient quantity.';
                return $return;
            }
            */
            
            //check duplicate id
            if(($result = $this->CI->db->query('select id from transactions_cache where store_item_id=? AND sales_id=? AND id<>?
                union distinct 
                select id from transactions where store_item_id=? AND sales_id=? AND id<>?
                limit 1',array($value['store_item_id'],$value['sales_id'],$id,$value['store_item_id'],$value['sales_id'],$id))) && $result->num_rows()){
                $return['message'] = 'Sales exists!';
                return $return;
            }

            if(($temp = explode('/', $value['payment_date'])) && sizeof($temp)==3){
                $value['payment_date'] = $temp[2].'-'.$temp[1].'-'.$temp[0];
            }
            if(($temp = explode('/', $value['shipment_date'])) && sizeof($temp)==3){
                $value['shipment_date'] = $temp[2].'-'.$temp[1].'-'.$temp[0];
            }
            if(trim($prev_tracking_number)=="" && strlen($value['tracking_number'])>0){
                if(($result = $this->CI->db->query('select * from couriers a')) && $result->num_rows()){
                    foreach($result->result_array() as $row){
                        if(strlen(trim($row['pattern']))>0 && preg_match('#^'.trim($row['pattern']).'$#iu', $value['tracking_number'], $matches)){
                            $value['courier_id'] = $row['id'];
                            break;
                        }
                    }
                }
            }
            $field_list = array('account_id','store_item_id','buyer_reference','buyer_id','buyer_name','buyer_address','buyer_address2','buyer_address3','buyer_city','buyer_state','buyer_postcode','buyer_country','buyer_contact','buyer_email','tracking_number','quantity','selling_currency','selling_price','shipping_charges_received','payment_date','shipment_date','courier_id','shipping_charges_paid','sales_id','sales_fees_pect','sales_fees_fixed','paypal_trans_id','paypal_fees_pect','paypal_fees_fixed');
            foreach($field_list as $field){
                if(isset($value[$field])){
                    $col_list[$field] = '`'.$field.'`='.$this->CI->db->escape($value[$field]);
                }
            }
            if($id>0){
                $this->update_query = sprintf('UPDATE transactions_cache SET %s WHERE id="%s"',implode(',',$col_list),$id);
            }else{
                $this->update_query = sprintf('INSERT INTO transactions_cache SET %s',implode(',',$col_list));
            }
            $return = parent::ajax_custom_form_save();
            if($return['status']=='1'){
                $return['return_data'] = $value;
            }
        }
        
        return $return;
    }
    
    function ajax_change_update(){
        $filter_list = array();
        $filter_list[] = ['name'=>'account_id'];
        $filter_list[] = ['name'=>'store_id','query'=>'SELECT a.id,concat(a.name," (",b.name,")") name FROM stores a,warehouses b WHERE a.warehouse_id=b.id AND a.account_id=? ORDER BY name','id'=>'account_id'];
        $filter_list[] = ['name'=>'product_id','query'=>'SELECT b.id,b.name FROM store_item a join warehouse_item wi on wi.id=a.warehouse_item_id 
join products b on wi.product_id=b.id WHERE a.store_id=? GROUP BY b.id ORDER BY name','id'=>'store_id'];
        $filter_list[] = ['name'=>'store_item_id','query'=>'SELECT a2.id,b.code2 name FROM store_item a 
            join warehouse_item wi on wi.id=a.warehouse_item_id 
            join store_item a2 on a2.store_id=a.store_id
            join warehouse_item wi2 on wi2.id=a2.warehouse_item_id and wi2.product_id=wi.product_id
            join option_item b on wi2.item_id=b.id 
            WHERE a.store_id=? AND wi.product_id=? GROUP BY b.id ORDER BY name','id'=>['store_id','product_id']];
        $filter_list[] = ['name'=>'store_skucode','query'=>'SELECT a.store_skucode id, a.store_skucode name FROM store_item a WHERE a.id=? Limit 1','id'=>'store_item_id'];
        $filter_list[] = ['name'=>'selling_currency','query'=>'select b.currency id,b.currency name from stores a join marketplaces b on a.marketplace_id=b.id WHERE a.id=? Limit 1','id'=>'store_id','update_only'=>'1'];
        $filter_list[] = ['name'=>'selling_price','query'=>'SELECT a.selling_price id, a.selling_price name FROM store_item a WHERE a.id=? Limit 1','id'=>'store_item_id'];
        $filter_list[] = ['name'=>'sales_fees_pect','query'=>'SELECT a.sales_fees_pect id, a.sales_fees_pect name FROM stores a WHERE a.id=? Limit 1','id'=>'store_id'];
        $filter_list[] = ['name'=>'sales_fees_fixed','query'=>'SELECT a.sales_fees_fixed id, a.sales_fees_fixed name FROM stores a WHERE a.id=? Limit 1','id'=>'store_id'];
        $filter_list[] = ['name'=>'paypal_fees_pect','query'=>'SELECT a.paypal_fees_pect id, a.paypal_fees_pect name FROM stores a WHERE a.id=? Limit 1','id'=>'store_id'];
        $filter_list[] = ['name'=>'paypal_fees_fixed','query'=>'SELECT a.paypal_fees_fixed id, a.paypal_fees_fixed name FROM stores a WHERE a.id=? Limit 1','id'=>'store_id'];
        $filter_list[] = ['name'=>'marketplace_template','query'=>'SELECT b.sales_template id,b.sales_template name FROM stores a,marketplaces b WHERE a.marketplace_id=b.id AND a.account_id=? AND b.sales_template<>"" GROUP BY b.sales_template ORDER BY length(b.sales_template),b.sales_template','id'=>'account_id'];
        
        $return = parent::ajax_change_update($filter_list);
        /*
        $quantity_list = array('0'=>'0');
        if($return['status']=='1' && !empty($return['data']['store_item_id']['value'])){
            //$temp = $this->get_available_quantity($return['data']['store_item_id']['value']);
            $temp = 99;
            if($temp>0){
                $quantity_list = array();
                for($i=1; $i<=min(100,$temp); $i++){
                    $quantity_list[$i] = $i;
                }
            }
        }
        $return['data']['quantity'] = ['name'=>'quantity','option_text'=>$quantity_list,'value'=>array_shift($quantity_list)];
        */
        return $return;
    }
    
    function ajax_save_transactions(){
        $this->CI->load->library('cmessage');
        
        if(($result = $this->CI->db->query('select * from transactions_cache a')) && $result->num_rows()){
            foreach($result->result_array() as $row){
                if(($result2 = $this->CI->db->query('select id from transactions where store_item_id=? and sales_id=? limit 1',array($row['store_item_id'],$row['sales_id']))) && ($row2 = $result2->row_array())){
                    $col_list = array();
                    $data_list = array();
                    foreach(array('selling_price','shipping_charges_received','shipping_charges_paid','sales_fees_pect','sales_fees_fixed') as $k){
                        $col_list[] = "`".$k."`=?";
                        $data_list[] = $row[$k];
                    }
                    $data_list[] = $row2['id'];
                    $sql = 'UPDATE transactions SET '.implode(",",$col_list).' WHERE id=?';
                    $this->CI->db->query($sql,$data_list);
                    $this->CI->db->query('delete from transactions_cache where id=?',array($row['id']));
                }else{
                    /*
                    //check available store_item_id
                    $temp = $this->get_available_quantity($row['store_item_id'],true);
                    if($row['quantity']>$temp){
                        $this->CI->cmessage->set_message_url('Row no. '.$row['id'].' - Insufficient quantity.','error','sales_entry');
                        continue;
                    }
                    */
                    $col_list = array();
                    foreach($row as $field => $v){
                        if(strtolower($field)!='id' && isset($row[$field])){
                            $col_list[$field] = '`'.$field.'`='.$this->CI->db->escape($row[$field]);
                        }
                    }
                    if($this->CI->db->query(sprintf('INSERT INTO transactions SET %s',implode(',',$col_list)))){
                        $trans_id = $this->CI->db->insert_id();
                        $this->CI->db->query('delete from transactions_cache where id=?',array($row['id']));
                        $this->adjust_quantity(0, ($row['quantity'] * -1), 0, $trans_id, 'S');
                    }
                }
            }
        }
        $this->CI->db->query('truncate table transactions_cache');
        $this->CI->cmessage->set_message_url('Transactions save.','success','sales_entry');
    }
    
    function ajax_shipping_export(){
        $courier_id = $this->CI->input->post('courier_id',true);
        $selection = $this->CI->input->post('selection',true);
        include_once(APPPATH.'libraries/classes/ImportHelper.php');
        $class = new ImportHelper;
        $class->shipping_export($courier_id,$_POST);
        exit;
    }
    
}
