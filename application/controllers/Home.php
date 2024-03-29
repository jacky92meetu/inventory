<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
    
        public function __construct() {
            parent::__construct();
        }
    
	public function index()
	{
            return $this->view('home');
            /*
            if(!isset($_SESSION['user'])){
                redirect(base_url("/home/login"),'location');
            }
            //$this->output->cache(10);
            $this->cpage->set('selected_menu','home');
            $this->load->view('page-home');
            */
	}
        
        public function view($view = "", $view2 = ""){
            if(!isset($_SESSION['user'])){
                redirect(base_url("/home/login"),'location');
            }
            $name = preg_replace_callback('#_([a-z])#iu',function($matches){return strtoupper($matches[1]);},'lenses'.ucfirst(strtolower($view)));
            $path = APPPATH.'libraries/lenses/'.$name.'.php';
            if(!file_exists($path)){
                redirect(base_url("/"),'location');
            }
            include_once($path);
            if(class_exists($name)){
                $class = new $name;
                if(!empty($view2) && method_exists($class, $view2)){
                    return call_user_func(array($class,$view2), trim($view."/".$view2,"/"));
                }else if(method_exists($class, 'view')){
                    return call_user_func(array($class,'view'), $view);
                }
            }
            redirect(base_url("/"),'location');
        }
        
        public function login(){
            if(isset($_SESSION['user'])){
                redirect(base_url("/"),'location');
            }
            $this->cpage->set_template('no_frame');
            if(($username = $this->input->post_get('username', TRUE))){
                $password = $this->input->post_get('password', TRUE);
                $result = $this->db->query('SELECT * FROM users');
                if(($result = $this->db->query('SELECT * FROM users WHERE username=? AND credential=? LIMIT 1',array($username,$password))) && $result->num_rows()){
                    $temp = $result->result_array();
                    $_SESSION['user'] = array();
                    $_SESSION['user']['id'] = $temp[0]['id'];
                    $_SESSION['user']['username'] = $temp[0]['username'];
                    $_SESSION['user']['name'] = $temp[0]['name'];
                    $_SESSION['user']['user_type'] = $temp[0]['user_type'];
                    $_SESSION['user']['config'] = json_decode($temp[0]['config'],true);
                    
                    $sql = 'select a.priv_id, ifnull(b.code,"") code, priv_status from user_group_privileges a left join user_group_privileges_list b on a.priv_id=b.id where a.group_id=?';
                    $temp2 = array();
                    if(($result = $this->db->query($sql,array($_SESSION['user']['user_type']))) && $result->num_rows()){
                        $temp = $result->result_array();
                        foreach($temp as $r){
                            $temp2[$r['priv_id']] = $r;
                        }
                    }
                    $_SESSION['user']['user_access_list'] = $temp2;
                    
                    redirect(base_url("/"),'location');
                }else{
                    $this->cmessage->set_response_message('Username or password not match!','error');
                }
            }
            //$this->cpage->set_template('no_frame');
            $this->load->view('page-login');
        }
        
        public function live_update(){
            //forex update
            $this->load->library('cbnmforex');
            $this->cbnmforex->update();
            $this->cbnmforex->rate_verification();
        }
        
        public function live_update2(){
            //dashboard update
            require_once(APPPATH."libraries/lenses/lensesHome.php");
            (new lensesHome)->get_data();
            exit;
        }
        
        public function logout(){
            try{
                if(isset($_SESSION['user'])){
                    $_SESSION['user'] = null;
                    unset($_SESSION['user']);
                }
            } catch (Exception $ex) {

            }
            redirect(base_url("/home/login"),'location');
        }
}
