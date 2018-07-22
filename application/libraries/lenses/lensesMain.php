<?php

error_reporting(0);

class lensesMain{
    
    var $CI = false;
    var $title = 'This Page';
    var $table = '';
    var $header = false;
    var $search_query = '';
    var $insert_query = '';
    var $update_query = '';
    var $delete_query = '';
    var $is_required = true;
    var $recordsTotal = 0;
    var $recordsFiltered = 0;
    var $freezePane = 1;
    var $data = array();
    var $default_length = 50;
    var $custom_form = false;
    var $ajax_url = "";
    var $add_btn = true;
    var $delete_btn = true;
    var $extra_btn = array();
    var $parent_id = false;
    var $selected_menu = '';
    var $custom_view_config = '';
    var $page_view = 'page-view';
    var $extra_filter_header = array();
    var $display_chart = false;
    var $default_date_option = array('td'=>'Today','yd'=>'Yesterday','7d'=>'1 week','21d'=>'3 weeks','30d'=>'30 days','180d'=>'180 days','365d'=>'365 days','cm'=>'Current Month','lm'=>'Last Month','custom'=>'Custom Refer Below:');
    
    function __construct(){
        $this->CI = get_instance();
        $this->CI->db->query('SET NAMES "utf8"');        
        //$this->CI->db->query('SET @@global.time_zone = "'.$this->get_global_config("timezone").':00"');
        $this->CI->db->query('SET @@session.time_zone = "'.$this->get_global_config("timezone").':00"');
        /*
        if(isset($_POST['columns'])){
            array_splice($_POST['columns'], 0, 1);
        }
        if(strlen($temp = $this->CI->input->post('order[0][column]',true))>0){
            $_POST['order'][0]['column'] = (int)$temp - 1;
        }
        */
        $this->default_length = $this->user_config_get('default_length',$this->default_length);
        
        $this->set_timezone();
        
        $this->check_quantity_alert();
    }
        
    function setup() {
        $this->init_header();
    }
    
    function init_header(){
        if(!$this->get_user_access($_SESSION['user']['user_type'],$this->selected_menu)){
            redirect(base_url("/"),'location');
        }
        if(!$this->header){
            $data = array();
            $sql = 'SELECT * FROM '.$this->table;
            if(strlen($this->search_query)>0){
                $sql = $this->search_query;
            }
            $sql .= ' LIMIT 1';
            $result = $this->CI->db->query($sql);
            foreach($result->list_fields() as $key){
                $col_exists = false;
                $temp = array('id'=>$key,'name'=>ucwords(strtolower($key)));
                if(preg_match('#(^id$)|(_date$)|(_gmtdate$)#iu', $key)==0){
                    //$temp['editable'] = true;
                    if(stripos($key, 'date')!==FALSE){
                        $temp['is_date'] = true;
                    }
                }
                $data[] = $temp;
            }
            $this->header = $data;
        }
        $extra_filter_list = array();
        foreach($this->header as $v){
            if(!empty($v['is_date'])){
                $name = $v['id'].'|range_date';
                $extra_filter_list[$name] = array('id'=>$name,'name'=>$v['name'],'option_text'=>$this->default_date_option,'value'=>'','editable'=>true);
                $name = $v['id'].'|from_date';
                $extra_filter_list[$name] = array('id'=>$name,'name'=>$v['name'].' (Custom From Date)','is_date'=>'1','value'=>'','editable'=>true);
                $name = $v['id'].'|to_date';
                $extra_filter_list[$name] = array('id'=>$name,'name'=>$v['name'].' (Custom To Date)','is_date'=>'1','value'=>'','editable'=>true);
            }
        }
        $this->extra_filter_header = array_merge($extra_filter_list,$this->extra_filter_header);
        if(sizeof($this->extra_filter_header)>0){
            foreach($this->user_config_get('extra_filter_'.$this->title, array()) as $k => $v){
                if(isset($this->extra_filter_header[$k])){
                    $this->extra_filter_header[$k]['value'] = $v;
                }
            }
            foreach($this->extra_filter_header as $k => $v){
                if(strpos($v['id'], "|range_date") && $v['value']==""){
                    $v['value'] = 'td';
                    $this->extra_filter_header[$k] = $v;
                }
                if(($t = $this->set_range_date($v['id'], $v['value'])) && sizeof($t)>0){
                    foreach($t as $k2 => $v2){
                        if(isset($this->extra_filter_header[$k2]['value'])){
                            $this->extra_filter_header[$k2]['value'] = $v2;
                        }
                    }
                }
            }
            $temp = array();
            $temp['type'] = array('id'=>'type','name'=>'type','value'=>'extra_filter','hidden'=>'1');
            foreach($this->extra_filter_header as $v){
                $temp[$v['id']] = $v;
            }
            $this->extra_filter_header = $temp;
        }
        list($this->header,$this->freezePane) = $this->set_custom_view();
    }
    
    function set_range_date($k,$v){
        $return = array();
        if(strpos($k, "|range_date") && $v!="custom"){
            $t = explode("|",$k);
            $fdate = "";
            $tdate = "";
            if($v=="td"){
                $tdate = $this->to_display_date();
                $fdate = $this->to_display_date();
            }else if($v=="yd"){
                $tdate = $this->to_display_date("-1 day");
                $fdate = $this->to_display_date("-1 day");
            }else if($v=="7d"){
                $tdate = $this->to_display_date();
                $fdate = $this->to_display_date("-6 day");
            }else if($v=="21d"){
                $tdate = $this->to_display_date();
                $fdate = $this->to_display_date("-20 day");
            }else if($v=="30d"){
                $tdate = $this->to_display_date();
                $fdate = $this->to_display_date("-29 day");
            }else if($v=="180d"){
                $tdate = $this->to_display_date();
                $fdate = $this->to_display_date("-179 day");
            }else if($v=="365d"){
                $tdate = $this->to_display_date();
                $fdate = $this->to_display_date("-364 day");
            }else if($v=="cm"){
                $tdate = $this->to_display_date("last day of this month");
                $fdate = $this->to_display_date("first day of this month");
            }else if($v=="lm"){
                $tdate = $this->to_display_date("last day of last month");
                $fdate = $this->to_display_date("first day of last month");
            }
            $name = $t[0]."|from_date";
            $return[$name] = $fdate;
            $name = $t[0]."|to_date";
            $return[$name] = $tdate;
        }
        return $return;
    }
    
