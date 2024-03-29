/*enhancement*/
if(typeof $.fn.dataTableExt === 'object'){
    $.fn.dataTableExt.oApi.fnStandingRedraw = function(oSettings) {
        //redraw to account for filtering and sorting
        // concept here is that (for client side) there is a row got inserted at the end (for an add)
        // or when a record was modified it could be in the middle of the table
        // that is probably not supposed to be there - due to filtering / sorting
        // so we need to re process filtering and sorting
        // BUT - if it is server side - then this should be handled by the server - so skip this step
        if(oSettings.oFeatures.bServerSide === false){
            var before = oSettings._iDisplayStart;
            oSettings.oApi._fnReDraw(oSettings);
            //iDisplayStart has been reset to zero - so lets change it back
            oSettings._iDisplayStart = before;
            oSettings.oApi._fnCalculateEnd(oSettings);
        }

        //draw the 'current' page
        oSettings.oApi._fnDraw(oSettings);
    };
}

/*Notification*/
function show_notification(message,title,type){
    if(title==undefined){
        title = 'Notifcation';
    }
    if(type==undefined || type=='info'){
        type = 'white';
    }
    $.Notification.notify(type,'top right', title, message);
    if(message.toLowerCase().indexOf('please login again')>=0){
        setTimeout(redirect(site_url),3000);
    }
}

function set_date(obj,todayHighlight){
    if(typeof obj === 'object' && $(obj).length){
        $(obj).datepicker({
            autoclose: true,
            todayHighlight: todayHighlight||false,
            format: "dd/mm/yyyy",
            todayBtn: "linked",
            toggleActive: true,
            clearBtn: true
        });
    }else{
        $('.datepicker-autoclose:visible').datepicker({
            autoclose: true,
            todayHighlight: todayHighlight||false,
            format: "dd/mm/yyyy",
            todayBtn: "linked",
            toggleActive: true,
            clearBtn: true
        });
    }
}

function redirect($url){
    location.href = $url;
}

function ajaxcall(path, params, method) {
    params = params || {};
    method = method || "POST"; // Set method to post by default if not specified.

    $.ajax({
        type: method,
        url: path,
        data: params,
        success: function(data){
            /*
            if(data.status=="1"){
                show_notification('Data save successfuly.','Notification','success');
            }
            */
            if(typeof data.message === 'string' && data.message.length>0){
                if(typeof data.status=='1'){
                    show_notification(data.message,'Notification','error');
                }else{
                    show_notification(data.message,'Notification');
                }
            }
            if(typeof data.func === 'function'){data.func();}else if(typeof data.func === 'string' && data.func.indexOf("function(")==0){eval('('+data.func+')')();}
        },
        dataType: 'json'
    })
    .error(function(){
        show_notification('Submission to server error!','Notification','error');
    });
}

function post(path, params, target, method) {
    params = params || {};
    target = target || "_self";
    method = method || "GET"; // Set method to post by default if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);
    form.setAttribute("target", target);

    for (var key in params) {
        if (params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
        }
    }
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

jQuery(function($){
    
});