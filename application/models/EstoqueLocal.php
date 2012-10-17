<?php
class EstoqueLocal extends Fgsl_Db_Table_Abstract
	{
	protected $_name = 'estoque_local';
	public function __construct()
		{
		parent::__construct();
		$this->_fieldKey = 'stq_id';
		$this->_fieldNames = $this->_getCols();
		$this->_fieldLabels = array(
				'stq_id' => 'ID do local de estoque',
		  	'stq_nome' => 'Descrição do local de estoque'
			);

	    //Seta atributos especias para os fields
	    $this->_fieldOptions = array();
	    $this->_fieldOptions['stq_nome'] = array(
	      'addValidator'=>array('NotEmpty'),
	      'setRequired'=>true,
	      'setAttrib'=>array('maxLength'=>'256', 'required'=>'true')
	      );
		//Insere atributo title para os campos setando o nome dos labels
		foreach($this->_fieldLabels as $key => $value)
			{
			if(isset($this->_fieldOptions[$key]) && isset($this->_fieldOptions[$key]['setAttrib']))
				$this->_fieldOptions[$key]['setAttrib']['title'] = $value;
			else
				$this->_fieldOptions[$key] = array('setAttrib'=>array('title'=>$value));
			}

		$this->_lockedFields = array('stq_id');
		$this->_orderField = 'stq_nome';
		$this->_searchField = 'stq_nome';
    $this->_typeElement = array(
			'stq_id' => Fgsl_Form_Constants::HIDDEN,
  		'stq_nome' => Fgsl_Form_Constants::TEXT
		  );
		}
	}