<?php

include_once 'importClass.php';

class importShippingClass extends importClass{
    
    function __construct() {
        parent::__construct();
    }
    
    function shipping_export($selection){
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
            , e.name option_name
            , a.buyer_id, a.buyer_name, a.buyer_address, a.buyer_city, a.buyer_state, a.buyer_postcode, a.buyer_country, a.buyer_contact, a.buyer_email, a.tracking_number
            , a.selling_currency, a.quantity
            , a.selling_price, a.shipping_charges_received, a.payment_date, a.shipment_date
            , f.name courier_name, a.shipping_charges_paid, a.sales_id
            , a.paypal_trans_id, a.sales_fees_pect, a.sales_fees_fixed, a.paypal_fees_pect, a.paypal_fees_fixed
            ,b.id account_id, a.store_item_id, a.courier_id, g.id store_id, d.id product_id
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
        
        if(stristr($name, 'sing')!==FALSE){
            $this->singpost_export($item_list);
        }else if(stristr($name, 'global')!==FALSE){
            $this->globalmail_export($item_list);
        }
        exit;
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
            $name = strtolower($v[0]);
            $country_list[$name] = $v[1];
        }
        unset($temp);
        
        $worksheet = $objPHPExcel->getSheetByName('Sheet1');
        
        $repeated_row = array();
        $field_limit = array();
        $field_limit['sales_id'] = 35;
        $field_limit['buyer_name'] = 30;
        $field_limit['buyer_address'] = 50;
        $field_limit['buyer_city'] = 30;
        
        $row = 2;
        foreach($item_list as $data){
            foreach(array('buyer_name','buyer_address') as $cf){
                $temp = $data[$cf];
                if(array_key_exists($temp, $repeated_row)!==FALSE){
                    $worksheet->getStyle('A'.$row)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '96ffdd')
                            )
                        )
                    );
                    
                    $worksheet->getStyle('A'.$repeated_row[$temp])->applyFromArray(
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
            
            if(strlen($data['buyer_name']) > $field_limit['buyer_name']){
                $worksheet->getStyle('G'.$row)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FF0000')
                        )
                    )
                );
            }
            $worksheet->setCellValueExplicitByColumnAndRow(6,$row, $data['buyer_name']);
            
            if(strlen($data['buyer_address']) > $field_limit['buyer_address']){
                $worksheet->getStyle('H'.$row)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FF0000')
                        )
                    )
                );
            }
            $worksheet->setCellValueExplicitByColumnAndRow(7,$row, $data['buyer_address']);
            
            if(strlen($data['buyer_city']) > $field_limit['buyer_city']){
                $worksheet->getStyle('K'.$row)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FF0000')
                        )
                    )
                );
            }
            $worksheet->setCellValueExplicitByColumnAndRow(10,$row, $data['buyer_city']);
            
            $worksheet->setCellValueExplicitByColumnAndRow(11,$row, $data['buyer_state']);
            $worksheet->setCellValueExplicitByColumnAndRow(12,$row, $data['buyer_postcode']);
            
            $temp = strtolower($data['buyer_country']);
            if(array_key_exists($temp, $country_list)!==FALSE){
                $temp = $country_list[$temp];
            }else{
                $worksheet->getStyle('N'.$row)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FF0000')
                        )
                    )
                );
            }
            $worksheet->setCellValueExplicitByColumnAndRow(13,$row, $temp);
            
            $worksheet->setCellValueExplicitByColumnAndRow(16,$row, (100 * $data['quantity']));
            $worksheet->setCellValueExplicitByColumnAndRow(20,$row, $data['selling_currency']);
            $worksheet->setCellValueExplicitByColumnAndRow(21,$row, $data['quantity'] * $data['selling_price']);
            $worksheet->setCellValueExplicitByColumnAndRow(33,$row, "Sunglasses case");
            $worksheet->setCellValueExplicitByColumnAndRow(37,$row, $data['product_name']." ".$data['option_name']." * ".$data['quantity']);
            $worksheet->setCellValueExplicitByColumnAndRow(40,$row, $data['selling_currency']);
            $worksheet->setCellValueExplicitByColumnAndRow(40,$row, $data['selling_currency']);
            $worksheet->setCellValueExplicitByColumnAndRow(41,$row, "MY");
            $worksheet->setCellValueExplicitByColumnAndRow(42,$row, $data['quantity']);
            $worksheet->setCellValueExplicitByColumnAndRow(44,$row, $data['product_name']." ".$data['option_name']." * ".$data['quantity']);
            
            $repeated_row[$data['buyer_name']] = $row;
            $repeated_row[$data['buyer_address']] = $row;
            $row++;
        }
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $inputFileType);
        if($inputFileType=="Excel2007"){
            header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $ext = ".xlsx";
        }else{
            header('Content-type: application/vnd.ms-excel');
            $ext = ".xls";
        }
        header('Content-Disposition: attachment; filename="globalmail_'.date("YmdHis").$ext.'"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }
    
    function singpost_export($item_list){
        
    }
}

?>