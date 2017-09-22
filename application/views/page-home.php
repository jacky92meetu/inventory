<link rel="stylesheet" href="<?php echo base_url('/assets/default'); ?>/plugins/morris/morris.css">
<script src="<?php echo base_url('/assets/default'); ?>/plugins/morris/morris.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/raphael/raphael-min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/pages/morris.init.js"></script>
<script>
    var CSS_COLOR_NAMES = ["#ffbf00","#bf5340","#40bf44","#b3bf40","#e2761d","#6040bf","#bf4088","#8359a6","#0033ff","#ff0026","DarkGreen","DarkKhaki","DarkMagenta","DarkOliveGreen","Darkorange","DarkOrchid","DarkRed","DarkSalmon","DarkSeaGreen","DarkSlateBlue","DarkSlateGray","DarkSlateGrey","DarkTurquoise","DarkViolet","DeepPink","DeepSkyBlue","DimGray","DimGrey","DodgerBlue","FireBrick","FloralWhite","ForestGreen","Fuchsia","Gainsboro","GhostWhite","Gold","GoldenRod","Gray","Grey","Green","GreenYellow","HoneyDew","HotPink","IndianRed","Indigo","Ivory","Khaki","Lavender","LavenderBlush","LawnGreen","LemonChiffon","LightBlue","LightCoral","LightCyan","LightGoldenRodYellow","LightGray","LightGrey","LightGreen","LightPink","LightSalmon","LightSeaGreen","LightSkyBlue","LightSlateGray","LightSlateGrey","LightSteelBlue","LightYellow","Lime","LimeGreen","Linen","Magenta","Maroon","MediumAquaMarine","MediumBlue","MediumOrchid","MediumPurple","MediumSeaGreen","MediumSlateBlue","MediumSpringGreen","MediumTurquoise","MediumVioletRed","MidnightBlue","MintCream","MistyRose","Moccasin","NavajoWhite","Navy","OldLace","Olive","OliveDrab","Orange","OrangeRed","Orchid","PaleGoldenRod","PaleGreen","PaleTurquoise","PaleVioletRed","PapayaWhip","PeachPuff","Peru","Pink","Plum","PowderBlue","Purple","Red","RosyBrown","RoyalBlue","SaddleBrown","Salmon","SandyBrown","SeaGreen","SeaShell","Sienna","Silver","SkyBlue","SlateBlue","SlateGray","SlateGrey","Snow","SpringGreen","SteelBlue","Tan","Teal","Thistle","Tomato","Turquoise","Violet","Wheat","White","WhiteSmoke","Yellow","YellowGreen"];
</script>

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
                            <?php foreach($dashboard_data['monthly_deals']['total2'] as $k => $v){ ?>
                            <li style="display:block;"><?php echo $dashboard_data['monthly_deals']['header'][$k]; ?> <span class="pull-right"><?php echo $v; ?></span></li>
                            <?php } ?>
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
                            <?php foreach($dashboard_data['monthly_profit']['total2'] as $k => $v){ ?>
                            <li style="display:block;"><?php echo $dashboard_data['monthly_profit']['header'][$k]; ?> <span class="pull-right"><?php echo $v; ?></span></li>
                            <?php } ?>
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