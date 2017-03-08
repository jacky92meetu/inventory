<?php

require_once('lensesMain.php');

class lensesSupplier extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Supplier List'=>''));
        $this->table = "suppliers";
        $this->title = "Supplier List";
        $this->selected_menu = "suppliers";
        $this->custom_form = false;
        $this->ajax_url = base_url('/ajax/supplier');
        
        parent::setup();
    }
    
    function ajax_delete(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        if(($result = $this->CI->db->query('select * from '.$this->table.' a where id in ?',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                if(($result2 = $this->CI->db->query('select * from products a where supp_id=? LIMIT 1',array($row['id']))) && $result2->num_rows()){
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