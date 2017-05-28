<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<?php
    $selected_menu = (isset($this->CI->cpage->template_data['selected_menu']))?$this->CI->cpage->template_data['selected_menu']:'home';
    $selected_menu = strtolower($selected_menu);
?>

        <header id="topnav">
            <div class="topbar-main">
                <div class="container">
                    <div class="topbar-main-hoverbar" style="position:absolute;top:0;left:0;width:100%;height:100%;background-color:transparent;z-index: 0;"></div>

                    <!-- Logo container-->
                    <div class="logo">
                        <a href="<?php echo base_url("/"); ?>" class="logo"><i class="md md-equalizer"></i> <span><?php echo config_item('site_name'); ?></span> </a>
                    </div>
                    <!-- End Logo container-->

                    <div class="menu-extras">

                        <ul class="nav navbar-nav navbar-right pull-right">
                            <?php /*
                            <li class="dropdown hidden-xs">
                                <a href="#" data-target="#" class="dropdown-toggle waves-effect waves-light"
                                   data-toggle="dropdown" aria-expanded="true">
                                    <i class="md md-notifications"></i> <span
                                        class="badge badge-xs badge-pink">3</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-lg">
                                    <li class="text-center notifi-title">Notification</li>
                                    <li class="list-group nicescroll notification-list">
                                        <!-- list item-->
                                        <a href="javascript:void(0);" class="list-group-item">
                                            <div class="media">
                                                <div class="pull-left p-r-10">
                                                    <em class="fa fa-diamond noti-primary"></em>
                                                </div>
                                                <div class="media-body">
                                                    <h5 class="media-heading">A new order has been placed A new
                                                        order has been placed</h5>
                                                    <p class="m-0">
                                                        <small>There are new settings available</small>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>

                                        <!-- list item-->
                                        <a href="javascript:void(0);" class="list-group-item">
                                            <div class="media">
                                                <div class="pull-left p-r-10">
                                                    <em class="fa fa-cog noti-warning"></em>
                                                </div>
                                                <div class="media-body">
                                                    <h5 class="media-heading">New settings</h5>
                                                    <p class="m-0">
                                                        <small>There are new settings available</small>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>

                                        <!-- list item-->
                                        <a href="javascript:void(0);" class="list-group-item">
                                            <div class="media">
                                                <div class="pull-left p-r-10">
                                                    <em class="fa fa-bell-o noti-success"></em>
                                                </div>
                                                <div class="media-body">
                                                    <h5 class="media-heading">Updates</h5>
                                                    <p class="m-0">
                                                        <small>There are <span class="text-primary">2</span> new
                                                            updates available
                                                        </small>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>

                                    </li>

                                    <li>
                                        <a href="javascript:void(0);" class=" text-right">
                                            <small><b>See all notifications</b></small>
                                        </a>
                                    </li>

                                </ul>
                            </li>
                            */ ?>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle waves-effect waves-light profile" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-user"></i> </a>
                                <ul class="dropdown-menu">
                                    <?php /*
                                    <li><a href="javascript:void(0)"><i class="ti-user m-r-5"></i> Profile</a></li>
                                    <li><a href="javascript:void(0)"><i class="ti-settings m-r-5"></i> Settings</a></li>
                                    <li><a href="javascript:void(0)"><i class="ti-lock m-r-5"></i> Lock screen</a></li>
                                    */ ?>
                                    <li><a href="<?php echo base_url('/home/logout'); ?>"><i class="ti-power-off m-r-5"></i> Logout</a></li>
                                </ul>
                            </li>
                        </ul>

                        <div class="menu-item">
                            <!-- Mobile menu toggle-->
                            <a class="navbar-toggle">
                                <div class="lines">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </a>
                            <!-- End mobile menu toggle-->
                        </div>
                    </div>

                </div>
            </div>
            <!-- End topbar -->


            <!-- Navbar Start -->
            <div class="navbar-custom">
                <div class="container">
                <div id="navigation">
                    <!-- Navigation Menu-->
                    <ul class="navigation-menu">
                        <li class="has-submenu <?php echo (($selected_menu=="home")?"active":""); ?>">
                            <a href="<?php echo base_url("/"); ?>"><i class="md md-dashboard"></i>Dashboard</a>
                        </li>
                        <li class="has-submenu">
                            <a href="#"><i class="md md-attach-money"></i>Sales</a>
                            <ul class="submenu">
                                <li class="<?php echo (($selected_menu=="sales_entry")?"active":""); ?>"><a href="<?php echo base_url("/sales_entry"); ?>">Sales Entry</a></li>
                                <li class="<?php echo (($selected_menu=="sales_history")?"active":""); ?>"><a href="<?php echo base_url("/sales_history"); ?>">Sales History</a></li>
                            </ul>
                        </li>
                        <li class="has-submenu">
                            <a href="#"><i class="md md-account-balance"></i>Inventory</a>
                            <ul class="submenu">
                                <li class="<?php echo (($selected_menu=="suppliers")?"active":""); ?>"><a href="<?php echo base_url("/supplier"); ?>">Suppliers</a></li>
                                <li class="<?php echo (($selected_menu=="options")?"active":""); ?>"><a href="<?php echo base_url("/options"); ?>">Options</a></li>
                                <li class="<?php echo (($selected_menu=="products")?"active":""); ?>"><a href="<?php echo base_url("/products"); ?>">Products</a></li>
                                <li class="<?php echo (($selected_menu=="warehouses")?"active":""); ?>"><a href="<?php echo base_url("/warehouses"); ?>">Warehouses</a></li>
                                <li class="<?php echo (($selected_menu=="stores")?"active":""); ?>"><a href="<?php echo base_url("/stores"); ?>">Online Store</a></li>
                            </ul>
                        </li>
                        <li class="has-submenu">
                            <a href="#"><i class="md md-insert-chart"></i>Reports</a>
                            <ul class="submenu">
                                <li class="<?php echo (($selected_menu=="report_yearly_sales")?"active":""); ?>"><a href="<?php echo base_url("/report_yearly_sales"); ?>">Yearly Sales</a></li>
                                <li class="<?php echo (($selected_menu=="warehouse_history")?"active":""); ?>"><a href="<?php echo base_url("/warehouse_history"); ?>">Warehouse History</a></li>
                            </ul>
                        </li>
                        
                        <li class="has-submenu">
                            <a href="#"><i class="md md-settings-applications"></i>Settings</a>
                            <ul class="submenu">
                                <?php if($_SESSION['user']['user_type']=="1"){ ?>
                                <li class="has-submenu">
                                    <a href="<?php echo base_url("/users"); ?>">User Management</a>
                                    <ul class="submenu">
                                        <li class="<?php echo (($selected_menu=="user_login")?"active":""); ?>"><a href="<?php echo base_url("/users"); ?>">User Login</a></li>
                                        <li class="<?php echo (($selected_menu=="user_group")?"active":""); ?>"><a href="<?php echo base_url("/user_group"); ?>">User group</a></li>
                                    </ul>
                                </li>
                                <li class="<?php echo (($selected_menu=="global_setting")?"active":""); ?>"><a href="<?php echo base_url("/settings"); ?>">Global Settings</a></li>
                                <?php } ?>
                                <li class="<?php echo (($selected_menu=="forex")?"active":""); ?>"><a href="<?php echo base_url("/forex"); ?>">Exchange Rate</a></li>
                                <li class="<?php echo (($selected_menu=="marketplace")?"active":""); ?>"><a href="<?php echo base_url("/marketplaces"); ?>">Market Places</a></li>
                                <li class="<?php echo (($selected_menu=="accounts")?"active":""); ?>"><a href="<?php echo base_url("/accounts"); ?>">Accounts</a></li>
                                <li class="<?php echo (($selected_menu=="couriers")?"active":""); ?>"><a href="<?php echo base_url("/couriers"); ?>">Courier Service</a></li>
                            </ul>
                        </li>
                        
                        <?php if(isset($_SESSION['notification'])){ ?>
                        <li class="has-submenu">
                            <a href="#"><font class="text-danger"><i class="md md-sms"></i>Notification</font> 
                                <?php foreach($_SESSION['notification'] as $temp2){ ?>
                                <span class="<?php echo $temp2['badge-class']; ?>"><?php echo $temp2['size']; ?></span>
                                <?php } ?>
                            </a>
                            <ul class="submenu">
                                <?php foreach($_SESSION['notification'] as $temp2){ ?>
                                <li><a href="<?php echo $temp2['url']; ?>"><?php echo $temp2['name']; ?> (<?php echo $temp2['size']; ?>)</a></li>
                                <?php } ?>
                            </ul>
                        </li>
                        <?php } ?>
                    </ul>
                    <!-- End navigation menu -->
                </div>
            </div>
            </div>
        </header>

<script>
jQuery(function($){
    $('.navigation-menu li.active').parents('li.has-submenu').addClass('active');
});
</script>