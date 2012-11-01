var formAfterDisplay = function()
{
    sendRequest('GET', path_url_api + '/marca/', null, function(data){
        $("#prd_marca_id").append( $("<option value=''>Selecione</option>") );
        for(i in data)
            $("#prd_marca_id").append(
                $("<option value='" + data[i].id + "'>" + data[i].mrc_titulo + "</option>")
            );
    });

    sendRequest('GET', path_url_api + '/unidade/', null, function(data){
        $("#prd_unidade_id").append( $("<option value=''>Selecione</option>") );
        for(i in data)
            $("#prd_unidade_id").append(
                $("<option value='" + data[i].id + "'>" + data[i].und_nome + "</option>")
            );
    });
}