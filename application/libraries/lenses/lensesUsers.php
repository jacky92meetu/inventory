<?php

require_once('lensesMain.php');

class lensesUsers extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('User List'=>''));
        $this->table = "users";
        $this->title = "User List";
        $this->selected_menu = "user_login";
        $this->custom_form = true;
        
        $user_group = array();
        if(($result = $this->CI->db->query('SELECT id,name FROM user_group ORDER BY id'))){
            foreach($result->result_array() as $d){
                $user_group[$d['id']] = $d['name'];
            }
        }
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'username','name'=>'Username','editable'=>true),array('id'=>'name','name'=>'Name','editable'=>true),array('id'=>'user_type','name'=>'Type','noorder'=>false,'option_text'=>$user_group,'editable'=>true));
    }
    
}