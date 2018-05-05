<link href="<?php echo base_url('/assets/default'); ?>/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

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
                            <div class="textbox" style="background:#eee;padding:6px 12px;"></div>
                            <input type="hidden" class="form-control">
                        </div>
                    </div>
                    <div class="col-xs-12 form-field default hidden-default hidden">
                        <input type="hidden" class="form-control">
                    </div>
                    <div class="col-xs-12 col-sm-6 form-field default date-default hidden">
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
                    <div class="col-xs-12 form-field default textarea-default hidden">
                        <div class="form-group">
                            <label class="control-label">Fieldname</label>
                            <textarea class="form-control" placeholder="" required></textarea>
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
                if(!$(e.target).is('textarea') && e.keyCode==13){
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
                                c.find('.textbox').html(data.data[i].value);
                            }else if(typeof data.data[i].hidden === 'string'){
                                var c = container.find('.form-field.hidden-default.default.hidden').clone();
                                c.removeClass('default hidden').appendTo(container);
                                c.find('input').attr('name',data.data[i].id).val(data.data[i].value);
                            }else if(typeof data.data[i].is_date === 'string'){
                                var c = container.find('.form-field.date-default.default.hidden').clone();
                                c.removeClass('default hidden').appendTo(container);
                                c.find('label').html(data.data[i].name);
                                c.find('input').attr('name',data.data[i].id).val(data.data[i].value);
                                set_date(c.find('input'),data.data[i].is_date_highlight||false);
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
                                if(data.data[i].id.indexOf('|range_date')>0){
                                    c.find('select').addClass('range_date').change(function(){
                                        var t = $(this).attr('name');
                                        var t2 = t.split("|");
                                        $('#custom_form_modal input[name="'+t2[0]+'|from_date"]').val("").datepicker("update");
                                        $('#custom_form_modal input[name="'+t2[0]+'|to_date"]').val("").datepicker("update");
                                    });
                                }
                            }else if(typeof data.data[i].is_textarea === 'string'){
                                var c = container.find('.form-field.textarea-default.default.hidden').clone();
                                c.removeClass('default hidden').appendTo(container);
                                c.find('label').html(data.data[i].name);
                                c.find('textarea').attr('name',data.data[i].id).val(data.data[i].value);
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
                        //$('.form-container:visible .form-field:not(.default) .form-control:not(.disabled,:disabled,[class*=date])').first().focus();
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
                        set_date(input,filter.is('.is_date_highlight'));
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
            //$('.form-container:visible .form-field:not(.default) .form-control:not(.disabled,:disabled,[class*=date])').first().focus();
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
                                    if($(this).find('input').length){
                                        value = $(this).find('input').val();
                                    }else if($(this).find('select').length){
                                        value = $(this).find('select option:selected').html();
                                    }
                                    var order_value = value;
                                    var temp = /^([0-9]{2})[^0-9]([0-9]{2})[^0-9]([0-9]{4})$/gi.exec(value);
                                    if(temp){
                                        order_value = new Date(temp[3]+"-"+temp[2]+"-"+temp[1]).getTime()/1000;
                                        console.log(order_value);
                                    }
                                    $(obj).closest('tbody').find('tr[data-id="'+$(obj).closest('tr.tr-edit').attr('data-id')+'"].hidden td:eq('+count+')')
                                        .attr('data-search',value)
                                        .attr('data-order',order_value)
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
            if($('#datatable-editable').length){
                $('#datatable-editable').DataTable().ajax.reload(function(){
                    if(typeof $('#datatable-editable').data("selected_data_id") !== 'undefined'){
                        $('#datatable-editable').parent().scrollTop($('#datatable-editable').data("selected_data_id"));
                    }
                },false);
            }
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
        if($(obj).attr('require_select')=="require_select" && list.length==0){
            swal({title:"",type:"warning",text:"Please select record!"});
            return false;
        }
        if($(obj).is('[custom_form]')){
            return data_edit(obj,$(obj).attr('custom_form'),true);
        }
        var url = $(obj).attr('data-goto');
        if(window.confirm("Please click the button to continue the \""+$(obj).text()+"\".")){
            var post_data = {};
            post_data['selection'] = list;
            if(url.indexOf("/ajax/")>=0){
                console.log(post_data);
                $.post(url, post_data ,function(data){
                    if(typeof data.message === 'string' && data.message.length>0){
                        show_notification(data.message,'Notification');
                    }
                    if(typeof data.func === 'function'){data.func(obj);}else if(typeof data.func === 'string' && data.func.indexOf("function(")==0){eval('('+data.func+')')();}
                }, 'json')
                .error(function(){
                    show_notification('Submission to server error!','Notification','error');
                })
                .always(function(){
                    show_processing(obj,false);
                    $('#datatable-editable').DataTable().ajax.reload(function(){
                        if(typeof $('#datatable-editable').data("selected_data_id") !== 'undefined'){
                            $('#datatable-editable').parent().scrollTop($('#datatable-editable').data("selected_data_id"));
                        }
                    },false);
                });
            }else{
                post(url, post_data, '_self', 'POST');
            }
        }
    }
    function header_save(){
        var list = {};
        $('#custom_form_modal2 .sortable.freeze_list li[data_id]').map(function(a,b){
            list[$(b).attr('data_id')] = '1';
        });
        $('#custom_form_modal2 .sortable.normal_list li[data_id]').map(function(a,b){
            list[$(b).attr('data_id')] = '0';
        });
        
        var post_data = {};
        post_data['method'] = 'custom_form_save';
        post_data['value'] = {};
        post_data['value']['type'] = 'header_change';
        post_data['value']['data'] = list;
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
            location.reload();
        }, 'json')
        .error(function(){
            show_notification('Submission to server error!','Notification','error');
        })
        .always(function(data){
            $('.modal').modal('hide');
        });
    }
    function header_reset(){
        var post_data = {};
        post_data['method'] = 'custom_form_save';
        post_data['value'] = {};
        post_data['value']['type'] = 'header_reset';
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
            location.reload();
        }, 'json')
        .error(function(){
            show_notification('Submission to server error!','Notification','error');
        })
        .always(function(data){
            $('.modal').modal('hide');
        });
    }
</script>

<script>
    jQuery(function ($) {
        $('#custom_form_modal').on('shown.bs.modal', function () {
            $('#custom_form_modal input:visible').not('.disabled,.hidden').first().focus();
        });
    });
</script>