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
                    <span class='mvh_cliente'></span>
                    <input type='hidden' name='prd_id' id='prd_id'>
                    <fieldset class='frame-panel'>
                        <legend>". $this->translate->translate('Informações sobre a nota') . "</legend>
                        <span class='oneLine threeElements'>
                          <span class='mvh_sequencia'></span>
                          <span class='mvh_data'></span>
                        </span>
                        <span class='oneLine'>
                          <span class='mvh_descricao'></span>
                        </span>
                    </fieldset>
                    <span class='oneLine' id='persona'>
                    </span>
                    <fieldset class='frame-panel'>
                        <legend>". $this->translate->translate('Produtos') . "</legend>
                        <span class='oneLine fourElements' id='products'>
                            <span class='prd_nome' style='width: 310px;'></span>
                            <span class='prd_valor_venda' style='width: 150px;'></span>
                            <span class='quantidade' style='width: 150px;'>
                                <label>". $this->translate->translate('Quantidade') . "</label>
                                <input type='text' value='1' name='quantidade' id='quantidade'>
                            </span>
                            <span class='valor_total' style='width: 150px;'>
                                <label>". $this->translate->translate('Valor total') . "</label>
                                <input type='text' disabled='disabled' name='valor_total' id='valor_total'>
                            </span>
                        </span>
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
                    </fieldset>
                </fieldset>
            </script>
            <script type='text/x-form-template' id='tbl-produto-template'>
                <td class='nome'></td>
                <td class='valor' align='right'></td>
                <td class='qtde' align='right'></td>
                <td class='total' align='right'></td>
                <td class='opcoes' align='center'>
                    <img src='/public/image/package/delete.png' title='Remover' class='delete' />
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
