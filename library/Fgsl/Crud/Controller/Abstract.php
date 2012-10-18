<?php
/**
 * Fgsl Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license, you can get it at www.fgsl.eti.br.
 *
 * @category   Fgsl
 * @package    Fgsl_Crud
 * @subpackage Fgsl_Crud_Controller_Abstract
 * @copyright  Copyright (c) 2009 Flávio Gomes da Silva Lisboa (http://www.fgsl.eti.br)
 * @license   New BSD License
 * @version    0.0.5
 */

/**
 * Fgsl_Crud_Controller_Abstract
 */
require("Interface.php");
abstract class Fgsl_Crud_Controller_Abstract extends Zend_Controller_Action implements Fgsl_Crud_Controller_Interface
{
	/**
	 * model object used by controller
	 * @var unknown_type
	 */
	protected $_model;
	/**
	 * data sent via HTTP POST
	 * @var unknown_type
	 */
	protected $_post;
	/**
	 * profiler of database operations
	 * @var unknown_type
	 */
	protected $_profiler;
	/**
	 * controller action name
	 * @var unknown_type
	 */
	protected $_controllerAction;
	/**
	 * module name
	 * @var unknown_type
	 */
	protected $_moduleName;
	/**
	 * field names of model
	 * @var unknown_type
	 */
	protected $_fieldNames;
	/**
	 * title of listing
	 * @var unknown_type
	 */
	protected $_title;
	/**
	 * base path of application
	 * @var unknown_type
	 */
	protected $_basePath;
	/**
	 * hyperlink that indicates where application has to go when let CRUD page
	 * @var unknown_type
	 */
	protected $_returnLink;
	/**
	 * hyperlink that indicates the menu of application
	 * @var unknown_type
	 */
	protected $_menuLink;
	/**
	 * amount items must be listed per page
	 * @var unknown_type
	 */
	protected $_itemsPerPage;
	/**
	 * indicates if application use modules structure
	 * @var unknown_type
	 */
	protected $_useModules;
	/**
	 * current page of listing
	 * @var unknown_type
	 */
	protected $_currentPage;
	/**
	 * last page of listing
	 * @var unknown_type
	 */
	protected $_lastPage;
	/**
	 * HTML table where data will be listed
	 * @var unknown_type
	 */
	protected $_table;
	/**
	 * indicates if there is unique pair of templates for all application
	 * @var unknown_type
	 */
	protected $_uniqueTemplatesForApp;

	/**
	 * defines the keys used to search records
	 * @var unknown_type
	 */
	protected $_searchOptions;

	/**
	 * defines the label of search button
	 * @var unknown_type
	 */
	protected $_searchButtonLabel;

	/**
	 * indicates if controller use its own templates (neither module nor application)
	 * @var unknown_type
	 */
	protected $_privateTemplates;
	/**
	 * Permite controlar o retorna da requisi��o
	 * dever� ser feita para AJAX (XML)
	 * @var Boolean
	 */
	public $return_ajax = false;
	/**
	 * Permite controlar se a p�gina ir� ser redirecionada ou n�o
	 * @var Boolean
	 */
	public $redirect = true;
	/**
	 * Array que conter� os links que iram no assign
	 * da p�gina de layout list
	 * @var array
	 */
	public $linksPersonalizados = array();
	/**
	 * Fgsl_Session_Namespace must be initialized by application
	 * and HTTP POST must be stored
	 */
	public function init()
	{
		$this->_itemsPerPage = 20;

		$this->_basePath = $this->getFrontController()->getBaseUrl();

		$this->_post = Fgsl_Session_Namespace::get('post');

		$db = Zend_Registry::get('db');
		$this->_profiler = $db->getProfiler();
		$this->_profiler->setEnabled(true);

		$this->_controllerAction = $this->getRequest()->getControllerName();
		$this->_moduleName = $this->getRequest()->getModuleName();

		Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		// prevents add controller name into script path
		$this->_helper->viewRenderer->setNoController(true);
	}

	/**
	 * Default action is listing data
	 * (non-PHPdoc)
	 * @see Crud/Controller/Fgsl_Crud_Controller_Interface#indexAction()
	 */
	public function indexAction()
	{
		$this->_forward('list');
	}

