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
        $this->is_required = false;
        $this->extra_btn = array();
        $this->extra_btn[] = array('name'=>'Item Import','custom_form'=>'item_import');
        $this->custom_form = true;
        $this->ajax_url = base_url('ajax/stores');
        $this->search_query = 'select * from (select a.id,a.name,a.account_id,a.marketplace_id,a.warehouse_id
            ,a.sales_fees_pect,a.sales_fees_fixed,a.paypal_fees_pect,a.paypal_fees_fixed,a.default_qty_deduct
            ,b.name account_name, m.name marketplace_name, w.name warehouse_name
            from stores a
            join accounts b on a.account_id=b.id
            join marketplaces m on a.marketplace_id=m.id
            join warehouses w on a.warehouse_id=w.id
            ) a';
        
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
        
        $this->header = array(array('id'=>'id','name'=>'ID'),array('id'=>'name','name'=>'Name','editable'=>true,'goto'=>base_url('/store_item')),array('id'=>'account_id','name'=>'Account','editable'=>true,'option_text'=>$account_list),array('id'=>'marketplace_id','name'=>'Market Place','editable'=>true,'option_text'=>$market_list),array('id'=>'warehouse_id','name'=>'Warehouse','editable'=>true,'option_text'=>$warehouse_list),array('id'=>'sales_fees_pect','name'=>'Sales Fees(%)','editable'=>true,'value'=>'0.00'),array('id'=>'sales_fees_fixed','name'=>'Sales Fees(Amount)','editable'=>true,'value'=>'0.00'),array('id'=>'paypal_fees_pect','name'=>'Paypal Fees(%)','editable'=>true,'value'=>'0.00'),array('id'=>'paypal_fees_fixed','name'=>'Paypal Fees(Amount)','editable'=>true,'value'=>'0.00'),array('id'=>'default_qty_deduct','name'=>'Default Deduction','editable'=>true,'option_text'=>array('0'=>'Normal','1'=>'Defected')));
        
        $this->item_import_header = array(
            array('id'=>'id','name'=>'ID','hidden'=>'1'),
            array('id'=>'type','name'=>'type','value'=>'item_import','hidden'=>'1'),
            array('id'=>'account_id','name'=>'Account','is_ajax'=>'1','option_text'=>$account_list,'editable'=>true),
            array('id'=>'marketplace_template','name'=>'MarketPlace','is_ajax'=>'1','option_text'=>array(),'editable'=>true),
            array('id'=>'file','name'=>'file','is_file'=>'1')
        );
    }
    
    function ajax_custom_form(){
        if($_REQUEST['type']=="item_import"){
            $data = $this->item_import_header;
            return parent::ajax_custom_form($data);
        }else{
            if(strlen($this->CI->input->post('id',true))>0 && $this->CI->input->post('id',true)>0){
                $data = array(array('id'=>'id','name'=>'ID','readonly'=>'1'),array('id'=>'name','name'=>'Name'),array('id'=>'account_name','name'=>'Account','readonly'=>'1'),array('id'=>'marketplace_name','name'=>'Market Place','readonly'=>'1'),array('id'=>'warehouse_name','name'=>'Warehouse','readonly'=>'1'),array('id'=>'sales_fees_pect','name'=>'Sales Fees(%)','editable'=>true,'value'=>'0.00'),array('id'=>'sales_fees_fixed','name'=>'Sales Fees(Amount)','editable'=>true,'value'=>'0.00'),array('id'=>'paypal_fees_pect','name'=>'Paypal Fees(%)','editable'=>true,'value'=>'0.00'),array('id'=>'paypal_fees_fixed','name'=>'Paypal Fees(Amount)','editable'=>true,'value'=>'0.00'),array('id'=>'default_qty_deduct','name'=>'Default Deduction','editable'=>true,'option_text'=>array('0'=>'Normal','1'=>'Defected')));
                return parent::ajax_custom_form($data);
            }
            return parent::ajax_custom_form();
        }
    }
    
    function ajax_custom_form_save(){
        $return = array("status"=>"0","message"=>"");
        
        $value = $this->CI->input->post('value',true);
        
        if(!empty($value['type']) && $value['type']=='item_import'){
            $return = array("status"=>"0","message"=>"");
            
            if(!empty($value['file'])){
                include_once(APPPATH.'libraries/classes/importClass.php');
                $class = new importClass;
                $file = tempnam(sys_get_temp_dir(), 'item_import_');
                $data = $value['file'];
                $data = base64_decode($data);
                file_put_contents($file, $data);
                $return = $class->item_import($value['account_id'],$value['marketplace_template'], $file);
                unlink($file);
            }
            return $return;
        }else{
            if(empty($this->CI->input->post('id',true))){
                //check existing account & marketplace
                $account_id = $this->CI->input->post('value[account_id]',true);
                $marketplace_id = $this->CI->input->post('value[marketplace_id]',true);
                if(($result2 = $this->CI->db->query('SELECT a.id FROM stores a WHERE a.account_id=? and a.marketplace_id=? LIMIT 1',array($account_id,$marketplace_id))) && ($row = $result2->row_array())){
                    return array("status"=>"0","message"=>"Marketplace exists!");
                }
            }
            $result = parent::ajax_custom_form_save();
            if($result['status']=='1' && isset($result['record_id'])){
                $rate = 1;
                $warehouse_id = 0;
                if(($result2 = $this->CI->db->query('SELECT a.warehouse_id,b.currency FROM stores a, marketplaces b WHERE a.id="'.$result['record_id'].'" and a.marketplace_id=b.id LIMIT 1')) && ($row = $result2->row_array())){
                    $rate = $this->get_rate($row['currency']);
                    $warehouse_id = $row['warehouse_id'];
                }
                $sql = 'insert into store_item(store_id,warehouse_item_id,store_skucode,selling_price,expire_date)
                    select "'.$result['record_id'].'",d.id,concat(a.code,"-",c.code) skucode,(ifnull(d.selling_price,0) * '.$rate.'),ifnull(d.expire_date,"0000-00-00") from products a
                    join options b on a.option_id=b.id
                    join option_item c on a.option_id=c.option_id
                    join warehouse_item d on d.product_id=a.id and item_id=c.id and d.warehouse_id="'.$warehouse_id.'"
                    left join store_item e on d.id=e.warehouse_item_id and e.store_id="'.$result['record_id'].'"
                    where e.id is null ORDER BY a.id,c.id';
                $this->CI->db->query($sql);
            }    
        }
        
        return $result;
    }
    
    function ajax_change_update(){
        $filter_list = array();
        $filter_list[] = ['name'=>'account_id'];
        $filter_list[] = ['name'=>'marketplace_template','query'=>'SELECT b.import_template id,b.import_template name FROM stores a,marketplaces b WHERE a.marketplace_id=b.id AND a.account_id=? GROUP BY b.import_template ORDER BY length(b.import_template),b.import_template','id'=>'account_id'];
        
        $return = parent::ajax_change_update($filter_list);
        
        return $return;
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