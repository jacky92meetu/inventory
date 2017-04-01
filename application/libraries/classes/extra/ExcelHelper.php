<?php

include 'PHPExcel_1.8.0/Classes/PHPExcel.php';

class ExcelHelper {
    
    var $file_type = 'Excel5';

    public function __construct($aConfig = array()) {
        $this->phpexcel = new PHPExcel();
    }
    
    public function __destruct() {
        $this->phpexcel->disconnectWorksheets();
        unset($this->phpexcel);
    }
    
    public function __call($name, $arguments) {
        $file = $_SERVER['DOCUMENT_ROOT'].'/app/includes/PHPExcel_1.8.0/reports/'.$name.'.php';
        if(file_exists($file)){
            return include($file);
        }
        return false;
    }
    
    public function export($file_name = "", $save_local = false){
        $objWriter = PHPExcel_IOFactory::createWriter($this->phpexcel, $this->file_type);
        if(strlen($file_name)==0){
            $file_name = "export_".date('YmdHis').".xls";
        }
        if($save_local){
            $objWriter->save($file_name);
        }else{
            // Write file to the browser
            header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="'.$file_name.'"');
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
        }
    }
    
    public function zip_export(){
        $sheetIndex = 0;
        $error = 0;
        $objPHPExcel = &$this->phpexcel;
        $sheetCount = $objPHPExcel->getSheetCount();
        $path = '/tmp/';
        $temp_file = md5($objPHPExcel->getProperties()->getTitle().time());
        mkdir($path.$temp_file, 0777);
        while ($sheetIndex < $sheetCount) {
            $workSheet = $objPHPExcel->getSheet($sheetIndex);
            $title = $workSheet->getProperties()->getTitle();
            $newObjPHPExcel = new PHPExcel();
            $newObjPHPExcel->removeSheetByIndex(0);
            $newObjPHPExcel->addExternalSheet($workSheet);
            $newObjPHPExcel->getProperties()->setTitle($title);
            $temp_url = $path.$temp_file.'/'.$title.'.xls';
            $objPHPExcelWriter = PHPExcel_IOFactory::createWriter($newObjPHPExcel,$this->file_type);
            $objPHPExcelWriter->save($temp_url);
            ++$sheetIndex;
        }
        $temp_url = "";
        if($error==0){
            $temp_url = $path.$temp_file.'.zip';
            $zip = new ZipArchive();
            $zip->open($temp_url, ZipArchive::OVERWRITE);
            $zip->addGlob($path.$temp_file.'/*.xls');
            $zip->close();
        }
        array_map('unlink', glob($path.$temp_file.'/*'));
        rmdir($path.$temp_file);
        if($error==0 && strlen($temp_url)>0 && file_exists($temp_url)){
            header('Content-type: application/zip');
            header('Content-Disposition: attachment; filename="'.basename($temp_url).'"');
            header('Content-Length: '.filesize($temp_url));
            header("Pragma: no-cache"); 
            header("Expires: 0"); 
            readfile($temp_url);
        }
    }

}

?>