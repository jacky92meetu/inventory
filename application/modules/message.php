<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
$CI = & get_instance();
$contents = '
		<DIV class="response_message"><DIV class="{class}">{message}</DIV></DIV>
	';
$contents = '<div class="alert {class} alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>{message}</div>';
$CI->load->library('cmessage');

$temp = $CI->cmessage->get_response_message();
$CI->cmessage->del_response_message();
if (empty($temp)) {
    return false;
}

if (is_string($temp)) {
    $temp = array("message" => $temp, "type" => "notice");
}
switch ($temp['type']) {
    case "warning":
        $contents = str_ireplace("{class}", "alert-warning", $contents);
        break;

    case "error":
        $contents = str_ireplace("{class}", "alert-danger", $contents);
        break;

    case "success":
        $contents = str_ireplace("{class}", "alert-success", $contents);
        break;
    
    case "notice":
    default:
        $contents = str_ireplace("{class}", "alert-info", $contents);
        break;
}
$contents = str_ireplace("{message}", $temp['message'], $contents);
$contents = str_ireplace("{type}", strtoupper($temp['type']), $contents);

echo $contents;
?>
