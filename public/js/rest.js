//$('div[class="ui-dialog-buttonset"]').find('button').each(function(){console.log($(this).html())})
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
        $('#errorResults').html(message);
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

            if(typeof(formPostDisplay) == 'function')
                formPostDisplay();
        }
    });
    dialog.dialog({resizable: true, modal: true, buttons: buttons, width: width});
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
        var translation = get_html_translation_table('HTML_SPECIALCHARS');

        if(elements == null)
            continue;

        element = Utf8.decode(decodeEntities(elements));
        for(i in translation)
            {
            console.log(translation[i] + " , " + i);
            element = element.replace(translation[i] , i);
            }
        element = $( element );
        var isCheckbox = (element.attr('type') == 'checkbox');
        //Não insere elemento de botão, pois a tela já conterá os mesmos
        if((typeof element.get(0) != 'undefined' && element.get(0).tagName == "button") || element.attr('type') == "button" || element.attr('type') == "submit")
            continue;

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

        //Se existir title então cria uma tag label e adiciona no formulário
        var title = element.attr('title');
        if(typeof title != "undefined")
            {
            if(!isCheckbox)
                var label = $("<label>" + title + "</label>");
            else
                var label = $("<label>" + decodeEntities(elements) + title + "</label>");

            recipiente.append( label );
            }
        //Inclui o elementxo no formulário
        if(!isCheckbox)
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
///// FUNCTIONS ESSENTIALS //////
/**
* Verifica se um valor existe no array passado.
* @param String needle
* @param Array haystack
* @return Boolean
*/
function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(typeof haystack[i] == 'object') {
            if(arrayCompare(haystack[i], needle)) return true;
        } else {
            if(haystack[i] == needle) return true;
        }
    }
    return false;
}

