<?php

require_once('lensesMain.php');

class lensesSalesHistory extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Sales History'=>''));
        $this->table = "transactions";
        $this->title = "Sales History";
        $this->selected_menu = "sales_history";
        $this->is_required = false;
        $this->custom_form = true;
        $this->add_btn = false;
        $this->ajax_url = base_url('ajax/sales_history');
        $this->search_query = 'select * from (select a.id
            , b.name account_name
            , g.name store_name
            , d.name product_name
            , e.name option_name
            , c.store_skucode
            , a.buyer_id, a.buyer_name, a.buyer_country, a.tracking_number, a.selling_currency, a.quantity
            , a.selling_price, a.shipping_charges_received, a.payment_date, a.shipment_date
            , f.name courier_name, a.shipping_charges_paid, a.sales_id
            , a.paypal_trans_id, a.sales_fees_pect, a.sales_fees_fixed, a.paypal_fees_pect, a.paypal_fees_fixed
            ,b.id account_id, a.store_item_id, a.courier_id
            from transactions a
            join accounts b on a.account_id=b.id
            join store_item c on a.store_item_id=c.id
            join warehouse_item wi on c.warehouse_item_id=wi.id
            join products d on wi.product_id=d.id
            join option_item e on wi.item_id=e.id
            join couriers f on a.courier_id=f.id
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
            array('id'=>'id','name'=>'ID'),
            array('id'=>'account_id','name'=>'Account'),
            array('id'=>'store_name','name'=>'Store'),
            array('id'=>'product_name','name'=>'Frame'),
            array('id'=>'store_item_id','name'=>'Color'),
            array('id'=>'store_skucode','name'=>'SKU'),
            array('id'=>'buyer_id','name'=>'Buyer ID','editable'=>true),
            array('id'=>'buyer_name','name'=>'Buyer Name','editable'=>true),
            array('id'=>'buyer_country','name'=>'Buyer Country','editable'=>true),
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
    }
    
    function ajax_custom_form(){
        $return = parent::ajax_custom_form();
        
        $quantity_list = array('0'=>'0');
        if(!empty($return['data']['store_item_id']['value'])){
            $temp = $this->get_available_quantity($return['data']['store_item_id']['value']);
            if($temp>0){
                $quantity_list = array();
                for($i=1; $i<=min(100,$temp); $i++){
                    $quantity_list[$i] = $i;
                }
            }
        }
        $return['data']['quantity']['option'] = $quantity_list;
        
        return $return;
    }
    
    function ajax_custom_form_save(){
        $return = array("status"=>"0","message"=>"");
        $id = $this->CI->input->post('id',true);
        
        //check available store_item_id
        $store_item_id = $this->CI->input->post('value[store_item_id]',true);
        $quantity = $this->CI->input->post('value[quantity]',true);
        /*
        $temp = $this->get_available_quantity($store_item_id);
        if($id>0 && ($result = $this->CI->db->query('select quantity from transactions where id=? limit 1',$id)) && ($row = $result->row_array())){
            $quantity -= $row['quantity'];
        }
        if($quantity>$temp){
            $return['message'] = 'Insufficient quantity.';
            return $return;
        }
        */
        $col_list = array();
        $value = $this->CI->input->post('value',true);
        if(($temp = explode('/', $value['payment_date'])) && sizeof($temp)==3){
            $value['payment_date'] = $temp[2].'-'.$temp[1].'-'.$temp[0];
        }
        if(($temp = explode('/', $value['shipment_date'])) && sizeof($temp)==3){
            $value['shipment_date'] = $temp[2].'-'.$temp[1].'-'.$temp[0];
        }
        $field_list = array('buyer_reference','buyer_id','buyer_name','tracking_number','buyer_country','quantity','selling_currency','selling_price','shipping_charges_received','payment_date','shipment_date','courier_id','shipping_charges_paid','sales_id','sales_fees_pect','sales_fees_fixed','paypal_trans_id','paypal_fees_pect','paypal_fees_fixed');
        foreach($field_list as $field){
            if(isset($value[$field])){
                $col_list[$field] = '`'.$field.'`='.$this->CI->db->escape($value[$field]);
            }
        }
        $this->update_query = sprintf('UPDATE transactions SET %s WHERE id="%s"',implode(',',$col_list),$id);
        $return = parent::ajax_custom_form_save();
        if($return['status']=='1'){
            $this->adjust_quantity(0, ($quantity * -1), 0, $id);
        }
        
        return $return;
    }
    
    function ajax_change_update(){
        $filter_list = array();
        $filter_list[] = ['name'=>'account_id'];
        $filter_list[] = ['name'=>'store_name','query'=>'SELECT id,name FROM stores WHERE account_id=? ORDER BY name','id'=>'account_id'];
        $filter_list[] = ['name'=>'product_name','query'=>'SELECT a.id,b.name FROM store_item a join warehouse_item wi on wi.id=a.warehouse_item_id 
join products b on wi.product_id=b.id WHERE a.store_id=? GROUP BY b.id ORDER BY name','id'=>'store_name'];
        $filter_list[] = ['name'=>'store_item_id','query'=>'SELECT a2.id,b.name FROM store_item a 
            join warehouse_item wi on wi.id=a.warehouse_item_id 
            join store_item a2 on a2.store_id=a.store_id
            join warehouse_item wi2 on wi2.id=a2.warehouse_item_id and wi2.product_id=wi.product_id
            join option_item b on wi2.item_id=b.id 
            WHERE a.id=? GROUP BY b.id ORDER BY name','id'=>'product_name'];
        $filter_list[] = ['name'=>'store_skucode','query'=>'SELECT a.store_skucode id, a.store_skucode name FROM store_item a WHERE a.id=? Limit 1','id'=>'store_item_id'];
        $filter_list[] = ['name'=>'selling_currency','query'=>'select b.currency id,b.currency name from stores a join marketplaces b on a.marketplace_id=b.id WHERE a.id=? Limit 1','id'=>'store_name','update_only'=>'1'];
        $filter_list[] = ['name'=>'selling_price','query'=>'SELECT a.selling_price id, a.selling_price name FROM store_item a WHERE a.id=? Limit 1','id'=>'store_item_id'];
        $filter_list[] = ['name'=>'sales_fees_pect','query'=>'SELECT a.sales_fees_pect id, a.sales_fees_pect name FROM stores a WHERE a.id=? Limit 1','id'=>'store_name'];
        $filter_list[] = ['name'=>'sales_fees_fixed','query'=>'SELECT a.sales_fees_fixed id, a.sales_fees_fixed name FROM stores a WHERE a.id=? Limit 1','id'=>'store_name'];
        $filter_list[] = ['name'=>'paypal_fees_pect','query'=>'SELECT a.paypal_fees_pect id, a.paypal_fees_pect name FROM stores a WHERE a.id=? Limit 1','id'=>'store_name'];
        $filter_list[] = ['name'=>'paypal_fees_fixed','query'=>'SELECT a.paypal_fees_fixed id, a.paypal_fees_fixed name FROM stores a WHERE a.id=? Limit 1','id'=>'store_name'];
        
        $return = parent::ajax_change_update($filter_list);
        $quantity_list = array('0'=>'0');
        if($return['status']=='1' && !empty($return['data']['store_item_id']['value'])){
            $temp = $this->get_available_quantity($return['data']['store_item_id']['value']);
            if($temp>0){
                $quantity_list = array();
                for($i=1; $i<=min(100,$temp); $i++){
                    $quantity_list[$i] = $i;
                }
            }
        }
        $return['data']['quantity'] = ['name'=>'quantity','option_text'=>$quantity_list,'value'=>array_shift($quantity_list)];
        
        return $return;
    }
    
    function ajax_delete(){
        $return = false;
        
        $selection = $this->CI->input->post('selection',true);
        if(($result = $this->CI->db->query('select * from transactions a where id in ?',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                $this->adjust_quantity(0, $row['quantity'], 0, $row['id']);
            }
        }
        
        return parent::ajax_delete();
    }
}
