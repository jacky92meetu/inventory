<?php

require_once('lensesMain.php');

class lensesHome extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
    }
    
    function view($view){
        $this->title = "Dashboard";
        $this->CI->cpage->set_html_title($this->title);
        return $this->CI->load->view('page-home');
    }
}