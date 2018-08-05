<?php

require_once('lensesMain.php');

class lensesUserGroupPrivileges extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $id = $this->CI->input->get('id',true);
        if(($result = $this->CI->db->query('SELECT * FROM user_group WHERE id=? LIMIT 1',array($id))) && $result->num_rows() && ($row = $result->row_array())){
            
        }else{
            $this->CI->load->library('cmessage');
            $this->CI->cmessage->set_message_url('ID not found','error','/user_group');
        }
        
        $this->CI->cpage->set('breadcrumb',array('User Group'=>base_url('/user_group'),$row['name'].'\'s Group Privileges'=>''));
        $this->table = "user_group_privileges";
        $this->title = $row['name'].'\'s Group Privileges';
        $this->selected_menu = "user_group";
        $this->custom_form = false;
        $this->add_btn = false;
        $this->delete_btn = false;
        $this->ajax_url = base_url('ajax/'.$this->table.'?id='.$id);
        $this->search_query = sprintf('SELECT * FROM (select a.id,replace(b.description,"|"," > ") description,if(%1$s=1,"1",ifnull(a.priv_status,"0")) priv_status from user_group_privileges a
            left join user_group_privileges_list b on a.priv_id=b.id
            where a.group_id=%1$s) a',$this->CI->db->escape($id));
        
        $sql = sprintf('insert into user_group_privileges(group_id,priv_id,priv_status)
            select %1$s as group_id,a.id priv_id,if(%1$s=1,"1","0") priv_status from user_group_privileges_list a
            where id not in (select priv_id from user_group_privileges where group_id=%1$s)',$this->CI->db->escape($id));
        $this->CI->db->query($sql);
        
        if($id=="1"){
            $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'description','name'=>'Description','filter-sorting'=>'asc'),array('id'=>'priv_status','name'=>'Status','option_text'=>array('1'=>'Enable')));
        }else{
            $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'description','name'=>'Description','filter-sorting'=>'asc'),array('id'=>'priv_status','name'=>'Status','editable'=>true,'option_text'=>array('0'=>'Disable','1'=>'Enable')));
        }
    }
    
}