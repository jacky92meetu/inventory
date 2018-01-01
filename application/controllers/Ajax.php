<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
    }

    public function func($view = "", $view2 = ""){
        ob_start();
        if(isset($_SESSION['user'])){
            $name = preg_replace_callback('#_([a-z])#iu',function($matches){return strtoupper($matches[1]);},'lenses'.ucfirst(strtolower($view)));
            $path = APPPATH.'libraries/lenses/'.$name.'.php';
            if(file_exists($path)){
                include_once($path);
                if(class_exists($name)){
                    $class = new $name;
                    if(!empty($view2) && strlen($view2)>0){
                        $method = "ajax_".$view2;
                    }else if(isset($_REQUEST['method'])){
                        $method = "ajax_".$_REQUEST['method'];
                    }
                    if(method_exists($class, $method)){
                        if(($result = call_user_func(array($class,$method)))){
                            echo json_encode($result);
                        }
                    }
                }
            }
        }
        $result = ob_get_contents();
        ob_end_clean();
        if(empty($result)){
            echo json_encode(array("status"=>"0","message"=>"Please login again!"));
        }else{
            echo $result;
        }
        exit;
    }
}
