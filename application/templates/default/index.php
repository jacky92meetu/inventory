<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
<?php
header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
header("Pragma: no-cache"); //HTTP 1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>

<!DOCTYPE html>
<html>
    
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="keywords" content="" />
        <meta name="description" content="" />

        <link rel='shortcut icon' href='/favicon.ico' type='image/x-icon' />

        <link href="<?php echo base_url('/assets/default'); ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url('/assets/default'); ?>/css/core.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url('/assets/default'); ?>/css/components.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url('/assets/default'); ?>/css/icons.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url('/assets/default'); ?>/css/pages.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url('/assets/default'); ?>/css/menu.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url('/assets/default'); ?>/css/responsive.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url('/assets/default'); ?>/plugins/sweetalert2/dist/sweetalert2.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo base_url('/assets/default'); ?>/css/default.css" rel="stylesheet" type="text/css" />
        
        <script>
            var site_url = '<?php base_url('/'); ?>';
        </script>

        <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->

        <script src="<?php echo base_url('/assets/default'); ?>/js/modernizr.min.js"></script>
        
        <!-- jQuery  -->
        <script src="<?php echo base_url('/assets/default'); ?>/js/jquery.min.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/js/bootstrap.min.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/js/detect.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/js/fastclick.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/js/jquery.blockUI.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/js/waves.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/js/wow.min.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/js/jquery.nicescroll.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/js/jquery.scrollTo.min.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/plugins/notifyjs/dist/notify.min.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/plugins/notifications/notify-metro.js"></script>
        <script type="text/javascript" src="<?php echo base_url('/assets/default'); ?>/plugins/parsleyjs/dist/parsley.min.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/plugins/sweetalert2/dist/sweetalert2.min.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/js/default.js"></script>

        <module type="head" />
    </head>

    <body>
        <module type="header" />
        <div class="wrapper">
            <div class="container">
                <div class="row"><div class="col-xs-12"><module type="message" /></div></div>
                <div class="row"><div class="col-xs-12"><module type="breadcrumb" /></div></div>
                <!-- Page-Title -->
                <div class="row">
                    <div class="col-sm-12">
                        <module type="contents" />
                    </div>
                </div>
                <!-- Page-Title -->
                
                <!-- Footer -->
                <module type="footer" />
                <!-- End Footer -->

            </div> <!-- end container -->
        </div>
        <!-- End wrapper -->
        
        <!-- Custom main Js -->
        <script src="<?php echo base_url('/assets/default'); ?>/js/jquery.core.js"></script>
        <script src="<?php echo base_url('/assets/default'); ?>/js/jquery.app.js"></script>
        
    </body>
    
</html>