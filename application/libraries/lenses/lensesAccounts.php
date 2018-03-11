<?php

require_once('lensesMain.php');

class lensesAccounts extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Accounts'=>''));
        $this->table = "accounts";
        $this->title = "Accounts";
        $this->selected_menu = "accounts";
        $this->freezePane = 2;
        $this->is_required = false;
        $this->custom_form = true;
        
        $this->header = array(
            array('id'=>'id','name'=>'ID'),
            array('id'=>'name','name'=>'Account Name','editable'=>true),
            array('id'=>'acc_comp_name','name'=>'Comp. Name','editable'=>true),
            array('id'=>'acc_comp_addr','name'=>'Comp. Addr.','editable'=>true,'is_textarea'=>'1'),
            array('id'=>'acc_comp_tel','name'=>'Comp. Tel.','editable'=>true),
            array('id'=>'acc_comp_fax','name'=>'Comp. Fax.','editable'=>true),
            array('id'=>'acc_comp_tax_template','name'=>'Inv. Template','editable'=>true),
            array('id'=>'acc_comp_tax_no','name'=>'Comp. Tax No.','editable'=>true),
            array('id'=>'acc_comp_reg_no','name'=>'Comp. Reg. No.','editable'=>true),
            array('id'=>'acc_comp_comments','name'=>'Inv. Comments','editable'=>true,'is_textarea'=>'1'),
            array('id'=>'acc_comp_inv_prefix','name'=>'Inv. Prefix','editable'=>true),
            array('id'=>'acc_comp_cn_prefix','name'=>'CN. Prefix','editable'=>true),
        );
    }
    
    function ajax_delete(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        if(($result = $this->CI->db->query('select * from '.$this->table.' a where id in ?',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                if(($result2 = $this->CI->db->query('select * from stores where account_id=? LIMIT 1',array($row['id']))) && $result2->num_rows()){
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