<?php

require_once('lensesMain.php');

class lensesOptions extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Option Group List'=>''));
        $this->table = "options";
        $this->title = "Option Group List";
        $this->selected_menu = "options";
        $this->custom_form = false;
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Option Group','editable'=>true,'goto'=>  base_url('/option_item')));
    }
    
    function ajax_delete(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        if(($result = $this->CI->db->query('select * from '.$this->table.' a where id in ?',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                if(($result2 = $this->CI->db->query('select f.id from options f
                    left join products e on e.option_id=f.id
                    left join option_item d on d.option_id=f.id
                    left join warehouse_item c on c.item_id=d.id
                    left join store_item b on b.warehouse_item_id=c.id
                    left join transactions a on a.store_item_id=b.id
                    where (a.id is not null or e.id is not null) and f.id=? LIMIT 1',array($row['id']))) && $result2->num_rows()){
                    $return['message'].= 'Delete Fail! Some data required "'.$row['name'].'".
    ';
                }else{
                    if(($result2 = $this->CI->db->query('select b.id from option_item d
                        join warehouse_item c on c.item_id=d.id
                        join store_item b on b.warehouse_item_id=c.id
                        where d.option_id=?',array($row['id']))) && $result2->num_rows()){
                        foreach($result2->result_array() as $row2){
                            $this->CI->db->query('DELETE FROM store_item WHERE id=?',array($row2['id']));
                        }
                    }
                    $this->CI->db->query('DELETE FROM warehouse_item WHERE item_id in (select id from option_item where option_id=?)',array($row['id']));
                    $this->CI->db->query('DELETE FROM option_item_combo WHERE item_id in (SELECT id FROM option_item WHERE type=2 AND option_id=?)',array($row['id']));
                    $this->CI->db->query('DELETE FROM option_item WHERE option_id=?',array($row['id']));
                    if($this->CI->db->query('DELETE FROM '.$this->table.' WHERE id=?',array($row['id']))){
                        $return['status'] = "1";
                    }
                }
            }
        }
        return $return;
    }
    
}