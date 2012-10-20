$(function(){
    $('#frmDialog').dialog({ autoOpen: false });
    $('#frmForm').dialog({ autoOpen: false });
    $('#errorResults').bind('click',function(){$(this).hide(2000);})
    //Inclusão de registros {POST}
    $(':button').filter('.action_window_add').bind('click',function(){
        var action = rest_url_path + "form";
        sendRequest("GET", action, null, function(data){
            mountForm(data,'insert')
        });
    });

    //Remoção de registros {DELETE}
    $('a').filter('.action_delete').bind('click',function(){
        var action = $(this).attr('href');
        var buttons = {
                "Confirmar": function() {
                    sendRequest("delete",action,null,function(data){
                        location.reload();
                        window.location.reload();
                    });
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            };
        dialog("error","ERROR","Deseja realmente excluir o ítem selecionado?",buttons);
        $(this).attr('href','javascript:void(0)');
    });

    //Edição de registros {PUT}
    $('a').filter('.action_edit').bind('click',function(){
        formToEdit($(this).attr('href'),'edit');
        return false;
    });

    $('a').filter('.action_view').bind('click',function(){
        formToEdit($(this).attr('href'),'view');
        return false;
    });
});

var formToEdit = function (page, status){
    var action_link = page;
    var id = action_link.match(/[0-9]*$/);
console.log(id);
console.log(action_link);
    var action = rest_url_path + "form/" + id[0];
    sendRequest("GET", action, null, function(data){
        mountForm(data,status)
    });
}

var formSubmit = function(element){
    if(element == 'undefined')
        return false;

    var formulario = element.find('form');

    if(validateForm(formulario) == false)
        return false;

    var arrResource = $(location).attr('href').substr(path_url.length + 1).split('/');
    var module = arrResource[0];
    var url = path_url + "/" + module;
    var data = {};
    var actionForm = formulario.attr('action');
    var methodForm = formulario.attr('method');
    var idForm = formulario.find('#id').val();
    if(typeof idForm=="undefined")
        idForm = "";
    formulario.find('input, textarea, select').each(function() {
        var value = ($(this).attr('type') !== 'checkbox' ? $(this).val() : ($(this).is(":checked") ? 1 : 0));
        data[$(this).attr('name')] = value;
    });
console.log(data);
    sendRequest(methodForm,actionForm,data,function(data){
        $(location).attr('href',url);
    });


    return false;
};

function sendRequest(type,url,data,callback)
{
    console.log(type,data,url);
    $.ajax({
        url: url,
        type: type,
        data: data,
//        complete: function (jqxhr, txt_status) {
//            console.log ("Complete: [ " + txt_status + " ] " + jqxhr.status,jqxhr);
//        }
    })
    .done(callback)
    .fail(function(jqXHR, textStatus) {
        var response = jqXHR.responseText;
        console.log(response);
        if(response.length > 0)
        {
            response = JSON.parse(response);
            var message = response.message;
        }
        displayMessageError(message);
    });
}

function displayMessageError(message)
{
    if($('#errorResults').parent().is(':visible'))
    {
        $('#errorResults').show(1000);
        $('#errorResults').html(message);5
    }
    else
        dialog("error","ERROR",message);
}

function dialog(type,title,message,buttons)
{
    var typesAccepts = ['exclamation','error','information'];
    var dialog = $('#frmDialog');
    //Seta um valor default se o type passado não for suportado
    if(!inArray(type,typesAccepts))
        type = 'information';
    dialog.attr('title',title);
    dialog.find('img').attr('src',base_url + '/public/image/package/' + type + '.png');
    dialog.find('p').html(message);
    dialog.dialog({resizable: false,modal: true, buttons: buttons});
    dialog.dialog('open');
}

function dialogForm(title,message,buttons,width)
{
    var dialog = $('#frmForm');
    dialog.attr('title',title);
    dialog.find('p form').remove();
    dialog.find('p').append(message);
    var  name_form = dialog.find('p').find('form').attr('id');

    if(width == 'undefined' || width == null)
        width = 300;

    dialog.dialog({
        open: function(event, ui) {
            initFunctions();
            is_disabled = false;
            for(i in buttons)
                if(i == 'Editar')
                    {
                    disabledFields(name_form, false);
                    is_disabled = true;
                    }
            if(is_disabled)
                $('div[class="ui-dialog-buttonset"]').find('button').find('span').each(function(){
                    if($(this).html() == 'Confirmar')
                        $(this).hide();
                });

            if(typeof(formAfterDisplay) == 'function')
                formAfterDisplay();
        }
    });

    var titleDialog = ($('#title-page') ? $('#title-page').html() : '');
    dialog.dialog({resizable: true, modal: true, buttons: buttons, width: width, title: titleDialog});
    dialog.dialog('open');
}

var disabledFields = function(idForm, toEnabled)
{
    $('#'+idForm+' input,#'+idForm+' textarea, #'+idForm+' select').each(function(){
        if($(this).attr('type') == 'checkbox' || $(this).get(0).tagName == "SELECT")
            if(!toEnabled)
                $(this).attr('disabled', 'disabled');
            else
                $(this).removeAttr('disabled');

        $(this).attr('readonly', !toEnabled);
        $(this).css('background-color',(toEnabled ? 'white' : 'lightgreen'));
        $(this).css('border','none');
    });
}

function mountForm(dataReturn, status)
{
    var method = (status == 'insert' ? 'POST' : 'PUT');
    var name_form = "myFORM_" + rand();
    var buttons = {};

    if(status == 'view')
        buttons.Editar = function() {
            disabledFields(name_form, true);
            $('div[class="ui-dialog-buttonset"]').find('button').find('span').each(function(){
                if($(this).html() == 'Editar')
                    $(this).hide();
                if($(this).html() == 'Confirmar')
                    $(this).show();
            });
        };
    buttons.Confirmar = function() { $('#' + name_form).submit(formSubmit($(this)))},
    buttons.Cancel = function() {$( this ).dialog( "close" )};

    console.log(dataReturn);
    //Cria o elemento do formulário
    var form = $( '<form action="' + rest_url_path + '" method="' + method + '" id="' + name_form + '">' );
    var recipiente = form;
    var otherRecipiente = false;
    var formTemplate;
    var width;
    var elementElement;

    if($('script#form-template').length)
        formTemplate = $( '<div>' ).append($('script#form-template').html());
    //Percorre os elementos para montar o formulário
    for(i in dataReturn)
        {
        var elements = (typeof dataReturn[i] == 'string' ? dataReturn[i].split('&lt;input') : []);
        elements = (elements.length == 3 ? '&lt;input' + elements[2] : dataReturn[i]);

        if(elements == null)
            continue;

        var objectReturn = getObjectToJSON(elements);
        var element = objectReturn.element;
        var label = objectReturn.label;

        if(!element) continue;

        if(typeof formTemplate != 'undefined')
            {
            elementElement = formTemplate.find('.' + element.attr('id'));
            if(elementElement.length)
                {
                otherRecipiente = true;
                recipiente = elementElement;
                }
            }
        else
            recipiente = form;

        if(label) recipiente.append(label);
        recipiente.append( element );
        }

    if(otherRecipiente)
        {
        form.append(formTemplate);
        width = form.find('fieldset').width() + 60;
        }

    dialogForm("INSERT",form,buttons, width);
}

var validateForm = function(formulario)
{
    var isValid = true;
    $('input[required="true"]').each(function(){
        if($(this).val() == "")
            {
            displayMessageError('Preencha o campo: ' + $(this).attr('title'));
            $(this).focus();
            isValid = false;
            }
    });

    return isValid;
}

var getObjectToJSON = function(jsonElement)
{
    var object = {};
    element = Utf8.decode(decodeEntities(jsonElement));
    var translation = get_html_translation_table('HTML_SPECIALCHARS');
    for(i in translation)
        element = element.replace(translation[i] , i);

    element = $( element );
    var isCheckbox = (element.attr('type') == 'checkbox');

    //Não insere elemento de botão, pois a tela já conterá os mesmos
    if((typeof element.get(0) != 'undefined' && element.get(0).tagName == "button") ||
        element.attr('type') == "button" ||
        element.attr('type') == "submit")
        return object;

    //Se existir title então cria uma tag label e adiciona no formulário
    var title = element.attr('title');
    var label;
    if(typeof title != "undefined")
        {
        if(!isCheckbox)
            label = $("<label>" + title + "</label>");
        else
            label = $("<label>" + decodeEntities(elements) + title + "</label>");
        }

    object.element = element;
    object.label = label;

    return object;
}
//scp -P 10222 emkt/dados/temp/power/servidor/int00_transac18_201210191115_89da6039_89da6039 tbessa@pmta01.akna.com.br:~/