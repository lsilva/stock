<?php
require_once "AbstractController.php";

class ProdutoCrudController extends AbstractController
{
    public function init()
    {
        //Remover
        Zend_Loader::loadClass('Papel');
        $this->_model = new Papel();
        $this->_rest_url = Fgsl_Session_Namespace::get('server_api') . "/produto/";

        parent::init();
        $this->_title = $this->translate->translate('Cadastro de Produtos');
        Fgsl_Session_Namespace::set('template-form',$this->setTemplate());
    }

    public function setTemplate()
    {
        $return = "
            <script type='text/x-form-template' id='form-template'>
              <fieldset style='width: 800px;'>
                <span class='id'></span>
                <span class='oneLine oneElements'>
                  <span class='prd_nome'></span>
                </span>
                <span class='oneLine fourElements'>
                  <span class='prd_marca_id'></span>
                  <span class='prd_unidade_id'></span>
                  <span class='prd_quantidade_total'></span>
                </span>
                <span class='oneLine fourElements'>
                  <span class='prd_valor_venda'></span>
                  <span class='prd_desconto_maximo'></span>
                  <span class='prd_custo_atual'></span>
                  <span class='prd_custo_medio'></span>
                </span>
                <span class='oneLine fourElements'>
                  <span class='prd_peso'></span>
                  <span class='prd_volume'></span>
                </span>
                <span class='oneLine fourElements'>
                  <span class='prd_estoque_quantidade'></span>
                  <span class='prd_estoque_alerta'></span>
                  <span class='prd_estoque_minimo'></span>
                  <span class='prd_estoque_reserva'></span>
                </span>
                <span class='oneLine twoElements'>
                  <span class='prd_descricao'></span>
                  <span class='prd_imagem'></span>
                </span>
              </fieldset>
            </script>
        ";

        return $return;
    }
}