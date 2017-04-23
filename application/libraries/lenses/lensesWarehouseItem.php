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
        $this->freezePane = 4;
        $this->custom_form = false;
        $this->add_btn = false;
        $this->delete_btn = false;
        $this->ajax_url = base_url('ajax/'.$this->table.'?id='.$id);
        $this->search_query = sprintf('select * from (select a.id,b.name product_name,c.name option_name,a.skucode,a.quantity,a.quantity2,a.cost_price,a.selling_price
,a.min_qty min_qty
,a.stop_qty stop_qty
,if(if(a.stop_qty>0,a.stop_qty,s2.value)>=(a.quantity+a.quantity2) and wih.id is not null,"stop",if(if(a.min_qty>0,a.min_qty,s1.value)>=(a.quantity+a.quantity2) and wih.id is not null,"warning",if(wih.id is null,"new","normal"))) qstatus
,a.product_id,a.item_id
from warehouse_item a
join products b on a.product_id=b.id
join option_item c on a.item_id=c.id and c.type="1"
left join settings s1 on s1.code="min_qty" 
left join settings s2 on s2.code="stop_qty"
left join warehouse_item_history wih on wih.warehouse_item_id=a.id
where a.warehouse_id=%s group by a.id) a',$this->CI->db->escape($id));
        $this->parent_id = array('key'=>'warehouse_id','value'=>$id);
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'product_name','name'=>'Frame Model'),array('id'=>'option_name','name'=>'Color'),array('id'=>'skucode','name'=>'SKU Code','editable'=>true),array('id'=>'quantity','name'=>'Available Quantity','editable'=>true,'custom_col'=>'adj_quantity'),array('id'=>'quantity2','name'=>'Defected Qty','editable'=>true),array('id'=>'cost_price','name'=>'Cost Price','editable'=>true),array('id'=>'selling_price','name'=>'Selling Price','editable'=>true),array('id'=>'min_qty','name'=>'Min Qty','editable'=>true),array('id'=>'stop_qty','name'=>'Stop Qty','editable'=>true),array('id'=>'qstatus','name'=>'Quantity Status','option_text'=>array('stop'=>'Stop','warning'=>'Warning','new'=>'New','normal'=>'Normal')));
    }
    
    function ajax_save(){
        $return = parent::ajax_save();
        if($return && $return['status']=="1" && isset($return['record_id'])){
            $sql = 'INSERT INTO warehouse_item_history(warehouse_item_id,quantity,cost_price,selling_price,expire_date,quantity2,quantity3) 
                    SELECT id,quantity,cost_price,selling_price,expire_date,quantity2,quantity3 FROM warehouse_item WHERE id = ?';
            $this->CI->db->query($sql,array($return['record_id']));
        }
        return $return;
    }
    
    function ajax_custom_form(){
        $data = array();
        if(strlen($temp = $this->CI->input->post('type',true))>0 && $temp=="adj_quantity"){
            $warehouse_list = array();
            $cur_warehouse_id = $this->CI->input->get('id',0);
            if(($result = $this->CI->db->query('SELECT id,name FROM warehouses WHERE id<>? ORDER BY name',array($cur_warehouse_id)))){
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
            $data['quantity'] = ['id'=>'quantity','name'=>'Current Quantity','value'=>'','readonly'=>'1'];
            $data['quantity2'] = ['id'=>'quantity2','name'=>'Defected Quantity','value'=>'','readonly'=>'1'];
            $data['adj_quantity'] = ['id'=>'adj_quantity','name'=>'Quantity Adjustment','value'=>'','optional'=>'1'];
            $data['adj_quantity2'] = ['id'=>'adj_quantity2','name'=>'Defected Quantity Adjustment','value'=>'','optional'=>'1'];
            $data['transfer_warehouse'] = ['id'=>'transfer_warehouse','name'=>'Warehouse Transfer','option_text'=>$warehouse_list,'value'=>'','optional'=>'1'];
            $data['transfer_quantity'] = ['id'=>'transfer_quantity','name'=>'Quantity Transfer','value'=>'','optional'=>'1'];
            $data['transfer_quantity2'] = ['id'=>'transfer_quantity2','name'=>'Defected Quantity Transfer','value'=>'','optional'=>'1'];
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
                $id = intval($this->CI->input->post('id',true));
                if($this->adjust_quantity($id, ($quantity1*-1), ($quantity2*-1))){
                    if(($result = $this->CI->db->query('SELECT id FROM warehouse_item WHERE warehouse_id=? AND product_id=? AND item_id=? LIMIT 1',array($transfer_warehouse,$product_id,$item_id))) && $result->num_rows() && ($row = $result->row_array())){
                        $id = intval($row['id']);
                        if($this->adjust_quantity($id, $quantity1, $quantity2)){
                            $return = array("status"=>"1","message"=>"");
                        }
                    }
                }
            }
        }
        
        return $return;
    }
    
}