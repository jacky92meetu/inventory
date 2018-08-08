<?php

require_once('lensesMain.php');

class lensesReportMonthlySales extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->title = "Monthly Sales Report (RM)";
        $this->freezePane = 2;
        $this->add_btn = false;
        $this->delete_btn = false;
        $this->display_chart = true;
        $this->page_view = 'page-view2';
        $this->search_query = 'select * from (select d.name account_name, c.name store_name, concat(left(a.payment_date,7),"-01") payment_date
            ,count(a.id) trans_count
            ,sum(ifnull(a.quantity,0)) sold_qty
            ,round(sum(ifnull(a.selling_price,0) * ifnull(a.quantity,0) / ifnull(er.rate,1)),4) selling_price
            ,round(sum(ifnull(a.shipping_charges_received,0) / ifnull(er.rate,1)),4) shipping_charges_received
            ,round(sum(ifnull(a.shipping_charges_paid,0) / ifnull(er.rate,1)),4) shipping_charges_paid
            ,round(
                sum((ifnull(a.sales_fees_pect,0) / 100 * ifnull(a.selling_price,0) * ifnull(a.quantity,0) / ifnull(er.rate,1))
                + (ifnull(a.sales_fees_fixed,0) / ifnull(er.rate,1))
                + (ifnull(a.paypal_fees_pect,0) / 100 * ifnull(a.selling_price,0) * ifnull(a.quantity,0) / ifnull(er.rate,1))
                + (ifnull(a.paypal_fees_fixed,0) / ifnull(er.rate,1)))
            ,4) fees
            ,round(sum(ifnull(a.cost_price,0) * ifnull(a.quantity,0)),4) cost_price
            ,c.marketplace_id
            ,c.account_id
            ,date_format(a.payment_date,"%b %Y") payment_month
            from transactions a
            join store_item b on b.id=a.store_item_id
            join stores c on c.id=b.store_id
            join accounts d on d.id=c.account_id
            left join exchange_rate er on er.from_code="MYR" and er.to_code=a.selling_currency and er.created_date=a.payment_date
            WHERE 1=1 '.((!$this->get_user_access($_SESSION['user']['user_type'],"view_all_user_transaction"))?' AND a.created_by="'.$_SESSION['user']['id'].'" ':'').'
            group by payment_month
            order by payment_date
            ) a {WHERE} ';
        
        $sales_year_list = array();
        if(($result = $this->CI->db->query('SELECT year(payment_date) sales_year FROM transactions group by sales_year order by sales_year'))){
            foreach($result->result_array() as $value){
                if(strlen($value['sales_year'])==4){
                    $sales_year_list[$value['sales_year']] = $value['sales_year'];
                }
            }
        }
        
        $marketplace_list = array();
        if(($result = $this->CI->db->query('SELECT id,name FROM marketplaces order by name'))){
            $marketplace_list[0] = "All Marketplaces";
            foreach($result->result_array() as $value){
                $marketplace_list[$value['id']] = $value['name'];
            }
        }
        
        $account_list = array();
        if(($result = $this->CI->db->query('SELECT id,name FROM accounts order by name'))){
            $account_list[0] = "All Accounts";
            foreach($result->result_array() as $value){
                $account_list[$value['id']] = $value['name'];
            }
        }
        
        $this->header = array(array('id'=>'store_name','name'=>'Store Name'),array('id'=>'selling_price','name'=>'Unit/Combo Selling Price'),array('id'=>'shipping_charges_received','name'=>'+Shipping $'),array('id'=>'shipping_charges_paid','name'=>'-Shipping $'),array('id'=>'fees','name'=>'Fees'),array('id'=>'cost_price','name'=>'Product Cost'),array('id'=>'payment_month','name'=>'Payment Month'),array('id'=>'marketplace_id','name'=>'MarketPlace ID'),array('id'=>'account_id','name'=>'Account ID'),array('id'=>'payment_date','name'=>'Payment Date','is_date'=>'1'));
        
        $this->extra_filter_header = array(
            'sales_year' => array('id'=>'sales_year','name'=>'Sales Year','option_text'=>$sales_year_list,'editable'=>true),
            'account_id' => array('id'=>'account_id','name'=>'Account','option_text'=>$account_list,'editable'=>true),
            'marketplace_id' => array('id'=>'marketplace_id','name'=>'Market Place','option_text'=>$marketplace_list,'editable'=>true),
        );
    }
    
    function view($view){
        $this->ajax_read();
        $data = array('title'=>'','header'=>array('Monthly_Item_Detail'),'data'=>array());
        $temp = $this->extra_filter_header['account_id']['option_text'][$this->extra_filter_header['account_id']['value']];
        if(empty($temp) || $temp=="0"){
            $temp = "All Accounts";
        }
        $data['title'] .= "[ ".$temp." ] : ";
        $temp = $this->extra_filter_header['marketplace_id']['option_text'][$this->extra_filter_header['marketplace_id']['value']];
        if(empty($temp) || $temp=="0"){
            $temp = "All Marketplaces";
        }
        $data['title'] .= "[ ".$temp." ] : ";
        $data['title'] .= "From ".$this->extra_filter_header['payment_date|from_date']['value']." To ".$this->extra_filter_header['payment_date|to_date']['value'];
        
        $data['data'] = array(
            array('Item Selling Price'),
            array('+Shipping $'),
            array('Gross Profit'),
            array('-Shipping $'),
            array('Fees'),
            array('Product Cost'),
            array('Net Profit'),
        );
        
        foreach($this->data as $v){
            $data['data'][0][] = $v[1];
            $data['data'][1][] = $v[2];
            $data['data'][2][] = $v[1]+$v[2];
            $data['data'][3][] = $v[3];
            $data['data'][4][] = $v[4];
            $data['data'][5][] = $v[5];
            $data['data'][6][] = $v[1]+$v[2]-$v[3]-$v[4]-$v[5];
            $data['header'][] = $v[6];
        }
        $this->CI->cpage->set_html_title($this->title);
        $this->CI->cpage->set('selected_menu',$this->selected_menu);
        $this->CI->cpage->set('view_title',$this->title);
        $this->CI->cpage->set('view_contents',$data);
        $this->CI->cpage->set('view_ajax_url',base_url('ajax/'.$view));
        $this->CI->cpage->set('extra_filter',$this->extra_filter_header);
        return $this->CI->load->view($this->page_view);
    }
    
    function ajax_custom_form_save(){
        if(!empty($_POST['value']['type']) && $_POST['value']['type']=="extra_filter" && !empty($_POST['value']['sales_year'])){
            $_POST['value']['payment_date|range_date'] = "custom";
            $_POST['value']['payment_date|from_date'] = "01/01/".$_POST['value']['sales_year'];
            $_POST['value']['payment_date|to_date'] = "31/12/".$_POST['value']['sales_year'];
        }
        $return = parent::ajax_custom_form_save();
        if($return['status']=="1"){
            $return['func'] = 'function(){location.reload();}';
        }
        return $return;
    }
    
}