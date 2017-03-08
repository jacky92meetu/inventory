<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Loader extends CI_Loader {

    function __construct() {
        $this->CI = & get_instance();
        parent::__construct();
    }

    public function view($view, $vars = array(), $return = FALSE) {
        if(is_bool($vars)){
            $return = $vars;
            $vars = array();
        }
        $CI = & get_instance();
        $path = VIEWPATH . DS . $CI->cpage->get_layout() . DS . $view . EXT;
        if (is_file($path)) {
            $view = $CI->cpage->get_layout() . DS . $view;
        }
        return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
    }

    function template($view, $vars = array(), $return = FALSE) {
        $CI = & get_instance();
        $path = $CI->cpage->get_path() . $view . EXT;
        if (!is_file($path)) {
            return false;
        }
        return $this->_ci_load(array('_ci_view' => $path, '_ci_path' => $path, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
    }

    function admin_view($view, $vars = array(), $return = FALSE) {
        $path = APPPATH . "admin" . DS . "views" . DS . $view . EXT;
        if (!is_file($path)) {
            return false;
        }
        return $this->_ci_load(array('_ci_view' => $path, '_ci_path' => $path, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
    }

}
