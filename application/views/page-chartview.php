<?php
$dashboard_data = $this->cpage->template_data['view_contents'];
?>
<link href="<?php echo base_url('/assets/default'); ?>/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="<?php echo base_url('/assets/default'); ?>/plugins/morris/morris.css">
<script src="<?php echo base_url('/assets/default'); ?>/plugins/morris/morris.min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/plugins/raphael/raphael-min.js"></script>
<script src="<?php echo base_url('/assets/default'); ?>/pages/morris.init.js"></script>
<script>
    var CSS_COLOR_NAMES = ["#ffbf00","#bf5340","#40bf44","#b3bf40","#e2761d","#6040bf","#bf4088","#8359a6","#0033ff","#ff0026","DarkGreen","DarkKhaki","DarkMagenta","DarkOliveGreen","Darkorange","DarkOrchid","DarkRed","DarkSalmon","DarkSeaGreen","DarkSlateBlue","DarkSlateGray","DarkSlateGrey","DarkTurquoise","DarkViolet","DeepPink","DeepSkyBlue","DimGray","DimGrey","DodgerBlue","FireBrick","FloralWhite","ForestGreen","Fuchsia","Gainsboro","GhostWhite","Gold","GoldenRod","Gray","Grey","Green","GreenYellow","HoneyDew","HotPink","IndianRed","Indigo","Ivory","Khaki","Lavender","LavenderBlush","LawnGreen","LemonChiffon","LightBlue","LightCoral","LightCyan","LightGoldenRodYellow","LightGray","LightGrey","LightGreen","LightPink","LightSalmon","LightSeaGreen","LightSkyBlue","LightSlateGray","LightSlateGrey","LightSteelBlue","LightYellow","Lime","LimeGreen","Linen","Magenta","Maroon","MediumAquaMarine","MediumBlue","MediumOrchid","MediumPurple","MediumSeaGreen","MediumSlateBlue","MediumSpringGreen","MediumTurquoise","MediumVioletRed","MidnightBlue","MintCream","MistyRose","Moccasin","NavajoWhite","Navy","OldLace","Olive","OliveDrab","Orange","OrangeRed","Orchid","PaleGoldenRod","PaleGreen","PaleTurquoise","PaleVioletRed","PapayaWhip","PeachPuff","Peru","Pink","Plum","PowderBlue","Purple","Red","RosyBrown","RoyalBlue","SaddleBrown","Salmon","SandyBrown","SeaGreen","SeaShell","Sienna","Silver","SkyBlue","SlateBlue","SlateGray","SlateGrey","Snow","SpringGreen","SteelBlue","Tan","Teal","Thistle","Tomato","Turquoise","Violet","Wheat","White","WhiteSmoke","Yellow","YellowGreen"];
</script>

<div class="panel">
    <div class="panel-body">
        <div class="row">
            <div class="col-xs-6">
                <?php if($this->cpage->template_data['extra_filter']){ ?>
                <span class="">
                    <button class="btn btn-success waves-effect waves-light" onclick="data_edit(this,'extra_filter',true)">Extra Filter <i class="fa fa-lg fa-search"></i></button>
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
                    <div class="row">
                        <div class="col-xs-12 col-sm-9">
                            <div id="morris-line-example" style="height: 300px;"></div>
                        </div>
                        <div class="col-xs-12 col-sm-3">
                            <div class="">
                                <div><h4>Total of Each<h4></div>
                                <ul class="list-inline chart-detail-list">
                                    <?php foreach($dashboard_data['total2'] as $k => $v){ ?>
                                    <li style="display:block;"><?php echo $dashboard_data['header'][$k]; ?> <span class="pull-right"><?php echo $v; ?></span></li>
                                    <?php } ?>
                                    <li style="display:block;border-top:1px solid #ccc;border-bottom:1px solid #ccc;">Total <span class="pull-right"><?php echo $dashboard_data['total']; ?></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <script>
                    $(function(){
                        //create line chart
                        var $data  = [
                            <?php 
                                $header = "['".implode("','",array_keys($dashboard_data['header']))."']";
                                $header2 = "['".implode("','",array_values($dashboard_data['header']))."']";
                                foreach($dashboard_data['data'] as $key => $value){
                                    $temp = "{y:'".$key."'";
                                    foreach($value as $key2 => $value2){
                                        $temp .= ", ".$key2.":".$value2;
                                    }
                                    $temp .= "},";
                                    echo $temp;
                                }
                            ?>
                        ];
                        $.MorrisCharts.createLineChart('morris-line-example', $data, 'y', <?php echo $header; ?>, <?php echo $header2; ?>,['0.1'],['#ffffff'],['#999999'], CSS_COLOR_NAMES);
                    });
                    </script>
                    
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
        
    }
    function data_edit(obj,type,is_custom){        
        is_custom = true;
        id = "";
        if((is_custom && typeof type === 'string')){
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
    function data_save(obj){
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
            $(obj).closest('.form-container').addClass('form-loading');
            $(obj).closest('.modal').find('.form-field:not(.default) .form-control:not(.disabled,:disabled,[type="file"])').each(function(){
                value_list[$(this).attr('name')] = $(this).val();
            });
            post_data['method'] = 'custom_form_save';
        }
        
        post_data['id'] = id;
        post_data['value'] = value_list;
        
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
            }
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