    function set_custom_view($data = false){
        static $custom_view = array();
        static $custom_freezePane = array();
        $header = $this->header;
        $md5_id = 'pageview_'.$this->title;
        
        if(!isset($custom_view[$md5_id])){
            if(($temp = $this->user_config_get($md5_id))){
                list($custom_view[$md5_id],$custom_freezePane[$md5_id]) = $this->process_pageview($temp);
            }else{
                include(dirname(__FILE__).'/config/pageview.php');
                if(isset($pageview[$this->custom_view_config])){
                    list($custom_view[$md5_id],$custom_freezePane[$md5_id]) = $this->process_pageview($pageview[$this->custom_view_config]);
                }else{
                    $custom_view[$md5_id] = $header;
                    $custom_freezePane[$md5_id] = $this->freezePane;
                }
            }
        }
        
        if(is_array($data) && sizeof($data)>0){
            $data_list = array();
            foreach($data as $a){
                $temp_remaining = $header;
                $temp = array();
                foreach($custom_view[$md5_id] as $value){
                    foreach($temp_remaining as $key2 => $value2){
                        if($value['id']==$value2['id'] && array_key_exists($value2['id'], $a)!==FALSE){
                            if(!empty($value2['is_date'])){
                                $value2['value'] = $this->to_display_date($value2['value']);
                            }
                            $temp[] = $a[$value2['id']];
                            unset($a[$value2['id']]);
                            unset($temp_remaining[$key2]);
                            break;
                        }
                    }
                }
                foreach($a as $value){
                    if(!empty($value['is_date'])){
                        $value['value'] = $this->to_display_date($value['value']);
                    }
                    $temp[] = $value;
                }
                $data_list[] = $temp;
            }
            $data = $data_list;
        }else{
            $data = array();
        }
        
        return array($custom_view[$md5_id],$custom_freezePane[$md5_id],$data);
    }
    
    function process_pageview($pageview_list){
        $temp_remaining = $this->header;
        $temp_main = array();
        $freezePane = 0;
        foreach($pageview_list as $key => $value){
            foreach($temp_remaining as $key2 => $value2){
                if($value2['id']==$key){
                    if($value=='1'){
                        $freezePane++;
                    }
                    $temp_main[] = $value2;
                    unset($temp_remaining[$key2]);
                    break;
                }
            }
        }
        foreach($temp_remaining as $value){
            $temp_main[] = $value;
        }
        return array($temp_main,$freezePane);
    }
    
    function view($view){
        if($this->display_chart){
            if(sizeof($this->data)==0){
                $this->ajax_read();
            }
            $this->page_view = 'page-chartview';
        }else{
            $this->init_header();
        }
        if($this->ajax_url==""){
            $this->ajax_url = base_url('ajax/'.$view);
        }
        
        if(!empty($this->data) && sizeof($this->data)>0){
            list($this->header,$this->freezePane,$this->data) = $this->set_custom_view($this->data);
        }
        
        $this->CI->cpage->set_html_title($this->title);
        $this->CI->cpage->set('selected_menu',$this->selected_menu);
        $this->CI->cpage->set('view_title',$this->title);
        $this->CI->cpage->set('view_header',$this->header);
        $this->CI->cpage->set('view_contents',$this->data);
        $this->CI->cpage->set('default_length',$this->default_length);
        $this->CI->cpage->set('view_ajax_url',$this->ajax_url);
        $this->CI->cpage->set('custom_form',$this->custom_form);
        $this->CI->cpage->set('add_btn',$this->add_btn);
        $this->CI->cpage->set('delete_btn',$this->delete_btn);
        $this->CI->cpage->set('extra_btn',$this->extra_btn);
        $this->CI->cpage->set('is_required',$this->is_required);
        $this->CI->cpage->set('freezePane',$this->freezePane);
        $this->CI->cpage->set('extra_filter',((sizeof($this->extra_filter_header)>0)?true:false));
        $this->CI->cpage->set('display_chart',$this->display_chart);
        return $this->CI->load->view($this->page_view);
    }
    
