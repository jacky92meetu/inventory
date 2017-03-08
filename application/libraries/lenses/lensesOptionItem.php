<?php

require_once('lensesMain.php');

class lensesOptionItem extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $id = $this->CI->input->get('id',true);
        if(($result = $this->CI->db->query('SELECT * FROM options WHERE id=? LIMIT 1',array($id))) && $result->num_rows() && ($row = $result->row_array())){
            
        }else{
            $this->CI->load->library('cmessage');
            $this->CI->cmessage->set_message_url('ID not found','error','/options');
        }
        
        $this->CI->cpage->set('breadcrumb',array('Option Group List'=>base_url('/options'),$row['name'].'\'s Items'=>''));
        $this->table = "option_item";
        $this->title = "Option Group Item";
        $this->selected_menu = "options";
        $this->custom_form = false;
        $this->ajax_url = base_url('ajax/'.$this->table.'?id='.$id);
        $this->parent_id = array('key'=>'option_id','value'=>$id);
        
        $this->search_query = sprintf('SELECT * FROM (select a.id,a.name,a.code,a.code2,a.type from option_item a
            where a.option_id=%s) a',$this->CI->db->escape($id));
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Color','editable'=>true),array('id'=>'code','name'=>'Code','editable'=>true),array('id'=>'code2','name'=>'Unique/Combo Code','editable'=>true),array('id'=>'type','name'=>'Type','editable'=>true,'option_text'=>array('1'=>'option','2'=>'combo')));
    }
    
    function ajax_save(){
        $result = parent::ajax_save();
        if($result['status']=='1' && isset($result['record_id'])){
            $sql = 'DELETE FROM option_item_combo WHERE item_id="'.$result['record_id'].'"';
            $this->CI->db->query($sql);
            
            $sql = 'SELECT * FROM '.$this->table.' WHERE id="'.$result['record_id'].'" LIMIT 1';
            if(($temp = $this->CI->db->query($sql)) && ($temp->num_rows()) && ($temp = $temp->row_array()) && $temp['type']=="2"){
                foreach(explode(",",$temp['code2']) as $v){
                    $sql = 'SELECT * FROM option_item WHERE type=1 AND option_id="'.$temp['option_id'].'" AND code2="'.$v.'"';
                    if(($result2 = $this->CI->db->query($sql)) && $result2->num_rows() && ($temp2 = $result2->row_array())){
                        $sql = 'INSERT INTO option_item_combo SET item_id=?,combo_id=?';
                        $this->CI->db->query($sql,array($temp['id'],$temp2['id']));
                    }
                }
            }
            
            $this->update_store();
        }
        return $result;
    }
    
    function ajax_delete(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        if(($result = $this->CI->db->query('select * from '.$this->table.' a where id in ?',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                if(($result2 = $this->CI->db->query('select * from warehouse_item a where item_id=? LIMIT 1',array($row['id']))) && $result2->num_rows()){
                    $return['message'].= 'Delete Fail! Some data required "'.$row['name'].'".
    ';
                }else{
                    if($this->CI->db->query('DELETE FROM '.$this->table.' WHERE id=?',array($row['id']))){
                        $return['status'] = "1";
                    }
                }
            }
        }
        return $return;
    }
}