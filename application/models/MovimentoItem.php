<?php
class MovimentoItem extends Fgsl_Db_Table_Abstract
	{
	protected $_name = 'movimento_item';
	public function __construct()
		{
		parent::__construct();
		$this->_fieldKey = 'mov_codigo';
		$this->_fieldNames = $this->_getCols();
		$this->_fieldLabels = array(
      'mov_codigo' => 'ID do movimento',
      'item_codigo' => 'ID do item',
      'pro_codigo' => 'ID do produto',
      'item_quantidade' => 'quantidade',
      'item_valor' => 'Valor do item'
			);
    //Seta atributos especias para os fields
    $this->_fieldOptions = array();
		//Insere atributo title para os campos setando o nome dos labels
		foreach($this->_fieldLabels as $key => $value)
			{
			if(isset($this->_fieldOptions[$key]) && isset($this->_fieldOptions[$key]['setAttrib']))
				$this->_fieldOptions[$key]['setAttrib']['title'] = $value;
			else
				$this->_fieldOptions[$key] = array('setAttrib'=>array('title'=>$value));
			}
		//$this->_lockedFields = array('tmv_id');
		$this->_orderField = 'mov_codigo';
		$this->_searchField = 'mov_codigo';

    $this->_typeElement = array(
      'mov_codigo' => Fgsl_Form_Constants::HIDDEN,
      'item_codigo' => Fgsl_Form_Constants::TEXT,
      'pro_codigo' => Fgsl_Form_Constants::TEXT,
      'item_quantidade' => Fgsl_Form_Constants::TEXT,
      'item_valor' => Fgsl_Form_Constants::TEXT
		  );
		}

	public function insert(array $data)
		{
		parent::insert($data);
		}

	public function update(array $data, $where)
		{
		parent::update($data,$where);
		}
	}