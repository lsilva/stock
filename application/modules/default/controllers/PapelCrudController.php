<?php
require_once "AbstractController.php";

class PapelCrudController extends AbstractController
{
    public function init()
    {
        Zend_Loader::loadClass('Papel');
        $this->_model = new Papel();
        $this->_rest_url = Fgsl_Session_Namespace::get('server_api') . "/user-group/";

        parent::init();
        $this->_title = $this->translate->translate('Cadastro de Papéis');
    }
}