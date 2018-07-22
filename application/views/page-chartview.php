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
                <div class="">
                    <div class="row">
                        <div class="col-xs-12 col-sm-9">
                            <div id="morris-line-example" style="height: 350px;"></div>
                        </div>
                        <div class="col-xs-12 col-sm-3">
                            <div class="">
                                <div><h4>Total of Each<h4></div>
                                <ul class="list-inline chart-detail-list">
                                    <?php $count=0;foreach($dashboard_data['total2'] as $k => $v){ ?>
                                    <li style="display:block;color:<?php echo $chart_color[$count]; ?>;"><?php echo (!empty($dashboard_data['header'][$k]))?$dashboard_data['header'][$k]:"Other"; ?> <span class="pull-right"><?php echo $v; ?></span></li>
                                    <?php $count++;} ?>
                                    <li style="display:block;border-top:1px solid #ccc;border-bottom:1px solid #ccc;">Total <span class="pull-right"><?php echo $dashboard_data['total']; ?></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <script>
                    $(function(){
                        //create line chart
                        var $data  = [
                            <?php 
                                $header = "['".implode("','",array_keys($dashboard_data['header']))."']";
                                $header2 = "['".implode("','",array_values($dashboard_data['header']))."']";
                                foreach($dashboard_data['data'] as $key => $value){
                                    $temp = "{y:'".$key."'";
                                    foreach($value as $key2 => $value2){
                                        $temp .= ", ".$key2.":".$value2;
                                    }
                                    $temp .= "},";
                                    echo $temp;
                                }
                            ?>
                        ];
                        $.MorrisCharts.createLineChart('morris-line-example', $data, 'y', <?php echo $header; ?>, <?php echo $header2; ?>,['0.1'],['#ffffff'],['#999999'], CSS_COLOR_NAMES, true);
                    });
                    </script>
                    
                </div>
            </div>
        </div>
    </div>
</div>