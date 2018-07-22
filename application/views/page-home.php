<?php
include(dirname(__FILE__).'/include-view2.php');
?>

<div class="row">
    <div class="col-xs-12">
        <span class="">
            <button type="button" onclick=ajaxcall("<?php echo base_url('/ajax/home?method=refresh'); ?>") class="btn btn-warning waves-effect waves-light">Refresh <i class="fa fa-lg fa-refresh"></i></button>
        </span>
        <span>
            Latest update: <?php echo $dashboard_data['latest_update_date']; ?>
        </span>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="card-box">
            <h4 class="text-dark  header-title m-t-0">30 Days Deals</h4>
            <div class="row">
                <div class="col-xs-12 col-sm-9">
                    <div id="morris-line-example" style="height: 300px;"></div>
                </div>
                <div class="col-xs-12 col-sm-3">
                    <div class="">
                        <div><h4>Total of Each<h4></div>
                        <ul class="list-inline chart-detail-list">
                            <?php $count=0;foreach($dashboard_data['monthly_deals']['total2'] as $k => $v){ ?>
                            <li style="display:block;color:<?php echo $chart_color[$count]; ?>;"><?php echo (!empty($dashboard_data['monthly_deals']['header'][$k]))?$dashboard_data['monthly_deals']['header'][$k]:"Other"; ?> <span class="pull-right"><?php echo $v; ?></span></li>
                            <?php $count++;} ?>
                            <li style="display:block;border-top:1px solid #ccc;border-bottom:1px solid #ccc;">Total <span class="pull-right"><?php echo $dashboard_data['monthly_deals']['total']; ?></span></li>
                        </ul>
                    </div>
                </div>
            </div>
            <script>
            $(function(){
                //create line chart
                var $data  = [
                    <?php 
                        $header = "['".implode("','",array_keys($dashboard_data['monthly_deals']['header']))."']";
                        $header2 = "['".implode("','",array_values($dashboard_data['monthly_deals']['header']))."']";
                        foreach($dashboard_data['monthly_deals']['data'] as $key => $value){
                            $temp = "{y:'".$key."'";
                            foreach($value as $key2 => $value2){
                                $temp .= ", ".$key2.":".$value2;
                            }
                            $temp .= "},";
                            echo $temp;
                        }
                    ?>
                ];
                $.MorrisCharts.createLineChart('morris-line-example', $data, 'y', <?php echo $header; ?>, <?php echo $header2; ?>,['0.1'],['#ffffff'],['#999999'], CSS_COLOR_NAMES);
            });
            </script>
        </div>
    </div>
    <div class="col-xs-12">
        <div class="card-box">
            <h4 class="text-dark  header-title m-t-0">30 Days Profit (RM)</h4>
            <div class="row">
                <div class="col-xs-12 col-sm-9">
                    <div id="morris-line-example2" style="height: 300px;"></div>
                </div>
                <div class="col-xs-12 col-sm-3">
                    <div class="">
                        <div><h4>Total of Each<h4></div>
                        <ul class="list-inline chart-detail-list">
                            <?php $count=0;foreach($dashboard_data['monthly_profit']['total2'] as $k => $v){ ?>
                            <li style="display:block;color:<?php echo $chart_color[$count]; ?>;"><?php echo (!empty($dashboard_data['monthly_profit']['header'][$k]))?$dashboard_data['monthly_profit']['header'][$k]:"Other"; ?> <span class="pull-right"><?php echo $v; ?></span></li>
                            <?php $count++;} ?>
                            <li style="display:block;border-top:1px solid #ccc;border-bottom:1px solid #ccc;">Total <span class="pull-right"><?php echo $dashboard_data['monthly_profit']['total']; ?></span></li>
                        </ul>
                    </div>
                </div>
            </div>
            <script>
            $(function(){
                //create line chart
                var $data  = [
                    <?php 
                        $header = "['".implode("','",array_keys($dashboard_data['monthly_profit']['header']))."']";
                        $header2 = "['".implode("','",array_values($dashboard_data['monthly_profit']['header']))."']";
                        foreach($dashboard_data['monthly_profit']['data'] as $key => $value){
                            $temp = "{y:'".$key."'";
                            foreach($value as $key2 => $value2){
                                $temp .= ", ".$key2.":".$value2;
                            }
                            $temp .= "},";
                            echo $temp;
                        }
                    ?>
                ];
                $.MorrisCharts.createLineChart('morris-line-example2', $data, 'y', <?php echo $header; ?>, <?php echo $header2; ?>,['0.1'],['#ffffff'],['#999999'], CSS_COLOR_NAMES);
            });
            </script>
        </div>
    </div>
</div>

<div class="row small">
    <div class="col-xs-12 col-sm-4">
        <div class="card-box">
            <h4 class="text-dark  header-title m-t-0">Top 10 Daily Deals</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $count = 0;
                            foreach($dashboard_data['top_10_daily_deals'] as $data){
                                $count++;
                        ?>
                        <tr>
                            <td><?php echo $count; ?></td>
                            <td><?php echo $data['product_name']; ?></td>
                            <td><?php echo $data['total_qty']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="card-box">
            <h4 class="text-dark  header-title m-t-0">Top 10 Weekly Deals</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $count = 0;
                            foreach($dashboard_data['top_10_weekly_deals'] as $data){
                                $count++;
                        ?>
                        <tr>
                            <td><?php echo $count; ?></td>
                            <td><?php echo $data['product_name']; ?></td>
                            <td><?php echo $data['total_qty']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="card-box">
            <h4 class="text-dark  header-title m-t-0">Top 10 - 30 Days Deals</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $count = 0;
                            foreach($dashboard_data['top_10_monthly_deals'] as $data){
                                $count++;
                        ?>
                        <tr>
                            <td><?php echo $count; ?></td>
                            <td><?php echo $data['product_name']; ?></td>
                            <td><?php echo $data['total_qty']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>