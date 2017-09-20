<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<?php 
if(empty($this->CI->cpage->template_data['breadcrumb']) && strlen($this->CI->cpage->get_html_title())>0){
    $this->CI->cpage->set('breadcrumb',array($this->CI->cpage->get_html_title()=>''));
}
if(isset($this->CI->cpage->template_data['breadcrumb'])){
?>
<div class="breadcrumb-container">
<?php
    $breadcrumb = $this->CI->cpage->template_data['breadcrumb'];
    $count = 0;
    foreach($breadcrumb as $key => $value){
        $count++;
        if($count==1 && strtolower($key)!="home"){
?>
<a href="<?php echo base_url('/'); ?>">Home</a>
<?php
        }
        if($value==""){
            $value = "javascript:void(0);";
        }
?>
<a href="<?php echo $value; ?>"><?php echo $key; ?></a>
<?php } ?>
</div>
<?php } ?>
