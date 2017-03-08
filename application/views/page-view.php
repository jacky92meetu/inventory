<!-- DataTables -->
<?php /*
<link href="<?php echo base_url('/assets/default'); ?>/plugins/datatables/jquery.dataTables.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/plugins/datatables/buttons.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/plugins/datatables/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/plugins/datatables/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/plugins/datatables/scroller.bootstrap.min.css" rel="stylesheet" type="text/css" />
*/ ?>
<link href="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/media/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/FixedColumns/css/fixedColumns.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/FixedHeader/css/fixedHeader.bootstrap.min.css" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/Responsive/css/responsive.bootstrap.min.css" rel="stylesheet" type="text/css" />
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
                    <button id="addToTable" class="btn btn-primary waves-effect waves-light" onclick="data_edit(null,'new',<?php echo $is_custom; ?>)">Add <i class="fa fa-lg fa-plus"></i></button>
                </span>
                <?php } ?>
                <?php if($this->cpage->template_data['delete_btn']){ ?>
                <span class="">
                    <button id="deleteFromTable" class="btn btn-danger waves-effect waves-light" onclick="data_delete()">Delete selected <i class="fa fa-lg fa-trash-o"></i></button>
                </span>
                <?php } ?>
            </div>
            <div class="col-xs-6">
                <?php if(sizeof($this->cpage->template_data['extra_btn'])>0){ 
                    foreach($this->cpage->template_data['extra_btn'] as $temp){ 
                        $class = "btn-default";
                        if(isset($temp['class'])){
                            $class = $temp['class'];
                        }
                ?>
                <span class=" pull-right">
                    <button class="btn <?php echo $class; ?> waves-effect waves-light" onclick="extra_btn(this)" data-goto="<?php echo $temp['url']; ?>"><?php echo $temp['name']; ?></button>
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
                            <tr>
                                <th width="10">No.</th>
                                <?php $count=1; foreach ($this->cpage->template_data['view_header'] as $header) { ?>
                                    <th><?php echo $header['name']; ?></th>
                                <?php $count+=1;} ?>
                                <th width="10">Actions</th>
                            </tr>
                        </thead>
                        <tfoot class="thead-search">
                            <tr>
                                <th width="10"></th>
                                <?php $count=1;
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
                                        <option value=""><?php echo "Search ".$header['name']; ?></option>
                                        <?php foreach($header['option_text'] as $key => $value){ ?>
                                        <option value="<?php echo $key; ?>" <?php echo (($search_get==$key)?"SELECTED":""); ?>><?php echo $value; ?></option>
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
                                <th width="10">
                                    <button class="resetFilter btn btn-warning waves-effect waves-light"><i class="fa fa-lg fa-refresh"></i></button>
                                </th>
                            </tr>
                        </tfoot>
                        <tbody>
                            <?php 
                            foreach ($this->cpage->template_data['view_contents'] as $data) { 
                                if(!isset($data['id'])){
                                    foreach($data as $t){
                                        $data['id'] = $t;
                                        break;
                                    }
                                    unset($t);
                                }
                            ?>
                                <tr data-id="<?php echo $data['id']; ?>">
                                    <td></td>
                                    <?php 
                                        $count=0;
                                        foreach ($data as $col) {
                                            $data_search = $col;
                                            if(isset($this->cpage->template_data['view_header'][$count]) && isset($this->cpage->template_data['view_header'][$count]['option_text']) && !empty($temp = $this->cpage->template_data['view_header'][$count]['option_text'][$col])){
                                                $col = $temp;
                                                $data_search = $temp;
                                            }else if(isset($this->cpage->template_data['view_header'][$count]) && isset($this->cpage->template_data['view_header'][$count]['goto'])){
                                                $col = '<a href="<'.$this->cpage->template_data['view_header'][$count]['goto'].'?id='.(!empty($this->cpage->template_data['view_header'][$count]['goto_id'])?$data[$this->cpage->template_data['view_header'][$count]['goto_id']]:$data['id']).'">'.$col.'</a>';
                                            }
                                    ?>
                                    <td data-search="<?php echo $data_search; ?>" data-order="<?php echo $data_search; ?>"><?php echo $col; ?></td>
                                    <?php $count+=1; } ?>
                                    <td class="actions">
                                        <a href="javascript:void(0)" onclick="data_edit(this)" class="on-default edit-row"><i class="fa fa-lg fa-pencil"></i></a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
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
                    <div class="col-md-12 form-field default text-default hidden">
                        <div class="form-group">
                            <label class="control-label">Fieldname</label>
                            <input type="text" class="form-control" placeholder="" required>
                        </div>
                    </div>
                    <div class="col-md-12 form-field default select-default hidden">
                        <div class="form-group">
                            <label class="control-label">Fieldname</label>
                            <select class="form-control"></select>
                        </div>
                    </div>
                    <div class="col-md-12 form-field default readonly-default hidden">
                        <div class="form-group">
                            <label class="control-label">Fieldname</label>
                            <input type="text" class="form-control disabled" DISABLED>
                            <input type="hidden" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12 form-field default hidden-default hidden">
                        <input type="hidden" class="form-control">
                    </div>
                    <div class="col-md-12 form-field default date-default hidden">
                        <div class="form-group">
                            <label class="control-label">Fieldname</label>
                            <div class="input-group">
                                <input type="text" class="form-control datepicker-autoclose" placeholder="dd/mm/yyyy">
                                <span class="input-group-addon bg-primary b-0 text-white"><i class="ion-calendar"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary waves-effect waves-light" onclick="data_save(this)">Save</button>
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
<script src="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/FixedHeader/js/dataTables.fixedHeader.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/js/DataTables-1.10.13/extensions/Responsive/js/dataTables.responsive.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/js/dataTables.select.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/js/action.js"></script>

<script>
    function show_processing(obj,show){
        if(typeof show === 'undefined'||show===true){
            $('.dataTable').parent().find('#datatable-editable_processing').show();
        }else if(show===false){
            $('.dataTable').parent().find('#datatable-editable_processing').hide();
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
        
        if((is_custom && typeof type === 'string') || $('.dataTable').is('.custom_form')){
            show_processing(obj);
            var post_data = {};
            post_data['method'] = 'custom_form';
            post_data['type'] = type;
            post_data['id'] = id;
            try{
                $.post('<?php echo $this->cpage->template_data['view_ajax_url']; ?>',post_data,function(data){
                    if(typeof data.data === 'object'){
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
                            }else if(typeof data.data[i].option_text === 'object'){
                                var c = container.find('.form-field.select-default.default.hidden').clone();
                                c.removeClass('default hidden').appendTo(container);
                                c.find('label').html(data.data[i].name);
                                c.find('select').attr('name',data.data[i].id);
                                for(var j in data.data[i].option_text){
                                    var t = $('<option value="'+j+'">'+data.data[i].option_text[j]+'</option>');
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
                        }
                        if(id.length>0 && id>0){
                            $('#custom_form_modal').find('.form-container').attr('data-id',id);
                        }
                        $('#custom_form_modal').modal('show');
                        $('.form-container:visible .form-field:not(.default) .form-control:not(.disabled,:disabled)').first().focus();
                        if(has_ajax){
                            ajax_change_update(has_ajax,true);
                        }
                        set_calculation(container);
                    }else if(typeof data.message === 'string' && data.message.length>0){
                        show_notification(data.message,'Notification','error');
                    }
                    if(typeof data.func === 'function'){data.func(obj);}
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
            if($(obj).closest('tr').length){
                var tr = $(obj).closest('tr');
                var clone = tr.clone();
            }else{
                var size = $('.dataTable').find('.thead-search th').length;
                var clone = $('<tr></tr>');
                for(var i = 0; i<size; i++){
                    $('<td></td>').appendTo(clone);
                }
            }
            if(id>0){
                clone.addClass('tr-edit');
            }else{
                clone.addClass('tr-add');
            }
            var count = 0;
            var has_ajax = false;
            clone.find('td').each(function(){
                if($('.dataTable').find('.thead-search th:eq('+count+').editable .column_filter').length){
                    var filter = $('.dataTable').find('.thead-search th:eq('+count+') .column_filter');
                    var input = $('<input value="" required />');
                    if(id>0){
                        input.val($(this).attr('data-search'));
                    }
                    if(filter.is('.is_date')){
                        input.addClass('.datepicker-autoclose');
                        set_date(input);
                    }else if(filter.is('select')){
                        var input = $('.dataTable').find('.thead-search th:eq('+count+') select.column_filter').clone().removeClass('column_filter');
                        input.find('option[value=""]').remove();
                        if(id>0){
                            input.find('option:contains("'+$(this).html()+'")').attr('selected','selected');
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
            if(id>0){
                clone.insertAfter(tr);
                tr.addClass('hidden');
            }else{
                clone.prependTo($('.dataTable').find('tbody'));
            }
            $('.form-container:visible .form-field:not(.default) .form-control:not(.disabled,:disabled)').first().focus();
            if(has_ajax){
                ajax_change_update(has_ajax,true);
            }
            set_calculation(clone);
        }
    }
    function set_calculation(container){
    
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
                        $(obj).closest('.form-container:visible').find('[name="'+data.data[i].name+'"].form-control').each(function(){
                            if(typeof data.data[i].option_text === 'object' && $(this).is('select')){
                                $(this).html('');
                                for(var j in data.data[i].option_text){
                                    var t = $('<option value="'+j+'">'+data.data[i].option_text[j]+'</option>');
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
            }else if(data.message.length>0){
                show_notification(data.message,'Notification','error');
            }
            if(typeof data.func === 'function'){data.func(obj);}
        }, 'json')
        .error(function(){
            show_notification('Submission to server error!','Notification','error');
        })
        .always(function(){
            
        });
    }
    function data_save(obj){
        if($('.dataTable').parent().find('#datatable-editable_processing').is(':visible')){
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
        }
        if($(obj).closest('.modal').length){
            $(obj).closest('.modal').find('.form-field:not(.default) .form-control:not(.disabled,:disabled)').each(function(){
                value_list[$(this).attr('name')] = $(this).val();
            });
            post_data['method'] = 'custom_form_save';
        }else{
            var obj = $('.dataTable').find('tr.tr-edit,tr.tr-add').first();
            var count = 0;
            $(obj).closest('tr').find('.form-field:not(.default) .form-control:not(.disabled,:disabled)').each(function(){
                value_list[count] = $(this).val();
                count++;
            });
            post_data['method'] = 'save';
        }
        
        post_data['id'] = id;
        post_data['value'] = value_list;
        $.post('<?php echo $this->cpage->template_data['view_ajax_url']; ?>',post_data,function(data){
            if(data.status=="1"){
                show_notification('Data save successfuly.','Notification','success');
            }else if(data.message.length>0){
                show_notification(data.message,'Notification','error');
            }
            if(typeof data.func === 'function'){data.func(obj);}
        }, 'json')
        .error(function(){
            show_notification('Submission to server error!','Notification','error');
        })
        .always(function(){
            if($(obj).closest('.modal').length){
                $(obj).closest('.modal').modal('hide');
            }else{
                show_processing(obj,false);
                if($(obj).closest('tr.tr-add').length){
                    $('.dataTable').DataTable().ajax.reload(function(){data_edit()},false);
                    return false;
                }
            }
            $('.dataTable').DataTable().ajax.reload(null,false);
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
        var obj = $('.dataTable tr[data-id] td:visible').first();
        var list = obj.closest('.dataTable').find('tr[data-id].selected').map(function(a,b){return $(this).attr('data-id');}).get();
        if(list.length==0){
            alert("Please select data to delete.");
            return false;
        }
        if(window.confirm("Once delete the data cannot be rollback. Are you sure you want to delete "+list.length+" record(s)?")){
            show_processing(obj);
            var post_data = {};
            post_data['method'] = 'delete';
            post_data['selection'] = list;
            $.post('<?php echo $this->cpage->template_data['view_ajax_url']; ?>',post_data,function(data){
                if(data.status=="1"){
                    show_notification('Data delete successfuly.','Notification','success');
                }else if(data.message.length>0){
                    show_notification(data.message,'Notification','error');
                }
                if(typeof data.func === 'function'){data.func(obj);}
            }, 'json')
            .error(function(){
                show_notification('Submission to server error!','Notification','error');
            })
            .always(function(){
                show_processing(obj,false);
                $('.dataTable').DataTable().ajax.reload(null,false);
            });
        }
    }
    function data_cancel(obj){
        show_edit(obj,false);
        $('#custom_form_modal .form-container[data-id]').removeAttr('data-id');
        $('#custom_form_modal').modal('hide');
        $('.dataTable').find('tr.tr-edit,tr.tr-add').each(function(){
            $('.dataTable').find('tr.hidden[data-id="'+$(this).attr('data-id')+'"]').removeClass('hidden');
            $(this).remove();
        });
    }
    function extra_btn(obj){
        var url = $(obj).attr('data-goto');
        var list = $('.dataTable').find('tr[data-id].selected').map(function(a,b){return $(this).attr('data-id');}).get();
        if(window.confirm("Please click the button to continue the \""+$(obj).text()+"\".")){
            var post_data = {};
            post_data['selection'] = list;
            post(url, post_data, '_self', 'POST');
        }
    }
</script>

<script>
    jQuery(function ($) {
        $('#custom_form_modal').on('shown.bs.modal', function () {
            $('#custom_form_modal input:visible').not('.disabled,.hidden').first().focus();
        });

        $('.dataTable').each(function () {
            var filter_sorting = [[ 1, "asc" ]];
            var obj = $(this);

            if(obj.find('.thead-search th .column_filter[filter-sorting]').length){
                var t = obj.find('.thead-search th .column_filter[filter-sorting]');
                filter_sorting = [[ t.attr('data-column'), t.attr('filter-sorting') ]];
            }

            var table = obj.on( 'init.dt', function () {
                    
                } ).DataTable({
                //"stateSave": true,
                "fixedHeader": {"header":true,"footer":true},
                //"responsive": true,
                //"select": true,
                <?php if(false && $this->cpage->template_data['freezePane']>0){ ?>
                "scrollX": true,
                "scrollCollapse": true,
                "fixedColumns": {
                    "rightColumns": 1,
                    "leftColumns": <?php echo $this->cpage->template_data['freezePane']; ?>
                },
                <?php } ?>
                "iDisplayLength": <?php echo $this->cpage->template_data['default_length']; ?>,
                "order": filter_sorting,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url":"<?php echo $this->cpage->template_data['view_ajax_url']; ?>",
                    "type": "POST",
                    "data": {'method':'read'}
                },
                "createdRow": function(row,data,dataIndex){
                    $(row).attr('data-id',data[1]);
                    for(var i=0; i<data.length; i++){
                        var val = data[i];
                        if(obj.find('.thead-search th:eq('+i+')[data-goto]').length){
                            $(row).find('td:eq('+i+')').html('<a href="'+obj.find('.thead-search th:eq('+i+')[data-goto]').attr('data-goto')+'?id='+data[1]+'">'+val+'</a>');
                        }else if(obj.find('.thead-search th:eq('+i+')[custom-col]').length){
                            $(row).find('td:eq('+i+')').html('<a href="javascript:void(0)" onclick="data_edit(this,\''+obj.find('.thead-search th:eq('+i+')[custom-col]').attr('custom-col')+'\',true)">'+val+'</a>');
                        }
                        $(row).find('td:eq('+i+')').attr('data-search',val).attr('data-order',val);
                        if(obj.find('.thead-search th:eq('+i+') select.column_filter option[value="'+val+'"]').length){
                            $(row).find('td:eq('+i+')').html(obj.find('.thead-search th:eq('+i+') select.column_filter option[value="'+val+'"]').text());
                        }
                    }
                    
                    <?php if($this->cpage->template_data['delete_btn'] || sizeof($this->cpage->template_data['extra_btn'])>0){ ?>
                            $(row).find('td').not(':last').on('click',function(){$(this).closest('tr').toggleClass('selected');});
                    <?php } ?>
                    
                    $(row).find('td').not(':last').on('dblclick',function(){data_edit($(this).closest('tr').find('td.actions .edit-row'));});
                    $(row).find('td').last().addClass('actions').html('<a href="javascript:void(0)" onclick="data_edit(this)" class="on-default edit-row"><i class="fa fa-lg fa-pencil"></i></a>');
                },
                "columnDefs": [
                    {
                        "targets": [ 0 ],
                        "orderable": false
                    },
                <?php $count=1; foreach ($this->cpage->template_data['view_header'] as $header) { ?>
                    {
                        "targets": [ <?php echo $count; ?> ],
                        "visible": <?php echo ((isset($header['hide']))?"false":"true"); ?>,
                        "searchable": <?php echo ((isset($header['nosearch']))?"false":"true"); ?>,
                        "orderable": <?php echo ((isset($header['noorder']))?"false":"true"); ?>
                    },
                <?php $count+=1;} ?>
                    {
                        "targets": [ -1 ],
                        "searchable": false,
                        "orderable": false
                    }
                ],
            });
            
            $('.dataTable').find('select.column_filter').each(function(){
                if($(this).val().length && $(this).val()!=='0'){
                    table.column($(this).attr('data-column')).search($(this).val(),true,true).draw();
                }else if(table.column($(this).attr('data-column')).search().length){
                    $(this).val(table.column($(this).attr('data-column')).search());
                }
                $(this).on('change', function(){
                    table.column($(this).attr('data-column')).search($(this).val(),true,true).draw();
                });
            });
            $('.dataTable').find('input.column_filter').each(function(){
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
                        if(active>5){
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
            
            $('.dataTable').find('.resetFilter').on('click',function(){
                $('.dataTable').find('input.column_filter').val("");
                $('.dataTable').find('select.column_filter').val("");
                table.search( '' ).columns().search( '' ).draw();
                table.rows().deselect();
            });
            table.on( 'order.dt search.dt', function () {
                table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    //cell.innerHTML = i+1;
                } );
            } ).draw();
        });
    });
</script>
