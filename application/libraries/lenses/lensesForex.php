<?php

require_once('lensesMain.php');

class lensesForex extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Exchange Rate'=>''));
        $this->table = "exchange_rate";
        $this->title = "Exchange Rate";
        $this->selected_menu = "forex";
        $this->custom_form = false;
        $this->extra_btn = array();
        $this->extra_btn[] = array('name'=>'Live update','url'=>base_url('ajax/forex?method=live_update'));
        $this->ajax_url = base_url('ajax/forex');
        
        if(!empty($temp = $this->get_global_config("support_currency"))){
            
        }else{
            $this->CI->load->library('cmessage');
            $this->CI->cmessage->set_message_url('Currency not set!','error','/settings');
        }
        
        $this->default_currency = $this->get_global_config("default_currency");
        $select_list = array();
        $table_list = array();
        $header_list = array();
        $header_list[] = array('id'=>'fdate','name'=>'Date','editable'=>true,'is_date'=>true,'is_date_highlight'=>true,'filter-sorting'=>'desc');
        $temp = explode(",",$temp);
        foreach($temp as $v){
            if(strtolower($this->default_currency) == strtolower($v)){continue;}
            $select_list[] = ',ifnull('.strtolower($v."_rate").',0) '.strtolower($v."_rate");
            $table_list[] = 'LEFT JOIN (SELECT created_date sdate, rate '.strtolower($v."_rate").' FROM exchange_rate WHERE from_code="'.$this->default_currency.'" AND to_code="'.$v.'") T_'.$v.' ON a.fdate=T_'.$v.'.sdate';
            $header_list[] = array('id'=>strtolower($v."_rate"),'name'=>strtoupper($v),'editable'=>true);
        }
        
        $this->search_query = 'select a.fdate '.implode(" ",$select_list).'
            from (SELECT created_date id, created_date fdate FROM exchange_rate group by created_date) a
            '.implode(" ", $table_list);
        
        $this->header = $header_list;
    }
    
    
    function ajax_save(){
        $fdate = date("Y-m-d");
        if(strlen($temp = $this->CI->input->post('value[fdate]',true))>0){
            $temp = explode("/",$temp);
            $fdate = $temp[2].'-'.$temp[1].'-'.$temp[0];
        }
        $query_list = array();
        foreach($this->CI->input->post('value',true) as $key => $value){
            if(stripos($key, '_rate')){
                $temp = explode("_",$key);
                $query_list[] = sprintf('("%s",%s,%s,%s)',$this->default_currency,$this->CI->db->escape(strtoupper($temp[0])),$this->CI->db->escape($fdate),$this->CI->db->escape($value));
            }
        }
        $sql = 'INSERT INTO exchange_rate(from_code,to_code,created_date,rate) VALUES '.implode(",",$query_list).' ON DUPLICATE KEY UPDATE rate=VALUES(rate)';
        $this->update_query = $sql;
        
        return parent::ajax_save();
    }
    
    function ajax_delete(){
        $sql = 'DELETE FROM '.$this->table.' WHERE created_date IN ?';
        $this->delete_query = $sql;
        
        return parent::ajax_delete();
    }
    
    function ajax_live_update(){
        $selection = $this->CI->input->post('selection',true);
        
        $this->CI->load->library('cbnmforex');
        $this->CI->cbnmforex->update();
        
        redirect(base_url('/forex'));
    }
}
