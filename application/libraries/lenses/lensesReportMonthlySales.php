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
        $this->display_chart = true;$this->page_view = 'page-monthlyview';
        $this->search_query = 'select * from (select d.name account_name, c.name store_name, concat(left(a.payment_date,7),"-01") payment_date
            ,count(a.id) trans_count
            ,sum(ifnull(a.quantity,0)) sold_qty
            ,round(sum(ifnull(a.selling_price,0) * ifnull(a.quantity,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1)),4) selling_price
            ,round(sum(ifnull(a.shipping_charges_received,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1)),4) shipping_charges_received
            ,round(sum(ifnull(a.shipping_charges_paid,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1)),4) shipping_charges_paid
            ,round(
                sum((ifnull(a.sales_fees_pect,0) / 100 * ifnull(a.selling_price,0) * ifnull(a.quantity,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1))
                + (ifnull(a.sales_fees_fixed,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1))
                + (ifnull(a.paypal_fees_pect,0) / 100 * ifnull(a.selling_price,0) * ifnull(a.quantity,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1))
                + (ifnull(a.paypal_fees_fixed,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1)))
            ,4) fees
            ,round(sum(ifnull(a.cost_price,0) * ifnull(a.quantity,0)),4) cost_price
            from transactions a
            left join store_item b on b.id=a.store_item_id
            left join stores c on c.id=b.store_id
            left join accounts d on d.id=c.account_id
            WHERE 1=1 '.((!$this->get_user_access($_SESSION['user']['user_type'],"view_all_user_transaction"))?' AND a.created_by="'.$_SESSION['user']['id'].'" ':'').'
            {WHERE_AND}
            group by b.store_id,payment_date
            ) a';
        
        $sales_year_list = array();
        if(($result = $this->CI->db->query('SELECT year(payment_date) sales_year FROM transactions group by sales_year order by sales_year'))){
            foreach($result->result_array() as $value){
                if(strlen($value['sales_year'])==4){
                    $sales_year_list[$value['sales_year']] = $value['sales_year'];
                }
            }
        }
        
        $this->header = array(array('id'=>'store_name','name'=>'Store Name'),array('id'=>'payment_date','name'=>'Payment Date','filter-sorting'=>'asc','is_date'=>'1'),array('id'=>'selling_price','name'=>'Unit/Combo Selling Price'),array('id'=>'shipping_charges_received','name'=>'+Shipping $'),array('id'=>'shipping_charges_paid','name'=>'-Shipping $'),array('id'=>'fees','name'=>'Fees'),array('id'=>'cost_price','name'=>'Product Cost'));
        
        $this->extra_filter_header = array(
            'sales_year' => array('id'=>'sales_year','name'=>'Sales Year','option_text'=>$sales_year_list,'editable'=>true),
        );
    }
    
    function view($view){
        $this->ajax_read();
        $data = array();
        if(sizeof($this->data)>0){
            $temp2 = array();
            $min_date = false;
            $max_date = false;
            foreach($this->data as $value){
                $value[1] = $this->from_display_date($value[1]);
                if(!isset($temp2[$value[0]])){
                    $temp2[$value[0]] = array();
                }
                $temp2[$value[0]][$value[1]] = floatval($value[2]) + floatval($value[3]) - floatval($value[4]) - floatval($value[5]) - floatval($value[6]);
                if(!$min_date || strtotime($value[1])<strtotime($min_date)){
                    $min_date = $value[1];
                }
                if(!$max_date || strtotime($value[1])>strtotime($max_date)){
                    $max_date = $value[1];
                }
            }
            $temp = array();
            $temp3 = array();
            $temp4 = array();
            $count = 'a';
            foreach(array_keys($temp2) as $v){
                $temp3[$count] = $v;
                $count++;
            }
            $sum_value = 0;
            $date = $min_date;
            if(($size = date_diff(new DateTime($min_date), new DateTime($max_date))) && $size->days>=0){
                for($i=0; $i<=$size->days; $i++){
                    $temp[$date] = array();
                    foreach($temp3 as $k => $v){
                        $temp[$date][$k] = (!empty($temp2[$v][$date]))?$temp2[$v][$date]:0;
                        $sum_value += $temp[$date][$k];
                        if(!isset($temp4[$k])){
                            $temp4[$k] = 0;
                        }
                        $temp4[$k] += $temp[$date][$k];
                    }
                    $date = date("Y-m-d",strtotime($date.' +1 day'));
                }
            }
            $data = array('total'=>$sum_value,'header'=>$temp3,'total2'=>$temp4,'data'=>$temp);
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