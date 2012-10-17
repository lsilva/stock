<?php
class PagamentoForma extends Fgsl_Db_Table_Abstract
	{
	protected $_name = 'pagamento_forma';
	public function __construct()
		{
		parent::__construct();
		$this->_fieldKey = 'pgt_id';
		$this->_fieldNames = $this->_getCols();
		$this->_fieldLabels = array(
				'pgt_id' => 'ID forma de pagamento',
		  	'pgt_nome' => 'Forma de pagamento'
			);

	    //Seta atributos especias para os fields
	    $this->_fieldOptions = array();
	    $this->_fieldOptions['pgt_nome'] = array(
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

		$this->_lockedFields = array('pgt_id');
		$this->_orderField = 'pgt_nome';
		$this->_searchField = 'pgt_nome';
    $this->_typeElement = array(
			'pgt_id' => Fgsl_Form_Constants::HIDDEN,
  		'pgt_nome' => Fgsl_Form_Constants::TEXT
		  );
		}
	}