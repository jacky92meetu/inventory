<?php

include_once 'importClass.php';

class importShippingClass extends importClass{
    
    var $fp_cur = "";
    var $fp_amt = "";
    
    function __construct() {
        parent::__construct();
    }
    
    function shipping_export($post_data){
        if(empty($post_data['selection'])){
            return false;
        }
        if(!empty($post_data['fp_amt']) && is_numeric($post_data['fp_amt']) && !empty($post_data['fp_cur'])){
            $this->fp_cur = $post_data['fp_cur'];
            $this->fp_amt = $post_data['fp_amt'];
        }
        $selection = $post_data['selection'];
        $temp = explode("|",$this->account_id);
        $this->account_id = $temp[0];
        $name = $temp[1];
        $template = $temp[2];
        
        $item_list = array();
        $sql = 'select a.id
            , b.name account_name
            , g.name store_name
            , c.store_skucode
            , d.name product_name
            , e.code2 option_name
            , a.buyer_id, a.buyer_name
            , ifnull(a.buyer_address,"") buyer_address, ifnull(a.buyer_address2,"") buyer_address2, ifnull(a.buyer_address3,"") buyer_address3
            , a.buyer_city, a.buyer_state, a.buyer_postcode, a.buyer_country, a.buyer_contact, a.buyer_email, a.tracking_number
            , a.selling_currency, a.quantity
            , a.selling_price, a.shipping_charges_received, a.payment_date, a.shipment_date
            , f.name courier_name, a.shipping_charges_paid, a.sales_id
            , a.paypal_trans_id, a.sales_fees_pect, a.sales_fees_fixed, a.paypal_fees_pect, a.paypal_fees_fixed
            ,b.id account_id, a.store_item_id, a.courier_id, g.id store_id, d.id product_id
            ,upper(if(ifnull(b.acc_comp_inv_prefix,"")<>"",b.acc_comp_inv_prefix,left(b.name,2))) account_code
            from transactions_cache a
            join accounts b on a.account_id=b.id
            join store_item c on a.store_item_id=c.id
            join warehouse_item wi on c.warehouse_item_id=wi.id
            join products d on wi.product_id=d.id
            join option_item e on wi.item_id=e.id
            left join couriers f on a.courier_id=f.id
            join stores g on c.store_id=g.id
            WHERE a.id IN ?';
        $binding = array($selection);
        if(($result = $this->CI->db->query($sql,$binding)) && $result->num_rows()){
            $item_list = $result->result_array();
        }
        
        if(stristr($template, 'sing')!==FALSE){
            $this->singpost_export($item_list);
        }else if(stristr($template, 'global')!==FALSE){
            $this->globalmail_export($item_list);
        }else if(stristr($template, 'parceldirect')!==FALSE){
            $this->parceldirect_export($item_list);
        }else if(stristr($template, 'fdc')!==FALSE){
            $this->fdc_export($item_list);
        }
        exit;
    }
    
