<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cmessage{
    
    function  __construct() {
		$this->CI =& get_instance();		
	}
	
	function set_message_url($message='',$type='notice',$url=''){
		$this->set_response_message($message, $type);
		if(strlen($url)>0){
			$this->CI->load->helper('url');
			redirect(base_url().$url);
		}
	}
	
	function set_response_message($message='',$type='notice'){
		if(empty($message) || strlen($message)===FALSE){
			return false;
		}
		$data = array("message"=>$message,"type"=>$type);
		$data = serialize($data);
		$_SESSION['response_message'] = $data;
		return true;
	}

	function get_response_message(){
		if(ISSET($_SESSION['response_message']) && !EMPTY($_SESSION['response_message'])){			
			return unserialize($_SESSION['response_message']);
		}		
		return false;
	}
	
	function del_response_message(){
		if(ISSET($_SESSION['response_message'])){			
			$_SESSION['response_message'] = null;
			return true;
		}		
		return false;
	}
}

?>
