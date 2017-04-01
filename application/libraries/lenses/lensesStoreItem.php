<?php

require_once('lensesMain.php');

class lensesStoreItem extends lensesMain{
    
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
        
        $this->CI->cpage->set('breadcrumb',array('Stores'=>base_url('/stores'),$row['name'].'\'s Items'=>''));
        $this->table = "store_item";
        $this->title = $row['name'].'\'s Items';
        $this->selected_menu = "stores";
        $this->custom_form = false;
        $this->add_btn = false;
        $this->delete_btn = false;
        $this->extra_btn = array();
        $this->extra_btn[] = array('name'=>'Show Products Only','url'=>base_url('/store_item_product?id='.$id));
        $this->ajax_url = base_url('ajax/'.$this->table.'?id='.$id);
        $this->search_query = sprintf('SELECT * FROM (select a.id,c.name,d.name option_name,a.store_skucode,a.selling_price
            ,a.marketplace_item_id,a.marketplace_variation,a.item_status,a.marketplace_item_name,a.marketplace_variation_order,a.marketplace_item_label
            ,a.discount_price,a.expire_date from store_item a
            join warehouse_item b on a.warehouse_item_id=b.id
            join products c on b.product_id=c.id
            join option_item d on b.item_id=d.id
            where a.store_id=%s) a',$this->CI->db->escape($id));
        $this->parent_id = array('key'=>'store_id','value'=>$id);
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Frame Model','custom_col'=>'adj_frame'),array('id'=>'option_name','name'=>'Color'),array('id'=>'store_skucode','name'=>'SKU Code','editable'=>true),array('id'=>'selling_price','name'=>'Selling Price','editable'=>true),array('id'=>'marketplace_item_id','name'=>'Item ID','editable'=>true),array('id'=>'marketplace_variation','name'=>'Variation Desc','editable'=>true),array('id'=>'item_status','name'=>'Enable?','editable'=>true,'option_text'=>array('0'=>'Disabled','1'=>'Enabled')));
    }
    
    function ajax_custom_form(){
        $data = array();
        if(strlen($temp = $this->CI->input->post('type',true))>0 && $temp=="adj_frame"){
            $data['type'] = ['id'=>'type','name'=>'Type','value'=>$temp,'hidden'=>'1'];
            $data['id'] = ['id'=>'id','name'=>'ID','value'=>'','hidden'=>'1'];
            $data['name'] = ['id'=>'name','name'=>'Frame Model','value'=>'','readonly'=>'1'];
            $data['marketplace_item_id'] = ['id'=>'marketplace_item_id','name'=>'Item ID','value'=>''];
            $data['marketplace_item_name'] = ['id'=>'marketplace_item_name','name'=>'Item Name','value'=>''];
            $data['marketplace_variation_order'] = ['id'=>'marketplace_variation_order','name'=>'Ebay Variation Order','value'=>''];
            $data['marketplace_item_label'] = ['id'=>'marketplace_item_label','name'=>'Ebay Item Label','value'=>''];
        }
        $return = parent::ajax_custom_form($data);
        
        return $return;
    }
    
    function ajax_custom_form_save(){
        $return = false;
        if($this->CI->input->post('value[type]',true)=="adj_frame"){
            $id = $_POST['value']['id'];
            if(($result = $this->CI->db->query('select c.id from store_item a
                join warehouse_item b on a.warehouse_item_id=b.id
                join store_item c on a.store_id=c.store_id
                join warehouse_item d on c.warehouse_item_id=d.id and b.product_id=d.product_id
                where a.id=?',array($id))) && $result->num_rows()){
                $temp = array();
                foreach($result->result_array() as $r){
                    $temp[$r['id']] = $r['id'];
                }
                if(sizeof($temp)>0){
                    if($this->CI->db->query('UPDATE store_item SET marketplace_item_id=?,marketplace_item_name=?,marketplace_variation_order=?,marketplace_item_label=? WHERE id in ('.implode(',', $temp).')',array($_POST['value']['marketplace_item_id'],$_POST['value']['marketplace_item_name'],$_POST['value']['marketplace_variation_order'],$_POST['value']['marketplace_item_label']))){
                        $return = array("status"=>"1","message"=>"");    
                    }
                }
            }
        }
        
        return $return;
    }
    
}