    function ajax_read(){
        $this->init_header();
        
        if(strlen($this->search_query)==0){
            $col_list = array();
            foreach($this->header as $col){
                $col_list[$col['id']] = $col['id'];
            }
            $sql = 'SELECT '.implode(",",$col_list).' FROM '.$this->table;
            $this->search_query = $sql;
        }
        
        $where_query = array();
        if(!empty($this->CI->input->post('columns',true))){
            $count = 0;
            foreach($this->CI->input->post('columns',true) as $c){
                if(strlen($c['search']['value'])>0 && isset($this->header[$count])){
                    if((bool)$c['search']['regex']===true && empty($this->header[$count]['option_text'])){
                        foreach(explode(" ",$c['search']['value']) as $t){
                            $where_query[] = $this->header[$count]['id'].' LIKE "%'.$this->CI->db->escape_like_str($t).'%" ESCAPE "!" ';
                        }
                    }else{
                        $where_query[] = $this->header[$count]['id'].' = '.$this->CI->db->escape($c['search']['value']);
                    }
                }
                $count += 1;
            }
        }
        if($this->extra_filter_header && sizeof($this->extra_filter_header)>0){
            foreach($this->extra_filter_header as $v){
                if(strpos($v['id'],"|range_date")!==false || strlen($v['value'])==0){continue;}
                $col = explode("|",$v['id']);
                foreach($this->header as $v2){
                    if($col[0]==$v2['id']){
                        $operator = ' = ';
                        if(isset($v['is_date'])){
                            $v['value'] = $this->from_display_date($v['value']);
                            if(stripos($col[1], 'from_date')!==false){
                                $operator = ' >= ';
                            }else if(stripos($col[1], 'to_date')!==false){
                                $operator = ' <= ';
                                $v['value'] .= ' 23:59:59';
                            }
                        }
                        $where_query[] = $col[0].$operator.$this->CI->db->escape($v['value']);
                        
                        break;
                    }
                }
            }
        }
        if(sizeof($where_query)>0){
            $where_query = ' WHERE ('.implode(" AND ",$where_query).') ';
        }else{
            $where_query = '';
        }
        $this->search_query = str_replace("{WHERE_AND}", str_replace(" WHERE ", " AND ", $where_query), $this->search_query);
        $this->search_query = str_replace("{WHERE}", $where_query, $this->search_query);
        
        $order_list = array();
        if(!empty($this->CI->input->post('order',true))){
            foreach($this->CI->input->post('order',true) as $t){
                if(isset($t['column']) && isset($this->header[$t['column']])){
                    $order_list[] = $this->header[$t['column']]['id'].' '.$t['dir'];
                }
            }
        }
        if(sizeof($order_list)>0){
            $order_query = ' ORDER BY '.implode(" , ",$order_list);
        }else{
            $order_query = '';
        }
        
        $limit_start = 0;
        $limit_length = 1000;
        $this->recordsTotal = 0;
        $this->recordsFiltered = 0;
        if(!$this->display_chart){
            $limit_start = ((!empty($this->CI->input->post('start',true)))?$this->CI->input->post('start',true):0);
            $limit_length = ((!empty($this->CI->input->post('length',true)))?$this->CI->input->post('length',true):$this->default_length);
            $this->user_config_set('default_length',$limit_length);
            
            $sql = $this->search_query;
            if(stristr($sql, 'COUNT(*)')===FALSE){
                $sql = 'SELECT COUNT(*) as counting FROM ('.$sql.') a';
            }
            if(($result = $this->CI->db->query($sql)) && $result->num_rows()){
                if(($row = $result->row_array())){
                    $this->recordsTotal = $row['counting'];
                }
            }
            
            $sql = $this->search_query;
            if(stristr($sql, 'COUNT(*)')===FALSE){
                $sql = 'SELECT COUNT(*) as counting FROM ('.$sql.') a';
            }
            if(($result = $this->CI->db->query($sql)) && $result->num_rows()){
                if(($row = $result->row_array())){
                    $this->recordsFiltered = $row['counting'];
                }
            }
        }        
        
        $temp_data = array();
        $sql = $this->search_query.$order_query.' LIMIT '.$limit_start.','.$limit_length;
        if(($result = $this->CI->db->query($sql)) && $result->num_rows()){
            $temp = $result->result_array();
            foreach($temp as $r){
                $temp2 = array();
                foreach($r as $k => $c){
                    foreach($this->header as $v3){
                        if($v3['id']==$k){
                            if(!empty($v3['is_date'])){
                                $c = $this->to_display_date($c);
                            }
                            $temp2[$k] = $c;
                            break;
                        }
                    }
                }
                $temp2[] = '';
                $temp_data[] = $temp2;
            }
        }
        
        list($this->header,$this->freezePane,$this->data) = $this->set_custom_view($temp_data);
        
        $temp = array();
        foreach($this->data as $v){
            $temp2 = array();
            foreach($v as $v2){
                $temp2[] = $v2;
            }
            $temp[] = $temp2;
        }
        $this->data = $temp;
        
        if($this->display_chart){
            return $this->data;
        }
        
        $draw = $this->CI->input->post('draw',true);
        $return = array("draw"=>$draw,"recordsTotal"=>$this->recordsTotal,"recordsFiltered"=>$this->recordsFiltered,"data"=>$this->data);
        return $return;
    }
    
    function ajax_custom_form($data = array()){
        $this->init_header();
        $return = array("status"=>"0","message"=>"","data"=>false,"type"=>"");
        
        if(!empty($this->CI->input->post('type',true))){
            $return['type'] = $this->CI->input->post('type',true); 
        }
        
        if(sizeof($data)==0){
            foreach(array((strlen($return['type'])>0?$return['type']:"").'_header','custom_header') as $var){
                if(isset($this->{$var})){
                    $data = $this->{$var};
                    break;
                }
            }
        }
        if(sizeof($data)==0){
            $sql = 'SELECT * FROM '.$this->table;
            if(strlen($this->search_query)>0){
                $sql = $this->search_query;
            }
            $sql .= ' LIMIT 1';
            $result = $this->CI->db->query($sql);
            $avail_key = $result->list_fields();
            foreach($this->header as $col){
                $temp = array('id'=>$col['id'],'name'=>$col['id'],'value'=>'');
                if(array_search($col['id'], $avail_key)!==FALSE){
                    $temp['name'] = $col['name'];
                    $data[$key] = $temp;
                }
            }
        }
        $data = $this->form_generator($data);
                
        if(strlen($this->CI->input->post('id',true))>0 && $this->CI->input->post('id',true)>0){
            $sql = 'SELECT * FROM '.$this->table;
            if(strlen($this->search_query)>0){
                $sql = $this->search_query;
            }
            $sql .= sprintf(' WHERE id=%s LIMIT 1',$this->CI->db->escape($this->CI->input->post('id',true)));
            if(($result = $this->CI->db->query($sql)) && $result->num_rows()){
                if(($row = $result->row_array())){
                    foreach($row as $key => $value){
                        if(isset($data[$key])){
                            $data[$key]['value'] = $value;
                            if(isset($data[$key]['is_date'])){
                                $data[$key]['value'] = $this->to_display_date($data[$key]['value']);
                            }
                        }
                    }
                }
            }
        }else{
            unset($data['id']);
        }
        
        $return['data'] = $data;
        
        return $return;
    }
    
