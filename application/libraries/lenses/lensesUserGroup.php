<?php

require_once('lensesMain.php');

class lensesUserGroup extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('User Group'=>''));
        $this->table = "user_group";
        $this->title = "User Group";
        $this->selected_menu = "user_group";
        $this->custom_form = false;
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Group Name','editable'=>true,'goto'=>base_url('/user_group_privileges')),array('id'=>'description','name'=>'Group Description','editable'=>true));
    }
    
}