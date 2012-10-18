//cpf:      mascara(this,cpf)
//cnpj:     mascara(this,cnpj)
//telefone: mascara(this,telefone)
//data:     mascara(this,data)
//cep:      mascara(this,cep)
//money:    mascara(this,money)
//float:    mascara(this,float)
//integer:  mascara(this,integer)

var validMasks = new Array("cpf","cnpj","telefone","data","cep","money","float","integer");
var initFunctions = function()
{
    $("form input[type=text][data=data-ui]").datepicker({
        changeMonth: true,
        changeYear: true,
        altFormat: "dd-mm-yy",
        dayNames: ["Domingo", "Segunda", "Terça", "Quarta", "Quinta", "Sexta", "Sábado"],
        dayNamesMin: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sab"]
    });

    $("form input[type=text]").bind('keypress',function(){
        var evento = $(this).attr('data');
        var value = $(this).val();

        if($.inArray(evento,validMasks) < 0)
            return;

        if(typeof(evento) == "undefined")
            return;

        $(this).css('textAlign','right');
        mascara(this,evento);
    });
}

$(function(){initFunctions()});

/*Função que padroniza CEP*/
function mask_cep(v)
{
    v=v.replace(/D/g,"")
    v=v.replace(/^(\d{5})(\d)/,"$1-$2")
    return v
}

function mask_cpf(v)
{
    v=v.replace(/\D/g,"");                    //Remove tudo o que não é dígito
    v=v.replace(/(\d{3})(\d)/,"$1.$2");       //Coloca um ponto entre o terceiro e o quarto dígitos
    v=v.replace(/(\d{3})(\d)/,"$1.$2");       //Coloca um ponto entre o terceiro e o quarto dígitos
                                               //de novo (para o segundo bloco de números)
    v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2"); //Coloca um h�fen entre o terceiro e o quarto dígitos
    return v;
}

/*Função que padroniza CNPJ*/
function mask_cnpj(v)
{
    v=v.replace(/\D/g,"")
    v=v.replace(/^(\d{2})(\d)/,"$1.$2")
    v=v.replace(/^(\d{2})\.(\d{3})(\d)/,"$1.$2.$3")
    v=v.replace(/\.(\d{3})(\d)/,".$1/$2")
    v=v.replace(/(\d{4})(\d)/,"$1-$2")
    return v
}

/*Função que padroniza telefone (11) 4184-1241*/
function mask_telefone(v)
{
    v=v.replace(/\D/g,"")
    v=v.replace(/^(\d\d)(\d)/g,"($1) $2")
    v=v.replace(/(\d{4})(\d)/,"$1-$2")
    return v
}

/*Função que padroniza DATA*/
function mask_data(v)
{
    v=v.replace(/\D/g,"")
    v=v.replace(/(\d{2})(\d)/,"$1/$2")
    v=v.replace(/(\d{2})(\d)/,"$1/$2")
    return v
}

/*Função que padroniza nros Float 0.00*/
function mask_float(v)
{
    v=v.replace(/\D/g,"")
    v=v.replace(/^0/g,"")
    if(v.length<3)
      while(v.length<3) v="0"+v
    v=v.replace(/(\d{0,})(\d{2})/,"$1.$2")
    return v
}

function mask_integer(v)
{
    v=v.replace(/\D/g,"")
    return v
}

//Mascara de entrada monetária do inputbox
function mask_money(v)
{
    var str=v;
    str=str.replace(/\D/g,"");
    if(str.length<3)str=str_repeat(0,3-str.length)+str;
    var dec=str.substr(str.length-2);
    var inteiro=str.substring(0,str.length-2);
    for(var i=0;i<inteiro.length;i++)
    {
      var unit = inteiro.substr(i,1);
      if(unit!=0 || i==inteiro.length-1)
      {
        inteiro=inteiro.substr(i,inteiro.length);
        break;
      }
    }
    if(inteiro.length>3)
    {
      var cent=inteiro.substr(str.length-5);
      var mil=inteiro.substr(0,str.length-5);
      inteiro=mil+"."+cent;
    }
    var number = inteiro+","+dec;
    return number;
}

function str_repeat(s, n)
{
    var a = [];
    while(a.length < n)
        a.push(s);

    return a.join('');
}

//Mascara de Entrada
function mascara(o,f)
{
    v_obj=o
    v_fun=f
    setTimeout("execmascara(v_fun,v_obj)",1)
}

function execmascara(v_fun,v_obj)
{
    eval("v_obj.value=mask_"+v_fun+"(v_obj.value)");
}
