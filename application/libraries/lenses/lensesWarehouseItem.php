<?php

require_once('lensesMain.php');

class lensesWarehouseItem extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $id = $this->CI->input->get('id',true);
        if(($result = $this->CI->db->query('SELECT * FROM warehouses WHERE id=? LIMIT 1',array($id))) && $result->num_rows() && ($row = $result->row_array())){
            
        }else{
            $this->CI->load->library('cmessage');
            $this->CI->cmessage->set_message_url('ID not found','error','/warehouses');
        }
        
        $this->CI->cpage->set('breadcrumb',array('Warehouses'=>base_url('/warehouses'),$row['name'].'\'s Items'=>''));
        $this->table = "warehouse_item";
        $this->title = $row['name'].'\'s Items';
        $this->selected_menu = "warehouses";
        $this->freezePane = 3;
        $this->custom_form = false;
        $this->add_btn = false;
        $this->delete_btn = false;
        $this->ajax_url = base_url('ajax/'.$this->table.'?id='.$id);
        $this->search_query = sprintf('select * from (select a.id,b.name product_name,c.code2 option_name,a.skucode,a.quantity,a.quantity2,a.cost_price,a.selling_price
,a.min_qty min_qty
,a.stop_qty stop_qty
,if(if(a.stop_qty>0,a.stop_qty,s2.value)>=(a.quantity+a.quantity2) and wih.id is not null,"stop",if(if(a.min_qty>0,a.min_qty,s1.value)>=(a.quantity+a.quantity2) and wih.id is not null,"warning",if(wih.id is null,"new","normal"))) qstatus
,a.product_id,a.item_id
from warehouse_item a
join products b on a.product_id=b.id
join option_item c on a.item_id=c.id
left join settings s1 on s1.code="min_qty" 
left join settings s2 on s2.code="stop_qty"
left join warehouse_item_history wih on wih.warehouse_item_id=a.id
left join warehouses w on a.warehouse_id=w.id
where (ifnull(w.allow_combo,"")="Y" or ifnull(c.type,"0")="1") and a.warehouse_id=%s group by a.id) a',$this->CI->db->escape($id));
        $this->parent_id = array('key'=>'warehouse_id','value'=>$id);
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'product_name','name'=>'Frame Model'),array('id'=>'option_name','name'=>'Color'),array('id'=>'skucode','name'=>'SKU Code','editable'=>true),array('id'=>'quantity','name'=>'Storage A Qty','editable'=>true,'custom_col'=>'adj_quantity'),array('id'=>'quantity2','name'=>'Storage B Qty','editable'=>true),array('id'=>'cost_price','name'=>'Cost Price','editable'=>true),array('id'=>'selling_price','name'=>'Unit/Combo Selling Price','editable'=>true),array('id'=>'min_qty','name'=>'Min Qty','editable'=>true),array('id'=>'stop_qty','name'=>'Stop Qty','editable'=>true),array('id'=>'qstatus','name'=>'Quantity Status','option_text'=>array('stop'=>'Stop','warning'=>'Warning','new'=>'New','normal'=>'Normal')));
    }
    
    function ajax_save(){
        $return = parent::ajax_save();
        if($return && $return['status']=="1" && isset($return['record_id'])){
            if(($result = $this->CI->db->query('SELECT * FROM warehouse_item WHERE id=? LIMIT 1',array($return['record_id']))) && $result->num_rows() && ($row = $result->row_array())){
                $this->adjust_quantity($row['id'], $row['quantity'], $row['quantity2'], 0);
            }
        }
        return $return;
    }
    
    function ajax_custom_form(){
        $data = array();
        if(strlen($temp = $this->CI->input->post('type',true))>0 && $temp=="adj_quantity"){
            $warehouse_list = array();
            $cur_warehouse_id = $this->CI->input->get('id',0);
            if(($result = $this->CI->db->query('SELECT id,name FROM warehouses WHERE id<>? ORDER BY name',array((!empty($cur_warehouse_id)?$cur_warehouse_id:0))))){
                foreach($result->result_array() as $value){
                    $warehouse_list[$value['id']] = $value['name'];
                }
            }
            
            $data['cur_warehouse_id'] = ['id'=>'cur_warehouse_id','name'=>'cur_warehouse_id','value'=>$cur_warehouse_id,'hidden'=>'1'];
            $data['type'] = ['id'=>'type','name'=>'Type','value'=>$temp,'hidden'=>'1'];
            $data['id'] = ['id'=>'id','name'=>'ID','value'=>'','hidden'=>'1'];
            $data['product_id'] = ['id'=>'product_id','name'=>'product_id','value'=>'','hidden'=>'1'];
            $data['item_id'] = ['id'=>'item_id','name'=>'item_id','value'=>'','hidden'=>'1'];
            $data['product_name'] = ['id'=>'product_name','name'=>'Frame Model','value'=>'','readonly'=>'1'];
            $data['option_name'] = ['id'=>'option_name','name'=>'Color','value'=>'','readonly'=>'1'];
            $data['quantity'] = ['id'=>'quantity','name'=>'Storage A Quantity','value'=>'','readonly'=>'1'];
            $data['quantity2'] = ['id'=>'quantity2','name'=>'Storage B Quantity','value'=>'','readonly'=>'1'];
            $data['adj_quantity'] = ['id'=>'adj_quantity','name'=>'Storage A Quantity Adjustment','value'=>'','editable'=>'1','optional'=>'1'];
            $data['adj_quantity2'] = ['id'=>'adj_quantity2','name'=>'Storage B Quantity Adjustment','value'=>'','editable'=>'1','optional'=>'1'];
            $data['transfer_warehouse'] = ['id'=>'transfer_warehouse','name'=>'Warehouse Transfer','option_text'=>$warehouse_list,'value'=>'','optional'=>'1'];
            $data['transfer_quantity'] = ['id'=>'transfer_quantity','name'=>'Storage A Quantity Transfer','value'=>'','editable'=>'1','optional'=>'1'];
            $data['transfer_quantity2'] = ['id'=>'transfer_quantity2','name'=>'Storage B Quantity Transfer','value'=>'','editable'=>'1','optional'=>'1'];
        }
        $return = parent::ajax_custom_form($data);
        
        return $return;
    }
    
    function ajax_custom_form_save(){
        $return = array("status"=>"0","message"=>"No record to be save.");
        if($this->CI->input->post('value[type]',true)=="adj_quantity"){
            $quantity1 = intval($this->CI->input->post('value[adj_quantity]',true));
            $quantity2 = intval($this->CI->input->post('value[adj_quantity2]',true));
            if(!empty($quantity1) || !empty($quantity2)){
                $id = intval($this->CI->input->post('id',true));
                if($this->adjust_quantity($id, $quantity1, $quantity2)){
                    $return = array("status"=>"1","message"=>"");
                }
            }
            /*transfer*/
            $transfer_warehouse = intval($this->CI->input->post('value[transfer_warehouse]',true));
            $product_id = intval($this->CI->input->post('value[product_id]',true));
            $item_id = intval($this->CI->input->post('value[item_id]',true));
            $quantity1 = intval($this->CI->input->post('value[transfer_quantity]',true));
            $quantity2 = intval($this->CI->input->post('value[transfer_quantity2]',true));
            if(!empty($quantity1) || !empty($quantity2)){
                if(($result = $this->CI->db->query('SELECT id FROM warehouse_item WHERE warehouse_id=? AND product_id=? AND item_id=? LIMIT 1',array($transfer_warehouse,$product_id,$item_id))) && $result->num_rows() && ($row = $result->row_array())){
                    $id = intval($this->CI->input->post('id',true));
                    $id2 = intval($row['id']);
                    if($this->adjust_quantity($id, ($quantity1*-1), ($quantity2*-1), $id2, 'T')){
                        if($this->adjust_quantity($id2, $quantity1, $quantity2, 0, 'A')){
                            $return = array("status"=>"1","message"=>"");
                        }
                    }
                }
            }
        }
        
        return $return;
    }
    
}