	/**
	 * Presents a page with a hyperlink to insert records and a table with records.
	 * Records can be filtered, edited and deleted.
	 * (non-PHPdoc)
	 * @see Crud/Controller/Fgsl_Crud_Controller_Interface#listAction()
	 */
	public function listAction()
	{
		Zend_Paginator::setDefaultScrollingStyle('Sliding');

		Zend_View_Helper_PaginationControl::setDefaultViewPartial('list.phtml');

		$this->_currentPage = $this->_getParam('page',1);
		$this->_currentPage = $this->_currentPage < 1 ? 1 : $this->_currentPage;

		/** TODO get total of records for $totalOfItems
		 * @var unknown_type
		 */
		$totalOfItems = $this->_itemsPerPage;

		$this->_lastPage = (int)(($totalOfItems/$this->_itemsPerPage));

		$paginator = $this->_getPagedData();
		$records = $this->_getProcessedRecords($paginator->getCurrentItems());

		$this->_model->setRelationships($records);

		$html = new Fgsl_Html();
		$this->_table = $html->createTable($records);

		$this->configureViewAssign();
		//Altera��o para que os links do assign possam ser editaveis
		if(count($this->linksPersonalizados)>0)
			foreach($this->linksPersonalizados as $k => $v)
				$this->view->assign($k,$v);

		$this->view->render('list.phtml');
	}

	/**
	 * Returns a object Zend_Paginator
	 */
	private function _getPagedData()
	{
		$where = isset($this->_post->key) ? ($this->_post->key." like '%".$this->_post->value."%'") : null;

		$select = $this->_model->getCustomSelect($where,$this->_model->getOrderField(),$this->_itemsPerPage,($this->_currentPage-1)*$this->_itemsPerPage);

		$rows = $this->_model->fetchAllAsArray($select);

		$this->_logProfile($this->_profiler->getLastQueryProfile());

		$paginator = Zend_Paginator::factory($rows);

		$paginator->setCurrentPageNumber($this->_currentPage)
					->setItemCountPerPage($this->_itemsPerPage);
		return $paginator;
	}

	/**
	 * Create an data array to be displayed as a table by view
	 * @param ArrayIterator $currentItems
	 */
	private function _getProcessedRecords(ArrayIterator $currentItems)
	{
		$baseUrl = $this->getFrontController()->getBaseUrl();

		$module = $this->_useModules ? $this->_moduleName.'/' : '';

		$fieldKey = $this->_model->getFieldKey();

		$records = array();
		foreach ($currentItems as $row)
		{
			$records[] = array();

			$id = $row[$fieldKey];
			$records[count($records)-1][$this->_model->getFieldLabel($fieldKey)] = '<a href="'.$baseUrl.'/'.$module.$this->_controllerAction.'/edit/'.$fieldKey.'/'.$id.'">'.$id.'</a>';

			foreach ($this->_fieldNames as $fieldName)
			{
				if ($fieldName == $fieldKey || !isset($row[$fieldName])) continue;
				$records[count($records)-1][$this->_model->getFieldLabel($fieldName)] = $row[$fieldName];
			}

			$records[count($records)-1]['remove'] = '<a href="'.$baseUrl.'/'.$module.$this->_controllerAction.'/remove/'.$fieldKey.'/'.$id.'">X</a>';
		}
		return $records;
	}

	/**
	 * Shows insert form
	 * (non-PHPdoc)
	 * @see Crud/Controller/Fgsl_Crud_Controller_Interface#insertAction()
	 */
	public function insertAction()
	{
		$module = $this->_useModules ? "{$this->_moduleName}/" : '';

		$data = $this->_getDataFromPost();
		$options = array(
		Fgsl_Form_Edit::DATA => $data,
		Fgsl_Form_Edit::ACTION => BASE_URL."/$module{$this->_controllerAction}/save",
		Fgsl_Form_Edit::MODEL => $this->_model
		);
		$this->view->assign('form', new Fgsl_Form_Edit($options));
		$this->view->render('insert.phtml');
	}

	/**
	 *
	 * (non-PHPdoc)
	 * @see Crud/Controller/Fgsl_Crud_Controller_Interface#editAction()
	 */
	public function editAction()
	{
		$fieldKey = $this->_model->getFieldKey();
		$record = $this->_model->fetchRow("{$fieldKey} = {$this->_getParam($fieldKey)}");

		$_POST = array();

		foreach ($this->_fieldNames as $fieldName)
		{
			if (isset($record->$fieldName))
				$_POST[$fieldName] = $record->$fieldName;
		}

		Fgsl_Session_Namespace::set('post',new Zend_Filter_Input(null,null,$_POST));

		$this->_forward('insert');
	}

	/**
	 * Gets a Fgsl_Form object
	 * (non-PHPdoc)
	 * @see Crud/Controller/Fgsl_Crud_Controller_Interface#getEditForm($dados, $action, $model)
	 */
	public function getEditForm(array $data,$action,$model)
	{
		$options = array(
		Fgsl_Form::DATA => $data,
		Fgsl_Form::ACTION => $action,
		Fgsl_Form::MODEL => $model
		);

		return new Fgsl_Form($options);
	}

