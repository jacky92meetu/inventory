<?php

class ImportHelper {
    
    function __construct() {
        $this->CI = get_instance();
    }
    
    function get_class($account_id,$type,$file,$method){
        $temp = explode("_",$type);
        $return = array("status"=>"0","message"=>"");
        $class_path = APPPATH.'libraries/classes/import/import'.ucfirst(strtolower($temp[0])).'.php';
        $class_name = 'import'.ucfirst(strtolower($temp[0])).'Class';
        if(file_exists($class_path)){
            include_once($class_path);
            if(class_exists($class_name)){
                $class = new $class_name;
                if(method_exists($class, $method)){
                    $class->account_id = $account_id;
                    $return = call_user_func_array(array($class,$method), array($file,(!empty($temp[1])?$temp[1]:"")));
                }
            }
        }
        return $return;
    }
    
    function payment_import($account_id,$type,$file){
        return $this->get_class($account_id, $type, $file, 'payment_import');
    }
    
    function sales_import($account_id,$type,$file){
        return $this->get_class($account_id, $type, $file, 'sales_import');
    }
    
    function item_import($account_id,$type,$file){
        return $this->get_class($account_id, $type, $file, 'item_import');
    }

    function item_export($account_id,$type){
        return $this->get_class($account_id, $type, "", "item_export");
    }
}

?>