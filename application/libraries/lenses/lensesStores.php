<?php

require_once('lensesMain.php');

class lensesStores extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Stores'=>''));
        $this->table = "stores";
        $this->title = "Stores";
        $this->selected_menu = "stores";
        $this->custom_form = false;
        
        $account_list = array();
        if(($result = $this->CI->db->query('SELECT id,name FROM accounts ORDER BY name'))){
            foreach($result->result_array() as $value){
                $account_list[$value['id']] = $value['name'];
            }
        }
        
        $market_list = array();
        if(($result = $this->CI->db->query('SELECT id,name FROM marketplaces ORDER BY name'))){
            foreach($result->result_array() as $value){
                $market_list[$value['id']] = $value['name'];
            }
        }
        
        $warehouse_list = array();
        if(($result = $this->CI->db->query('SELECT id,name FROM warehouses ORDER BY name'))){
            foreach($result->result_array() as $value){
                $warehouse_list[$value['id']] = $value['name'];
            }
        }
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Name','editable'=>true,'goto'=>base_url('/store_item')),array('id'=>'account_id','name'=>'Account','editable'=>true,'option_text'=>$account_list),array('id'=>'marketplace_id','name'=>'Market Place','editable'=>true,'option_text'=>$market_list),array('id'=>'warehouse_id','name'=>'Warehouse','editable'=>true,'option_text'=>$warehouse_list),array('id'=>'sales_fees_pect','name'=>'Sales Fees(%)','editable'=>true),array('id'=>'sales_fees_fixed','name'=>'Sales Fees(Amount)','editable'=>true),array('id'=>'paypal_fees_pect','name'=>'Paypal Fees(%)','editable'=>true),array('id'=>'paypal_fees_fixed','name'=>'Paypal Fees(Amount)','editable'=>true),array('id'=>'default_qty_deduct','name'=>'Default Deduction','editable'=>true,'option_text'=>array('0'=>'Normal','1'=>'Defected')));
    }
    
    function ajax_save(){
        $result = parent::ajax_save();
        if($result['status']=='1' && isset($result['record_id'])){
            $rate = 1;
            if(($result2 = $this->CI->db->query('SELECT b.currency FROM stores a, marketplaces b WHERE a.id="'.$result['record_id'].'" and a.marketplace_id=b.id LIMIT 1')) && ($row = $result2->row_array())){
                $rate = $this->get_rate($row['currency']);
            }
            $sql = 'insert into store_item(store_id,warehouse_item_id,store_skucode,selling_price,expire_date)
                select "'.$result['record_id'].'",d.id,concat(a.code,"-",c.code) skucode,(ifnull(d.selling_price,0) * '.$rate.'),ifnull(d.expire_date,"0000-00-00") from products a
                join options b on a.option_id=b.id
                join option_item c on a.option_id=c.option_id
                join warehouse_item d on d.product_id=a.id and item_id=c.id
                left join store_item e on d.id=e.warehouse_item_id and e.store_id="'.$result['record_id'].'"
                where e.id is null ORDER BY a.id,c.id';
            $this->CI->db->query($sql);
        }
        return $result;
    }
    
    function ajax_delete(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        if(($result = $this->CI->db->query('select * from '.$this->table.' a where id in ?',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                if(($result2 = $this->CI->db->query('select * from transactions a join store_item b on a.store_item_id=b.id where b.store_id=? LIMIT 1',array($row['id']))) && $result2->num_rows()){
                    $return['message'].= 'Delete Fail! Some data required "'.$row['name'].'".
    ';
                }else{
                    $this->CI->db->query('DELETE FROM store_item WHERE store_id=?',array($row['id']));
                    if($this->CI->db->query('DELETE FROM '.$this->table.' WHERE id=?',array($row['id']))){
                        $return['status'] = "1";
                    }
                }
            }
        }
        return $return;
    }
    
}