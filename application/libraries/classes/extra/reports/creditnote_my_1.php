<?php
/*
 * $arguments['header']
 * $arguments['data']
 */
$template_path = APPPATH.'libraries/classes/templates/creditnote_my_1.xlsx';
if(!file_exists($template_path)){
    exit;
}

$data = array('header'=>array(),'data'=>array());
if(($result = $this->CI->db->query('select a.*,b.*,c.inv_text, d.cn_text, d.cn_reason, d.created_date cn_date from transactions a,accounts b,transactions_inv c, transactions_inv_cn d where b.id=c.custom_account_id and c.account_id=a.account_id and c.sales_id=a.sales_id and d.account_id=c.account_id and d.inv_id=c.inv_id and a.id = ? LIMIT 1',array($selected_id))) && $result->num_rows()){
    $data['header'] = $result->row_array();
    $data['header']['buyer_fulladdr'] = preg_replace("#[\s]*,[,\s]+#iu",", ",trim($data['header']['buyer_address'].",\n".$data['header']['buyer_address2'].",\n".$data['header']['buyer_address3'].",\n".$data['header']['buyer_city'].", ".$data['header']['buyer_state'].", ".$data['header']['buyer_postcode'].", ".$data['header']['buyer_country']));
}
if(($result = $this->CI->db->query('select d.name product_name, e.code2 option_name, a.* from transactions a 
        join store_item c on a.store_item_id=c.id
        join warehouse_item wi on c.warehouse_item_id=wi.id
        join products d on wi.product_id=d.id
        join option_item e on wi.item_id=e.id
        where a.sales_id = ?',array($data['header']['sales_id']))) && $result->num_rows()){
    foreach($result->result_array() as $row){
        if(!isset($data['header']['rate'])){
            $data['header']['currency'] = $row['selling_currency'];
            $data['header']['rate'] = $arguments['lensesClass']->get_rate($row['selling_currency'], $row['payment_date']);
        }
        $data['data'][] = $row;
    }
}
$arguments = array_merge($arguments,$data);

if(!isset($arguments['return_object'])){
    $filename = $arguments['header']['name']."_".$arguments['header']['cn_text'];
}

$this->filetype = PHPExcel_IOFactory::identify($template_path);
$this->phpexcel = PHPExcel_IOFactory::createReader($this->filetype);
$objPHPExcel = &$this->phpexcel;
$objPHPExcel = $objPHPExcel->load($template_path);
$objPHPExcel->setActiveSheetIndex(0);

$worksheet = $objPHPExcel->getActiveSheet();

$worksheet->setCellValueExplicit('A2', $arguments['header']['acc_comp_name']);
$worksheet->setCellValueExplicit('A3', $arguments['header']['acc_comp_addr']);
$worksheet->setCellValueExplicit('B5', $arguments['header']['acc_comp_tel']);
$worksheet->setCellValueExplicit('B6', $arguments['header']['acc_comp_fax']);
$worksheet->setCellValueExplicit('H5', $arguments['header']['acc_comp_tax_no']);
$worksheet->setCellValueExplicit('H6', $arguments['header']['acc_comp_reg_no']);
$worksheet->setCellValueExplicit('B9', $arguments['header']['buyer_name']);
$worksheet->setCellValueExplicit('B10', $arguments['header']['buyer_fulladdr']);
$worksheet->getStyle('B10')->getAlignment()->setWrapText(true);
$worksheet->setCellValueExplicit('H9', date("d-m-Y",strtotime($arguments['header']['cn_date'])));
$worksheet->setCellValueExplicit('H10', $arguments['header']['cn_text']);
$worksheet->setCellValueExplicit('H12', $arguments['header']['inv_text']);
$worksheet->setCellValueExplicit('H13', date("d-m-Y",strtotime($arguments['header']['payment_date'])));

$temp = "";
if(strlen($arguments['header']['cn_reason'])>0){
    $temp = "Reason: ". $arguments['header']['cn_reason'];
}
$worksheet->setCellValueExplicit('A26', $temp);
$worksheet->getStyle('A26')->getFont()->setSize(11);

$worksheet->setCellValueExplicit('A28', "Currency rate: MYR".round(1/floatval($arguments['header']['rate']),4)." = ".strtoupper($arguments['header']['currency'])."1");
$worksheet->getStyle('A28')->getFont()->setSize(10);

$data_row = 16;
$total_price = 0;
$count = 0;
foreach($arguments['data'] as $row){
    if($count>0){
        $worksheet->insertNewRowBefore($data_row, 1);
    }
    //$worksheet->setCellValueExplicit('A'.$data_row, "IM_0");
    $worksheet->setCellValueExplicit('A'.$data_row, "ZRE");
    $worksheet->mergeCells('B'.$data_row.':F'.$data_row);
    $worksheet->setCellValueExplicit('B'.$data_row, "[".$row['store_skucode']."] ".$row['product_name']." ".$row['option_name']." @ (".$row['selling_currency'].$row['selling_price'].")");
    $quantity = $row['quantity'];
    $worksheet->setCellValueExplicit('G'.$data_row, $quantity);
    $price = round(floatval($row['selling_price']) / floatval($arguments['header']['rate']),2);
    $worksheet->setCellValueExplicit('H'.$data_row, $price);
    $worksheet->setCellValueExplicit('I'.$data_row, $price * $quantity);
    $total_price += $price * $quantity;
    $worksheet->getRowDimension($data_row)->setRowHeight(-1);
    $worksheet->getStyle('A'.$data_row.':I'.$data_row)->getAlignment()->setWrapText(true);
    $worksheet->getStyle('A'.$data_row.':I'.$data_row)->getFont()->setSize(11);
    $worksheet->getStyle('A'.$data_row.':I'.$data_row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
    $worksheet->getStyle('A'.$data_row.':F'.$data_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    $worksheet->getStyle('G'.$data_row.':I'.$data_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    $count++;
}

$worksheet->setCellValueExplicit('I'.(18+sizeof($arguments['data'])-1), $total_price);
$worksheet->setCellValueExplicit('I'.(20+sizeof($arguments['data'])-1), $total_price);
$worksheet->setCellValueExplicit('I'.(22+sizeof($arguments['data'])-1), $total_price);
$worksheet->setCellValueExplicit('I'.(23+sizeof($arguments['data'])-1), $total_price);
    