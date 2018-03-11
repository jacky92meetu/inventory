<?php

require_once('lensesMain.php');

class lensesSalesInvoice extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->CI->cpage->set('breadcrumb',array('Sales Invoice'=>''));
        $this->table = "transactions";
        $this->title = "Sales Invoice";
        $this->selected_menu = "sales_invoice";
        $this->freezePane = 5;
        $this->is_required = false;
        $this->extra_btn = array();
        $this->extra_btn[] = array('name'=>'Generate Invoice','url'=>base_url('ajax/sales_invoice?method=generate_invoice'),'require_select'=>'1');
        $this->extra_btn[] = array('name'=>'Generate Credit Note','url'=>base_url('ajax/sales_invoice?method=generate_creditnote'),'require_select'=>'1');
        $this->extra_btn[] = array('name'=>'Download Invoice as Excel','url'=>base_url('ajax/sales_invoice?method=generate_invoice&method2=download_invoice'),'require_select'=>'1');
        $this->extra_btn[] = array('name'=>'Download Invoice as PDF','url'=>base_url('ajax/sales_invoice?method=generate_invoice&method2=download_invoice_pdf'),'require_select'=>'1');
        $this->extra_btn[] = array('name'=>'Download Credit Note as Excel','url'=>base_url('ajax/sales_invoice?method=generate_creditnote&method2=download_creditnote'),'require_select'=>'1');
        $this->extra_btn[] = array('name'=>'Download Credit Note as PDF','url'=>base_url('ajax/sales_invoice?method=generate_creditnote&method2=download_creditnote_pdf'),'require_select'=>'1');
        $this->custom_form = false;
        $this->add_btn = false;
        $this->delete_btn = false;
        $this->ajax_url = base_url('ajax/sales_invoice');
        $this->search_query = 'select * from (select a.id
            , b.id account_id
            , g.name store_name
            , GROUP_CONCAT(DISTINCT a.store_skucode separator "\n") store_skucode
            , GROUP_CONCAT(DISTINCT TRIM(CONCAT(d.name," ",e.code2," X ",a.quantity)) separator "\n") product_name
            , a.buyer_name, a.buyer_email
            , a.payment_date, a.sales_id
            ,if(ifnull(ti.sales_id,"")<>"",ti.inv_text,"") inv_no
            ,if(ifnull(ti.sales_id,"")<>"",ifnull(ti.created_date,""),"") inv_date
            ,if(ifnull(ti.sales_id,"")<>"",1,0) inv_create
            ,if(ifnull(tcn.inv_id,"")<>"",tcn.cn_text,"") cn_no
            ,if(ifnull(tcn.inv_id,"")<>"",ifnull(tcn.created_date,""),"") cn_date
            ,if(ifnull(tcn.inv_id,"")<>"",1,0) cn_create
            from transactions a
            join accounts b on a.account_id=b.id
            join store_item c on a.store_item_id=c.id
            join warehouse_item wi on c.warehouse_item_id=wi.id
            join products d on wi.product_id=d.id
            join option_item e on wi.item_id=e.id
            left join couriers f on a.courier_id=f.id
            join stores g on c.store_id=g.id
            left join transactions_inv ti on ti.sales_id=a.sales_id
            left join transactions_inv_cn tcn on tcn.inv_id=ti.inv_id
            group by a.sales_id
            ) a';
        
        $supp_list = array();
        if(($result = $this->CI->db->query('SELECT id,name FROM accounts ORDER BY name'))){
            foreach($result->result_array() as $value){
                $supp_list[$value['id']] = $value['name'];
            }
        }
        
        $this->header = array(
            array('id'=>'id','name'=>'ID'),
            array('id'=>'account_id','name'=>'Account','option_text'=>$supp_list),
            array('id'=>'store_name','name'=>'Store'),
            array('id'=>'store_skucode','name'=>'SKU'),
            array('id'=>'product_name','name'=>'Frame/Color'),
            array('id'=>'buyer_name','name'=>'Buyer Name'),
            array('id'=>'buyer_email','name'=>'Buyer Email'),
            array('id'=>'payment_date','name'=>'Payment Date','is_date'=>'1','is_date_highlight'=>'1'),
            array('id'=>'sales_id','name'=>'Sales ID'),
            array('id'=>'inv_no','name'=>'Inv. No','goto'=>base_url('sales_invoice/print_invoice')),
            array('id'=>'inv_date','name'=>'Inv. Created Date'),
            array('id'=>'inv_create','name'=>'Inv. Was Create','option_text'=>array('0'=>'No','1'=>'Yes')),
            array('id'=>'cn_no','name'=>'CN. No','goto'=>base_url('sales_invoice/print_creditnote')),
            array('id'=>'cn_date','name'=>'CN. Created Date'),
            array('id'=>'cn_create','name'=>'CN. Was Create','option_text'=>array('0'=>'No','1'=>'Yes')),
        );
    }
    
    function ajax_generate_invoice(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        $method2 = $this->CI->input->post_get('method2',true);
        if(($result = $this->CI->db->query('select a.sales_id, a.account_id from transactions a left join transactions_inv b on b.sales_id=a.sales_id where b.sales_id is null and a.id in ? group by a.id',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                $this->CI->db->query('INSERT INTO transactions_inv SET account_id=?, sales_id=?',array($row['account_id'],$row['sales_id']));
                $this->CI->db->query('UPDATE transactions_inv a,accounts b SET a.inv_text=concat(ifnull(b.acc_comp_inv_prefix,""),right(concat("00000000",ifnull(a.inv_id,"")),8)) WHERE a.account_id=b.id and a.sales_id=?',array($row['sales_id']));
            }
            $return['message'] = "Invoice(s) generated successfully.";
            $return['status'] = "1";
        }else{
            $return['message'] = "Fail to generate Invoice.";
        }
        if(array_search($method2,array("download_invoice","download_invoice_pdf"))!==FALSE){
            $return['message'] .= "<div>Download in process...</div>";
            $return['func'] = "function(){post('".base_url('/sales_invoice/'.$method2)."', {selection:\"".implode(",",$selection)."\"}, '_blank', 'POST');}";
        }
        return $return;
    }
    
    function ajax_generate_creditnote(){
        $return = array("status"=>"0","message"=>"");
        $selection = $this->CI->input->post('selection',true);
        $method2 = $this->CI->input->post_get('method2',true);
        if(($result = $this->CI->db->query('select b.inv_id, a.account_id from transactions a join transactions_inv b on b.sales_id=a.sales_id left join transactions_inv_cn c on c.inv_id=b.inv_id where c.cn_id is null and a.id in ? group by a.id',array($selection))) && $result->num_rows()){
            foreach($result->result_array() as $row){
                $this->CI->db->query('INSERT INTO transactions_inv_cn SET account_id=?, inv_id=?',array($row['account_id'],$row['inv_id']));
                $this->CI->db->query('UPDATE transactions_inv_cn a,accounts b SET a.cn_text=concat(ifnull(b.acc_comp_cn_prefix,""),right(concat("00000000",ifnull(a.cn_id,"")),8)) WHERE a.account_id=b.id and a.inv_id=?',array($row['inv_id']));
            }
            $return['message'] = "Credit Note(s) generated successfully.";
            $return['status'] = "1";
        }else{
            $return['message'] = "Fail to generate Credit Note.";
        }
        if(array_search($method2,array("download_creditnote","download_creditnote_pdf"))!==FALSE){
            $return['message'] .= "<div>Download in process...</div>";
            $return['func'] = "function(){post('".base_url('/sales_invoice/'.$method2)."', {selection:\"".implode(",",$selection)."\"}, '_blank', 'POST');}";
        }
        return $return;
    }
    
    function download_invoice(){
        include_once(APPPATH.'libraries/classes/ExcelHelper.php');
        $class = new ExcelHelper;
        $selection = $this->CI->input->post('selection',true);
        return $class->exec('invoice_my_1',array('selected_id'=>explode(",",$selection),'lensesClass'=>$this),false);
    }
    
    function download_invoice_pdf(){
        include_once(APPPATH.'libraries/classes/ExcelHelper.php');
        $class = new ExcelHelper;
        $selection = $this->CI->input->post('selection',true);
        return $class->exec('invoice_my_1',array('selected_id'=>explode(",",$selection),'lensesClass'=>$this),true);
    }
    
    function print_invoice(){
        include_once(APPPATH.'libraries/classes/ExcelHelper.php');
        $class = new ExcelHelper;
        $id = $this->CI->input->post_get('id',true);
        return $class->exec('invoice_my_1',array('selected_id'=>$id,'lensesClass'=>$this),true);
    }
    
    function download_creditnote(){
        include_once(APPPATH.'libraries/classes/ExcelHelper.php');
        $class = new ExcelHelper;
        $selection = $this->CI->input->post('selection',true);
        return $class->exec('creditnote_my_1',array('selected_id'=>explode(",",$selection),'lensesClass'=>$this),false);
    }
    
    function download_creditnote_pdf(){
        include_once(APPPATH.'libraries/classes/ExcelHelper.php');
        $class = new ExcelHelper;
        $selection = $this->CI->input->post('selection',true);
        return $class->exec('creditnote_my_1',array('selected_id'=>explode(",",$selection),'lensesClass'=>$this),true);
    }
    
    function print_creditnote(){
        include_once(APPPATH.'libraries/classes/ExcelHelper.php');
        $class = new ExcelHelper;
        $id = $this->CI->input->post_get('id',true);
        return $class->exec('creditnote_my_1',array('selected_id'=>$id,'lensesClass'=>$this),true);
    }
}
