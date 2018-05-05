<?php
$dashboard_data = $this->cpage->template_data['view_contents'];
include(dirname(__FILE__).'/include-view2.php');
include(dirname(__FILE__).'/include-view1.php');
?>

<div class="panel">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-6">
                <?php if($this->cpage->template_data['extra_filter']){ ?>
                <span class="">
                    <button class="btn btn-success waves-effect waves-light" onclick="data_edit(this,'extra_filter',true)">Extra Filter <i class="fa fa-lg fa-search"></i></button>
                </span>
                <?php } ?>
            </div>
            <div class="col-xs-6 text-right">
                
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-xs-12">
                <table></table>
            </div>
        </div>
    </div>
</div>