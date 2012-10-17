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
                  <span class='nome'></span>
                </span>
                <span class='oneLine fourElements'>
                  <span class='marca_id'></span>
                  <span class='unidade_id'></span>
                  <span class='quantidade_total'></span>
                </span>
                <span class='oneLine fourElements'>
                  <span class='valor_venda'></span>
                  <span class='desconto_maximo'></span>
                  <span class='custo_atual'></span>
                  <span class='custo_medio'></span>
                </span>
                <span class='oneLine fourElements'>
                  <span class='peso'></span>
                  <span class='volume'></span>
                </span>
                <span class='oneLine fourElements'>
                  <span class='estoque_quantidade'></span>
                  <span class='estoque_alerta'></span>
                  <span class='estoque_minimo'></span>
                  <span class='estoque_reserva'></span>
                </span>
                <span class='oneLine twoElements'>
                  <span class='descricao'></span>
                  <span class='imagem'></span>
                </span>
              </fieldset>
            </script>
        ";

        return $return;
    }
}