var formAfterDisplay = function(){
    sendRequest('GET', path_url_api + '/movimento-item/', null, function(data){
        console.log(data);
        var table = $('#tblProdutos');
        for(i in data)
            {
            rows = $( '<tr>' ).append($('script#tbl-produto-template').html());
            rows.find('td').each(function(){
                if($(this).attr('class') == 'produto_id')
                    $(this).html(data[i].pro_id);
                if($(this).attr('class') == 'qtde')
                    $(this).html(data[i].quantidade);
                if($(this).attr('class') == 'valor')
                    $(this).html(data[i].valor);
                });
            rows.find('.total').html(data[i].valor * data[i].quantidade);
            table.append(rows);
            }
        //Preenche campo da pessoa
        getPersonaFields($('#persona'));

    });

    setTimeout(function(){
        $("#fantasia").autocomplete({
            source: path_url_api + '/persona-business/autosuggest/',
            minLength: 3,
            select: function(event, ui) {
                console.log(ui.item);
                $("#cliente").val(ui.item.id);
                $("#cnpj").val(ui.item.cnpj);
            }
        });
      $('#dots').append('.');
    }, 1000);
};

var getPersonaFields = function(output){
    sendRequest('GET', path_url_api + '/persona-business/form/', null, function(data){
        template = $( '<div>' ).append($('script#form-template-pbusiness').html());
        output.append(getMergeTemplate(template,data));
    });
/*
*/
}
/* TODO: essa função deve ser generalizada para ser utilizada na função mountForm */
var getMergeTemplate = function(template, elements)
{
    for(i in elements)
    {
        var objectReturn = getObjectToJSON(elements[i]);
        var element = objectReturn.element;
        var label = objectReturn.label;
        if(!element) continue;

        template.find('span').each(function(){
            if($(this).attr('class') == element.attr('id'))
                {
                if(label) $(this).append(label);
                $(this).append(element);
                }
            });

    }
    return template;
}