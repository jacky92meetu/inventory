<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Output extends CI_Output {

    var $isajax;
    var $force_ajax;

    function __construct() {
        $this->force_ajax = 0;
        $this->isajax = (ISSET($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
        parent::__construct();
    }

    function set_ajax($value = 1) {
        $this->force_ajax = $value;
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();
    }

    function check_ajax_request() {
        if ($this->force_ajax || $this->isajax) {
            return true;
        }
        return false;
    }

    function show_modules($position) {
        $data = "";
        $file = APPPATH . "modules/" . strtolower($position) . EXT;
        if (is_file($file)) {
            $contents = "";
            ob_start();
            include_once($file);
            $data = $data . ob_get_contents();
            ob_end_clean();
        }
        return $data;
    }

    function _display($output = '') {
        if ($this->check_ajax_request()) {
            $data = $this->get_output();
            $output = ob_get_contents();
            if ($output) {
                ob_end_clean();
            }
            $data = $output . $data;
            $output = null;
            $javascript = "";
            preg_match_all('#<script.*</script>#smiU', $data, $matches);
            $matches = $matches[0];
            if ($matches) {
                $javascript = $matches;
                for ($i = 0; $i < sizeof($matches); $i++) {
                    $data = str_replace($matches[$i], '', $data);
                }
            }
            //$data = htmlentities($data);
            $result = array('status' => 1, 'data' => $data, 'javascript' => $javascript);
            print json_encode($result);
        } else {
            if (!class_exists('CI_Controller') && strlen($output) > 0) {
                return parent::_display($output);
            }
            $this->CI = & get_instance();
            $this->CI->load->library('cpage');
            $data = $this->CI->load->template($this->CI->cpage->get_template(), $this->CI->cpage->template_data, TRUE);
            if (!$data) {
                return false;
            }

            $replace = array();
            $matches = array();
            if (preg_match_all('#<module\ type="([^"]+)" .*\/>#iU', $data, $matches)) {
                $matches[0] = array_reverse($matches[0]);
                $matches[1] = array_reverse($matches[1]);

                $count = count($matches[1]);

                for ($i = 0; $i < $count; $i++) {
                    $replace[$i] = $this->show_modules($matches[1][$i]);
                }

                $data = str_replace($matches[0], $replace, $data);
            }

            $this->set_output($data);
            $data = null;
            parent::_display();
        }
    }

}
