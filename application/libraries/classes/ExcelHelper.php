<?php

include 'extra/PHPExcel_1.8.0/Classes/PHPExcel.php';

class ExcelHelper {
    
    public function __construct($aConfig = array()) {
        $this->CI = get_instance();
        $this->phpexcel = new PHPExcel();
        $this->filetype = "Excel2007";
    }
    
    public function __destruct() {
        $this->phpexcel->disconnectWorksheets();
        unset($this->phpexcel);
    }
    
    public function exec($name = "", $arguments = array(), $to_pdf = false) {
        $file = dirname(__FILE__).'/extra/reports/'.$name.'.php';
        if(file_exists($file)){
            if(is_array($arguments['selected_id']) && sizeof($arguments['selected_id'])==1){
                $arguments['selected_id'] = implode("",$arguments['selected_id']);
            }
            if(is_array($arguments['selected_id'])){
                $zip_file = tempnam("tmp", "zip");
                $zip = new ZipArchive();
                $zip->open($zip_file, ZipArchive::OVERWRITE);
                foreach($arguments['selected_id'] as $selected_id){
                    include($file);
                    $contents = $this->export($this->filetype,"",$to_pdf);
                    $zip->addFromString($filename.$this->tmpfilename,$this->export($this->filetype,"",$to_pdf));
                    unset($contents);
                }
                $zip->close();
                if(!isset($arguments['return_object'])){
                    header('Content-Type: application/zip');
                    header('Content-Length: ' . filesize($zip_file));
                    header('Content-Disposition: attachment; filename="file_'.time().'.zip"');
                    readfile($zip_file);
                    unlink($zip_file);
                    exit;
                }else{
                    ob_start();
                    readfile($zip_file);
                    unlink($zip_file);
                    return ob_get_clean();
                }
            }else{
                $selected_id = $arguments['selected_id'];
                include($file);
                if(!isset($arguments['return_object']) && !empty($filename)){
                    $this->export($this->filetype,$filename,$to_pdf);
                    exit;
                }
                return $this->export($this->filetype,"",$to_pdf);
            }
        }
        return false;
    }
    
    public function export($inputFileType="",$filename="",$to_pdf=false){
        try{
            $objPHPExcel = &$this->phpexcel;
            if($inputFileType=="" && !empty($this->filetype)){
                $inputFileType = $this->filetype;
            }
            if($to_pdf){
                $rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
                $rendererLibraryPath = dirname(__FILE__).'/extra/mpdf';
                if (!PHPExcel_Settings::setPdfRenderer(
                  $rendererName,
                  $rendererLibraryPath
                 )) {
                 die(
                  'NOTICE: Please set the $rendererName and $rendererLibraryPath values' .
                  '<br />' .
                  'at the top of this script as appropriate for your directory structure'
                 );
                }
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
                if(strlen($filename)>0){
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment;filename="'.$filename.'.pdf"');
                    header('Cache-Control: max-age=0');
                    $objWriter->save('php://output');
                }else{
                    $this->tmpfilename = $filename.".pdf";
                    ob_start();
                    $objWriter->save('php://output');
                    return ob_get_clean();
                }
            }else{
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $inputFileType);
                if(strlen($filename)>0){
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
                }else{
                    $this->tmpfilename = $filename.(($inputFileType=="Excel2007")?".xlsx":".xls");
                    ob_start();
                    $objWriter->save('php://output');
                    return ob_get_clean();
                }
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
        exit;
    }

}

?>