<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	$CI =& get_instance();
	$contents = "";
	
	/*
	 * set html title
	 */
	$temp = $CI->cpage->get_html_title();
	$temp = $CI->config->item('site_name').((strlen($temp)>0)?" :: ".$temp:"");	
	$contents .= '
		';		
	$contents .= '<TITLE>'.$temp.'</TITLE>';
	
	/*
	 * set meta
	 */
	$contents .= '
			';
	$contents .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
		
	/*
	 * set stylesheet
	 */
	foreach($CI->cpage->get_stylesheet() as $key => $value){
		if(is_file($value)){

		}else if(is_file(APPPATH."templates/".$CI->cpage->get_layout()."/css/".$value)){
			$value = "/".APPPATH."templates/".$CI->cpage->get_layout()."/css/".$value;
		}else if(is_file(BASEPATH."/assets/".$CI->cpage->get_layout()."/css/".$value)){
			$value = BASEPATH."/assets/".$CI->cpage->get_layout()."/css/".$value;
		}
		$contents .= '
			';		
		$contents .= '<link rel="stylesheet" type="text/css" href="'.$value.'" />';
	}

	/*
	 * set javascript
	 */
	$contents .= '
			';
	
	foreach($CI->cpage->get_javascript() as $key => $value){
		if(is_file($value)){

		}else if(is_file(APPPATH."templates/".$CI->cpage->get_layout()."/js/".$value)){
			$value = "/".APPPATH."templates/".$CI->cpage->get_layout()."/js/".$value;
		}else if(is_file(BASEPATH."/assets/".$CI->cpage->get_layout()."/js/".$value)){
			$value = BASEPATH."/assets/".$CI->cpage->get_layout()."/js/".$value;
		}
		$contents .= '
			';
		$contents .= '<script type="text/javascript" src="'.$value.'"></script>';
	}
	
	// Date in the past
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

	// always modified
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);

	// HTTP/1.0
	header("Pragma: no-cache");
		
	echo $contents;
?>