    function form_generator($row_data){
        $data = array();
        
        if(is_array($row_data)){
            foreach($row_data as $col){
                $temp = array('id'=>$col['id'],'name'=>$col['id'],'value'=>'');
                $temp['name'] = $col['name'];
                if(isset($col['is_date'])){
                    $temp['is_date'] = '1';
                    if(empty($temp['value'])){
                        if(empty($temp['value']) || strtotime($temp['value'])<=0 || date("Y-m-d",strtotime($temp['value']))=="1970-01-01"){
                            $temp['value'] = '';
                        }
                    }
                }else if(isset($col['option_text'])){
                    $temp['option_text'] = $col['option_text'];
                }else if(isset($col['is_file'])){
                    $temp['is_file'] = '1';
                }else if(isset($col['is_textarea'])){
                    $temp['is_textarea'] = '1';
                }else if(isset($col['hidden'])){
                    $temp['hidden'] = '1';
                }else if(!isset($col['editable'])){
                    $temp['readonly'] = '1';
                }
                if(isset($col['is_date_highlight'])){
                    $temp['is_date_highlight'] = '1';
                    $temp['value'] = $this->to_display_date();
                }
                if(isset($col['is_ajax'])){
                    $temp['is_ajax'] = '1';
                    if(!isset($temp['option_text'])){
                        $temp['option_text'] = array();
                    }
                }
                if(isset($col['value'])){
                    $temp['value'] = $col['value'];
                }
                if(isset($col['form_class'])){
                    $temp['form_class'] = $col['form_class'];
                }
                if(isset($col['form_divider'])){
                    $temp['form_divider'] = '1';
                }
                if(isset($col['optional'])){
                    $temp['optional'] = '1';
                }
                
                $data[$temp['id']] = $temp;
            }
        }
        
        return $data;
    }
    
    function ajax_custom_form_save(){
        $return = array("status"=>"0","message"=>"");
        if($this->CI->input->post('value[type]',true)=="extra_filter"){
            if(($value = $this->CI->input->post('value',true))){
                $temp2 = array();
                foreach($value as $k => $v){
                    if(($t = $this->set_range_date($k, $v)) && sizeof($t)>0){
                        $value = array_merge($value,$t);
                    }
                }
                foreach($value as $k => $v){
                    if(array_search($k, array('type'))===false){
                        $temp2[$k] = $v;
                    }
                }
                $this->user_config_set('extra_filter_'.$this->title, $temp2);
            }
            $return['status'] = "1";
            return $return;
        }else if($this->CI->input->post('value[type]',true)=="header_change"){
            if(($value = $this->CI->input->post('value',true))){
                $this->user_config_set('pageview_'.$this->title, $value['data']);
            }
            $return['status'] = "1";
            return $return;
        }else if($this->CI->input->post('value[type]',true)=="header_reset"){
            $this->user_config_unset('pageview_'.$this->title);
            $return['status'] = "1";
            return $return;
        }
        $id = 0;
        if(strlen($temp = $this->CI->input->post('value[id]',true))>0){
            $id = $temp;
            unset($_POST['value']['id']);
        }else if(!empty($this->CI->input->post('id',true))){
            $id = $this->CI->input->post('id',true);
        }
        
        if(strlen($this->update_query)>0){
            if(($result = $this->CI->db->query($this->update_query))){
                $return['status'] = "1";
            }
        }else if(strlen($this->update_query)==0 || strlen($this->insert_query)==0){
            $sql = 'SELECT * FROM '.$this->table;
            if(strlen($this->search_query)>0){
                $sql = $this->search_query;
            }
            $sql .= ' LIMIT 1';
            $result = $this->CI->db->query($sql);
            $row = $result->list_fields();
            $col_list = array();
            foreach($this->CI->input->post('value',true) as $key => $value){
                if(array_search($key, $row)!==FALSE && $key!='id'){
                    $col_list[$key] = '`'.$key.'`='.$this->CI->db->escape($value);
                }
            }
            if($this->parent_id && array_key_exists($this->parent_id['key'], $col_list)===FALSE){
                $col_list[$this->parent_id['key']] = '`'.$this->parent_id['key'].'`="'.$this->parent_id['value'].'"';
            }
            if(strlen($this->update_query)==0){
                $sql = 'UPDATE '.$this->table.' SET '.implode(",",$col_list).' WHERE id='.$this->CI->db->escape($id);
                $this->update_query = $sql;
            }
            if(strlen($this->insert_query)==0){
                $sql = 'INSERT INTO '.$this->table.' SET '.implode(",",$col_list);
                $this->insert_query = $sql;
            }

            $sql = 'SELECT id FROM '.$this->table.' WHERE id='.$this->CI->db->escape($id).' LIMIT 1';
            if($id>0 && $this->CI->db->simple_query($sql)){
                //$sql = vsprintf($this->update_query,array_merge($this->CI->input->post('value',true),array($id)));
                if(($result = $this->CI->db->query($this->update_query))){
                    $return['status'] = "1";
                }
            }else if($id=="" || $id==0){
                //$sql = vsprintf($this->insert_query,$this->CI->input->post('value',true));
                if(($result = $this->CI->db->query($this->insert_query))){
                    $return['status'] = "1";
                    $id = $this->CI->db->insert_id();
                }
            }
            $return['record_id'] = $id;
        }
        
        return $return;
    }
    
    function ajax_save(){
        $this->init_header();
        $return = array("status"=>"0","message"=>"");
        $id = $this->CI->input->post('id',true);
        $value = $this->CI->input->post('value',true);
        
        if(strlen($this->update_query)>0){
            if(($result = $this->CI->db->query($this->update_query))){
                $return['status'] = "1";
            }
        }else if(strlen($this->update_query)==0 || strlen($this->insert_query)==0){
            $col_list = array();
            foreach($this->header as $col){
                if(isset($col['editable'])){
                    $col_list[$col['id']] = '`'.$col['id'].'`=?';
                }
            }
            if($this->parent_id && array_key_exists($this->parent_id['key'], $col_list)===FALSE){
                $col_list[$this->parent_id['key']] = '`'.$this->parent_id['key'].'`="'.$this->parent_id['value'].'"';
            }
            if(strlen($this->update_query)==0){
                $sql = 'UPDATE '.$this->table.' SET '.implode(",",$col_list).' WHERE id=?';
                $this->update_query = $sql;
            }
            if(strlen($this->insert_query)==0){
                $sql = 'INSERT INTO '.$this->table.' SET '.implode(",",$col_list);
                $this->insert_query = $sql;
            }
            $sql = 'SELECT id FROM '.$this->table.' WHERE id="'.$id.'" LIMIT 1';
            if($id>0 && $this->CI->db->simple_query($sql)){
                //$sql = vsprintf($this->update_query,array_merge($value,array($id)));
                if(($result = $this->CI->db->query($this->update_query,array_merge($value,array($id))))){
                    $return['status'] = "1";
                }
            }else if($id=="" || $id==0){
                //$sql = vsprintf($this->insert_query,$value);
                if(($result = $this->CI->db->query($this->insert_query,$value))){
                    $return['status'] = "1";
                    $id = $this->CI->db->insert_id();
                }
            }
            $return['record_id'] = $id;
        }
                
        return $return;
    }
    
