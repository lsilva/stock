<?php
require_once "AbstractController.php";

class PbusinessCrudController extends AbstractController
{
    public function init()
    {
        //Remover
        Zend_Loader::loadClass('Papel');
        $this->_model = new Papel();
        $this->_rest_url = Fgsl_Session_Namespace::get('server_api') . "/persona-business/";

        parent::init();
        $this->_title = $this->translate->translate('Cadastro de Marcas');
    }
}