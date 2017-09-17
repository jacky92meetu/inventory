<link rel="stylesheet" href="<?php echo base_url('/assets/default'); ?>/plugins/morris/morris.css">
<script src="<?php echo base_url('/assets/default'); ?>/plugins/morris/morris.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/raphael/raphael-min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/pages/morris.init.js"></script>

<div class="row">
    <div class="col-xs-12">
        <div class="card-box">
            <h4 class="text-dark  header-title m-t-0">Monthly Deals</h4>
            <div class="text-center">
                <ul class="list-inline chart-detail-list">
                    <li>
                        <h5><i class="fa fa-circle m-r-5" style="color: #3bafda;"></i>Series A</h5>
                    </li>
                    <li>
                        <h5><i class="fa fa-circle m-r-5" style="color: #dcdcdc;"></i>Series B</h5>
                    </li>
                    <li>
                        <h5><i class="fa fa-circle m-r-5" style="color: #80deea;"></i>Series C</h5>
                    </li>
                </ul>
            </div>
            <div id="morris-line-example" style="height: 300px;"></div>
            <script>
            $(function(){
                //create line chart
                var $data  = [
                    { y: '2010', a: 30,  b: 20 , c: 10 },
                    { y: '2011', a: 50,  b: 40 , c: 30 },
                    { y: '2012', a: 75,  b: 65 , c: 50 },
                    { y: '2013', a: 50,  b: 40 , c: 22 },
                    { y: '2014', a: 75,  b: 65 , c: 50 },
                    { y: '2015', a: 100, b: 90 , c: 65 }
                  ];
                $.MorrisCharts.createLineChart('morris-line-example', $data, 'y', ['a', 'b','c'], ['Series A', 'Series B', 'Series C'],['0.1'],['#ffffff'],['#999999'], ["#00b19d", "#29b6f6", "#3f51b5"]);
            });
            </script>
        </div>
    </div>
    <div class="col-xs-12">
        <div class="card-box">
            <h4 class="text-dark  header-title m-t-0">Monthly Sales</h4>
            <div class="text-center">
                <ul class="list-inline chart-detail-list">
                    <li>
                        <h5><i class="fa fa-circle m-r-5" style="color: #3bafda;"></i>Series A</h5>
                    </li>
                    <li>
                        <h5><i class="fa fa-circle m-r-5" style="color: #dcdcdc;"></i>Series B</h5>
                    </li>
                    <li>
                        <h5><i class="fa fa-circle m-r-5" style="color: #80deea;"></i>Series C</h5>
                    </li>
                </ul>
            </div>
            <div id="morris-line-example2" style="height: 300px;"></div>
            <script>
            $(function(){
                //create line chart
                var $data  = [
                    { y: '2010', a: 30,  b: 20 , c: 10 },
                    { y: '2011', a: 50,  b: 40 , c: 30 },
                    { y: '2012', a: 75,  b: 65 , c: 50 },
                    { y: '2013', a: 50,  b: 40 , c: 22 },
                    { y: '2014', a: 75,  b: 65 , c: 50 },
                    { y: '2015', a: 100, b: 90 , c: 65 }
                  ];
                $.MorrisCharts.createLineChart('morris-line-example2', $data, 'y', ['a', 'b','c'], ['Series A', 'Series B', 'Series C'],['0.1'],['#ffffff'],['#999999'], ["#00b19d", "#29b6f6", "#3f51b5"]);
            });
            </script>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-4">
        <div class="card-box">
            <h4 class="text-dark  header-title m-t-0">Top 10 Daily Deals</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Minton Admin v1</td>
                            <td>12</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="card-box">
            <h4 class="text-dark  header-title m-t-0">Top 10 Daily Deals</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Minton Admin v1</td>
                            <td>12</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-4">
        <div class="card-box">
            <h4 class="text-dark  header-title m-t-0">Top 10 Daily Deals</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Minton Admin v1</td>
                            <td>12</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>