    function ajax_delete(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        
        if($selection=="ALL"){
            $sql = 'DELETE FROM '.$this->table;
            $this->delete_query = $sql;
        }else if(strlen($this->delete_query)==0){
            $sql = 'DELETE FROM '.$this->table.' WHERE id IN ?';
            $this->delete_query = $sql;
        }
        //$sql = sprintf($this->delete_query,implode(',',$selection));
        if(($result = $this->CI->db->query($this->delete_query,array($selection)))){
            $return['status'] = "1";
        }
        
        return $return;
    }
        
    function ajax_change_update($filter_list = array()){
        $name = $this->CI->input->post('name',true);
        $value = $this->CI->input->post('value',true);
        $reset = $this->CI->input->post('reset',true);
        $pre_data = $this->CI->input->post('pre_data',true);
        if(empty($pre_data)){
            $pre_data = array();
        }
        $pre_data[$name] = $value;
        $return = array("status"=>"0","message"=>"","data"=>false);
        
        if(strlen($reset)>0){
            if(($result = $this->CI->db->query($this->search_query.' WHERE id=? limit 1',array($reset)))){
                foreach($result->result_array() as $v){
                    $reset = $v;
                    break;
                }
            }else{
                $reset = false;
            }
        }
        
        $data = array();
        $start = false;
        
        foreach($filter_list as $key => $list_val){
            if($name==$list_val['name'] || $start){
                $start = true;
                $name = $list_val['name'];
                $supp_list = array();
                $value2 = "";
                if(empty($list_val['query'])){
                    continue;
                }
                $cond_list = array();
                if(is_array($list_val['id'])){
                    foreach($list_val['id'] as $v){
                        array_push($cond_list, (!empty($reset[$v]))?$reset[$v]:$pre_data[$v]);
                    }
                }else{
                    $v = $list_val['id'];
                    array_push($cond_list, (!empty($reset[$v]))?$reset[$v]:$pre_data[$v]);
                }
                if(($result = $this->CI->db->query($list_val['query'],$cond_list))){
                    $count = 0;
                    foreach($result->result_array() as $v){
                        if($count==0){
                            $value2 = $v['id'];
                        }
                        $supp_list[$v['id']] = $v['name'];
                        $count++;
                    }
                }
                $data[$name] = ['name'=>$name,'value'=>((!empty($reset[$name]))?$reset[$name]:((!empty($pre_data[$name]))?$pre_data[$name]:$value2))];
                if(!isset($list_val['update_only'])){
                    $data[$name]['option_text'] = $supp_list;
                }
                $pre_data[$name] = $data[$name]['value'];
            }
        }
        
        if(sizeof($data)>0){
            $return['status'] = '1';
            $return['data'] = $data;
        }
        
        return $return;
    }
    
    function get_global_config($name = "",$return = false){
        static $instance = false;
        if(!$instance){
            $sql = 'SELECT * FROM settings';
            if(($result = $this->CI->db->query($sql)) && $result->num_rows()){
                $instance = array();
                $temp = $result->result_array();
                foreach($temp as $r){
                    $instance[$r['code']] = $r;
                }
            }
        }
        if(strlen($name)>0){
            if(isset($instance[$name])){
                if(!$return){
                    return $instance[$name]['value'];
                }
                return $instance[$name];
            }
            return false;
        }
        return $instance;
    }
    
    function get_user_access($group_id = 0, $priv_id = 0){
        static $instance = array();
        if($group_id==0){
            return true;
        }
        if(!isset($instance[$group_id])){
            if($_SESSION['user']['user_type']==$group_id){
                $instance[$group_id] = $_SESSION['user']['user_access_list'];
            }else{
                $sql = 'select a.priv_id, ifnull(b.code,"") code, priv_status from user_group_privileges a left join user_group_privileges_list b on a.priv_id=b.id where a.group_id=?';
                if(($result = $this->CI->db->query($sql,array($group_id))) && $result->num_rows()){
                    $instance[$group_id] = array();
                    $temp = $result->result_array();
                    foreach($temp as $r){
                        $instance[$group_id][$r['priv_id']] = $r;
                    }
                }
            }
        }
        
        $temp = false;
        if((isset($instance[$group_id]) && array_key_exists($priv_id, $instance[$group_id])!==FALSE)){
            $temp = $instance[$group_id][$priv_id];
        }
        if(!$temp && isset($instance[$group_id])){
            foreach($instance[$group_id] as $v){
                if($v['code']==$priv_id){
                    $temp = $v;
                    break;
                }
            }
        }
        if($temp && $temp['priv_status']=="0"){
            return false;
        }
        return true;
    }
    
    function user_config_unset($name){
        if(!isset($_SESSION['user']['config'])){
            $_SESSION['user']['config'] = array();
        }
        if(isset($_SESSION['user']['config'][$name])){
            unset($_SESSION['user']['config'][$name]);
        }
        $temp = json_encode($_SESSION['user']['config']);
        return $this->CI->db->query('UPDATE users SET config=? WHERE id=? LIMIT 1',array($temp,$_SESSION['user']['id']));
    }
    
    function user_config_set($name,$value){
        if(!isset($_SESSION['user']['config'])){
            $_SESSION['user']['config'] = array();
        }
        $_SESSION['user']['config'][$name] = $value;
        $temp = json_encode($_SESSION['user']['config']);
        return $this->CI->db->query('UPDATE users SET config=? WHERE id=? LIMIT 1',array($temp,$_SESSION['user']['id']));
    }
    
    function user_config_get($name,$default = false){
        if(!isset($_SESSION['user']['config'])){
            $_SESSION['user']['config'] = array();
        }
        if(!empty($_SESSION['user']['config'][$name])){
            return $_SESSION['user']['config'][$name];
        }
        return $default;
    }
    
