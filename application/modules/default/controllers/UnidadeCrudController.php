<?php
require_once "AbstractController.php";

class UnidadeCrudController extends AbstractController
{
    public function init()
    {
        //Remover
        Zend_Loader::loadClass('Papel');
        $this->_model = new Papel();
        $this->_rest_url = Fgsl_Session_Namespace::get('server_api') . "/unidade/";

        parent::init();
        $this->_title = $this->translate->translate('Cadastro de Unidades');
    }
}