    function parceldirect_export($item_list){
        $template_path = APPPATH.'libraries/classes/templates/globalmail_export.xlsx';
        if(!file_exists($template_path)){
            exit;
        }
        
        $inputFileType = PHPExcel_IOFactory::identify($template_path);
        $objPHPExcel = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objPHPExcel->load($template_path);
                
        $worksheet = $objPHPExcel->getSheetByName('Country Code');
        $country_list = array();
        $temp = $worksheet->toArray('',true,true,false);
        foreach($temp as $v){
            $name = strtoupper($v[0]);
            $country_list[$name] = strtoupper($v[1]);
        }
        unset($temp);
        $avail_shipping_list = array('PLE'=>array('US'),'PLT'=>array('GB', 'AU', 'TH', 'SG'));
        $avail_Incoterm_list = array('DDP'=>array('US', 'TH', 'SG'),'DDU'=>array('AU', 'GB'));
        
        $worksheet = $objPHPExcel->getSheetByName('Sheet1');
        
        $repeated_row = array();
        $field_limit = array();
        $field_limit['sales_id'] = array('col_name'=>'C','col_id'=>2,'size'=>35);
        $field_limit['buyer_name'] = array('col_name'=>'G','col_id'=>6,'size'=>30);
        $field_limit['buyer_address'] = array('col_name'=>'H','col_id'=>7,'size'=>50);
        $field_limit['buyer_address2'] = array('col_name'=>'I','col_id'=>8,'size'=>50);
        $field_limit['buyer_address3'] = array('col_name'=>'J','col_id'=>9,'size'=>30);
        $field_limit['buyer_city'] = array('col_name'=>'K','col_id'=>10,'size'=>30);
        
        $row = 2;
        foreach($item_list as $data){
            if(strlen($this->fp_amt)>0 && strlen($this->fp_cur)>0 && strtolower($this->fp_cur)!='default'){
                $data['selling_currency'] = strtoupper($this->fp_cur);
                $data['selling_price'] = $this->fp_amt;
            }
            
            foreach(array('buyer_name','buyer_address') as $cf){
                $temp = $data[$cf];
                if(array_key_exists($temp, $repeated_row)!==FALSE){
                    $worksheet->getStyle('A'.$row.':CA'.$row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '96ffdd')
                            )
                        )
                    );
                    
                    $worksheet->getStyle('A'.$repeated_row[$temp].':CA'.$repeated_row[$temp])->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '96ffdd')
                            )
                        )
                    );
                    
                    break;
                }    
            }
            
            $worksheet->setCellValueExplicitByColumnAndRow(0,$row, "5254892643");
            $worksheet->setCellValueExplicitByColumnAndRow(1,$row, $data['store_name']);
            $worksheet->setCellValueExplicitByColumnAndRow(2,$row, $data['sales_id']);
            
            foreach($field_limit as $k => $v){
                if(!isset($data[$k])){continue;}
                if(strlen($data[$k]) > $v['size']){
                    $worksheet->getStyle('N'.$row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'FF0000')
                            )
                        )
                    );
                }
                $worksheet->setCellValueExplicitByColumnAndRow($v['col_id'],$row, $data[$k]);
            }
            
            $worksheet->setCellValueExplicitByColumnAndRow(11,$row, $data['buyer_state']);
            $worksheet->setCellValueExplicitByColumnAndRow(12,$row, $data['buyer_postcode']);
            
/**START********************************/            
            $temp = strtoupper($data['buyer_country']);
            if(array_key_exists($temp, $country_list)!==FALSE){
                $temp = $country_list[$temp];
            }else if(strlen($temp)==2 && array_search($temp, $country_list)!==FALSE){
                
            }else{
                $worksheet->getStyle('N'.$row)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FF0000')
                        )
                    )
                );
                $temp = $data['buyer_country'];
            }
            $worksheet->setCellValueExplicitByColumnAndRow(13,$row, $temp);
                        
            $selected_shipping = "";
            foreach($avail_shipping_list as $k => $v){
                if(array_search(strtoupper($temp), $v)!==FALSE){
                    $selected_shipping = $k;
                    break;
                }
            }
            if($selected_shipping==""){
                $worksheet->getStyle('E'.$row)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FF0000')
                        )
                    )
                );
            }
            $worksheet->setCellValueExplicitByColumnAndRow(4,$row, $selected_shipping);
            
            $selected_shipping = "";
            foreach($avail_Incoterm_list as $k => $v){
                if(array_search(strtoupper($temp), $v)!==FALSE){
                    $selected_shipping = $k;
                    break;
                }
            }
            if($selected_shipping==""){
                $worksheet->getStyle('W'.$row)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FF0000')
                        )
                    )
                );
            }
            $worksheet->setCellValueExplicitByColumnAndRow(22,$row, $selected_shipping);
