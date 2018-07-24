<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cbnmforex{
    
    function __construct() {
        set_time_limit(0);
        $this->CI =& get_instance();
    }
    
    function update(){
        $month = date('m');
        $year = date('Y');
        if(($result = $this->CI->db->query('SELECT * FROM exchange_rate ORDER BY created_date DESC LIMIT 1'))){
            foreach($result->result_array() as $value){
                $temp = $value['created_date'];
                break;
            }
            $month = date('m',strtotime($temp));
            $year = date('Y',strtotime($temp));
        }
        $temp = $year.'-'.$month.'-01';
        foreach(array(date('Y-m',strtotime($temp)),date('Y-m',strtotime($temp.'+ 1 month'))) as $value){
            $temp = explode("-",$value);
            if($data =  $this->get_data($temp[1],$temp[0])){
                $record = $this->retrive_data($data);
                foreach($record as $data1){
                    if(empty($data1['currency'])){
                        continue;
                    }
                    foreach($data1['data'] as $key => $value){
                        $this->CI->db->query('INSERT INTO exchange_rate SET from_code=?, to_code=?, rate=?, created_date=? ON DUPLICATE KEY UPDATE rate=VALUES(rate)',array('MYR',$data1['currency'],$value,$key));
                    }
                }
            }
        }
    }
    
    function retrive_data($html = ""){
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $xpath = new DOMXPath($doc);
        $record = false;
        if(!$xpath){
            return false;
        }
        $container = $xpath->query('//div[@id="dvData"]');
        if($container->length){
            $record = array();
            $count1 = 0;
            $count2 = 0;
            $spos = 0;
            $element = $xpath->query('.//tr', $container->item(0));
            foreach ($element as $e) {
                if(!empty($e->attributes->getNamedItem('class')) && $e->attributes->getNamedItem('class')->nodeValue=="TblHdr"){
                    $count1 = $spos;
                    $count2 = $count1;
                    $element2 = $xpath->query('.//th', $e);
                    foreach ($element2 as $e2) {
                        $temp = trim($e2->nodeValue);
                        if(strlen($temp)>0){
                            preg_match('#([a-z]+)([0-9]*)#iu',$temp,$matches);
                            $record[$count2] = ['name'=>$temp,'divider'=>max(1,(int)$matches[2]),'currency'=>strtoupper($matches[1]),'data'=>[]];
                            $count2++;
                        }
                    }
                    $spos = $count2;
                }else{
                    $count2 = $count1;
                    $count3 = 0;
                    $temp2 = "";
                    $element2 = $xpath->query('.//td', $e);
                    foreach ($element2 as $e2) {
                        $temp = trim($e2->nodeValue);
                        if($count3==0){
                            $temp = explode('/',$temp);
                            if(sizeof($temp)==3){
                                $temp2 = date("Y-m-d",strtotime($temp[2]."-".$temp[1]."-".$temp[0]));
                            }
                        }else{
                            if(isset($record[$count2]['data'])){
                                $record[$count2]['data'][$temp2] = preg_replace('#[^0-9\.]#iu', '', $temp);
                            }
                            $count2++;
                        }
                        $count3++;
                    }
                }
            }
        }
        
        return $record;
    }
    
    function get_data($month=1,$year=2017){
        $url = sprintf('http://www.bnm.gov.my/index.php?ch=statistic&pg=stats_exchangerates&lang=en&StartMth=%1$d&StartYr=%2$d&EndMth=%1$d&EndYr=%2$d&sess_time=1700&pricetype=Mid&unit=fx',$month,$year);
        $data = "";
        //$data = file_get_contents($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        
        if(stristr($data, '<body')!==FALSE){
            preg_match_all('#<body[^>]*>(.*)</body>#smiU', $data, $matches);
            $data = $matches[1][0];
        }

        if (strlen($data)) {
            preg_match_all('#<script.*</script>#smiU', $data, $matches);
            if ($matches) {
                for ($i = 0; $i < sizeof($matches); $i++) {
                    $data = str_replace($matches[$i], '', $data);
                }
            }
        }
        if (strlen($data) > 0) {
            return $data;
        }
        return false;
    }
    
    function rate_verification(){
        $stop = 0;
        $count = 0;
        while($stop==0 and $count<1000){
            if(!$this->_insert_missing_data()){
                $stop=1;
            }
            $count++;
        }
    }
    
    private function _insert_missing_data(){
        if(($result = $this->CI->db->query('select a.from_code,a.to_code,a.rate,date_add(a.created_date,interval +1 day) created_date
                from exchange_rate a
                left join exchange_rate b on b.from_code=a.from_code and b.to_code=a.to_code and b.created_date=date_add(a.created_date,interval +1 day)
                where b.id is null and a.created_date<(select max(created_date) from exchange_rate)'))){
            foreach($result->result_array() as $value){
                $this->CI->db->query('INSERT IGNORE INTO exchange_rate SET from_code=?, to_code=?, rate=?, created_date=?',$value);
            }
            return $result->num_rows();
        }
        return false;
    }
}