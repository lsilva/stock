<?php
require_once "AbstractController.php";

class MarcaCrudController extends AbstractController
{
    public function init()
    {
        //Remover
        Zend_Loader::loadClass('Papel');
        $this->_model = new Papel();
        $this->_rest_url = Fgsl_Session_Namespace::get('server_api') . "/marca/";
        parent::init();
    }
}