function get_html_translation_table (table, quote_style) {
  // http://kevin.vanzonneveld.net
  // +   original by: Philip Peterson
  // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: noname
  // +   bugfixed by: Alex
  // +   bugfixed by: Marco
  // +   bugfixed by: madipta
  // +   improved by: KELAN
  // +   improved by: Brett Zamir (http://brett-zamir.me)
  // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
  // +      input by: Frank Forte
  // +   bugfixed by: T.Wild
  // +      input by: Ratheous
  // %          note: It has been decided that we're not going to add global
  // %          note: dependencies to php.js, meaning the constants are not
  // %          note: real constants, but strings instead. Integers are also supported if someone
  // %          note: chooses to create the constants themselves.
  // *     example 1: get_html_translation_table('HTML_SPECIALCHARS');
  // *     returns 1: {'"': '&quot;', '&': '&amp;', '<': '&lt;', '>': '&gt;'}
  var entities = {},
    hash_map = {},
    decimal;
  var constMappingTable = {},
    constMappingQuoteStyle = {};
  var useTable = {},
    useQuoteStyle = {};

  // Translate arguments
  constMappingTable[0] = 'HTML_SPECIALCHARS';
  constMappingTable[1] = 'HTML_ENTITIES';
  constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
  constMappingQuoteStyle[2] = 'ENT_COMPAT';
  constMappingQuoteStyle[3] = 'ENT_QUOTES';

  useTable = !isNaN(table) ? constMappingTable[table] : table ? table.toUpperCase() : 'HTML_SPECIALCHARS';
  useQuoteStyle = !isNaN(quote_style) ? constMappingQuoteStyle[quote_style] : quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT';

  if (useTable !== 'HTML_SPECIALCHARS' && useTable !== 'HTML_ENTITIES') {
    throw new Error("Table: " + useTable + ' not supported');
    // return false;
  }

  entities['38'] = '&amp;';
  if (useTable === 'HTML_ENTITIES') {
    entities['160'] = '&nbsp;';
    entities['161'] = '&iexcl;';
    entities['162'] = '&cent;';
    entities['163'] = '&pound;';
    entities['164'] = '&curren;';
    entities['165'] = '&yen;';
    entities['166'] = '&brvbar;';
    entities['167'] = '&sect;';
    entities['168'] = '&uml;';
    entities['169'] = '&copy;';
    entities['170'] = '&ordf;';
    entities['171'] = '&laquo;';
    entities['172'] = '&not;';
    entities['173'] = '&shy;';
    entities['174'] = '&reg;';
    entities['175'] = '&macr;';
    entities['176'] = '&deg;';
    entities['177'] = '&plusmn;';
    entities['178'] = '&sup2;';
    entities['179'] = '&sup3;';
    entities['180'] = '&acute;';
    entities['181'] = '&micro;';
    entities['182'] = '&para;';
    entities['183'] = '&middot;';
    entities['184'] = '&cedil;';
    entities['185'] = '&sup1;';
    entities['186'] = '&ordm;';
    entities['187'] = '&raquo;';
    entities['188'] = '&frac14;';
    entities['189'] = '&frac12;';
    entities['190'] = '&frac34;';
    entities['191'] = '&iquest;';
    entities['192'] = '&Agrave;';
    entities['193'] = '&Aacute;';
    entities['194'] = '&Acirc;';
    entities['195'] = '&Atilde;';
    entities['196'] = '&Auml;';
    entities['197'] = '&Aring;';
    entities['198'] = '&AElig;';
    entities['199'] = '&Ccedil;';
    entities['200'] = '&Egrave;';
    entities['201'] = '&Eacute;';
    entities['202'] = '&Ecirc;';
    entities['203'] = '&Euml;';
    entities['204'] = '&Igrave;';
    entities['205'] = '&Iacute;';
    entities['206'] = '&Icirc;';
    entities['207'] = '&Iuml;';
    entities['208'] = '&ETH;';
    entities['209'] = '&Ntilde;';
    entities['210'] = '&Ograve;';
    entities['211'] = '&Oacute;';
    entities['212'] = '&Ocirc;';
    entities['213'] = '&Otilde;';
    entities['214'] = '&Ouml;';
    entities['215'] = '&times;';
    entities['216'] = '&Oslash;';
    entities['217'] = '&Ugrave;';
    entities['218'] = '&Uacute;';
    entities['219'] = '&Ucirc;';
    entities['220'] = '&Uuml;';
    entities['221'] = '&Yacute;';
    entities['222'] = '&THORN;';
    entities['223'] = '&szlig;';
    entities['224'] = '&agrave;';
    entities['225'] = '&aacute;';
    entities['226'] = '&acirc;';
    entities['227'] = '&atilde;';
    entities['228'] = '&auml;';
    entities['229'] = '&aring;';
    entities['230'] = '&aelig;';
    entities['231'] = '&ccedil;';
    entities['232'] = '&egrave;';
    entities['233'] = '&eacute;';
    entities['234'] = '&ecirc;';
    entities['235'] = '&euml;';
    entities['236'] = '&igrave;';
    entities['237'] = '&iacute;';
    entities['238'] = '&icirc;';
    entities['239'] = '&iuml;';
    entities['240'] = '&eth;';
    entities['241'] = '&ntilde;';
    entities['242'] = '&ograve;';
    entities['243'] = '&oacute;';
    entities['244'] = '&ocirc;';
    entities['245'] = '&otilde;';
    entities['246'] = '&ouml;';
    entities['247'] = '&divide;';
    entities['248'] = '&oslash;';
    entities['249'] = '&ugrave;';
    entities['250'] = '&uacute;';
    entities['251'] = '&ucirc;';
    entities['252'] = '&uuml;';
    entities['253'] = '&yacute;';
    entities['254'] = '&thorn;';
    entities['255'] = '&yuml;';
  }

  if (useQuoteStyle !== 'ENT_NOQUOTES') {
    entities['34'] = '&quot;';
  }
  if (useQuoteStyle === 'ENT_QUOTES') {
    entities['39'] = '&#39;';
  }
  entities['60'] = '&lt;';
  entities['62'] = '&gt;';


  // ascii decimals to real symbols
  for (decimal in entities) {
    if (entities.hasOwnProperty(decimal)) {
      hash_map[String.fromCharCode(decimal)] = entities[decimal];
    }
  }

  return hash_map;
}

var rand = function(min, max) {
    // Returns a random number
    //
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/rand
    // +   original by: Leslie Hoare
    // +   bugfixed by: Onno Marsman
    // %          note 1: See the commented out code below for a version which will work with our experimental (though probably unnecessary) srand() function)
    // *     example 1: rand(1, 1);
    // *     returns 1: 1
    var argc = arguments.length;
    if (argc === 0) {
        min = 0;
        max = 2147483647;
    } else if (argc === 1) {
        throw new Error('Warning: rand() expects exactly 2 parameters, 1 given');
    }
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

var decodeEntities = (function(str) {
  // this prevents any overhead from creating the object each time
  var element = document.createElement('div');

  function decodeHTMLEntities (str) {
    if(str && typeof str === 'string') {
      // strip script/html tags
      str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
      str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
      element.innerHTML = str;
      str = element.textContent;
      element.textContent = '';
    }

    return str;
  }
  return decodeHTMLEntities;
})();

/**
*
*  UTF-8 data encode / decode
*  http://www.webtoolkit.info/
*
**/

var Utf8 = {

    // public method for url encoding
    encode : function (string) {
        string = string.replace(/\r\n/g,"\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    // public method for url decoding
    decode : function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

}