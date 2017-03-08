<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Errors extends CI_Controller {

	function index($id='404'){
		$id = $_GET['id'];
		$this->error_404();
	}

	function error_404(){
		show_404();		
	}
}