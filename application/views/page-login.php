<?php
$this->CI->cpage->set_html_title('Login');

$version = "";
if(file_exists(APPPATH."../version.yml")){
    $version = file_get_contents(APPPATH."../version.yml");
}
?>


<div class="wrapper-page">

    <div class="text-center">
        <a href="<?php echo base_url("/"); ?>" class="logo logo-lg"><i class="md md-equalizer"></i> <span>Inventory Management</span> </a>
    </div>

    <form class="form-horizontal m-t-20" action="<?php echo base_url('/home/login'); ?>" method="POST">

        <div class="form-group">
            <div class="col-xs-12">
                <input class="form-control" name="username" type="text" required="" placeholder="Username">
                <i class="md md-account-circle form-control-feedback l-h-34"></i>
            </div>
        </div>

        <div class="form-group">
            <div class="col-xs-12">
                <input class="form-control" name="password" type="password" required="" placeholder="Password">
                <i class="md md-vpn-key form-control-feedback l-h-34"></i>
            </div>
        </div>
        <?php /*
          <div class="form-group">
          <div class="col-xs-12">
          <div class="checkbox checkbox-primary">
          <input id="checkbox-signup" type="checkbox">
          <label for="checkbox-signup">
          Remember me
          </label>
          </div>

          </div>
          </div>
         */ ?>
        <div class="form-group text-right m-t-20">
            <div class="col-xs-12">
                <button class="btn btn-primary btn-custom w-md waves-effect waves-light" type="submit">Log In
                </button>
            </div>
        </div>
        <?php /*
          <div class="form-group m-t-30">
          <div class="col-sm-7">
          <a href="pages-recoverpw.html" class="text-muted"><i class="fa fa-lock m-r-5"></i> Forgot your
          password?</a>
          </div>
          <div class="col-sm-5 text-right">
          <a href="pages-register.html" class="text-muted">Create an account</a>
          </div>
          </div>
         */ ?>
    </form>

    <div style="display:inline-block;position:fixed;padding:10px;right:0;bottom:0;color:#ccc;"><?php echo $version; ?></div>
</div>

<script>
    jQuery(function ($) {
        $('input[name="username"]').focus();
    });
</script>