	/**
	 *
	 * (non-PHPdoc)
	 * @see Crud/Controller/Fgsl_Crud_Controller_Interface#saveAction()
	 */
	public function saveAction()
	{
		/*if (isset($this->_post->Return))
		{
			$this->_redirect($this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/list');
			return;
		}
*/
		$options = array();
		$options[Fgsl_Form_Edit::ACTION] = '';
		$options[Fgsl_Form_Edit::DATA] = $this->_getDataFromPost();
		$options[Fgsl_Form_Edit::MODEL] = $this->_model;

		$form = new Fgsl_Form_Edit($options);

    $fieldNames = $this->_model->getFieldNames();

    $is_edit_pass = in_array("web_new_pass",$fieldNames) && in_array("web_pass_re",$fieldNames) && in_array("web_pass",$fieldNames);
    $err_pass = false;
    if($is_edit_pass)
	    {
      	$dataAuth = Fgsl_Session_Namespace::get('data_auth');
	    $err_pass = !$this->_model->checkPass($dataAuth->web_id,$this->_post->web_pass);
	    $message_error="";
	    if($err_pass)
	      	{
	        $message_error = "<campo value=\"".$this->_model->getFieldLabel('web_pass')."\">\n";
	        $message_error.= "<error type=\"Pass_NotFound\" message=\"".utf8_encode("Senha passada n�o corresponde � senha cadastrada para este usu�rio.")."\" />\n";
	        $message_error.= "</campo>\n";
	      	}
	    if(!$err_pass && ($this->_post->web_new_pass!=$this->_post->web_pass_re))
	      	{
	        $message_error = "<campo value=\"".$this->_model->getFieldLabel('web_new_pass')."\">\n";
	        $message_error.= "<error type=\"Pass_NotFound\" message=\"".utf8_encode("Senhas digitadas n�o s�o iguais.")."\" />\n";
	        $message_error.= "</campo>\n";
	        $err_pass = true;
        	}	
	    }

		if (!$form->isValid($_POST) || $err_pass)
			{
			$continue = false;
			if($this->return_ajax)
				{
				$this->_helper->layout->disableLayout();
				$xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
				$xml .= "<root type=\"ValidError\">\n";
				$arrKeys = array_keys($this->_getDataFromPost());
				for($i=0;$i<count($arrKeys);$i++)
					{
					eval('$return = $form->'.$arrKeys[$i].'->getMessages();');
					if(count($return)>0)
						{

						if($continue && count($return)!=0) $continue = false;

						if(isset($return["notInArray"]))
							{
							unset($return["notInArray"]);
							if(count($return)==0)
								{
								$continue = true;
								continue;
								}
							}

						$xml.= "<campo value=\"".$this->_model->getFieldLabel($arrKeys[$i])."\">\n";
						foreach($return as $k => $v)
							$xml.= "<error type=\"{$k}\" message=\"{$v}\" />\n";

							$xml.= "</campo>\n";
						}
					}

				if($err_pass)	$xml .= $message_error;

				$xml .= '</root>';

				if(!$continue)
					{
					header("Content-type:application/xml; charset=utf-8");
					die($xml);
					}
				}
			else
				$this->_redirect('error/valid');
			}
		$data = array();
		$unlockedData = array();
		foreach ($fieldNames as $fieldName)
		{
			$unlockedData[$fieldName] = $this->_model->getCastValue($fieldName,$this->_post->$fieldName);
			if ($this->_model->isLocked($fieldName)) continue;
			$data[$fieldName] = $this->_model->getCastValue($fieldName,$this->_post->$fieldName);
		}

		$this->save($data,$unlockedData);
		if($this->return_ajax)
			{
			$this->_helper->layout->disableLayout();
			$xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			$xml .= "<root type=\"Sucess\">\n";
			$xml .= '</root>';
			header("Content-type:application/xml; charset=utf-8");
			die($xml);
			}
		else
			{
			if($this->redirect)
				$this->_redirect($this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/list');
			}
	}

	/**
	 * Method to save records (insert or update)
	 * @see application/controllers/ICrudController#save()
	 */
	public function save(array $data, array $unlockedData)
	{
		try {
			if (isset($this->_post->Insert) || ($this->_post->action == "insert"))
			{
				$this->_model->insert($data);
			}
			else
			{
				$fieldKey = $this->_model->getFieldKey();
				$this->_model->update($data,"$fieldKey = {$unlockedData[$fieldKey]}");
			}
		}
		catch(Exception $e )
		{
		if($this->return_ajax)
			{
			$this->_helper->layout->disableLayout();
			$xml  = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
			$xml .= "<root type=\"errorMessage\">\n";
			$xml .= "	<message value=\"".utf8_encode($e->getMessage())."\" />\n";
			$xml .= '</root>';
			header("Content-type:application/xml; charset=utf-8");
			die($xml);
			}
		else
			{
			Fgsl_Session_Namespace::set('exception',$e);
			$this->_redirect('error/message');
			}
		}
		return true;
	}

	/**
	 * Method to remove records
	 * @see application/controllers/ICrudController#removeAction()
	 */
	public function removeAction()
	{
		$key = $this->_getParam($this->_model->getFieldKey());

		try {
			$this->_model->delete("{$this->_model->getFieldKey()} = $key");
		} catch (Exception $e) {
			Fgsl_Session_Namespace::set('exception',$e);
			$this->_redirect('error/message');
		}

		$this->_redirect($this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/list');
	}

	/**
	 * Sets attribute $_fieldNames with fieldnames of model
	 * and configures path of alternative view object
	 * (non-PHPdoc)
	 * @see Crud/Controller/Fgsl_Crud_Controller_Interface#_config()
	 */
	public function _config()
	{
		$this->_fieldNames = $this->_model->getFieldNames();

		$controller = ($this->_privateTemplates ? '/'.$this->getRequest()->getControllerName() : '');

		$viewPath = APPLICATION_PATH."/views/scripts".$controller;

		if ($this->_useModules && !$this->_uniqueTemplatesForApp)
			$viewPath = APPLICATION_PATH."/modules/{$this->getRequest()->getModuleName()}/views/scripts".$controller;

		$this->view->addScriptPath($viewPath);
	}

	/**
	 * Gets data sent by method HTTP POST
	 * @param $fieldNames Array
	 * @return unknown_type
	 */
	protected function _getDataFromPost($fieldNames = null)
	{
	  if(is_null($fieldNames))
	    $fieldNames = $this->_fieldNames;
		// gets filtered POST
		$post = Fgsl_Session_Namespace::get('post');

		$data = array();

		if (count($_POST) === 0)
		{
			foreach ($fieldNames as $fieldName)
			{
				$data[$fieldName] = '';
			}
		}
		else
		{
			foreach ($_POST as $fieldName => $value)
			{
				@$data[$fieldName] = $post->$fieldName;
			}
		}
		Fgsl_Session_Namespace::set('post',null);

		return $data;
	}

	/**
	 * Return url to be used in hyperlinks
	 * @return unknown_type
	 */
	public function getUrl()
	{
		$url = $this->_basePath.'/'.$this->_controllerAction;
		if ($this->_useModules)
		{
			$url = $this->_basePath.'/'.$this->_moduleName.'/'.$this->_controllerAction;
		}

		return $url;
	}

	/**
	 * Configure items to be assigned to object view
	 * @return unknown_type
	 */
	public function configureViewAssign()
	{
		$this->view->assign('title',$this->view->escape($this->_title));
		$this->view->assign('table',$this->_table);
		$this->view->assign('insertLink',$this->getUrl().'/insert');
		$this->view->assign('listLink',$this->getUrl().'/list');
		$this->view->assign('returnLink',$this->getUrl().'/list');
		$this->view->assign('menuLink',$this->_menuLink);
		$this->view->assign('currentPage',$this->_currentPage);
		$this->view->assign('lastPage',$this->_lastPage);
		//$this->view->assign('searchField',$this->_model->getSearchField());
		//$this->view->assign('labelSearchField',$this->_model->getFieldLabel($this->_model->getSearchField()));
		$this->view->assign('searchForm',$this->_getSearchForm());
	}

	/**
	 * Builds a search form
	 * @return unknown_type
	 */
	protected function _getSearchForm()
	{
		$form = new Zend_Form();
		$form->setAction($this->getUrl().'/list');
		$form->setMethod('post');

		$elements = array();

		$element = new Zend_Form_Element_Select('key');
		$element->setMultiOptions($this->_searchOptions);
		$elements[] = $element;

		$element = new Zend_Form_Element_Text('value');
		$elements[] = $element;

		foreach($elements as $element)
		{
			$element->addDecorators(array(
				array('HtmlTag', array('tag' => 'td')),
    			array('Label', array('tag' => 'td'))
			));
			$form->addElement($element);
		}
		$element = new Zend_Form_Element_Submit($this->_searchButtonLabel);
		$form->addElement($element);

		return $form;
	}

	/**
	 * save profile info into log
	 * @param $profile
	 * @return unknown_type
	 */
	protected function _logProfile($profile)
	{

	}
}
?>