var formAfterDisplay = function(){

    if($("#id").val() == "")
    {
        /** TODO: TABELA VAZIA */
    }
    else
        sendRequest('GET', path_url_api + '/movimento-historico/' + $("#id").val() + '/itens', null, function(data){
            console.log(data);
            for(i in data)
                addTDProduct(data[i].prd_nome,data[i].prd_id,data[i].mvi_quantidade,data[i].mvi_valor,data[i].id);

            totalizaTable();
        });
    //Preenche campo da pessoa
    getPersonaFields($('#persona'));
    getProductsFields($('#products'));

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

        $("#prd_nome").autocomplete({
            source: path_url_api + '/produto/autosuggest/',
            minLength: 3,
            select: function(event, ui) {
                console.log(ui.item);
                $("#prd_id").val(ui.item.id);
                $("#prd_valor_venda").val(ui.item.prd_valor_venda);
                $("#valor_total").val(ui.item.prd_valor_venda * $("#quantidade").val());
            }
        });

        $('#cnpj').attr('disabled',true);
        $("#prd_valor_venda").attr('disabled',true);
        appendImage($("#valor_total"),'add', addProductInTable);

        removeRequiredFields();
    }, 1000);

    $("#quantidade").bind('keypress',function(){
        $("#valor_total").val($('#prd_valor_venda').val() * $(this).val());
    });

    $("#quantidade").focusout(function(){
        $("#valor_total").val($('#prd_valor_venda').val() * $(this).val());
    });

};

var afterRequest = function(objectMovHist)
{
    var itens = saveItens();

    for(i in itens)
        {
        itens[i].mvh_id = objectMovHist.message;
        sendRequest('post',path_url_api + '/movimento-item/',itens[i],function(){})
        }
}

var removeRequiredFields = function()
{
    $("#prd_valor_venda").removeAttr('required');
    $("#prd_nome").removeAttr('required');
}

var addProductInTable = function()
{
    //Verifica se não existe produto selecionado
    if(isEmpty($('#prd_nome').val()))
        return false;

    addTDProduct(
        $('#prd_nome').val(),
        $('#prd_id').val(),
        $('#quantidade').val(),
        $('#prd_valor_venda').val()
        );
    //Zera os valores referentes aos produtos
    $('#prd_nome').val('');
    $('#prd_id').val('');
    $('#quantidade').val('1');
    $('#prd_valor_venda').val('');
    $('#valor_total').val('');

    totalizaTable();
}

var saveItens = function()
{
    var itens = [];
    var item = {};
    cells = $("#tblProdutos tr[class^='prd_id'] td");
    cells.each(function(){
        if($(this).hasClass('qtde'))
            item.quantidade = $(this).html();

        if($(this).hasClass('valor'))
            item.valor = $(this).html();

        if($(this).hasClass('total'))
        {
            item.produto_id = $(this).parent().attr('class').replace('prd_id_','');
            itens.push(item);
            item = {};
        }
    });

    return itens;
}

var totalizaTable = function()
{
    var total = qtde = valor = 0;
    cells = $("#tblProdutos tr[class^='prd_id'] td");
    cells.each(function(){
        if($(this).hasClass('qtde'))
            qtde = $(this).html();

        if($(this).hasClass('valor'))
            valor = $(this).html();

        if($(this).hasClass('total'))
        {
            var result = valor * qtde;
            $(this).html(result);
            total += result;
            valor = qtde = 0;
        }
    });
    //Remove a linha de total se existir
    $('#trTotal').remove();
    //Inclui a linha de total
    $("#tblProdutos").append( $("<tr id='trTotal'><td colspan='3'>Total</td><td align='right'>" + total + "</td><td align='center'>-</td>") );
    $("#mvh_valor_total").val(total);
}

var addTDProduct = function(nome, id, qtde, valor, item_id)
{
    table = $('#tblProdutos');
    if($('#tblProdutos tr').filter('.prd_id_' + id).length)
    {
        var buttons = {
                "Confirmar": function() {
                    $('#tblProdutos tr').filter('.prd_id_' + id).remove();
                    addTDProduct(nome, id, qtde, valor, item_id);
                    totalizaTable();
                    $( this ).dialog( "close" );
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            };
        dialog("error","ERROR","Este produto já foi adicionado, deseja subistitui-lo?",buttons);
    }
    else
    {
        rows = $( '<tr class="prd_id_' + id + '">' ).append($('script#tbl-produto-template').html());
        rows.find('td').each(function(){
            if($(this).attr('class') == 'nome') $(this).html(nome);
            if($(this).attr('class') == 'qtde') $(this).html(qtde);
            if($(this).attr('class') == 'valor') $(this).html( mask_money( valor ) );
            });
        table.append(rows);
    }
    //
    $('#tblProdutos tr td').filter('.opcoes').find('.delete').bind('click',function(){
        //TODO: Colocar dialog para solicitar confirmação
        var line = $(this).parent().parent();
        var buttons = {
                "Confirmar": function() {
                    line.remove();
                    totalizaTable();
                    $( this ).dialog( "close" );
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            };
        dialog("error","ERROR","Deseja excluir o produto?",buttons);
    });
}

function appendImage(element,type,callback)
{
    var image_width = 16;
    var element_width = element.width();
    var img = $('<img>');
    img.attr('src','/public/image/package/'+ type +'.png');
    img.width(image_width);
    img.height(image_width);
    img.css('padding','3px 3px');
    img.css('float','left');
    img.bind('click',callback);

    element.css('float','left');
    element.width(element_width - image_width - 8);
    element.parent().append(img);
}

var getPersonaFields = function(output){
    var url = path_url_api + '/persona-business/form/';
    var cliente = $('#mvh_cliente').val();
    if(!isEmpty(cliente))
        url = url + cliente;

    sendRequest('GET', url, null, function(data){
        template = $( '<div>' ).append($('script#form-template-pbusiness').html());
        output.append(getMergeTemplate(template,data));
    });
}

var getProductsFields = function(output){
    sendRequest('GET', path_url_api + '/produto/form/', null, function(data){
        getMergeTemplate(output,data);
    });
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