    function tzdate($date = "",$tz = 0){
        if($date==""){
            $date = $this->utcdate();
        }
        if($tz==0 && ($temp = $this->get_global_config("timezone")) && (int)$temp!=0){
            $tz = $temp;
        }
        return date("Y-m-d H:i:s",(strtotime($date) + ((int)$tz*3600)));
    }
    
    function utcdate($date = ""){
        if($date==""){
            $date = date("Y-m-d H:i:s");
        }
        $temp = explode(" ",gmdate("Y-m-d H:i:s",strtotime($date)));
        return $temp[0]." ".$temp[1]."";
    }
    
    function from_display_date($date){
        if(empty($date) || strlen($date)==0){
            return date("Y-m-d");
        }
        if(strlen($date)>0 && ($temp = explode('/', $date)) && sizeof($temp)==3){
            $date = $temp[2].'-'.$temp[1].'-'.$temp[0];
        }else{
            $date = "";
        }
        return $date;
    }
    
    function to_display_date($date){
        if(empty($date) || strlen($date)==0){
            return date('d/m/Y');
        }
        if(strlen($date)>0 && $date!="0000-00-00" && strtotime($date)>0 && date("Y-m-d",strtotime($date))!="1970-01-01"){
            $date = date('d/m/Y',strtotime($date));
        }else{
            $date = "";
        }
        return $date;
    }
    
    function set_timezone($tz = ""){
        $temp = array();
        $temp['-11'] = "Pacific/Apia";
        $temp['-10'] = "US/Hawaii";
        $temp['-9'] = "US/Alaska";
        $temp['-8'] = "US/Pacific";
        $temp['-7'] = "US/Arizona";
        $temp['-6'] = "US/Central";
        $temp['-5'] = "US/Eastern";
        $temp['-4'] = "Canada/Atlantic";
        $temp['-3'] = "Greenland";
        $temp['-2'] = "Atlantic/Stanley";
        $temp['-1'] = "Atlantic/Azores";
        $temp['0'] = "Europe/London";
        $temp['+1'] = "Europe/Berlin";
        $temp['+2'] = "Europe/Istanbul";
        $temp['+3'] = "Asia/Kuwait";
        $temp['+4'] = "Asia/Baku";
        $temp['+5'] = "Asia/Karachi";
        $temp['+6'] = "Asia/Dhaka";
        $temp['+7'] = "Asia/Bangkok";
        $temp['+8'] = "Asia/Kuala_Lumpur";
        $temp['+9'] = "Asia/Tokyo";
        $temp['+10'] = "Australia/Sydney";
        $temp['+11'] = "Asia/Vladivostok";
        $temp['+12'] = "Pacific/Auckland";
        
        if($tz=="" && ($temp = $this->get_global_config("timezone"))){
            $tz = $temp;
        }
        
        if(isset($temp[$tz])){
            date_default_timezone_set($temp[$tz]);
        }
    }
    
    function get_random_id(){
        return "SALES-".date("YmdHis")."-".rand(0, 9);
    }
    
    function get_rate($cur,$date=''){
        static $instance = array();
        if($date==''){
            $date = date("Y-m-d H:i:s");
        }
        $temp = $cur."_".$date;
        if(empty($instance[$temp])){
            $instance[$temp] = 1;
            if(($result = $this->CI->db->query('SELECT rate FROM exchange_rate WHERE to_code=? AND created_date<=? ORDER BY created_date DESC LIMIT 1',array($cur,$date))) && ($row = $result->row_array())){
                $instance[$temp] = $row['rate'];
            }
        }
        return $instance[$temp];
    }
    
    function get_available_quantity($store_item_id=0, $exclude_cache = false){
        $return = 0;
        $sql = 'select min(ifnull(b.quantity,0)+ifnull(b.quantity2,0)+ifnull(d.quantity,0)+ifnull(d.quantity2,0)) quantity from store_item a
            join warehouse_item b on a.warehouse_item_id=b.id
            left join option_item_combo c on b.item_id=c.item_id
            left join warehouse_item d on b.warehouse_id=d.warehouse_id and b.product_id=d.product_id and c.combo_id=d.item_id
            where a.id=?';
        if(($result = $this->CI->db->query($sql,array($store_item_id))) && ($row = $result->row_array()) && $row['quantity']>0){
            $return = $row['quantity'];
            if(!$exclude_cache){
                $sql = 'select sum(a.quantity) quantity
                    from transactions_cache a
                    where a.store_item_id=?';
                if(($result = $this->CI->db->query($sql,array($store_item_id))) && ($row = $result->row_array()) && $row['quantity']>0){
                    $return -= $row['quantity'];
                }
            }
        }
        return $return;
    }
    
    function get_available_quantity_from_id($id=0, $exclude_cache = false){
        $return = 0;
        $sql = 'select min(ifnull(b.quantity,0)+ifnull(b.quantity2,0)+ifnull(d.quantity,0)+ifnull(d.quantity2,0)) quantity 
            from warehouse_item b
            left join option_item_combo c on b.item_id=c.item_id
            left join warehouse_item d on b.warehouse_id=d.warehouse_id and b.product_id=d.product_id and c.combo_id=d.item_id
            where b.id=?';
        if(($result = $this->CI->db->query($sql,array($id))) && ($row = $result->row_array()) && $row['quantity']>0){
            $return = $row['quantity'];
            if(!$exclude_cache){
                $sql = 'select sum(a.quantity) quantity
                    from transactions_cache a
                    left join store_item b on a.store_item_id=b.id
                    left join warehouse_item c on b.warehouse_item_id=c.id
                    where c.id=?';
                if(($result = $this->CI->db->query($sql,array($id))) && ($row = $result->row_array()) && $row['quantity']>0){
                    $return -= $row['quantity'];
                }
            }
        }
        return $return;
    }
    
