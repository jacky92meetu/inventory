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
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Frame Model','editable'=>true,'goto'=>base_url('/warehouse_item')));
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
    
    function ajax_delete(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        if(($result = $this->CI->db->query('select * from '.$this->table.' a where id in ?',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                if(($result2 = $this->CI->db->query('select * from stores a where warehouse_id=? LIMIT 1',array($row['id']))) && $result2->num_rows()){
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