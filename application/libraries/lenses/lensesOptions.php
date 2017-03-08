<?php

require_once('lensesMain.php');

class lensesOptions extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Option Group List'=>''));
        $this->table = "options";
        $this->title = "Option Group List";
        $this->selected_menu = "options";
        $this->custom_form = false;
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Option Group','editable'=>true,'goto'=>  base_url('/option_item')));
    }
    
    function ajax_delete(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        if(($result = $this->CI->db->query('select * from '.$this->table.' a where id in ?',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                if(($result2 = $this->CI->db->query('select * from products a where option_id=? LIMIT 1',array($row['id']))) && $result2->num_rows()){
                    $return['message'].= 'Delete Fail! Some data required "'.$row['name'].'".
    ';
                }else{
                    $this->CI->db->query('DELETE FROM option_item_combo WHERE item_id in (SELECT id FROM option_item WHERE type=2 AND option_id=?)',array($row['id']));
                    $this->CI->db->query('DELETE FROM option_item WHERE option_id=?',array($row['id']));
                    if($this->CI->db->query('DELETE FROM '.$this->table.' WHERE id=?',array($row['id']))){
                        $return['status'] = "1";
                    }
                }
            }
        }
        return $return;
    }
    
}