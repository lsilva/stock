<?php

class PagamentoformaCrudController extends Fgsl_Crud_Controller_Abstract
	{

	public function init()
    {
    parent::init();
    Zend_Loader::loadClass('PagamentoForma');
    $this->_useModules = true;
    $this->_uniqueTemplateForApp = false;
    $this->_model = new PagamentoForma();
    $this->_title = 'Cadastro de Formas de Pagamentos';
    $this->_searchButtonLabel = 'Pesquisar';
    $this->_searchOptions = array('pgt_id' => 'ID');
    $this->_config();
    }
	}

