<?php

error_reporting(0);

class lensesMain{
    
    var $CI = false;
    var $title = '';
    var $table = '';
    var $header = false;
    var $search_query = '';
    var $insert_query = '';
    var $update_query = '';
    var $delete_query = '';
    var $is_required = true;
    var $recordsTotal = 0;
    var $recordsFiltered = 0;
    var $freezePane = 2;
    var $data = array();
    var $default_length = 50;
    var $custom_form = false;
    var $ajax_url = "";
    var $add_btn = true;
    var $delete_btn = true;
    var $extra_btn = array();
    var $parent_id = false;
    
    function __construct(){
        $this->CI = get_instance();
        $this->CI->db->query('SET NAMES "utf8"');        
        //$this->CI->db->query('SET @@global.time_zone = "'.$this->get_global_config("timezone").':00"');
        $this->CI->db->query('SET @@session.time_zone = "'.$this->get_global_config("timezone").':00"');
        
        if(isset($_POST['columns'])){
            array_splice($_POST['columns'], 0, 1);
        }
        if(strlen($temp = $this->CI->input->post('order[0][column]',true))>0){
            $_POST['order'][0]['column'] = (int)$temp - 1;
        }
        
        if(isset($_SESSION['default_length'])){
            $this->default_length = $_SESSION['default_length'];
        }
        
        $this->set_timezone();
        
        $this->check_quantity_alert();
    }
        
    function setup() {
        $this->init_header();
    }
    
