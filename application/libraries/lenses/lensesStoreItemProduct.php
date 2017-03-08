<?php

require_once('lensesMain.php');

class lensesStoreItemProduct extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $id = $this->CI->input->get('id',true);
        if(($result = $this->CI->db->query('SELECT * FROM stores WHERE id=? LIMIT 1',array($id))) && $result->num_rows() && ($row = $result->row_array())){
            
        }else{
            $this->CI->load->library('cmessage');
            $this->CI->cmessage->set_message_url('ID not found','error','/stores');
        }
        
        $this->CI->cpage->set('breadcrumb',array('Stores'=>base_url('/stores'),$row['name'].'\'s Items'=>base_url('/store_item?id='.$id),$row['name'].'\'s Product Only'=>''));
        $this->table = "store_item";
        $this->title = $row['name'].'\'s Product Only';
        $this->selected_menu = "stores";
        $this->custom_form = true;
        $this->add_btn = false;
        $this->delete_btn = false;
        $this->ajax_url = base_url('ajax/store_item_product?id='.$id);
        $this->search_query = sprintf('SELECT * FROM (select a.id,c.name
            ,a.marketplace_item_id,a.marketplace_item_name,a.marketplace_variation_order
            ,a.store_id,b.product_id
            from store_item a
            join warehouse_item b on a.warehouse_item_id=b.id
            join products c on b.product_id=c.id
            where a.store_id=%s group by b.product_id) a',$this->CI->db->escape($id));
        $this->parent_id = array('key'=>'store_id','value'=>$id);
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Frame Model'),array('id'=>'marketplace_item_id','name'=>'Item ID','editable'=>true),array('id'=>'marketplace_item_name','name'=>'Item Name','editable'=>true),array('id'=>'marketplace_variation_order','name'=>'Ebay Variation Order','editable'=>true));
    }
    
    function ajax_custom_form(){
        $data = array();
        if(true){
            $data['type'] = ['id'=>'type','name'=>'Type','value'=>$temp,'hidden'=>'1'];
            $data['id'] = ['id'=>'id','name'=>'ID','value'=>'','hidden'=>'1'];
            $data['store_id'] = ['id'=>'store_id','name'=>'store_id','value'=>'','hidden'=>'1'];
            $data['product_id'] = ['id'=>'product_id','name'=>'product_id','value'=>'','hidden'=>'1'];
            $data['name'] = ['id'=>'name','name'=>'Frame Model','value'=>'','readonly'=>'1'];
            $data['marketplace_item_id'] = ['id'=>'marketplace_item_id','name'=>'Item ID','value'=>''];
            $data['marketplace_item_name'] = ['id'=>'marketplace_item_name','name'=>'Item Name','value'=>''];
            $data['marketplace_variation_order'] = ['id'=>'marketplace_variation_order','name'=>'Ebay Variation Order','value'=>''];
        }
        $return = parent::ajax_custom_form($data);
        
        return $return;
    }
    
    function ajax_custom_form_save(){
        $return = false;
        if(true){
            $store_id = $_POST['value']['store_id'];
            $product_id = $_POST['value']['product_id'];
            if(($result = $this->CI->db->query('select a.id from store_item a
                join warehouse_item b on a.warehouse_item_id=b.id
                where a.store_id=? AND b.product_id=?',array($store_id,$product_id))) && $result->num_rows()){
                $temp = array();
                foreach($result->result_array() as $r){
                    $temp[$r['id']] = $r['id'];
                }
                if(sizeof($temp)>0){
                    if($this->CI->db->query('UPDATE store_item SET marketplace_item_id=?,marketplace_item_name=?,marketplace_variation_order=? WHERE id in ('.implode(',', $temp).')',array($_POST['value']['marketplace_item_id'],$_POST['value']['marketplace_item_name'],$_POST['value']['marketplace_variation_order']))){
                        $return = array("status"=>"1","message"=>"");    
                    }
                }
            }
        }
        
        return $return;
    }
    
}