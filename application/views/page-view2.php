<link href="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/media/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/FixedColumns/css/fixedColumns.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/Scroller/css/scroller.bootstrap.min.css" rel="stylesheet" type="text/css" />
<style>
body.dragging, body.dragging * {
  cursor: move !important;
}

.dragged {
  position: absolute;
  opacity: 0.5;
  z-index: 2000;
}
</style>
<?php
$contents = $this->cpage->template_data['view_contents'];
$editable = false;
include(dirname(__FILE__).'/include-view2.php');
include(dirname(__FILE__).'/include-view1.php');
?>

<div class="panel">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-6">
                <?php if($this->cpage->template_data['extra_filter']){ ?>
                <span class="">
                    <button class="btn btn-success waves-effect waves-light" onclick="data_edit(this,'extra_filter',true)" title="Extra filter"><i class="fa fa-lg fa-search"></i></button>
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

                    <table id="datatable-editable" class="dataTable table table-striped table-bordered table-hover" width="100%">
                        <thead>
                            <tr>
                                <?php if(!empty($contents['header'])){ foreach ($contents['header'] as $v) {
                                    echo '<th>'.$v.'</th>';
                                }}else{
                                    echo '<th>&nbsp;</th>';
                                }?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($contents['data'])){ foreach ($contents['data'] as $v) {
                                    echo '<tr>';
                                    foreach ($v as $v2) {
                                        echo '<td>'.$v2.'</td>';
                                    }
                                    echo '</tr>';
                                }}else{
                                echo '<tr><td>No Data</td></tr>';
                            }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/media/js/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/media/js/dataTables.bootstrap.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/FixedColumns/js/dataTables.fixedColumns.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/Scroller/js/dataTables.scroller.min.js"></script>

<script>
    //horizontal scroll by dragging
    var table;
    var clicked = false, clickX, clickY;
    var dataTableMouseMoveOverlay = $('<div class="dataTableMouseMoveOverlay" style="position:fixed;top:0;left:0;width:100%;height:100%;z-index:99999;display:none;cursor:move;"></div>');
    dataTableMouseMoveOverlay.on({
        'mousemove': function(e) {
            e.preventDefault();
            if(clicked){
                var temp = 0;
                temp = clickX - e.pageX;
                $('#datatable-editable').parent().scrollLeft($('#datatable-editable').parent().scrollLeft() + temp);
                temp = clickY - e.pageY;
                $('#datatable-editable').parent().scrollTop($('#datatable-editable').parent().scrollTop() + temp);
                clickY = e.pageY;
                clickX = e.pageX;
            }
        },
        'mouseup': function(e) {
            clicked = false;
            $(this).hide();
        },
        'mouseout': function(e) {
            clicked = false;
            $(this).hide();
        }
    }).appendTo($('body'));
    jQuery(function ($) {
        $('#datatable-editable').each(function () {
            var obj = $(this);
            table = obj.on('init.dt',function(){
                $('#datatable-editable').parent().parent().parent().on({
                    'mousedown': function(e) {
                        clicked = true;
                        clickY = e.pageY;
                        clickX = e.pageX;
                        $(this).on({
                            'mousemove': function(e) {
                                e.preventDefault();
                                if(clicked && (Math.abs(clickX - e.pageX)>0 || Math.abs(clickY - e.pageY)>0)){
                                    $(this).off('mousemove');
                                    dataTableMouseMoveOverlay.show();
                                    
                                }
                            }
                        });
                    },
                    'mouseup': function(e) {
                        clicked = false;
                        dataTableMouseMoveOverlay.hide();
                        $(this).off('mousemove');
                    }
                });
            }).DataTable({
                paging: false,
                deferRender:    true,
                scrollY:        500,
                scrollX:        true,
                scrollCollapse: true,
                fixedColumns: {
                    rightColumns: 0,
                    leftColumns: 1
                },
                order: 0,
                "columnDefs": [
                <?php $count=0; foreach ($contents['header'] as $header) { ?>
                    {
                        "targets": [ <?php echo $count; ?> ],
                        "searchable": false,
                        "orderable": false
                    },
                <?php $count+=1;} ?>
                ],
            });
        });
        
    });
</script>