    function get_combo_list($store_item_id=0){
        $return = 0;
        
        $result = $result = $this->CI->db->query('select ifnull(b.id,0) warehouse_item_id, ifnull(b.item_id,0) item_id 
            ,ifnull(b.quantity,0) quantity1
            ,ifnull(b.quantity2,0) quantity2
            from store_item a
            join warehouse_item b on a.warehouse_item_id=b.id
            join warehouses c on b.warehouse_id=c.id
            where c.allow_combo="Y" and a.id=? limit 1',array($store_item_id));
        
        if(!$result){
            $result = $this->CI->db->query('select ifnull(d.id,b.id) warehouse_item_id, ifnull(d.item_id,b.item_id) item_id 
            ,ifnull(b.quantity,0)+ifnull(d.quantity,0) quantity1
            ,ifnull(b.quantity2,0)+ifnull(d.quantity2,0) quantity2
            from store_item a
            join warehouse_item b on a.warehouse_item_id=b.id
            left join option_item_combo c on b.item_id=c.item_id
            left join warehouse_item d on b.warehouse_id=d.warehouse_id and b.product_id=d.product_id and c.combo_id=d.item_id
            where a.id=?',array($store_item_id));
        }
        
        if($result && $result->num_rows()){
            $return = array();
            foreach($result->result_array() as $r){
                $return[$r['warehouse_item_id']] = $r;
            }
        }
        return $return;
    }
    
    function get_combo_list_from_id($id=0){
        $return = 0;
        if(($result = $this->CI->db->query('select b.id warehouse_item_id, b.item_id 
            ,ifnull(b.quantity,0) quantity1
            ,ifnull(b.quantity2,0) quantity2
            from warehouse_item b 
            join warehouses c on b.warehouse_id=c.id
            where c.allow_combo="Y" and b.id=? limit 1',array($id))) && $result->num_rows() && ($row = $result->row_array())){
            $return = array();
            $return[$row['warehouse_item_id']] = $row;
        }else if(($result = $this->CI->db->query('select ifnull(d.id,b.id) warehouse_item_id, ifnull(d.item_id,b.item_id) item_id
            ,ifnull(b.quantity,0)+ifnull(d.quantity,0) quantity1
            ,ifnull(b.quantity2,0)+ifnull(d.quantity2,0) quantity2
            from warehouse_item b
            left join option_item_combo c on b.item_id=c.item_id
            left join warehouse_item d on b.warehouse_id=d.warehouse_id and b.product_id=d.product_id and c.combo_id=d.item_id
            where b.id=?',array($id))) && $result->num_rows()){
            $return = array();
            foreach($result->result_array() as $r){
                $return[$r['warehouse_item_id']] = $r;
            }
        }
        return $return;
    }
    
    function sales_cancel($trans_id=0){
        if(($result = $this->CI->db->query('select warehouse_item_id,sum(adj_quantity) adj_quantity,sum(adj_quantity2) adj_quantity2 from warehouse_item_history a where trans_id=? GROUP BY warehouse_item_id',array($trans_id))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                $this->adjust_quantity($row['warehouse_item_id'], ($row['adj_quantity'] * -1), ($row['adj_quantity2'] * -1),0,'C');
            }
            $this->CI->db->query('DELETE FROM warehouse_item_history WHERE movement_type="S" and trans_id=?',array($trans_id));
        }
    }
    
    function adjust_quantity($warehouse_item_id=0,$quantity1=0,$quantity2=0,$trans_id=0,$movement_type='A'){
        if($quantity1==0 && $quantity2==0){
            return true;
        }
        $item_list = array();
        $sql = 'select store_item_id,c.default_qty_deduct from transactions a
            join store_item b on a.store_item_id=b.id
            join stores c on b.store_id=c.id
            where a.id=? limit 1';
        $default_qty_deduct = '0';
        $total_quantity = $quantity1 + $quantity2;
        if($movement_type=='S' && $trans_id>0 && ($result = $this->CI->db->query($sql,$trans_id)) && $result->num_rows() && ($row = $result->row_array())){
            $default_qty_deduct = $row['default_qty_deduct'];
            $item_list = $this->get_combo_list($row['store_item_id']);
        }else{
            $item_list = $this->get_combo_list_from_id($warehouse_item_id);
        }
        
        $count = 0;
        foreach($item_list as $wid => $wid_data){
            if($movement_type=='S' && $trans_id>0){
                $quantity1 = 0;
                $quantity2 = 0;
                if($default_qty_deduct=="1"){
                    $quantity2 = (((float)$total_quantity<0)?(min(max(0,$wid_data['quantity2']),abs($total_quantity)) * -1):$total_quantity);
                    $quantity1 = (((float)$total_quantity<0)?(min(max(0,$wid_data['quantity1']),abs($total_quantity - $quantity2)) * -1):$total_quantity - $quantity2);
                }else{
                    $quantity1 = (((float)$total_quantity<0)?(min(max(0,$wid_data['quantity1']),abs($total_quantity)) * -1):$total_quantity);
                    $quantity2 = (((float)$total_quantity<0)?(min(max(0,$wid_data['quantity2']),abs($total_quantity - $quantity1)) * -1):$total_quantity - $quantity1);
                }
                $temp = (float)$total_quantity - ($quantity1 + $quantity2);
                if($temp<>0){
                    $quantity1 += $temp;
                }
            }
            $sql = sprintf('UPDATE warehouse_item SET quantity=quantity+%d,quantity2=quantity2+%d WHERE id=%d',intval($quantity1),intval($quantity2),$this->CI->db->escape($wid));
            if($this->CI->db->query($sql)){
                $sql = 'INSERT INTO warehouse_item_history(warehouse_item_id,quantity,cost_price,selling_price,expire_date,quantity2,quantity3,adj_quantity,adj_quantity2,trans_id,movement_type) 
                        SELECT id,quantity,cost_price,selling_price,expire_date,quantity2,quantity3,"'.$quantity1.'","'.$quantity2.'","'.$trans_id.'","'.$movement_type.'" FROM warehouse_item WHERE id = ?';
                $this->CI->db->query($sql,array($wid));
                $count++;
            }
        }
        if($count){
            return true;
        }
        return false;
    }
    
    function check_quantity_alert(){
        if(($result = $this->CI->db->query('select a.warehouse_id,w.name
            ,sum(if(if(a.stop_qty>0,a.stop_qty,s2.value)>=(a.quantity+a.quantity2) and wih.id is not null,1,0)) pstop
            ,sum(if(if(a.stop_qty>0,a.stop_qty,s2.value)<(a.quantity+a.quantity2) and if(a.min_qty>0,a.min_qty,s1.value)>=(a.quantity+a.quantity2) and wih.id is not null,1,0)) pwarning
            from warehouse_item a
            join warehouses w on a.warehouse_id=w.id
            join products b on a.product_id=b.id
            join option_item c on a.item_id=c.id
            left join settings s1 on s1.code="min_qty" 
            left join settings s2 on s2.code="stop_qty"
            left join warehouse_item_history wih on wih.warehouse_item_id=a.id
            left join warehouse_item_history wih2 on wih2.warehouse_item_id=a.id and wih.id>wih2.id
            where ((if(a.stop_qty>0,a.stop_qty,s2.value)>=(a.quantity+a.quantity2) and wih.id is not null)
            or (if(a.min_qty>0,a.min_qty,s1.value)>=(a.quantity+a.quantity2) and wih.id is not null))
            and wih2.id is null
            group by a.warehouse_id')) && $result->num_rows()){
            $return = array();
            $_SESSION['notification'] = array('badge_list'=>array('total_danger'=>array('badge-class'=>'badge badge-sm badge-danger','size'=>0),'total_warning'=>array('badge-class'=>'badge badge-sm badge-warning','size'=>0)),'data_list'=>array());
            foreach($result->result_array() as $r){
                if($r['pstop']>0){
                    $_SESSION['notification']['badge_list']['total_danger']['size'] += $r['pstop'];
                    $_SESSION['notification']['data_list'][] = array('name'=>'Warehouse:'.$r['name'].' <font class="text-danger">out of stock</font>','url'=>base_url("/warehouse_item?id=".$r['warehouse_id']."&search_qstatus=stop"),'size'=>$r['pstop']);
                }
                if($r['pwarning']>0){
                    $_SESSION['notification']['badge_list']['total_warning']['size'] += $r['pwarning'];
                    $_SESSION['notification']['data_list'][] = array('name'=>'Warehouse:'.$r['name'].' <font class="text-warning">limited stock</font>','url'=>base_url("/warehouse_item?id=".$r['warehouse_id']."&search_qstatus=warning"),'size'=>$r['pwarning']);
                }
            }
        }
    }
    
    function update_store(){
        //update warehouses
        $sql = 'insert into warehouse_item(warehouse_id,product_id,item_id,quantity,skucode)
select w.id,a.id,c.id item_id,0,concat(a.code,"-",c.code) skucode from warehouses w
                            left join products a on a.id is not null
            join options b on a.option_id=b.id
            join option_item c on a.option_id=c.option_id
            left join warehouse_item d on d.product_id=a.id and item_id=c.id and d.warehouse_id=w.id
            where d.id is null ORDER BY d.warehouse_id,a.id,c.id';
        $this->CI->db->query($sql);

        //update stores
        $sql = 'SELECT a.id,a.warehouse_id,b.currency FROM stores a, marketplaces b WHERE a.marketplace_id=b.id';
        if(($result2 = $this->CI->db->query($sql)) && $result2->num_rows()){
            foreach($result2->result_array() as $row){
                $rate = $this->get_rate($row['currency']);
                $store_id = $row['id'];
                $warehouse_id = $row['warehouse_id'];
                
                $sql = 'insert into store_item(store_id,warehouse_item_id,store_skucode,selling_price,expire_date)
                    select "'.$store_id.'",d.id,concat(a.code,"-",c.code) skucode,(ifnull(d.selling_price,0) * '.$rate.'),ifnull(d.expire_date,"0000-00-00") from products a
                    join options b on a.option_id=b.id
                    join option_item c on a.option_id=c.option_id
                    join warehouse_item d on d.product_id=a.id and item_id=c.id and d.warehouse_id="'.$warehouse_id.'"
                    left join store_item e on d.id=e.warehouse_item_id and e.store_id="'.$store_id.'"
                    where e.id is null ORDER BY d.warehouse_id,a.id,c.id';
                $this->CI->db->query($sql);
            }
        }
    }
    
    function maintain_store(){
        $sql = 'UPDATE store_item a,stores b,warehouse_item c,warehouse_item d
                SET a.warehouse_item_id=d.id
                WHERE a.store_id=b.id 
                and a.warehouse_item_id=c.id 
                and d.product_id=c.product_id and d.item_id=c.item_id and d.warehouse_id=b.warehouse_id
                and a.warehouse_item_id<>d.id';
        $this->CI->db->query($sql);
    }
    
    function maintain_transaction_cache(){
        $sql = 'update transactions a,store_item_bk b,stores c,warehouse_item d,warehouse_item e,store_item f
                set a.store_item_id=f.id
                where a.store_item_id=b.id 
                and b.store_id=c.id
                and b.warehouse_item_id=d.id
                and e.warehouse_id=c.warehouse_id and e.product_id=d.product_id and e.item_id=d.item_id
                and f.store_id=c.id and f.warehouse_item_id=e.id
                and a.store_item_id<>f.id';
        $this->CI->db->query($sql);
    }
    
    function write_js_form($action,$params = array(),$method = "POST"){
        $temp = "function(){";
        $temp .= 'var form = document.createElement("form");';
        $temp .= 'form.setAttribute("method", "'.$method.'");';
        $temp .= 'form.setAttribute("action", "'.$action.'");';
        $temp .= 'form.setAttribute("target", "_blank");';
        $temp .= 'var hiddenField;';
        foreach($params as $k => $v){
            if(is_array($v)){
                foreach($v as $v2){
                    $temp .= 'hiddenField = document.createElement("input");';
                    $temp .= 'hiddenField.setAttribute("name", "'.$k.'[]");';
                    $temp .= 'hiddenField.setAttribute("value", "'.$v2.'");';
                    $temp .= 'form.appendChild(hiddenField);';
                }
            }else{
                $temp .= 'hiddenField = document.createElement("input");';
                $temp .= 'hiddenField.setAttribute("name", "'.$k.'");';
                $temp .= 'hiddenField.setAttribute("value", "'.$v.'");';
                $temp .= 'form.appendChild(hiddenField);';
            }
        }
        $temp .= 'document.body.appendChild(form);';
        $temp .= 'form.submit();';
        $temp .= 'form.remove();';
        $temp .= '}';
        return $temp;
    }
}
