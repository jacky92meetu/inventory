<?php

require_once('lensesMain.php');

class lensesCouriers extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Couriers'=>''));
        $this->table = "couriers";
        $this->title = "Couriers";
        $this->selected_menu = "couriers";
        $this->custom_form = false;
        $this->add_btn = false;
        $this->delete_btn = false;
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Name','editable'=>true),array('id'=>'pattern','name'=>'Pattern','editable'=>true,'optional'=>'1'),array('id'=>'export_template','name'=>'Template','readonly'=>'1'));
    }
    
    function ajax_delete(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        if(($result = $this->CI->db->query('select * from '.$this->table.' a where id in ?',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                if(($result2 = $this->CI->db->query('select * from transactions where courier_id=? LIMIT 1',array($row['id']))) && $result2->num_rows()){
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