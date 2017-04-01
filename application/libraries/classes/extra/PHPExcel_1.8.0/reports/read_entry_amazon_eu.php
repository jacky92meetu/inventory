<?php
        $view = $arguments[0];
        $file = $_SERVER['DOCUMENT_ROOT'].'/app/includes/PHPExcel_1.8.0/templates/ac_template_user.xls';
        $inputFileType = PHPExcel_IOFactory::identify($file);
        $this->phpexcel = PHPExcel_IOFactory::createReader($inputFileType);
        $objPHPExcel = &$this->phpexcel;
        $objPHPExcel = $objPHPExcel->load($file);
        
        $objPHPExcel->getActiveSheet()->setTitle('Auto Count User List Report');
        
        $row_num = 3;
        $count = 0;

        foreach($view as $row){
            $user_id = '300-'.strtoupper($row->user_code1.substr("00000".$row->user_code2,-5));
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$row_num,$user_id);
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$row_num,strtoupper(substr($row->user_fullname,0,80)));
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$row_num,strtoupper(substr($row->user_address_state,0,40)));
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$row_num,'CUSTOMER');
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$row_num,'CREDIT CARD');
            $objPHPExcel->getActiveSheet()->setCellValue('I'.$row_num,'I');
            $objPHPExcel->getActiveSheet()->setCellValue('J'.$row_num,'N');
            $objPHPExcel->getActiveSheet()->setCellValue('K'.$row_num,"MYR");
            $objPHPExcel->getActiveSheet()->setCellValue('L'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('M'.$row_num,strtoupper(substr($row->user_address_street,0,40)));
            $objPHPExcel->getActiveSheet()->setCellValue('N'.$row_num,strtoupper(substr($row->user_address_city,0,40)));
            $objPHPExcel->getActiveSheet()->setCellValue('O'.$row_num,strtoupper(substr($row->user_address_state,0,40)));
            $objPHPExcel->getActiveSheet()->setCellValue('P'.$row_num,strtoupper(substr($row->user_address_country,0,40)));
            $objPHPExcel->getActiveSheet()->setCellValue('Q'.$row_num,strtoupper(substr($row->user_address_postcode,0,10)));
            $objPHPExcel->getActiveSheet()->setCellValue('R'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('S'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('T'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('U'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('V'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('W'.$row_num,strtoupper(substr($row->user_fullname,0,80)));
            $objPHPExcel->getActiveSheet()->setCellValueExplicit('X'.$row_num, substr($row->user_mobile,0,25),PHPExcel_Cell_DataType::TYPE_STRING);
            $objPHPExcel->getActiveSheet()->setCellValue('Y'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('Z'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('AA'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('AB'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('AC'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('AD'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('AE'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('AF'.$row_num,"");
            $objPHPExcel->getActiveSheet()->setCellValue('AG'.$row_num,substr($row->user_email,0,80));
            $row_num += 1;
            $count += 1;
        }
        
        $objPHPExcel->setActiveSheetIndex(0);
        $this->export();
?>