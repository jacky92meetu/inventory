<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class CPage {

    var $layout;
    var $layout_path;
    var $template_data;
    var $template_name;
    var $javascript;
    var $stylesheet;
    var $html_title;
    var $page_title;
    var $sub_menu;

    function __construct() {
        $this->CI = & get_instance();
        $this->layout_path = config_item('output_layout_path');
        $this->template_name = config_item('output_template_name');
        $this->layout = config_item('output_layout_folder');
        $this->template_data = array();
        $this->javascript = array();
        $this->stylesheet = array();
        $this->html_title = "";
        $this->page_title = "";
        //$this->set_javascript("cpage.js");
        //$this->set_stylesheet("cpage.css");
    }

    function set_javascript($str) {
        if (!ISSET($this->javascript[$str])) {
            $this->javascript[$str] = $str;
        }
    }

    function get_javascript() {
        return $this->javascript;
    }

    function set_html_title($str) {
        $this->html_title = $str;
    }

    function get_html_title() {
        return $this->html_title;
    }

    function set_page_title($str) {
        $this->page_title = $str;
    }

    function get_page_title() {
        return $this->page_title;
    }

    function set_stylesheet($str) {
        if (!ISSET($this->stylesheet[$str])) {
            $this->stylesheet[$str] = $str;
        }
    }

    function get_stylesheet() {
        return $this->stylesheet;
    }

    function set_sub_menu($data = array()) {
        if (sizeof($data) > 0) {
            foreach ($data as $key => $url) {
                $this->sub_menu[$key] = $url;
            }
        }
    }

    function get_sub_menu() {
        return $this->sub_menu;
    }

    function get_path($file = "") {
        $temp = $this->layout_path . $this->get_layout() . "/" . $file;
        if (is_dir($temp)) {
            return $temp;
        }

        return false;
    }

    function get_layout() {
        if (empty($this->layout) || !is_dir($this->layout_path . "/" . $this->layout)) {
            $this->set_layout('default');
        }
        return $this->layout;
    }

    function set_layout($layout = 'default') {
        $this->layout = $layout;
    }

    function get_template() {
        if (!is_file($this->layout_path . DS . $this->get_layout() . DS . $this->template_name . EXT)) {
            //$this->template_name = "index";
            show_error("Template not found!");
        }
        return $this->template_name;
    }

    function set_template($template) {
        $this->template_name = $template;
        $this->get_template();
    }

    function set($name, $value) {
        $this->template_data[$name] = $value;
    }

}

?>