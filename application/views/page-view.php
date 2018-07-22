<?php /*
<link href="<?php echo base_url('/assets/default'); ?>/plugins/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/plugins/datatables/buttons.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/plugins/datatables/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/plugins/datatables/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/plugins/datatables/scroller.bootstrap.min.css" rel="stylesheet" type="text/css" />
*/ ?>
<link href="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/media/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/FixedColumns/css/fixedColumns.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/Scroller/css/scroller.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/css/select.dataTables.min.css" rel="stylesheet" type="text/css" />
<style>
body.dragging, body.dragging * {
  cursor: move !important;
}

.dragged {
  position: absolute;
  opacity: 0.5;
  z-index: 2000;
}
ul.sortable{border:2px solid #007ebd;padding:2px;margin:0;height:300px;overflow-x:scroll;}
ul.sortable li{list-style-type: none;padding:5px;border:2px solid #007e70;margin:2px;}
ul.sortable.freeze_list li{background-color: #007ebd;color: #fff;}
ul.sortable li.placeholder {
  position: relative;
}
ul.sortable li.placeholder:before {
  position: absolute;
}
</style>
<?php
$editable = false;
include(dirname(__FILE__).'/include-view2.php');
include(dirname(__FILE__).'/include-view1.php');
?>

<div class="panel">
    <div class="panel-body">
        <?php /*
        <div class="row">
            <div class="col-xs-12">
                <h4 class="page-title"><b><?php echo $this->CI->cpage->template_data['view_title']; ?></b></h4>
            </div>
        </div>
        */ ?>
        <div class="row">
            <div class="col-xs-6">
                <span class="">
                    <button class="btn btn-danger waves-effect waves-light" onclick="javascript:$('#custom_form_modal2').modal();" title="Table header settings"><i class="fa fa-lg fa-table"></i></button>
                </span>
                <?php if($this->cpage->template_data['extra_filter']){ ?>
                <span class="">
                    <button class="btn btn-success waves-effect waves-light" onclick="data_edit(this,'extra_filter',true)" title="Extra filter"><i class="fa fa-lg fa-search"></i></button>
                </span>
                <?php } ?>
                <?php if($this->cpage->template_data['add_btn']){ $is_custom = ($this->cpage->template_data['add_btn']==='custom_form')?"true":"false"; ?>
                <span class="">
                    <button id="addToTable" class="btn btn-primary waves-effect waves-light" onclick="data_edit(null,'new',true)" title="Add"><i class="fa fa-lg fa-plus"></i></button>
                </span>
                <?php } ?>
                <?php if($this->cpage->template_data['delete_btn']){ ?>
                <span class="">
                    <button id="deleteFromTable" class="btn btn-danger waves-effect waves-light" onclick="data_delete()" title="Delete record"><i class="fa fa-lg fa-trash-o"></i></button>
                </span>
                <?php } ?>
            </div>
            <div class="col-xs-6 text-right">
                <div class="btn-group">
                    <button type="button" class="btn dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false">More Function <span class="caret"></span></button>
                    <ul class="dropdown-menu" role="menu">
                <?php if(sizeof($this->cpage->template_data['extra_btn'])>0){ 
                    foreach($this->cpage->template_data['extra_btn'] as $temp){ 
                        $class = "btn-default";
                        if(isset($temp['class'])){
                            $class = $temp['class'];
                        }
                        $custom_form = "";
                        if(isset($temp['custom_form'])){
                            $custom_form = 'custom_form="'.$temp['custom_form'].'"';
                        }
                        $require_select = "";
                        if(isset($temp['require_select'])){
                            $require_select = 'require_select="require_select"';
                        }
                ?>
                    <li><a href="javascript:void(0)" class="<?php echo $class; ?>" onclick="extra_btn(this)" <?php echo $custom_form; ?> <?php echo $require_select; ?> data-goto="<?php echo $temp['url']; ?>"><?php echo $temp['name']; ?></a></li>
                <?php /*
                <span>
                    <button class="btn <?php echo $class; ?> waves-effect waves-light" onclick="extra_btn(this)" <?php echo $custom_form; ?> <?php echo $require_select; ?> data-goto="<?php echo $temp['url']; ?>"><?php echo $temp['name']; ?></button>
                </span>
                */ ?>
                <?php }} ?>
                    </ul>
                </div>
            </div>
        </div>
        <br />
        <div class="row">
            <div class="col-xs-12">
                <div class="">

                    <table id="datatable-editable" class="dataTable table table-striped table-bordered table-hover <?php echo ((isset($this->cpage->template_data['custom_form']) && $this->cpage->template_data['custom_form'])?"custom_form":""); ?>" width="100%">
                        
                        <thead>
                            <tr class="thead-search">
                                <?php $count=0;
                                    foreach ($this->cpage->template_data['view_header'] as $header) {
                                        $goto_url = "";
                                        $class = "";
                                        $class2 = "";
                                        $sorting = "";
                                        $search_get = "";
                                        $temp = "search_".$header['id'];
                                        if(isset($_GET[$temp]) && strlen($_GET[$temp])>0){
                                            $search_get = $_GET[$temp];
                                        }
                                        if(isset($header['editable'])){
                                            $class .= " editable";
                                            $editable = true;
                                        }
                                        if(isset($header['is_date'])){
                                            $class2 .= " is_date";
                                        }
                                        if(isset($header['is_date_highlight'])){
                                            $class2 .= " is_date_highlight";
                                        }
                                        if(isset($header['is_ajax'])){
                                            $class2 .= " is_ajax";
                                        }
                                        if(isset($header['optional'])){
                                            $class2 .= " optional";
                                        }
                                        if(isset($header['goto'])){
                                            $goto_url = 'data-goto="'.$header['goto'].'"';
                                        }else if(isset($header['custom_col'])){
                                            $goto_url = 'custom-col="'.$header['custom_col'].'"';
                                        }
                                        if(isset($header['filter-sorting'])){
                                            $sorting = 'filter-sorting="'.$header['filter-sorting'].'"';
                                        }
                                        if(isset($header['option_text'])){
                                ?>
                                <th class="<?php echo $class; ?>">
                                    <select class="column_filter <?php echo $class2; ?>" data-column="<?php echo $count; ?>" name="<?php echo $header['id']; ?>" <?php echo $sorting; ?>>
                                        <option value=""><?php echo "No Filter"; ?></option>
                                        <?php foreach($header['option_text'] as $key => $value){ ?>
                                        <option value="<?php echo $key; ?>" <?php echo ((!empty($search_get) && $search_get==$key)?"SELECTED":""); ?> display="<?php echo $value; ?>"><?php echo $value; ?></option>
                                        <?php } ?>
                                    </select>
                                </th>
                                <?php
                                        }else{
                                ?>
                                <th class="<?php echo $class; ?>" <?php echo $goto_url; ?>><input type="text" class="column_filter <?php echo $class2; ?>" placeholder="<?php echo "Search ".$header['name']; ?>" data-column="<?php echo $count; ?>" name="<?php echo $header['id']; ?>" value="<?php echo $search_get; ?>" <?php echo $sorting; ?>></th>
                                <?php   } 
                                        $count+=1;
                                    }
                                ?>
                                <?php if(true){ //if($editable){ ?>
                                <th width="10" style="vertical-align:top;text-align:right;">
                                    <div style="position:relative;">
                                        <div class="btn-group btn-group-xs actions_label" style="position:absolute;top:0;right:0;">
                                            <button class="resetFilter btn btn-warning waves-effect waves-light" title="Reset Filter"><i class="fa fa-lg fa-refresh"></i></button>
                                            <button class="select_all_checkbox btn btn-primary waves-effect waves-light" title="Select All"><i class="fa fa-lg fa-check-square-o"></i></button>
                                            <input type="checkbox" class="select_all_checkbox hidden">
                                        </div>
                                    </div>
                                </th>
                                <?php } ?>
                            </tr>
                            <tr>
                                <?php $count=0; foreach ($this->cpage->template_data['view_header'] as $header) { ?>
                                    <th><?php echo $header['name']; ?></th>
                                <?php $count+=1;} ?>
                                <?php if(true){ //if($editable){ ?>
                                    <th style="color:transparent;">Actions</th>    
                                <?php } ?>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="custom_form_modal2" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true" style="display:none;">
    <div class="modal-dialog">
        <div class="modal-content form-container">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Header Options</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <?php
                    $freeze_list = array();
                    $normal_list = array();
                    $count = 1;
                    $freeze_count = $this->cpage->template_data['freezePane'];
                    foreach ($this->cpage->template_data['view_header'] as $header) {
                        if($count<=$freeze_count){
                            $freeze_list[$header['id']] = $header['name'];
                        }else{
                            $normal_list[$header['id']] = $header['name'];
                        }
                        $count++;
                    }
                    ?>
                    <div class="col-sm-6">
                        <div>Freeze Header</div>
                        <ul class='sortable header_list freeze_list'>
                            <?php foreach($freeze_list as $k => $v){ ?>
                            <li data_id="<?php echo $k; ?>"><?php echo $v; ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                    <div class="col-sm-6">
                        <div>Normal Header</div>
                        <ul class='sortable header_list normal_list'>
                            <?php foreach($normal_list as $k => $v){ ?>
                            <li data_id="<?php echo $k; ?>"><?php echo $v; ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning waves-effect" onclick="header_reset(this)">Reset Header</button>
                <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary waves-effect waves-light" onclick="header_save(this)">Save</button>
            </div>
            
            <div class="modal-footer-loading">
                Loading...
            </div>
        </div>
    </div>
</div>

<?php /*
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/dataTables.bootstrap.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/dataTables.buttons.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/buttons.bootstrap.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/pdfmake.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/vfs_fonts.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/buttons.html5.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/buttons.print.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/dataTables.fixedHeader.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/dataTables.keyTable.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/dataTables.responsive.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/responsive.bootstrap.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/datatables/dataTables.scroller.min.js"></script>
*/ ?>
<script src="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/media/js/jquery.dataTables.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/media/js/dataTables.bootstrap.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/FixedColumns/js/dataTables.fixedColumns.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/Scroller/js/dataTables.scroller.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/js/dataTables.select.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/js/jquery-sortable.js"></script>

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
            var filter_sorting = [[ 0, "asc" ]];
            var obj = $(this);
            var thead_search = obj.find('.thead-search');

            if(thead_search.find('th .column_filter[filter-sorting]').length){
                var t = thead_search.find('th .column_filter[filter-sorting]');
                filter_sorting = [[ t.attr('data-column'), t.attr('filter-sorting') ]];
            }
            
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
                setTimeout(function(){
                    $('#datatable-editable_length').each(function(){
                        var clone = $(this).find('select[name="datatable-editable_length"]').hide().clone(true);
                        clone.show().val("<?php echo $this->cpage->template_data['default_length']; ?>");
                        $('.actions_label').prepend(clone);
                        $(this).addClass('hidden');
                    });
                    $('select.column_filter').each(function(){
                        if($(this).val().length && $(this).val()!=='0'){
                            table.column($(this).attr('data-column')).search($(this).val(),true,true).draw();
                        }else if(table.column($(this).attr('data-column')).search().length){
                            $(this).val(table.column($(this).attr('data-column')).search());
                        }
                        $(this).on('change', function(){
                            table.column($(this).attr('data-column')).search($(this).val(),true,true).draw();
                        });
                    });
                    $('input.column_filter').each(function(){
                        if($(this).val().length && $(this).val()!=='0'){
                            table.column($(this).attr('data-column')).search($(this).val(),true,true).draw();
                        }else if(table.column($(this).attr('data-column')).search().length){
                            $(this).val(table.column($(this).attr('data-column')).search());
                        }
                        (function(obj){
                            var active = 0;
                            setInterval(function(){
                                if(active>0){
                                    active = active + 1;
                                }
                                if(active>3){
                                    active = 0;
                                    table.column($(obj).attr('data-column')).search($(obj).val(),true,true).draw();
                                }
                            },100);
                            obj.unbind()
                            .bind('keyup', function(e){
                                if(table.column($(obj).attr('data-column')).search()==$(obj).val()) return;
                                if($(obj).val().length > 0 && $(obj).val().length < 1 && e.keyCode != 13) return;
                                if(e.keyCode != 13){active = 1;}else{active = 100;}
                            });
                        })($(this));
                    });
                    $('.resetFilter').on('click',function(){
                        $('input.column_filter').val("");
                        $('select.column_filter').val("");
                        table.search( '' ).columns().search( '' ).draw();
                        table.rows().deselect();
                        $('input.select_all_checkbox').prop('checked',false);
                    });
                    $('button.select_all_checkbox').on('click',function(){
                        if($('input.select_all_checkbox').prop('checked')){
                            table.rows().deselect();
                            $('input.select_all_checkbox').prop('checked',false);
                        }else{
                            table.rows().select();
                            $('input.select_all_checkbox').prop('checked',true);
                        }
                    });
                },100);
            }).DataTable({
                paging: true,
                //"stateSave": true,
                //scroller: true,//{boundaryScale: 0, displayBuffer: 20},
                deferRender:    true,
                scrollY:        500,
                scrollX:        true,
                scrollCollapse: true,
                <?php if($this->cpage->template_data['freezePane']>0){ ?>
                "fixedColumns": {
                    <?php if(true){ //if($editable){ ?>
                    "rightColumns": 1,
                    <?php } ?>
                    "leftColumns": <?php echo $this->cpage->template_data['freezePane']; ?>
                },
                <?php } ?>
                "iDisplayLength": <?php echo min(25,max(100,$this->cpage->template_data['default_length'])); ?>,
                "lengthMenu": [[25, 50, 100], [25, 50, 100]],
                "order": filter_sorting,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url":"<?php echo $this->cpage->template_data['view_ajax_url']; ?>",
                    "type": "POST",
                    "data": {'method':'read'}
                },
                "createdRow": function(row,data,dataIndex){
                    var $data_id = data[0];
                    $(row).attr('data-id',$data_id);
                    for(var i=0; i<data.length; i++){
                        var val = data[i];
                        if(thead_search.find('th:eq('+i+')[data-goto]').length){
                            $(row).find('td:eq('+i+')').html('<a href="javascript:void(0)" onclick="post(\''+thead_search.find('th:eq('+i+')[data-goto]').attr('data-goto')+'\',{id:\''+$data_id+'\'},\'_blank\')">'+val+'</a>');
                        }else if(thead_search.find('th:eq('+i+')[custom-col]').length){
                            $(row).find('td:eq('+i+')').html('<a href="javascript:void(0)" onclick="data_edit(this,\''+thead_search.find('th:eq('+i+')[custom-col]').attr('custom-col')+'\',true)">'+val+'</a>');
                        }
                        var order_value = val;
                        var temp = /^([0-9]{2})[^0-9]([0-9]{2})[^0-9]([0-9]{4})$/gi.exec(val);
                        if(temp){
                            temp = new Date(temp[3]+"-"+temp[2]+"-"+temp[1]).getTime()/1000;
                            order_value = temp;
                        }
                        $(row).find('td:eq('+i+')').attr('data-search',val).attr('data-order',order_value);
                        if(thead_search.find('th:eq('+i+') select.column_filter option[value="'+val+'"]').length){
                            $(row).find('td:eq('+i+')').html(thead_search.find('th:eq('+i+') select.column_filter option[value="'+val+'"]').text());
                        }
                        if(thead_search.find('th:eq('+i+').editable').length){
                            $(row).find('td:eq('+i+')').addClass('editable_td');
                        }
                    }
                    <?php if($editable){ ?>
                    $(row).find('td').last().addClass('actions').html('<a href="javascript:void(0)" onclick="data_save(this)" class="on-editing save-row" title="Save"><i class="fa fa-lg fa-save"></i></a><a href="javascript:void(0)" onclick="data_cancel(this)" class="on-editing cancel-row" title="Cancel"><i class="fa fa-lg fa-times"></i></a><a href="javascript:void(0)" onclick="data_edit(this)" class="on-default edit-row" title="Edit"><i class="fa fa-lg fa-pencil"></i></a>');
                    $(row).find('td.editable_td').not('.actions').on('dblclick',function(){data_edit($(this).closest('tr').find('td.actions .edit-row'));});
                    <?php } ?>
                    <?php /*
                    $(row).find('td').last().addClass('actions').html($(row).find('td').last().addClass('actions').html()+'<a href="javascript:void(0)" class="" title="test"><i class="fa fa-lg fa-plus"></i></a>');
                    */ ?>
                    <?php if($this->cpage->template_data['delete_btn'] || sizeof($this->cpage->template_data['extra_btn'])>0){ ?>
                    $(row).find('td').not('.actions').on('click',function(){ $('tr[data-id="'+$(this).closest('tr[data-id]').attr('data-id')+'"]').toggleClass('selected'); });
                    //$(row).find('td:first').html('<input type="checkbox" class="select_checkbox" />');
                    <?php } ?>
                },
                "columnDefs": [
                    /*
                    {
                        "targets": [ 0 ],
                        "orderable": false
                    },
                    */
                <?php $count=0; foreach ($this->cpage->template_data['view_header'] as $header) { ?>
                    {
                        "targets": [ <?php echo $count; ?> ],
                        "visible": <?php echo ((isset($header['hide']))?"false":"true"); ?>,
                        "searchable": <?php echo ((isset($header['nosearch']))?"false":"true"); ?>,
                        "orderable": <?php echo ((isset($header['noorder']))?"false":"true"); ?>
                    },
                <?php $count+=1;} ?>
                <?php if(true){ //if($editable){ ?>
                    {
                        "targets": [ -1 ],
                        "searchable": false,
                        "orderable": false
                    }
                <?php } ?>
                ],
            });
            /*
            table.on( 'order.dt search.dt', function () {
                table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    //cell.innerHTML = i+1;
                } );
            } ).draw();
            */
        });
        
        $('#custom_form_modal2 .sortable').sortable({group:'header_list'});
        
    });
</script>
