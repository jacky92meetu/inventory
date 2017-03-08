<?php

require_once('lensesMain.php');

class lensesSettings extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Global Settings'=>''));
        $this->table = "settings";
        $this->title = "Global Settings";
        $this->selected_menu = "global_setting";
        $this->custom_form = true;
        $this->add_btn = false;
        $this->delete_btn = false;
        $this->search_query = 'select a.id,a.code,a.description,a.value from settings a';
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'code','name'=>'Code'),array('id'=>'description','name'=>'Description'),array('id'=>'value','name'=>'Value','editable'=>true));
    }
    
    function ajax_custom_form(){
        $data = parent::ajax_custom_form();
        
        if(strlen($temp = $this->CI->input->post('id',true))>0 && ($result = $this->CI->db->query('SELECT options FROM settings WHERE id='.$temp.' limit 1'))){
            if(($row = $result->row_array()) && strlen($row['options'])>0){
                $temp = explode("|",$row['options']);
                $data['data']['value']['option'] = array_combine($temp, $temp);
            }
        }
        
        return $data;
    }
    
}