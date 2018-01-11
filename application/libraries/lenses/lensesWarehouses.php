<?php

require_once('lensesMain.php');

class lensesWarehouses extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Warehouses'=>''));
        $this->table = "warehouses";
        $this->title = "Warehouses";
        $this->selected_menu = "warehouses";
        $this->extra_btn = array();
        $this->extra_btn[] = array('name'=>'Item Transfer','custom_form'=>'item_transfer');
        $this->custom_form = false;
        
        $supp_list = array();
        if(($result = $this->CI->db->query('SELECT id,name FROM suppliers ORDER BY name'))){
            foreach($result->result_array() as $value){
                $supp_list[$value['id']] = $value['name'];
            }
        }
        
        $option_list = array();
        if(($result = $this->CI->db->query('SELECT id,name FROM options ORDER BY name'))){
            foreach($result->result_array() as $value){
                $option_list[$value['id']] = $value['name'];
            }
        }
        
        $warehouse_list = array();
        if(($result = $this->CI->db->query('SELECT id,name FROM warehouses ORDER BY name'))){
            foreach($result->result_array() as $value){
                $warehouse_list[$value['id']] = $value['name'];
            }
        }
        
        $quantity_list = array('0'=>'0');
        for($i=1; $i<=100; $i++){
            $quantity_list[$i] = $i;
        }
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Warehouse Name','editable'=>true,'goto'=>base_url('/warehouse_item')),array('id'=>'allow_combo','name'=>'Allow Combo Quantity','option_text'=>array('N'=>'Disable','Y'=>'Enable'),'editable'=>true,'value'=>'N'));
        
        $this->item_transfer_header = array(
            array('id'=>'type','name'=>'type','hidden'=>'1','value'=>'item_transfer'),
            array('id'=>'from_warehouse','name'=>'From Warehouse','is_ajax'=>'1','option_text'=>$warehouse_list,'editable'=>true,'form_class'=>'col-md-6 pull-left'),
            array('id'=>'to_warehouse','name'=>'To Warehouse','option_text'=>$warehouse_list,'editable'=>true,'form_class'=>'col-md-6 pull-right'),
            array('id'=>'from_product','name'=>'From Frame','is_ajax'=>'1','option_text'=>array(),'editable'=>true,'form_class'=>'col-md-6 pull-left'),
            array('id'=>'transfer_quantity','name'=>'Storage A Quantity Transfer','option_text'=>$quantity_list,'editable'=>true,'form_class'=>'col-md-6 pull-right'),
            array('id'=>'from_item','name'=>'From Color','is_ajax'=>'1','option_text'=>array(),'editable'=>true,'form_class'=>'col-md-6 pull-left'),
            array('id'=>'transfer_quantity2','name'=>'Storage B Quantity Transfer','option_text'=>$quantity_list,'editable'=>true,'form_class'=>'col-md-6 pull-right'),
            array('id'=>'from_skucode','name'=>'From SKU Code','readonly'=>'1','form_class'=>'col-md-6 pull-left'),
        );
    }
    
    function ajax_save(){
        $result = parent::ajax_save();
        if($result['status']=='1' && isset($result['record_id'])){
            $sql = 'insert into warehouse_item(warehouse_id,product_id,item_id,quantity,skucode)
                select "'.$result['record_id'].'",a.id,c.id item_id,0,concat(a.code,"-",c.code) skucode from products a
                join options b on a.option_id=b.id
                join option_item c on a.option_id=c.option_id
                left join warehouse_item d on d.product_id=a.id and item_id=c.id and d.warehouse_id="'.$result['record_id'].'"
                where d.id is null ORDER BY a.id,c.id';
            $this->CI->db->query($sql);
        }
        return $result;
    }
    
    function ajax_custom_form(){
        $data = array();
        if(strlen($temp = $this->CI->input->post('type',true))>0 && $temp=="item_transfer"){
            $data = $this->item_transfer_header;
        }
        $return = parent::ajax_custom_form($data);
        
        return $return;
    }
    
    function ajax_custom_form_save(){
        $return = array("status"=>"0","message"=>"No record to be save.");
        if($this->CI->input->post('value[type]',true)=="item_transfer"){
            /*transfer*/
            $from_warehouse = intval($this->CI->input->post('value[from_warehouse]',true));
            $product_id = intval($this->CI->input->post('value[from_product]',true));
            $item_id = intval($this->CI->input->post('value[from_item]',true));
            $to_warehouse = intval($this->CI->input->post('value[to_warehouse]',true));
            $quantity1 = intval($this->CI->input->post('value[transfer_quantity]',true));
            $quantity2 = intval($this->CI->input->post('value[transfer_quantity2]',true));
            if(!empty($quantity1) || !empty($quantity2)){
                $result = $this->CI->db->query('SELECT id FROM warehouse_item WHERE warehouse_id=? AND product_id=? AND item_id=? LIMIT 1',array($from_warehouse,$product_id,$item_id));
                $result2 = $this->CI->db->query('SELECT id FROM warehouse_item WHERE warehouse_id=? AND product_id=? AND item_id=? LIMIT 1',array($to_warehouse,$product_id,$item_id));
                if($result && $result->num_rows() && ($row = $result->row_array())){
                    if($result2 && $result2->num_rows() && ($row2 = $result2->row_array())){
                        if($this->adjust_quantity($row['id'], ($quantity1*-1), ($quantity2*-1),$row2['id'],'T')){
                            if($this->adjust_quantity($row2['id'], $quantity1, $quantity2,$row['id'],'U')){
                                $return = array("status"=>"1","message"=>"");
                            }
                        }
                    }
                }
            }
        }else{
            $return = parent::ajax_custom_form_save();
            if($return['status']=='1'){
                $this->update_store();
            }
        }
        
        return $return;
    }
    
    function ajax_change_update(){
        $filter_list = array();
        $filter_list[] = ['name'=>'from_warehouse'];
        $filter_list[] = ['name'=>'from_product','query'=>'SELECT b.id,b.name FROM warehouse_item a join products b on b.id=a.product_id WHERE a.warehouse_id=? ORDER BY name','id'=>'from_warehouse'];
        $filter_list[] = ['name'=>'from_item','query'=>'SELECT b.id,b.name FROM warehouse_item a join option_item b on b.id=a.item_id WHERE a.warehouse_id=? and a.product_id=? ORDER BY name','id'=>['from_warehouse','from_product']];
        $filter_list[] = ['name'=>'from_skucode','query'=>'SELECT a.skucode id, a.skucode name FROM warehouse_item a WHERE a.warehouse_id=? and a.product_id=? and a.item_id=? Limit 1','id'=>['from_warehouse','from_product','from_item']];
        $filter_list[] = ['name'=>'to_warehouse','query'=>'SELECT a.id,a.name FROM warehouses a WHERE a.id<>? ORDER BY name','id'=>'from_warehouse'];
        
        $return = parent::ajax_change_update($filter_list);
        
        return $return;
    }
    
    function ajax_delete(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        if(($result = $this->CI->db->query('select * from '.$this->table.' a where id in ?',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                if(($result2 = $this->CI->db->query('select c.id from warehouse_item c 
                    left join store_item b on b.warehouse_item_id=c.id
                    left join transactions a on a.store_item_id=b.id
                    where (a.id is not null or b.id is not null) and c.warehouse_id=? LIMIT 1',array($row['id']))) && $result2->num_rows()){
                    $return['message'].= 'Delete Fail! Some data required "'.$row['name'].'".
    ';
                }else{
                    $this->CI->db->query('DELETE FROM warehouse_item WHERE warehouse_id=?',array($row['id']));
                    if($this->CI->db->query('DELETE FROM '.$this->table.' WHERE id=?',array($row['id']))){
                        $return['status'] = "1";
                    }
                }
            }
        }
        return $return;
    }
    
}