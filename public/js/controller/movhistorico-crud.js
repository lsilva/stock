var formPostDisplay = function(){
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
    });
};