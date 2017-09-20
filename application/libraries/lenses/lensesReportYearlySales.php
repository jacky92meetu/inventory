<?php

require_once('lensesMain.php');

class lensesReportYearlySales extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
        $this->setup();
    }
    
    function setup(){
        $this->title = "Yearly Sales Report";
        $this->freezePane = 2;
        $this->add_btn = false;
        $this->delete_btn = false;
        $this->display_chart = true;$this->page_view = 'page-chartview';
        $this->search_query = 'select * from (select c.name store_name, a.payment_date
            ,round(ifnull(a.selling_price,0) * ifnull(a.quantity,0) / ifnull(d1.rate,1),4) selling_price
            ,round(ifnull(a.shipping_charges_received,0) / ifnull(d1.rate,1),4) shipping_charges_received
            ,round(ifnull(a.shipping_charges_paid,0) / ifnull(d1.rate,1),4) shipping_charges_paid
            ,round(
                (ifnull(a.sales_fees_pect,0) / 100 * ifnull(a.selling_price,0) * ifnull(a.quantity,0) / ifnull(d1.rate,1))
                + (ifnull(a.sales_fees_fixed,0) / ifnull(d1.rate,1))
                + (ifnull(a.paypal_fees_pect,0) / 100 * ifnull(a.selling_price,0) * ifnull(a.quantity,0) / ifnull(d1.rate,1))
                + (ifnull(a.paypal_fees_fixed,0) / ifnull(d1.rate,1))
            ,4) fees
            ,round(ifnull(e.cost_price,0) * 1,4) cost_price
            from transactions a
            left join store_item b on b.id=a.store_item_id
            left join stores c on c.id=b.store_id
            left join exchange_rate d1 on d1.from_code="MYR" and d1.to_code=a.selling_currency and d1.created_date<=a.payment_date
            left join exchange_rate d2 on d2.from_code="MYR" and d2.to_code=a.selling_currency and d2.created_date<=a.payment_date and d2.id<d1.id
            left join warehouse_item e on e.id=b.warehouse_item_id
            where d2.id is null
            group by b.store_id,a.payment_date
            ) a';
        
        $this->header = array(array('id'=>'store_name','name'=>'Store Name'),array('id'=>'payment_date','name'=>'Payment Date','filter-sorting'=>'asc'),array('id'=>'selling_price','name'=>'Selling Price'),array('id'=>'shipping_charges_received','name'=>'+Shipping $'),array('id'=>'shipping_charges_paid','name'=>'-Shipping $'),array('id'=>'fees','name'=>'Fees'),array('id'=>'cost_price','name'=>'Product Cost'));
        
        $this->extra_filter_header = array(
            array('id'=>'payment_date|from_date','name'=>'From Date','is_date'=>'1','value'=>date("d/m/Y",strtotime('-30 day')),'editable'=>true),
            array('id'=>'payment_date|to_date','name'=>'To Date','is_date'=>'1','value'=>date("d/m/Y"),'editable'=>true)
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
            if(($size = date_diff(new DateTime($min_date), new DateTime($max_date))) && $size->days>0){
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
    
}