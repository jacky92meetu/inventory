<?php

require_once('lensesMain.php');

class lensesHome extends lensesMain{
    
    var $CI = false;
    
    function __construct(){
        parent::__construct();
        $this->CI = get_instance();
    }
    
    function view($view){
        $this->title = "Dashboard";
        $this->CI->cpage->set_html_title($this->title);
        
        $data = array();
        
        $temp = array();
        if(($result = $this->CI->db->query('select concat(d.name," ",code2) product_name,sum(ifnull(a.quantity,0)) total_qty
                from transactions a
                left join store_item b on b.id=a.store_item_id
                left join warehouse_item c on c.id=warehouse_item_id
                left join products d on d.id=c.product_id
                left join option_item e on e.id=c.item_id
                where a.payment_date=DATE_FORMAT(now(), "%Y-%m-%d")
                '.((!$this->get_user_access($_SESSION['user']['user_type'],"view_all_user_transaction"))?' AND a.created_by="'.$_SESSION['user']['id'].'" ':'').'
                group by a.store_item_id
                order by total_qty desc limit 10;'))){
            foreach($result->result_array() as $value){
                $temp[] = $value;
            }
        }
        $data['top_10_daily_deals'] = $temp;
        
        $temp = array();
        if(($result = $this->CI->db->query('select concat(d.name," ",code2) product_name,sum(ifnull(a.quantity,0)) total_qty
                from transactions a
                left join store_item b on b.id=a.store_item_id
                left join warehouse_item c on c.id=warehouse_item_id
                left join products d on d.id=c.product_id
                left join option_item e on e.id=c.item_id
                where a.payment_date>=DATE_FORMAT(date_add(now(),INTERVAL -7 DAY), "%Y-%m-%d")
                '.((!$this->get_user_access($_SESSION['user']['user_type'],"view_all_user_transaction"))?' AND a.created_by="'.$_SESSION['user']['id'].'" ':'').'
                group by a.store_item_id
                order by total_qty desc limit 10;'))){
            foreach($result->result_array() as $value){
                $temp[] = $value;
            }
        }
        $data['top_10_weekly_deals'] = $temp;
        
        $temp = array();
        if(($result = $this->CI->db->query('select concat(d.name," ",code2) product_name,sum(ifnull(a.quantity,0)) total_qty
                from transactions a
                left join store_item b on b.id=a.store_item_id
                left join warehouse_item c on c.id=warehouse_item_id
                left join products d on d.id=c.product_id
                left join option_item e on e.id=c.item_id
                where a.payment_date>=DATE_FORMAT(date_add(now(),INTERVAL -29 DAY), "%Y-%m-%d")
                '.((!$this->get_user_access($_SESSION['user']['user_type'],"view_all_user_transaction"))?' AND a.created_by="'.$_SESSION['user']['id'].'" ':'').'
                group by a.store_item_id
                order by total_qty desc limit 10;'))){
            foreach($result->result_array() as $value){
                $temp[] = $value;
            }
        }
        $data['top_10_monthly_deals'] = $temp;
                
        $temp2 = array();
        if(($result = $this->CI->db->query('select c.name store_name, a.payment_date, sum(ifnull(a.quantity,0)) total_qty
            from transactions a
            left join store_item b on b.id=a.store_item_id
            left join stores c on c.id=b.store_id
            where a.payment_date>DATE_FORMAT(date_add(now(),INTERVAL -29 DAY), "%Y-%m-%d")
            '.((!$this->get_user_access($_SESSION['user']['user_type'],"view_all_user_transaction"))?' AND a.created_by="'.$_SESSION['user']['id'].'" ':'').'
            group by b.store_id,a.payment_date
            ;'))){
            foreach($result->result_array() as $value){
                if(!isset($temp2[$value['store_name']])){
                    $temp2[$value['store_name']] = array();
                }
                $temp2[$value['store_name']][$value['payment_date']] = $value['total_qty'];
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
        $date = date("Y-m-d");
        $sum_value = 0;
        for($i=0; $i<30; $i++){
            $temp[$date] = array();
            foreach($temp3 as $k => $v){
                $temp[$date][$k] = (!empty($temp2[$v][$date]))?$temp2[$v][$date]:0;
                $sum_value += $temp[$date][$k];
                if(!isset($temp4[$k])){
                    $temp4[$k] = 0;
                }
                $temp4[$k] += $temp[$date][$k];
            }
            $date = date("Y-m-d",strtotime($date.' -1 day'));
        }
        $data['monthly_deals'] = array('total'=>$sum_value,'header'=>$temp3,'total2'=>$temp4,'data'=>array_reverse($temp));
        
        $temp2 = array();
        if(($result = $this->CI->db->query('select c.name store_name, a.payment_date
            ,round(sum(
                    (ifnull(a.selling_price,0) * ifnull(a.quantity,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1))
                + (ifnull(a.shipping_charges_received,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1))
                    - (ifnull(a.shipping_charges_paid,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1))
                    - (ifnull(a.sales_fees_pect,0) / 100 * ifnull(a.selling_price,0) * ifnull(a.quantity,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1))
                    - (ifnull(a.sales_fees_fixed,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1))
                    - (ifnull(a.paypal_fees_pect,0) / 100 * ifnull(a.selling_price,0) * ifnull(a.quantity,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1))
                    - (ifnull(a.paypal_fees_fixed,0) / ifnull((select rate from exchange_rate where from_code="MYR" and to_code=a.selling_currency and created_date<=a.payment_date order by id desc limit 1),1))
                    - (ifnull(a.cost_price,0) * ifnull(a.quantity,0))
            ),4) profit_amount
            from transactions a
            left join store_item b on b.id=a.store_item_id
            left join stores c on c.id=b.store_id
            where a.payment_date>=DATE_FORMAT(date_add(now(),INTERVAL -29 DAY), "%Y-%m-%d")
            '.((!$this->get_user_access($_SESSION['user']['user_type'],"view_all_user_transaction"))?' AND a.created_by="'.$_SESSION['user']['id'].'" ':'').'
            group by b.store_id,a.payment_date
            ;'))){
            foreach($result->result_array() as $value){
                if(!isset($temp2[$value['store_name']])){
                    $temp2[$value['store_name']] = array();
                }
                $temp2[$value['store_name']][$value['payment_date']] = $value['profit_amount'];
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
        $date = date("Y-m-d");
        $sum_value = 0;
        for($i=0; $i<30; $i++){
            $temp[$date] = array();
            foreach($temp3 as $k => $v){
                $temp[$date][$k] = (!empty($temp2[$v][$date]))?$temp2[$v][$date]:0;
                $sum_value += $temp[$date][$k];
                if(!isset($temp4[$k])){
                    $temp4[$k] = 0;
                }
                $temp4[$k] += $temp[$date][$k];
            }
            $date = date("Y-m-d",strtotime($date.' -1 day'));
        }
        $data['monthly_profit'] = array('total'=>$sum_value,'header'=>$temp3,'total2'=>$temp4,'data'=>array_reverse($temp));
        
        return $this->CI->load->view('page-home',array('dashboard_data'=>$data));
    }
}