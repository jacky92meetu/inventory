<?php
$editable = false;
?>
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
<link href="<?php echo base_url('/assets/default'); ?>/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

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
                <?php if($this->cpage->template_data['add_btn']){ $is_custom = ($this->cpage->template_data['add_btn']==='custom_form')?"true":"false"; ?>
                <span class="">
                    <button id="addToTable" class="btn btn-primary waves-effect waves-light" onclick="data_edit(null,'new',true)">Add <i class="fa fa-lg fa-plus"></i></button>
                </span>
                <?php } ?>
                <?php if($this->cpage->template_data['delete_btn']){ ?>
                <span class="">
                    <button id="deleteFromTable" class="btn btn-danger waves-effect waves-light" onclick="data_delete()">Delete record <i class="fa fa-lg fa-trash-o"></i></button>
                </span>
                <?php } ?>
            </div>
            <div class="col-xs-6 text-right">
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
                <span>
                    <button class="btn <?php echo $class; ?> waves-effect waves-light" onclick="extra_btn(this)" <?php echo $custom_form; ?> <?php echo $require_select; ?> data-goto="<?php echo $temp['url']; ?>"><?php echo $temp['name']; ?></button>
                </span>
                <?php }} ?>
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
                                        <option value="<?php echo $key; ?>" <?php echo (($search_get==$key)?"SELECTED":""); ?> display="<?php echo $value; ?>"><?php echo $value; ?></option>
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
                                <?php if($editable){ ?>
                                <th width="10" style="vertical-align:top;text-align:right;">
                                    <div style="position:relative;">
                                        <div class="btn-group btn-group-xs actions_label" style="position:absolute;top:0;right:0;">
                                            <button class="resetFilter btn btn-warning waves-effect waves-light"><i class="fa fa-lg fa-refresh"></i></button>
                                            <button class="select_all_checkbox btn btn-primary waves-effect waves-light"><i class="fa fa-lg fa-check-square-o"></i></button>
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
                                <?php if($editable){ ?>
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

<div id="custom_form_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display:none;">
    <div class="modal-dialog">
        <div class="modal-content form-container">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title">Form</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12 form-field default text-default hidden">
                        <div class="form-group">
                            <label class="control-label">Fieldname</label>
                            <input type="text" class="form-control" placeholder="" required>
                        </div>
                    </div>
                    <div class="col-xs-12 form-field default select-default hidden">
                        <div class="form-group">
                            <label class="control-label">Fieldname</label>
                            <select class="form-control"></select>
                        </div>
                    </div>
                    <div class="col-xs-12 form-field default readonly-default hidden">
                        <div class="form-group">
                            <label class="control-label">Fieldname</label>
                            <input type="text" class="form-control disabled" DISABLED>
                            <input type="hidden" class="form-control">
                        </div>
                    </div>
                    <div class="col-xs-12 form-field default hidden-default hidden">
                        <input type="hidden" class="form-control">
                    </div>
                    <div class="col-xs-12 form-field default date-default hidden">
                        <div class="form-group">
                            <label class="control-label">Fieldname</label>
                            <div class="input-group">
                                <input type="text" class="form-control datepicker-autoclose" placeholder="dd/mm/yyyy">
                                <span class="input-group-addon bg-primary b-0 text-white"><i class="ion-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 form-field default file-default hidden">
                        <div class="form-group">
                            <label class="control-label">Fieldname</label>
                            <input type="file">
                            <input type="hidden" class="form-control">
                            <div class="loading_status">Upload file here</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary waves-effect waves-light" onclick="data_save(this)">Save</button>
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
<script src="<?php echo base_url('/assets/default'); ?>/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/js/action.js"></script>

<script>
    var max_size = (1000 * 1000 * 5);
    function show_processing(obj,show){
        if(typeof show === 'undefined'||show===true){
            $('#datatable-editable').parent().find('#datatable-editable_processing').show();
        }else if(show===false){
            $('#datatable-editable').parent().find('#datatable-editable_processing').hide();
        }
    }
    function show_edit(obj,show){
        if(typeof show === 'undefined'||show===true){
            $(window).on('keyup',function(e){
                if(e.keyCode==13){
                    data_save(obj);
                    return false;
                }else if(e.keyCode==27){
                    data_cancel(obj);
                    return false;
                }
                return true;
            });
            $(window).on('mousedown',function(e){
                if(e.button == 2){
                    data_cancel(obj);
                    return false; 
                }
                return true;
            });
        }else if(show===false){
            $(window).unbind('keyup contextmenu mousedown');
        }
    }
    function data_edit(obj,type,is_custom){
        data_cancel(obj);
        show_edit(obj);
        var id = "";
        if(typeof obj==='object' && $(obj).closest('[data-id]').length){
            id = $(obj).closest('[data-id]').attr('data-id');
        }
        if(type=="new"){
            is_custom = true;
        }
        if((is_custom && typeof type === 'string') || $('#datatable-editable').is('.custom_form')){
            show_processing(obj);
            var post_data = {};
            post_data['method'] = 'custom_form';
            post_data['type'] = type;
            post_data['id'] = id;
            try{
                $.post('<?php echo $this->cpage->template_data['view_ajax_url']; ?>',post_data,function(data){
                    if(typeof data.data === 'object'){
                        $('#custom_form_modal .form-container.form-loading').removeClass('form-loading');
                        var container = $('#custom_form_modal .modal-body > .row');
                        container.find('.form-field').not('.default').remove();
                        var has_ajax = false;
                        for(var i in data.data){
                            if(typeof data.data[i].value === 'undefined'){
                                data.data[i].value = "";
                            }
                            if(typeof data.data[i].readonly === 'string'){
                                var c = container.find('.form-field.readonly-default.default.hidden').clone();
                                c.removeClass('default hidden').appendTo(container);
                                c.find('label').html(data.data[i].name);
                                c.find('input').attr('name',data.data[i].id).val(data.data[i].value);
                            }else if(typeof data.data[i].hidden === 'string'){
                                var c = container.find('.form-field.hidden-default.default.hidden').clone();
                                c.removeClass('default hidden').appendTo(container);
                                c.find('input').attr('name',data.data[i].id).val(data.data[i].value);
                            }else if(typeof data.data[i].is_date === 'string'){
                                var c = container.find('.form-field.date-default.default.hidden').clone();
                                c.removeClass('default hidden').appendTo(container);
                                c.find('label').html(data.data[i].name);
                                c.find('input').attr('name',data.data[i].id).val(data.data[i].value);
                                set_date(c.find('input'));
                            }else if(typeof data.data[i].is_file === 'string'){
                                var c = container.find('.form-field.file-default.default.hidden').clone();
                                c.removeClass('default hidden').appendTo(container);
                                c.find('label').html(data.data[i].name);
                                var temp = c.find('input[type="hidden"]');
                                temp.attr('name',data.data[i].id);
                                c.find('input[type="file"]').change(function(){
                                    temp.parent().find('.loading_status').html('Uploading...');
                                    if($(this)[0].files.length){
                                        var uploaded_file = $(this)[0].files[0];
                                        if(uploaded_file.size > max_size){
                                            temp.val('');
                                            temp.parent().find('.loading_status').html('<span class="text-danger">File size limit exceed.(Max: '+(max_size/(1000*1000)).toFixed(4)+' MB)</span>');
                                        }else{
                                            var FR= new FileReader();
                                            FR.addEventListener("load", function(e) {
                                                temp.val(e.target.result.match(/,(.*)$/)[1]);
                                                temp.parent().find('.loading_status').html('<span class="text-success">Type: '+uploaded_file.type+' ('+(uploaded_file.size/(1000*1000)).toFixed(4)+' MB)</span>');
                                            });
                                            FR.readAsDataURL( uploaded_file );
                                        }
                                    }else{
                                        temp.val('');
                                        temp.parent().find('.loading_status').html('Fail to upload.');
                                    }
                                });
                            }else if(typeof data.data[i].option_text === 'object'){
                                var c = container.find('.form-field.select-default.default.hidden').clone();
                                c.removeClass('default hidden').appendTo(container);
                                c.find('label').html(data.data[i].name);
                                c.find('select').attr('name',data.data[i].id);
                                for(var j in data.data[i].option_text){
                                    var t = $('<option value="'+j+'" display="'+data.data[i].option_text[j]+'">'+data.data[i].option_text[j]+'</option>');
                                    c.find('select').append(t);
                                }
                                if(data.data[i].value.length>0){
                                    c.find('select option[value="'+data.data[i].value+'"]').attr('selected','selected');
                                }else{
                                    c.find('select option:first').attr('selected','selected');
                                }
                                if(typeof data.data[i].is_ajax === 'string'){
                                    if(!has_ajax){
                                        has_ajax = c.find('select');
                                    }
                                    c.find('select').addClass('is-ajax').on('change',function(){
                                        ajax_change_update($(this));
                                    });
                                }
                            }else{
                                var c = container.find('.form-field.text-default.default.hidden').clone();
                                c.removeClass('default hidden').appendTo(container);
                                c.find('label').html(data.data[i].name);
                                c.find('input').attr('name',data.data[i].id).val(data.data[i].value);
                            }
                            if(typeof data.data[i].optional === 'string'){
                                c.find('.form-control').addClass('optional');
                            }
                            if(typeof data.data[i].form_class === 'string'){
                                c.addClass(data.data[i].form_class);
                            }
                            if(typeof data.data[i].form_divider === 'string'){
                                $('<hr style="display:inline-block;width:100%;background-color:#e5e5e5;height:2px;" />').insertAfter(c);
                            }
                        }
                        if(id.length>0){
                            $('#custom_form_modal').find('.form-container').attr('data-id',id);
                        }
                        $('#custom_form_modal').modal('show');
                        //$('.form-container:visible .form-field:not(.default) .form-control:not(.disabled,:disabled)').first().focus();
                        if(has_ajax){
                            ajax_change_update(has_ajax,true);
                        }
                    }
                    if(typeof data.message === 'string' && data.message.length>0){
                        if(typeof data.status=='1'){
                            show_notification(data.message,'Notification','error');
                        }else{
                            show_notification(data.message,'Notification');
                        }
                    }
                    if(typeof data.func === 'function'){data.func(obj);}else if(typeof data.func === 'string' && data.func.indexOf("function(")==0){eval('('+data.func+')')();}
                }, 'json')
                .error(function(){
                    show_notification('Submission to server error!','Notification','error');
                })
                .always(function(){
                    show_processing(obj,false);
                });
            }catch(e){
                console.log(e);
            }
        }else{
            if($('#datatable-editable tr[data-id="'+id+'"]').length){
                var tr = $('#datatable-editable tr[data-id="'+id+'"]');
                var clone = tr.clone();
            }else{
                var size = $('#datatable-editable').find('.thead-search th').length;
                var clone = $('<tr></tr>');
                for(var i = 0; i<size; i++){
                    $('<td></td>').appendTo(clone);
                }
            }
            if(id.length>0){
                clone.addClass('tr-edit');
                $('.DTFC_RightWrapper tr[data-id="'+id+'"]').addClass('tr-edit');
            }else{
                clone.addClass('tr-add');
            }
            var count = 0;
            var has_ajax = false;
            clone.find('td').each(function(){
                if($('#datatable-editable').find('.thead-search th:eq('+count+').editable .column_filter').length){
                    var filter = $('#datatable-editable').find('.thead-search th:eq('+count+') .column_filter');
                    var input = $('<input value="" required />');
                    if(id.length>0){
                        input.val($(this).attr('data-search'));
                    }
                    if(filter.is('.is_date')){
                        input.addClass('.datepicker-autoclose');
                        set_date(input);
                    }else if(filter.is('select')){
                        var input = $('#datatable-editable').find('.thead-search th:eq('+count+') select.column_filter').clone().removeClass('column_filter');
                        input.find('option[value=""]').remove();
                        if(id.length>0){
                            if(input.find('option[value="'+$(this).html()+'"]').length){
                                input.find('option[value="'+$(this).html()+'"]').attr('selected','selected');
                            }else{
                                input.find('option:contains("'+$(this).html()+'")').attr('selected','selected');
                            }
                        }else{
                            input.find('option:first').attr('selected','selected');
                        }
                        if(filter.is('.is_ajax')){
                            if(!has_ajax){
                                has_ajax = input;
                            }
                            input.addClass('is-ajax').on('change',function(){
                                ajax_change_update($(this));
                            });
                        }
                    }else{
                        input.attr('placeholder',filter.attr('placeholder').replace('Search','').trim());
                    }
                    input.addClass('form-control');
                    if(filter.is('.optional')){
                        input.addClass('optional');
                    }
                    input.attr('name',filter.attr('name'));
                    $(this).addClass('form-field');
                    $(this).html($('<div class="form-group" style="width:100%"></div>').append(input));
                }
                count = count + 1;
            });
            clone.addClass('form-container');
            clone.find('td:last').addClass('actions').html('<a href="javascript:void(0)" onclick="data_save(this)" class="on-editing save-row"><i class="fa fa-lg fa-save"></i></a><a href="javascript:void(0)" onclick="data_cancel(this)" class="on-editing cancel-row"><i class="fa fa-lg fa-times"></i></a>');
            if(id.length>0){
                clone.insertAfter(tr);
                tr.addClass('hidden');
            }else{
                clone.prependTo($('#datatable-editable').find('tbody'));
            }
            //$('.form-container:visible .form-field:not(.default) .form-control:not(.disabled,:disabled)').first().focus();
            if(has_ajax){
                ajax_change_update(has_ajax,true);
            }
        }
    }
    function ajax_change_update(obj,reset){
        var id = "";
        var post_data = {};
        post_data['method'] = 'change_update';
        post_data['name'] = $(obj).attr('name');
        try{
            post_data['value'] = $(obj).val();    
        }catch(e){}
        if($(obj).closest('.form-container[data-id]').length){
            id = $(obj).closest('.form-container[data-id]').attr('data-id');
        }
        post_data['id'] = id;
        if(typeof reset !== 'undefined'){
            post_data['reset'] = id;
        }
        post_data['pre_data'] = {};
        var post_name = $(obj).attr('name');
        $(obj).closest('.form-container:visible').find('[name].is-ajax').each(function(){
            if(post_name==$(this).attr('name') || post_name===false){
                post_name = false;
                return;
            }
            post_data['pre_data'][$(this).attr('name')] = $(this).val();
        });
        $.post('<?php echo $this->cpage->template_data['view_ajax_url']; ?>',post_data,function(data){
            if(data.status=="1"){
                if(typeof data.data === "object"){
                    for(var i in data.data){
                        $(obj).closest('.form-container').find('[name="'+data.data[i].name+'"].form-control').each(function(){
                            if(typeof data.data[i].option_text === 'object' && $(this).is('select')){
                                $(this).html('');
                                for(var j in data.data[i].option_text){
                                    var t = $('<option value="'+j+'" display="'+data.data[i].option_text[j]+'">'+data.data[i].option_text[j]+'</option>');
                                    $(this).append(t);
                                }
                            }
                            if(typeof data.data[i].value === 'undefined'){
                                data.data[i].value = "";
                            }
                            $(this).val(data.data[i].value);
                        });
                    }
                }
            }
            if(typeof data.message === 'string' && data.message.length>0){
                if(typeof data.status=='1'){
                    show_notification(data.message,'Notification','error');
                }else{
                    show_notification(data.message,'Notification');
                }
            }
            if(typeof data.func === 'function'){data.func(obj);}else if(typeof data.func === 'string' && data.func.indexOf("function(")==0){eval('('+data.func+')')();}
        }, 'json')
        .error(function(){
            show_notification('Submission to server error!','Notification','error');
        })
        .always(function(){
            
        });
    }
    function data_save(obj){
        if($('#datatable-editable').parent().find('#datatable-editable_processing').is(':visible')){
            return false;
        }
        if($('.form-container:visible .form-field:not(.default) .form-control:not(.disabled,:disabled)').length==0){
            data_cancel(obj);
            return false;
        }
        obj = $('.form-container:visible .form-field:not(.default) .form-control:not(.disabled,:disabled)').first();
    
        var id = "";
        var post_data = {};
        var value_list = {};
        if(!data_validate(obj)){
            show_notification('Please key in all the required fields!','Notification','error');
            return false;
        }
        show_processing(obj);
        if($(obj).closest('.form-container:visible[data-id]').length){
            id = $(obj).closest('.form-container:visible[data-id]').attr('data-id');
            $('#datatable-editable').data('selected_data_id',$('#datatable-editable').parent().scrollTop());
        }
        if($(obj).closest('.modal').length){
            $(obj).closest('.form-container').addClass('form-loading');
            $(obj).closest('.modal').find('.form-field:not(.default) .form-control:not(.disabled,:disabled,[type="file"])').each(function(){
                value_list[$(this).attr('name')] = $(this).val();
            });
            post_data['method'] = 'custom_form_save';
        }else{
            var obj = $('#datatable-editable').find('tr.tr-edit,tr.tr-add').first();
            var count = 0;
            $(obj).closest('tr').find('.form-field:not(.default) .form-control:not(.disabled,:disabled)').each(function(){
                if($(this).attr('name')){
                    value_list[$(this).attr('name')] = $(this).val();
                }else{
                    value_list[count] = $(this).val();
                }
                count++;
            });
            post_data['method'] = 'save';
        }
        
        post_data['id'] = id;
        post_data['value'] = value_list;
        
        var list = $('#datatable-editable').find('tr[data-id].selected').map(function(a,b){return $(this).attr('data-id');}).get();
        if(list.length){
            post_data['selection'] = list;
        }
        
        $.post('<?php echo $this->cpage->template_data['view_ajax_url']; ?>',post_data,function(data){
            if(data.status=="1"){
                show_notification('Data save successfuly.','Notification','success');
            }
            if(typeof data.message === 'string' && data.message.length>0){
                if(typeof data.status=='1'){
                    show_notification(data.message,'Notification','error');
                }else{
                    show_notification(data.message,'Notification');
                }
            }
            if(typeof data.func === 'function'){data.func(obj);}else if(typeof data.func === 'string' && data.func.indexOf("function(")==0){eval('('+data.func+')')();}
        }, 'json')
        .error(function(){
            show_notification('Submission to server error!','Notification','error');
        })
        .always(function(data){
            if($(obj).closest('.modal').length){
                $(obj).closest('.modal').modal('hide');
            }else{
                show_processing(obj,false);
                if($(obj).closest('tr.tr-add').length){
                    //$('#datatable-editable').DataTable().ajax.reload(function(){data_edit()},false);
                    return false;
                }else if($(obj).closest('tr.tr-edit').length){
                    if(data.status=="1"){
                        $(obj).closest('tr.tr-edit').each(function(){
                            var count = 0;
                            $(this).find('td').each(function(){
                                if($(this).is('.form-field')){
                                    var value = "";
                                    var value2 = "";
                                    if(typeof data.return_data !== 'undefined' && data.return_data[$(this).find('input,select').attr('name')].length){
                                        value2 = data.return_data[$(this).find('input,select').attr('name')];
                                    } 
                                    if($(this).find('input').length){
                                        value = value2;
                                    }else if($(this).find('select').length){
                                        value = $(this).find('select option[value="'+value2+'"]').html();
                                    }
                                    $(obj).closest('tbody').find('tr[data-id="'+$(obj).closest('tr.tr-edit').attr('data-id')+'"].hidden td:eq('+count+')')
                                        .attr('data-search',value)
                                        .attr('data-order',value)
                                        .html(value);
                                }
                                count++;
                            });
                        });
                    }
                    $(obj).closest('tbody').find('tr[data-id="'+$(obj).closest('tr.tr-edit').attr('data-id')+'"].hidden').removeClass('hidden selected');
                    $('.DTFC_RightWrapper tr.tr-edit').removeClass('tr-edit');
                    $('.DTFC_RightWrapper tr[data-id="'+$(obj).closest('tr.tr-edit').attr('data-id')+'"]').removeClass('selected');
                    $('.DTFC_LeftWrapper tr[data-id="'+$(obj).closest('tr.tr-edit').attr('data-id')+'"]').removeClass('selected');
                    $(obj).closest('tr.tr-edit').remove();
                    return false;
                }
            }
            $('#datatable-editable').DataTable().ajax.reload(function(){
                if(typeof $('#datatable-editable').data("selected_data_id") !== 'undefined'){
                    $('#datatable-editable').parent().scrollTop($('#datatable-editable').data("selected_data_id"));
                }
            },false);
        });
    }
    function data_validate(obj){
        if('<?php echo (int)($this->cpage->template_data['is_required']); ?>'==='0'){
            return true;
        }
        if($(obj).closest('.form-container:visible').length){
            var error = 0;
            $(obj).closest('.form-container:visible').find('.form-field:not(.default) .form-control:not(.disabled,:disabled,:hidden,.optional)').each(function(){
                if($(this).parsley().validate()!==true){
                    error++;
                }
            });
            if(error===0){
                return true;
            }
        }
        return false;
    }
    function data_delete(){
        var obj = $('#datatable-editable tr[data-id] td:visible').first();
        var list = obj.closest('#datatable-editable').find('tr[data-id].selected').map(function(a,b){return $(this).attr('data-id');}).get();
        var confirm = false;
        if(list.length==0){
            confirm = window.confirm("Once delete the data cannot be rollback. Are you sure you want to delete all record(s)?");
            if(confirm){
                confirm = false;
                var temp = window.prompt("Please key in \"YES\" to delete all records.");
                if(temp=="YES"){
                    confirm = true;
                    list = "ALL";
                }else{
                    swal({title:"",type:"warning",text:"Wrong Passphrase!"});
                }
            }
        }else{
            confirm = window.confirm("Once delete the data cannot be rollback. Are you sure you want to delete "+list.length+" record(s)?");
        }
        if(confirm){
            show_processing(obj);
            var post_data = {};
            post_data['method'] = 'delete';
            post_data['selection'] = list;
            $.post('<?php echo $this->cpage->template_data['view_ajax_url']; ?>',post_data,function(data){
                if(data.status=="1"){
                    show_notification('Data delete successfuly.','Notification','success');
                }
                if(typeof data.message === 'string' && data.message.length>0){
                    if(typeof data.status=='1'){
                        show_notification(data.message,'Notification','error');
                    }else{
                        show_notification(data.message,'Notification');
                    }
                }
                if(typeof data.func === 'function'){data.func(obj);}else if(typeof data.func === 'string' && data.func.indexOf("function(")==0){eval('('+data.func+')')();}
            }, 'json')
            .error(function(){
                show_notification('Submission to server error!','Notification','error');
            })
            .always(function(){
                show_processing(obj,false);
                $('#datatable-editable').DataTable().ajax.reload(null,false);
            });
        }
        return false;
    }
    function data_cancel(obj){
        show_edit(obj,false);
        $('#custom_form_modal .form-container[data-id]').removeAttr('data-id');
        $('#custom_form_modal').modal('hide');
        $('#datatable-editable').find('tr.tr-edit,tr.tr-add').remove();
        $('tr.hidden[data-id]').removeClass('hidden');
        $('.DTFC_RightWrapper tr.tr-edit').removeClass('tr-edit tr-add');
    }
    function extra_btn(obj){
        var list = $('#datatable-editable').find('tr[data-id].selected').map(function(a,b){return $(this).attr('data-id');}).get();
        if($(obj).is('[custom_form]')){
            if($(obj).attr('require_select')=="require_select" && list.length==0){
                swal({title:"",type:"warning",text:"Please select record!"});
                return false;
            }
            return data_edit(obj,$(obj).attr('custom_form'),true);
        }
        var url = $(obj).attr('data-goto');
        if(window.confirm("Please click the button to continue the \""+$(obj).text()+"\".")){
            var post_data = {};
            post_data['selection'] = list;
            post(url, post_data, '_self', 'POST');
        }
    }
</script>

<script>
    //horizontal scroll by dragging
    var table;
    var clicked = false, clickX, clickY;
    var dataTableMouseMoveOverlay = $('<div class="dataTableMouseMoveOverlay" style="position:fixed;top:0;left:0;width:100%;height:100%;z-index:99999;display:none;cursor:move;"></div>');
    dataTableMouseMoveOverlay.on({
        'mousemove': function(e) {
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
        'mouseup': function() {
            clicked = false;
            $(this).hide();
        },
        'mouseout': function() {
            clicked = false;
            $(this).hide();
        }
    }).appendTo($('body'));
    jQuery(function ($) {
        $('#custom_form_modal').on('shown.bs.modal', function () {
            $('#custom_form_modal input:visible').not('.disabled,.hidden').first().focus();
        });

        $('#datatable-editable').each(function () {
            var filter_sorting = [[ 1, "asc" ]];
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
                                if(clicked && (Math.abs(clickX - e.pageX)>0 || Math.abs(clickY - e.pageY)>0)){
                                    $(this).off('mousemove');
                                    dataTableMouseMoveOverlay.show();
                                    
                                }
                            }
                        });
                    },
                    'mouseup': function() {
                        clicked = false;
                        dataTableMouseMoveOverlay.hide();
                        $(this).off('mousemove');
                    }
                });
                setTimeout(function(){
                    $('#datatable-editable_length').each(function(){
                        var clone = $(this).find('select[name="datatable-editable_length"]').clone(true);
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
                    <?php if($editable){ ?>
                    
                    <?php } ?>
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
                    <?php if($editable){ ?>
                    "rightColumns": 1,
                    <?php } ?>
                    "leftColumns": <?php echo $this->cpage->template_data['freezePane']; ?>
                },
                <?php } ?>
                "iDisplayLength": <?php echo $this->cpage->template_data['default_length']; ?>,
                "lengthMenu": [[50, 100, 200], [50, 100, 200]],
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
                            $(row).find('td:eq('+i+')').html('<a href="'+thead_search.find('th:eq('+i+')[data-goto]').attr('data-goto')+'?id='+$data_id+'">'+val+'</a>');
                        }else if(thead_search.find('th:eq('+i+')[custom-col]').length){
                            $(row).find('td:eq('+i+')').html('<a href="javascript:void(0)" onclick="data_edit(this,\''+thead_search.find('th:eq('+i+')[custom-col]').attr('custom-col')+'\',true)">'+val+'</a>');
                        }
                        $(row).find('td:eq('+i+')').attr('data-search',val).attr('data-order',val);
                        if(thead_search.find('th:eq('+i+') select.column_filter option[value="'+val+'"]').length){
                            $(row).find('td:eq('+i+')').html(thead_search.find('th:eq('+i+') select.column_filter option[value="'+val+'"]').text());
                        }
                        if(thead_search.find('th:eq('+i+').editable').length){
                            $(row).find('td:eq('+i+')').addClass('editable_td');
                        }
                    }
                    <?php if($editable){ ?>
                    $(row).find('td').last().addClass('actions').html('<div class="pull-right"><a href="javascript:void(0)" onclick="data_save(this)" class="on-editing save-row"><i class="fa fa-lg fa-save"></i></a><a href="javascript:void(0)" onclick="data_cancel(this)" class="on-editing cancel-row"><i class="fa fa-lg fa-times"></i></a><a href="javascript:void(0)" onclick="data_edit(this)" class="on-default edit-row"><i class="fa fa-lg fa-pencil"></i></a></div>');
                    <?php if($this->cpage->template_data['delete_btn'] || sizeof($this->cpage->template_data['extra_btn'])>0){ ?>
                        $(row).find('td').not('.actions').on('click',function(){ $('tr[data-id="'+$(this).closest('tr[data-id]').attr('data-id')+'"]').toggleClass('selected'); });
                        //$(row).find('td:first').html('<input type="checkbox" class="select_checkbox" />');
                    <?php } ?>
                    $(row).find('td.editable_td').not('.actions').on('dblclick',function(){data_edit($(this).closest('tr').find('td.actions .edit-row'));});
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
                <?php if($editable){ ?>
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
        
        
    });
</script>