/**END********************************/            
            
            //$worksheet->setCellValueExplicitByColumnAndRow(16,$row, (100 * $data['quantity']));
            $worksheet->setCellValueExplicitByColumnAndRow(16,$row, "100");
            
            $worksheet->setCellValueExplicitByColumnAndRow(20,$row, $data['selling_currency']);
            if(strlen($this->fp_amt)>0 && strlen($this->fp_cur)>0 && strtolower($this->fp_cur)!='default'){
                $worksheet->setCellValueExplicitByColumnAndRow(21,$row, $data['selling_price']);
            }else{
                $worksheet->setCellValueExplicitByColumnAndRow(21,$row, $data['quantity'] * $data['selling_price']);
            }
            /*
            $worksheet->setCellValueExplicitByColumnAndRow(20,$row, 'USD');
            $worksheet->setCellValueExplicitByColumnAndRow(21,$row, 15);
            */
            $worksheet->setCellValueExplicitByColumnAndRow(33,$row, "Sunglasses case");
            
            $title = $data['product_name']." ".$data['option_name'];
            if($data['quantity']>1){
                $temp = array();
                foreach(explode(",",$data['option_name']) as $v){
                    $temp[] = $v." * ".$data['quantity'];
                }
                $temp = implode(", ",$temp);
                $title = $data['product_name']." ".$temp;
            }
            $title = strtoupper(substr($data['store_name'],0,1))."-".$title;
            $worksheet->setCellValueExplicitByColumnAndRow(37,$row, "Sunglasses case");
            //$worksheet->setCellValueExplicitByColumnAndRow(40,$row, $data['selling_price']);
            $worksheet->setCellValueExplicitByColumnAndRow(40,$row, 15);
            $worksheet->setCellValueExplicitByColumnAndRow(41,$row, "MY");
            
            //$worksheet->setCellValueExplicitByColumnAndRow(42,$row, $data['quantity']);
            $worksheet->setCellValueExplicitByColumnAndRow(42,$row, "1");
            
            $worksheet->setCellValueExplicitByColumnAndRow(44,$row, "Sunglasses case");
            $worksheet->setCellValueExplicitByColumnAndRow(47,$row, $title);
            
            $repeated_row[$data['buyer_name']] = $row;
            $repeated_row[$data['buyer_address']] = $row;
            $row++;
        }
        
        $this->_export($objPHPExcel, $inputFileType, 'Parcel_Direct_'.date("YmdHis"));
    }
    
    function globalmail_export($item_list){
        $template_path = APPPATH.'libraries/classes/templates/globalmail_export.xlsx';
        if(!file_exists($template_path)){
            exit;
        }
        
        $inputFileType = PHPExcel_IOFactory::identify($template_path);
        $objPHPExcel = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objPHPExcel->load($template_path);
                
        $worksheet = $objPHPExcel->getSheetByName('Country Code');
        $country_list = array();
        $temp = $worksheet->toArray('',true,true,false);
        foreach($temp as $v){
            $name = strtoupper($v[0]);
            $country_list[$name] = strtoupper($v[1]);
        }
        unset($temp);
        
        $worksheet = $objPHPExcel->getSheetByName('Sheet1');
        
        $repeated_row = array();
        $field_limit = array();
        $field_limit['sales_id'] = array('col_name'=>'C','col_id'=>2,'size'=>35);
        $field_limit['buyer_name'] = array('col_name'=>'G','col_id'=>6,'size'=>30);
        $field_limit['buyer_address'] = array('col_name'=>'H','col_id'=>7,'size'=>50);
        $field_limit['buyer_address2'] = array('col_name'=>'I','col_id'=>8,'size'=>50);
        $field_limit['buyer_address3'] = array('col_name'=>'J','col_id'=>9,'size'=>30);
        $field_limit['buyer_city'] = array('col_name'=>'K','col_id'=>10,'size'=>30);
        
        $row = 2;
        foreach($item_list as $data){
            if(strlen($this->fp_amt)>0 && strlen($this->fp_cur)>0 && strtolower($this->fp_cur)!='default'){
                $data['selling_currency'] = strtoupper($this->fp_cur);
                $data['selling_price'] = $this->fp_amt;
            }
            
            foreach(array('buyer_name','buyer_address') as $cf){
                $temp = $data[$cf];
                if(array_key_exists($temp, $repeated_row)!==FALSE){
                    $worksheet->getStyle('A'.$row.':CA'.$row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '96ffdd')
                            )
                        )
                    );
                    
                    $worksheet->getStyle('A'.$repeated_row[$temp].':CA'.$repeated_row[$temp])->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '96ffdd')
                            )
                        )
                    );
                    
                    break;
                }    
            }
            
            $worksheet->setCellValueExplicitByColumnAndRow(0,$row, "550443685");
            $worksheet->setCellValueExplicitByColumnAndRow(1,$row, $data['store_name']);
            $worksheet->setCellValueExplicitByColumnAndRow(2,$row, $data['sales_id']);
            $worksheet->setCellValueExplicitByColumnAndRow(4,$row, "PPS");
            
            foreach($field_limit as $k => $v){
                if(!isset($data[$k])){continue;}
                if(strlen($data[$k]) > $v['size']){
                    $worksheet->getStyle($v['col_name'].$row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'FF0000')
                            )
                        )
                    );
                }
                $worksheet->setCellValueExplicitByColumnAndRow($v['col_id'],$row, $data[$k]);
            }
            
            $worksheet->setCellValueExplicitByColumnAndRow(11,$row, $data['buyer_state']);
            $worksheet->setCellValueExplicitByColumnAndRow(12,$row, $data['buyer_postcode']);
            
            $temp = strtoupper($data['buyer_country']);
            if(array_key_exists($temp, $country_list)!==FALSE){
                $temp = $country_list[$temp];
            }else if(strlen($temp)==2 && array_search($temp, $country_list)!==FALSE){
                
            }else{
                $worksheet->getStyle('N'.$row)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FF0000')
                        )
                    )
                );
                $temp = $data['buyer_country'];
            }
            $worksheet->setCellValueExplicitByColumnAndRow(13,$row, $temp);
            
            //$worksheet->setCellValueExplicitByColumnAndRow(16,$row, (100 * $data['quantity']));
            $worksheet->setCellValueExplicitByColumnAndRow(16,$row, "100");
            
            $worksheet->setCellValueExplicitByColumnAndRow(20,$row, $data['selling_currency']);
            if(strlen($this->fp_amt)>0 && strlen($this->fp_cur)>0){
                $worksheet->setCellValueExplicitByColumnAndRow(21,$row, $data['selling_price']);
            }else{
                $worksheet->setCellValueExplicitByColumnAndRow(21,$row, $data['quantity'] * $data['selling_price']);
            }
            /*
            $worksheet->setCellValueExplicitByColumnAndRow(20,$row, 'USD');
            $worksheet->setCellValueExplicitByColumnAndRow(21,$row, 15);
            */
            $worksheet->setCellValueExplicitByColumnAndRow(33,$row, "Sunglasses case");
            
            $title = $data['product_name']." ".$data['option_name'];
            if($data['quantity']>1){
                $temp = array();
                foreach(explode(",",$data['option_name']) as $v){
                    $temp[] = $v." * ".$data['quantity'];
                }
                $temp = implode(", ",$temp);
                $title = $data['product_name']." ".$temp;
            }
            $title = strtoupper(substr($data['store_name'],0,1))."-".$title;
            $worksheet->setCellValueExplicitByColumnAndRow(37,$row, $title);
            //$worksheet->setCellValueExplicitByColumnAndRow(40,$row, $data['selling_price']);
            $worksheet->setCellValueExplicitByColumnAndRow(40,$row, 15);
            $worksheet->setCellValueExplicitByColumnAndRow(41,$row, "MY");
            
            //$worksheet->setCellValueExplicitByColumnAndRow(42,$row, $data['quantity']);
            $worksheet->setCellValueExplicitByColumnAndRow(42,$row, "1");
            
            $worksheet->setCellValueExplicitByColumnAndRow(44,$row, $title);
            
            $repeated_row[$data['buyer_name']] = $row;
            $repeated_row[$data['buyer_address']] = $row;
            $row++;
        }
        
        $this->_export($objPHPExcel, $inputFileType, 'globalmail_'.date("YmdHis"));
    }
    
    function singpost_export($item_list){
        $template_path = APPPATH.'libraries/classes/templates/singpost_export.xlsx';
        if(!file_exists($template_path)){
            exit;
        }
        
        $inputFileType = PHPExcel_IOFactory::identify($template_path);
        $objPHPExcel = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = $objPHPExcel->load($template_path);
        
        $worksheet = $objPHPExcel->getSheetByName('Country List');
        $country_list = array();
        $temp = $worksheet->toArray('',true,true,false);
        foreach($temp as $v){
            $name = strtoupper($v[2]);
            $country_list[$name] = strtoupper($v[1]);
        }
        unset($temp);
        
        $worksheet = $objPHPExcel->getSheetByName('Quantium_International');
        
        $repeated_row = array();
        $field_limit = array();
        $field_limit['buyer_name'] = array('col_name'=>'A','col_id'=>0,'size'=>35);
        $field_limit['buyer_address'] = array('col_name'=>'C','col_id'=>2,'size'=>35);
        $field_limit['buyer_address2'] = array('col_name'=>'D','col_id'=>3,'size'=>35);
        $field_limit['buyer_address3'] = array('col_name'=>'E','col_id'=>4,'size'=>35);
        $field_limit['buyer_city'] = array('col_name'=>'F','col_id'=>5,'size'=>35);
        $field_limit['buyer_state'] = array('col_name'=>'G','col_id'=>6,'size'=>30);
        $field_limit['buyer_state'] = array('col_name'=>'I','col_id'=>8,'size'=>10);
        $field_limit['buyer_contact'] = array('col_name'=>'J','col_id'=>9,'size'=>20);
        $field_limit['buyer_email'] = array('col_name'=>'K','col_id'=>10,'size'=>255);
        
        $row = 2;
        foreach($item_list as $data){
            if(strlen($this->fp_amt)>0 && strlen($this->fp_cur)>0 && strtolower($this->fp_cur)!='default'){
                $data['selling_currency'] = strtoupper($this->fp_cur);
                $data['selling_price'] = $this->fp_amt;
            }
            
            if(strlen($data['buyer_address2'])==0){
                $data['buyer_address2'] = ".";
            }
            
            foreach(array('buyer_name','buyer_address') as $cf){
                $temp = $data[$cf];
                if(array_key_exists($temp, $repeated_row)!==FALSE){
                    $worksheet->getStyle('A'.$row.':AH'.$row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '96ffdd')
                            )
                        )
                    );
                    
                    $worksheet->getStyle('A'.$repeated_row[$temp].':AH'.$repeated_row[$temp])->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '96ffdd')
                            )
                        )
                    );
                    
                    break;
                }    
            }
            
            foreach($field_limit as $k => $v){
                if(!isset($data[$k])){continue;}
                if(strlen($data[$k]) > $v['size']){
                    $worksheet->getStyle($v['col_name'].$row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'FF0000')
                            )
                        )
                    );
                }
                $worksheet->setCellValueExplicitByColumnAndRow($v['col_id'],$row, $data[$k]);
            }
            
            $temp = strtoupper($data['buyer_country']);
            if(array_key_exists($temp, $country_list)!==FALSE){
                $temp = $country_list[$temp];
            }else if(strlen($temp)==2 && array_search($temp, $country_list)!==FALSE){
                
            }else{
                $worksheet->getStyle('H'.$row)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FF0000')
                        )
                    )
                );
                $temp = $data['buyer_country'];
            }
            $worksheet->setCellValueExplicitByColumnAndRow(7,$row, $temp);
            
            
            
            $title = $data['product_name']." ".$data['option_name'];
            if($data['quantity']>1){
                $temp = array();
                foreach(explode(",",$data['option_name']) as $v){
                    $temp[] = $v." * ".$data['quantity'];
                }
                $temp = implode(", ",$temp);
                $title = $data['product_name']." ".$temp;
            }
            $title = strtoupper(substr($data['store_name'],0,1))."-".$title;
            $worksheet->setCellValueExplicitByColumnAndRow(11,$row, $title);
            $worksheet->setCellValueExplicitByColumnAndRow(12,$row, "PACKAGE");
            $worksheet->setCellValueExplicitByColumnAndRow(13,$row, "M");
            $worksheet->setCellValueExplicitByColumnAndRow(15,$row, "EZYPRI");
            $worksheet->setCellValueExplicitByColumnAndRow(15,$row, "EZYPRI");
            if(strlen($this->fp_amt)>0 && strlen($this->fp_cur)>0 && strtolower($this->fp_cur)!='default'){
                $worksheet->setCellValueExplicitByColumnAndRow(16,$row, $data['selling_price']);
            }else{
                $worksheet->setCellValueExplicitByColumnAndRow(16,$row, $data['quantity'] * $data['selling_price']);
            }
            $worksheet->setCellValueExplicitByColumnAndRow(17,$row, $data['selling_currency']);
            $worksheet->setCellValueExplicitByColumnAndRow(18,$row, "Sunglasses case");
            $worksheet->setCellValueExplicitByColumnAndRow(19,$row, $data['quantity']);
            $worksheet->setCellValueExplicitByColumnAndRow(20,$row, (0.1 * $data['quantity']));
            $worksheet->setCellValueExplicitByColumnAndRow(21,$row, "1");
            $worksheet->setCellValueExplicitByColumnAndRow(22,$row, "1");
            $worksheet->setCellValueExplicitByColumnAndRow(23,$row, (1 * $data['quantity']));
            $worksheet->setCellValueExplicitByColumnAndRow(25,$row, "MY");
            $worksheet->setCellValueExplicitByColumnAndRow(27,$row, "S");
            
            $repeated_row[$data['buyer_name']] = $row;
            $repeated_row[$data['buyer_address']] = $row;
            $row++;
        }
        
        $this->_export($objPHPExcel, $inputFileType, 'singpost_'.date("YmdHis"));
    }
    
    function fdc_export($item_list){
        $template_path = APPPATH.'libraries/classes/templates/FDC_export_include.php';
        if(!file_exists($template_path)){
            exit;
        }
        include($template_path);
        
        $data_list = array();
        foreach($item_list as $data){
            $temp = $data['buyer_name'].$data['buyer_address'].$data['buyer_city'].$data['buyer_postcode'];
            if(isset($data_list[$temp])){
                $data_list[$temp] = array();
            }
            $data_list[$temp][] = $data;
        }
        
        $file = tempnam(sys_get_temp_dir(), 'tmp');
        $fp = fopen($file, 'w');
        
        /*header*/
        fputcsv($fp, $report_header);
        
        foreach($data_list as $a){
            $fields = array();
            $count = 0;
            foreach($a as $data){
                if(strlen($this->fp_amt)>0 && strlen($this->fp_cur)>0 && strtolower($this->fp_cur)!='default'){
                    $data['selling_currency'] = strtoupper($this->fp_cur);
                    $data['selling_price'] = $this->fp_amt;
                }
                if(($count%8)==0){
                    if($count>0){
                        fputcsv($fp, $fields);
                    }
                    $fields = array_merge($report_body,['Order Id'=>$data['sales_id'],'Bill First'=>$data['buyer_name'],'Bill Last'=>$data['buyer_name'],'Bill Address1'=>$data['buyer_address'],'Bill Address2'=>trim(trim($data['buyer_address2'].",".$data['buyer_address3']),","),'Bill City'=>$data['buyer_city'],'Bill State'=>$data['buyer_state'],'Bill Zip'=>$data['buyer_postcode'],'Bill Country'=>$data['buyer_country'],'Bill Phone'=>$data['buyer_contact'],'Bill Email'=>$data['buyer_email'],'Ship First'=>$data['buyer_name'],'Ship Last'=>$data['buyer_name'],'Ship Address1'=>$data['buyer_address'],'Ship Address2'=>trim(trim($data['buyer_address2'].",".$data['buyer_address3']),","),'Ship City'=>$data['buyer_city'],'Ship State'=>$data['buyer_state'],'Ship Zip'=>$data['buyer_postcode'],'Ship Country'=>$data['buyer_country'],'Ship Phone'=>$data['buyer_contact'],'Ship Email'=>$data['buyer_email'],'Ship Method Name'=>'DHL GLOBAL PARCEL EXPEDITED MAX']);
                }
                                
                foreach(['Product Name'=>$data['store_skucode'],'Product Description'=>'','Product Price'=>$data['selling_price'],'Product SKU #'=>$data['store_skucode'],'Product Category'=>'','Quantity'=>$data['quantity']] as $k => $v){
                    $temp = $k."_".$count;
                    $fields[$temp] = $v;
                }
                
                $count++;
            }
            if(sizeof($fields)>0){
                fputcsv($fp, $fields);
            }
        }
            
        fclose($fp);
        
        $this->_export_echo($file, 'FDC_shipping_'.date("YmdHis"));
    }
    
    private function _export($objPHPExcel,$inputFileType,$filename){
        try{
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $inputFileType);
            if($inputFileType=="Excel2007"){
                header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                $ext = ".xlsx";
            }else{
                header('Content-type: application/vnd.ms-excel');
                $ext = ".xls";
            }
            header('Content-Disposition: attachment; filename="'.$filename.$ext.'"');
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
        exit;
    }
    
    private function _export_echo($file,$filename){
        try{
            header('Content-type: '.mime_content_type($file));
            $ext = ".csv";
            header('Content-Disposition: attachment; filename="'.$filename.$ext.'"');
            header('Cache-Control: max-age=0');
            echo file_get_contents($file);
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
        exit;
    }
}

?>