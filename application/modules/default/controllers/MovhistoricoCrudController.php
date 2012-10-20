<?php
require_once "AbstractController.php";

class MovhistoricoCrudController extends AbstractController
{
    public function init()
    {
        //Remover
        Zend_Loader::loadClass('Papel');
        $this->_model = new Papel();

        $this->_rest_url = Fgsl_Session_Namespace::get('server_api') . "/movimento-historico/";

        parent::init();
        $this->_title = $this->translate->translate('Entrada');
        Fgsl_Session_Namespace::set('template-form',$this->setTemplate());
    }

    public function setTemplate()
    {
        $return = "
            <script type='text/x-form-template' id='form-template'>
                <fieldset style='width: 800px;'>
                    <span class='id'></span>
                    <fieldset class='frame-panel'>
                        <legend>". $this->translate->translate('Informações sobre a nota') . "</legend>
                        <span class='oneLine threeElements'>
                          <span class='sequencia'></span>
                          <span class='data'></span>
                          <span class='valor_total'></span>
                        </span>
                    </fieldset>
                    <span class='oneLine' id='persona'>
                    </span>
                    <span class='oneLine'>
                        <table id='tblProdutos' class='ui-widget ui-widget-content' style='margin: 1em 0; border-collapse: collapse; width: 100%;'>
                            <thead>
                                <tr class='ui-widget-header'>
                                    <th>Produto</th>
                                    <th width='80px'>R$ uni.</th>
                                    <th width='80px'>Qtde.</th>
                                    <th width='80px'>R$ tot.</th>
                                    <th width='80px'>Opções</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </span>
                    <span class='oneLine'>
                      <span class='descricao'></span>
                    </span>
                </fieldset>
            </script>
            <script type='text/x-form-template' id='tbl-produto-template'>
                <td class='produto_id'></td>
                <td class='valor'></td>
                <td class='qtde'></td>
                <td class='total'></td>
                <td>
                    <img src='' />
                </td>
            </script>
            <script type='text/x-form-template' id='form-template-pbusiness'>
                <fieldset class='frame-panel'>
                    <legend>". $this->translate->translate('Informações sobre o cliente / fornecedor') . "</legend>
                    <span class='oneLine twoElements'>
                      <span class='fantasia'></span>
                      <span class='cnpj'></span>
                    </span>
                </fieldset>
            </script>
        ";
        return $return;
    }
}
