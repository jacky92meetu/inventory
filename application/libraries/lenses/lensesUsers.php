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
        
        $this->custom_header = array(
            array('id'=>'id','name'=>'ID')
            ,array('id'=>'username','name'=>'Username','editable'=>true)
            ,array('id'=>'new_password','name'=>'New Password Here','editable'=>true)
            ,array('id'=>'name','name'=>'Name','editable'=>true)
            ,array('id'=>'user_type','name'=>'Type','noorder'=>false,'option_text'=>$user_group,'editable'=>true)
        );
    }
    
    function ajax_custom_form_save(){
        $value = $this->CI->input->post('value',true);
        $return = parent::ajax_custom_form_save();
        
        if($return['status']=="1" && isset($value['id']) && $value['id']>0 && isset($value['new_password']) && strlen($value['new_password'])>0){
            $this->CI->db->query('UPDATE users SET credential=? WHERE id=?',array($value['new_password'],$value['id']));
        }
        
        return $return;
    }
    
    function ajax_change_password(){
        $return = array("status"=>"0","message"=>"");
        $old_password = $this->CI->input->post('old_password',true);
        $new_password = $this->CI->input->post('new_password',true);
        if(($result = $this->CI->db->query('SELECT id FROM users WHERE username=? AND credential=? LIMIT 1',array($_SESSION['user']['username'],$old_password)))){
            if(($d = $result->row_array()) && $this->CI->db->query('UPDATE users SET credential=? WHERE id=?',array($new_password,$d['id']))){
                $return['status'] = "1";
            }
        }
        echo json_encode($return);
        exit;
    }
}