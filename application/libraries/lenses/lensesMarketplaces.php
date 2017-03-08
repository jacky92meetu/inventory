<?php

require_once('lensesMain.php');

class lensesMarketplaces extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Market Places'=>''));
        $this->table = "marketplaces";
        $this->title = "Market Places";
        $this->selected_menu = "marketplace";
        $this->custom_form = false;
        $this->add_btn = false;
        $this->delete_btn = false;
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Name'),array('id'=>'currency','name'=>'Currency'));
    }
    
}