    function init_header(){
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
                    $temp['editable'] = true;
                    if(stripos($key, 'date')!==FALSE){
                        $temp['is_date'] = true;
                    }
                }
                $data[] = $temp;
            }
            $this->header = $data;
        }
    }
    
    function view(){
        $this->init_header();
        $contents = array();
        if($this->ajax_url==""){
            $this->ajax_url = base_url('ajax/'.$this->table);
        }
        $this->CI->cpage->set_html_title($this->title);
        $this->CI->cpage->set('selected_menu',$this->selected_menu);
        $this->CI->cpage->set('view_title',$this->title);
        $this->CI->cpage->set('view_header',$this->header);
        $this->CI->cpage->set('view_contents',$contents);
        $this->CI->cpage->set('default_length',$this->default_length);
        $this->CI->cpage->set('view_ajax_url',$this->ajax_url);
        $this->CI->cpage->set('custom_form',$this->custom_form);
        $this->CI->cpage->set('add_btn',$this->add_btn);
        $this->CI->cpage->set('delete_btn',$this->delete_btn);
        $this->CI->cpage->set('extra_btn',$this->extra_btn);
        $this->CI->cpage->set('is_required',$this->is_required);
        $this->CI->cpage->set('freezePane',$this->freezePane);
        return $this->CI->load->view('page-view');
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
                        $where_query[] = $this->header[$count]['id'].' LIKE "%'.$this->CI->db->escape_like_str($c['search']['value']).'%"';
                    }else{
                        $where_query[] = $this->header[$count]['id'].' = '.$this->CI->db->escape($c['search']['value']);
                    }
                }
                $count += 1;
            }
        }
        if(sizeof($where_query)>0){
            $where_query = ' WHERE '.implode(" AND ",$where_query);
        }else{
            $where_query = '';
        }
        
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
        
        $limit_start = ((!empty($this->CI->input->post('start',true)))?$this->CI->input->post('start',true):0);
        $limit_length = ((!empty($this->CI->input->post('length',true)))?$this->CI->input->post('length',true):$this->default_length);
        $_SESSION['default_length'] = $limit_length;
        
        $this->recordsTotal = 0;
        $sql = $this->search_query;
        if(stristr($sql, 'COUNT(*)')===FALSE){
            $sql = 'SELECT COUNT(*) as counting FROM ('.$sql.') a';
        }
        if(($result = $this->CI->db->query($sql)) && $result->num_rows()){
            if(($row = $result->row_array())){
                $this->recordsTotal = $row['counting'];
            }
        }
        
        $this->recordsFiltered = 0;
        $sql = $this->search_query.$where_query;
        if(stristr($sql, 'COUNT(*)')===FALSE){
            $sql = 'SELECT COUNT(*) as counting FROM ('.$sql.') a';
        }
        if(($result = $this->CI->db->query($sql)) && $result->num_rows()){
            if(($row = $result->row_array())){
                $this->recordsFiltered = $row['counting'];
            }
        }
        
        $this->data = array();
        $sql = $this->search_query.$where_query.$order_query.' LIMIT '.$limit_start.','.$limit_length;
        if(($result = $this->CI->db->query($sql)) && $result->num_rows()){
            $temp = $result->result_array();
            $count2 = $limit_start+1;
            foreach($temp as $r){
                $count = 0;
                $temp2 = array();
                $temp2[] = $count2;
                foreach($r as $c){
                    /*
                    if(isset($this->header[$count]['option_text']) && isset($this->header[$count]['option_text'][$c])){
                        $c = $this->header[$count]['option_text'][$c];
                    }
                    */
                    if(isset($this->header[$count]['is_date'])){
                        if(strtotime($c)>0 && date("Y-m-d",strtotime($c))!="1970-01-01"){
                            $c = date('d/m/Y',strtotime($c));
                        }else{
                            $c = "";
                        }
                    }
                    $temp2[] = $c;
                    $count += 1;
                }
                $temp2[] = '';
                $this->data[] = $temp2;
                $count2 += 1;
            }
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
        
        if(isset($this->custom_header) && sizeof($data)==0){
            $temp = array();
            foreach($this->custom_header as $value){
                $temp[$value['id']] = $value;
            }
            $this->custom_header = $temp;
            $data = $this->custom_header;
        }
        
        if(sizeof($data)==0){
            $sql = 'SELECT * FROM '.$this->table;
            if(strlen($this->search_query)>0){
                $sql = $this->search_query;
            }
            $sql .= ' LIMIT 1';
            $result = $this->CI->db->query($sql);
            foreach($this->header as $col){
                $col_exists = false;
                $temp = array('id'=>$col['id'],'name'=>$col['id'],'value'=>'');
                foreach($result->list_fields() as $key){
                    if($col['id']==$key){
                        $col_exists = true;
                        $temp['name'] = $col['name'];
                        if(isset($col['is_date'])){
                            $temp['is_date'] = '1';
                            $temp['value'] = date("d/m/Y");
                        }else if(isset($col['option_text'])){
                            $temp['option_text'] = $col['option_text'];
                        }
                        if(isset($col['is_ajax'])){
                            $temp['is_ajax'] = '1';
                            if(!isset($temp['option_text'])){
                                $temp['option_text'] = array();
                            }
                        }
                        if(isset($col['hidden'])){
                            $temp['hidden'] = '1';
                        }
                        if(!isset($col['editable'])){
                            $temp['readonly'] = '1';
                        }
                        if(isset($col['is_file'])){
                            $temp['is_file'] = '1';
                        }
                        if(isset($col['value'])){
                            $temp['value'] = $col['value'];
                        }
                        $data[$key] = $temp;
                        break;
                    }
                }
            }
        }
        $temp = array();
        foreach($data as $d){
            if($d['is_date']=='1' && empty($d['value'])){
                if(empty($d['value']) || strtotime($d['value'])<=0 || date("Y-m-d",strtotime($d['value']))=="1970-01-01"){
                    $d['value'] = date("d/m/Y");
                }
            }
            if(!isset($d['value'])){
                $d['value'] = '';
            }
            $temp[$d['id']] = $d;
        }
        $data = $temp;
        
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
                                if(strtotime($data[$key]['value'])>0 && date("Y-m-d",strtotime($data[$key]['value']))!="1970-01-01"){
                                    $data[$key]['value'] = date('d/m/Y',strtotime($data[$key]['value']));
                                }else{
                                    $data[$key]['value'] = "";
                                }
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
    
    function ajax_custom_form_save(){
        $return = array("status"=>"0","message"=>"");
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
        if(($result = $this->CI->db->query('select ifnull(d.id,b.id) warehouse_item_id, ifnull(d.item_id,b.item_id) item_id 
            ,ifnull(b.quantity,0)+ifnull(d.quantity,0) quantity1
            ,ifnull(b.quantity2,0)+ifnull(d.quantity2,0) quantity2
            from store_item a
            join warehouse_item b on a.warehouse_item_id=b.id
            left join option_item_combo c on b.item_id=c.item_id
            left join warehouse_item d on b.warehouse_id=d.warehouse_id and b.product_id=d.product_id and c.combo_id=d.item_id
            where a.id=?',array($store_item_id))) && $result->num_rows()){
            $return = array();
            foreach($result->result_array() as $r){
                $return[$r['warehouse_item_id']] = $r;
            }
        }
        return $return;
    }
    
    function get_combo_list_from_id($id=0){
        $return = 0;
        if(($result = $this->CI->db->query('select ifnull(d.id,b.id) warehouse_item_id, ifnull(d.item_id,b.item_id) item_id
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
                $this->adjust_quantity($row['warehouse_item_id'], ($row['adj_quantity'] * -1), ($row['adj_quantity2'] * -1));
            }
            $this->CI->db->query('DELETE FROM warehouse_item_history WHERE trans_id=?',array($trans_id));
        }
    }
    
    function adjust_quantity($warehouse_item_id=0,$quantity1=0,$quantity2=0,$trans_id=0){
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
        if($trans_id>0 && ($result = $this->CI->db->query($sql,$trans_id)) && $result->num_rows() && ($row = $result->row_array())){
            $default_qty_deduct = $row['default_qty_deduct'];
            $item_list = $this->get_combo_list($row['store_item_id']);
        }else{
            $item_list = $this->get_combo_list_from_id($warehouse_item_id);
        }
        
        $count = 0;
        foreach($item_list as $wid => $wid_data){
            if($trans_id>0){
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
            $sql = sprintf('UPDATE warehouse_item SET quantity=quantity+%d,quantity2=quantity2+%d WHERE id=%d',$this->CI->db->escape($quantity1),$this->CI->db->escape($quantity2),$this->CI->db->escape($wid));
            if($this->CI->db->query($sql)){
                $sql = 'INSERT INTO warehouse_item_history(warehouse_item_id,quantity,cost_price,selling_price,expire_date,quantity2,quantity3,adj_quantity,adj_quantity2,trans_id) 
                        SELECT id,quantity,cost_price,selling_price,expire_date,quantity2,quantity3,"'.$quantity1.'","'.$quantity2.'","'.$trans_id.'" FROM warehouse_item WHERE id = ?';
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
            join option_item c on a.item_id=c.id and c.type="1"
            left join settings s1 on s1.code="min_qty" 
            left join settings s2 on s2.code="stop_qty"
            left join warehouse_item_history wih on wih.warehouse_item_id=a.id
            left join warehouse_item_history wih2 on wih2.warehouse_item_id=a.id and wih.id>wih2.id
            where ((if(a.stop_qty>0,a.stop_qty,s2.value)>=(a.quantity+a.quantity2) and wih.id is not null)
            or (if(a.min_qty>0,a.min_qty,s1.value)>=(a.quantity+a.quantity2) and wih.id is not null))
            and wih2.id is null
            group by a.warehouse_id')) && $result->num_rows()){
            $return = array();
            $_SESSION['notification'] = array();
            foreach($result->result_array() as $r){
                if($r['pstop']>0){
                    $_SESSION['notification']['quantity-danger'] = array('name'=>'Warehouse:'.$r['name'].' <font class="text-danger">out of stock</font>','url'=>base_url("/warehouse_item?id=".$r['warehouse_id']."&search_qstatus=stop"),'badge-class'=>'badge badge-sm badge-danger','size'=>$r['pstop']);
                }
                if($r['pwarning']>0){
                    $_SESSION['notification']['quantity-warning'] = array('name'=>'Warehouse:'.$r['name'].' <font class="text-warning">limited stock</font>','url'=>base_url("/warehouse_item?id=".$r['warehouse_id']."&search_qstatus=warning"),'badge-class'=>'badge badge-sm badge-warning','size'=>$r['pwarning']);
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
}
