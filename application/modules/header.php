<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');
?>
<?php
    $selected_menu = (isset($this->CI->cpage->template_data['selected_menu']))?$this->CI->cpage->template_data['selected_menu']:'home';
    $selected_menu = strtolower($selected_menu);
    
    $menu_config = array(
        'sales'=>array('class'=>'md-attach-money'),
        'inventory'=>array('class'=>'md-account-balance'),
        'reports'=>array('class'=>'md-insert-chart'),
        'settings'=>array('class'=>'md-settings-applications')
    );
    
    $menu_list = array();
    $sql = 'select * from user_group_privileges_list';
    if(($result = $this->CI->db->query($sql)) && $result->num_rows()){
        require_once(APPPATH.'libraries/lenses/lensesMain.php');
        $class = new lensesMain();
        $temp = $result->result_array();
        foreach($temp as $r){
            if(!$class->get_user_access($_SESSION['user']['user_type'], $r['code'])){
                continue;
            }
            $temp2 = &$menu_list;
            foreach(explode("|",$r['description']) as $title){
                $a = strtolower($title);
                if(!isset($temp2[$a])){
                    $temp2[$a] = array('title'=>$title,'submenu'=>array());
                }
                $temp2 = &$temp2[$a]['submenu'];
            }
            $temp2 = array('code'=>$r['code'],'url'=>$r['url']);
        }
    }
?>
<script src="<?php echo base_url('/assets/default'); ?>/js/action.js"></script>
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
                                    <li><a href="javascript:void(0)"><i class="ti-user m-r-5"></i> <b>Welcome, <?php echo $_SESSION['user']['name']; ?></b></a></li>
                                    <li><a href="javascript:void(0)" onclick="show_password_form(this)"><i class="ti-lock m-r-5"></i> Change Password</a></li>
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
                        
                        <?php
                            foreach($menu_list as $key => $v1){
                                if(isset($v1['submenu']) && !isset($v1['submenu']['url'])){
                                    ?>
                                    <li class="has-submenu">
                                        <a href="#"><i class="md <?php echo (isset($menu_config[$key]))?$menu_config[$key]['class']:""; ?>"></i><?php echo $v1['title']; ?></a>
                                        <ul class="submenu">
                                    <?php
                                    foreach($v1['submenu'] as $v2){
                                        if(isset($v2['submenu']) && !isset($v2['submenu']['url'])){
                                            ?>
                                            <li class="has-submenu">
                                                <a href="#"><?php echo $v2['title']; ?></a>
                                                <ul class="submenu">
                                            <?php
                                            foreach($v2['submenu'] as $v3){
                                                ?><li class="<?php echo (($selected_menu==$v3['submenu']['code'])?"active":""); ?>"><a href="<?php echo base_url($v3['submenu']['url']); ?>"><?php echo $v3['title']; ?></a></li><?php
                                            }
                                            ?>
                                                </ul>
                                            </li>
                                            <?php
                                        }else{
                                            ?><li class="<?php echo (($selected_menu==$v2['submenu']['code'])?"active":""); ?>"><a href="<?php echo base_url($v2['submenu']['url']); ?>"><?php echo $v2['title']; ?></a></li><?php
                                        }
                                    }
                                    ?>
                                        </ul>
                                    </li>
                                    <?php
                                }
                            }
                        ?>
                        
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

<div id="change_password_form_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="change_password_form_modal" aria-hidden="true" style="display:none;">
    <div class="modal-dialog">
        <div class="modal-content form-container">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Change Password</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12 form-field">
                        <div class="form-group">
                            <label class="control-label">Old Password</label>
                            <input type="password" class="form-control" placeholder="" name="old_password" required>
                        </div>
                    </div>
                    <div class="col-xs-12 form-field">
                        <div class="form-group">
                            <label class="control-label">New Password</label>
                            <input type="password" class="form-control" placeholder="" name="new_password" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary waves-effect waves-light" onclick="change_password_save(this)">Save</button>
            </div>
            <div class="modal-footer-loading">
                Loading...
            </div>
        </div>
    </div>
</div>

<script>
jQuery(function($){
    $('.navigation-menu li.active').parents('li.has-submenu').addClass('active');
});

function show_password_form(){
    $('#change_password_form_modal').modal('show');
}

function change_password_save(obj){
    var post_data = {};
    post_data['old_password'] = $(obj).closest('.modal').find('[name="old_password"]').val();
    post_data['new_password'] = $(obj).closest('.modal').find('[name="new_password"]').val();
    $.post('<?php echo base_url('ajax/users?method=change_password'); ?>',post_data,function(data){
        if(data.status=="1"){
            show_notification('Password update successfuly.','Notification','success');
        }else{
            show_notification('Password update fail.','Notification','error');
        }
    }, 'json')
    .error(function(){
        show_notification('Submission to server error!','Notification','error');
    })
    .always(function(){
        $('#change_password_form_modal').modal('hide');
    });
}
</script>