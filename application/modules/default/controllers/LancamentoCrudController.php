<?php
require_once "AbstractController.php";

class LancamentoCrudController extends AbstractController
{
    public function init()
    {
        //Remover
        Zend_Loader::loadClass('Papel');
        $this->_model = new Papel();
        $this->_rest_url = "http://localhost/private/api/lancamento/";

        parent::init();
        $this->_title = $this->translate->translate('Cadastro de Marcas');
    }
}