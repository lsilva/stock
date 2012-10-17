<?php

class EstoquelocalCrudController extends Fgsl_Crud_Controller_Abstract
	{

	public function init()
    {
    parent::init();
    Zend_Loader::loadClass('EstoqueLocal');
    $this->_useModules = true;
    $this->_uniqueTemplateForApp = false;
    $this->_model = new EstoqueLocal();
    $this->_title = 'Cadastro de locais de estocagem de produtos';
    $this->_searchButtonLabel = 'Pesquisar';
    $this->_searchOptions = array('stq_id' => 'ID');
    $this->_config();
    }
	}

