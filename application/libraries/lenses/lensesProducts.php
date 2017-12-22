<?php

require_once('lensesMain.php');

class lensesProducts extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Products'=>''));
        $this->table = "products";
        $this->title = "Products";
        $this->selected_menu = "products";
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
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Frame Model','editable'=>true),array('id'=>'code','name'=>'Code','editable'=>true),array('id'=>'supp_id','name'=>'Supplier','editable'=>true,'option_text'=>$supp_list),array('id'=>'option_id','name'=>'Option Group','editable'=>true,'option_text'=>$option_list),array('id'=>'item_weight','name'=>'Weight(Kg)','editable'=>true,'optional'=>true),array('id'=>'item_dimension','name'=>'Dimension(HxWxD)','editable'=>true,'optional'=>true));
    }
    
    function ajax_save(){
        $result = parent::ajax_save();
        if($result['status']=='1'){
            $this->update_store();
        }
        return $result;
    }
    
    function ajax_custom_form_save(){
        $return = parent::ajax_custom_form_save();
        if($return['status']=='1'){
            $this->update_store();
        }
        return $return;
    }
    
    function ajax_delete(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        if(($result = $this->CI->db->query('select * from '.$this->table.' a where id in ?',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                if(($result2 = $this->CI->db->query('select * from warehouse_item a where product_id=? LIMIT 1',array($row['id']))) && $result2->num_rows()){
                    $return['message'].= 'Delete Fail! Some data required "'.$row['name'].'".
    ';
                }else{
                    if($this->CI->db->query('DELETE FROM '.$this->table.' WHERE id=?',array($row['id']))){
                        $return['status'] = "1";
                    }
                }
            }
        }
        return $return;
    }
    
}