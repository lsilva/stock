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
 * @package    Fgsl_Db
 * @subpackage Fgsl_Db_Table
 * @copyright  Copyright (c) 2009 Flávio Gomes da Silva Lisboa (http://www.fgsl.eti.br)
 * @license   New BSD License
 * @version    0.0.2
 */

/**
 * Fgsl_Db_Table_Abstract
 */
abstract class Fgsl_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
	const INT_TYPE = 0;
	const FLOAT_TYPE = 1;
	const BOOLEAN_TYPE = 2;
	const ARRAY_TYPE = 3;
	const OBJECT_TYPE = 4;

	/**
	 * Set these attributes
	 * @var unknown_type
	 */
	protected $_fieldKey;
	protected $_fieldNames = array();
	protected $_fieldOptions = array();
	protected $_fieldLabels = array();
	protected $_orderField;
	protected $_searchField;
	protected $_selectOptions = array();
	protected $_typeElement = array();
	protected $_typeValue = array();
	protected $_lockedFields = array();

	/**
	 * That is a generic handler for dynamic getters and setters.
	 * It catches calls as setAttribute($value) or getAttribute().
	 * @param string $name
	 * @param array $arguments
	 * @return unknown_type
	 */
	public function __call($name,$arguments)
	{
		$prefix = substr($name,0,3);

		$name = substr($name,3);
		$name = strtolower(substr($name,0,1)).substr($name,1);

		if ($prefix == 'set')
		{
			$this->$name = $arguments[0];
		}
		if ($prefix == 'get')
		{
			return $this->$name;
		}
	}

	/**
	 * Returns value of primary key from an array with fields data.
	 * @param array $data
	 * @return unknown_type
	 */
	public function getKeyValue(array $data)
	{
		return @$data[$this->getFieldKey()];
	}

	/**
	 * Returns type of HTML component that renders the content of attribute.
	 * It needs that attribute $_typeElement is set.
	 * @return unknown_type
	 */
	public function getTypeElement($fieldName)
	{
		if (!isset($this->_typeElement[$fieldName]))
		{
			return Fgsl_Form_Constants::TEXT;
		}
		return $this->_typeElement[$fieldName];
	}

	/**
	 * Returns label that appears before HTML field.
	 * @return unknown_type
	 */
	public function getFieldLabel($fieldName)
	{
		return isset($this->_fieldLabels[$fieldName]) ? $this->_fieldLabels[$fieldName] : 'No label found';
	}
	/**
	* Retorna todos os fieldLabels
	* @return Array
	*/
	public function getFieldLabels()
	{
		return $this->_fieldLabels;
	}
	/**
	 * Returns options of a HTML SELECT
	 * @return unknown_type
	 */
	public function getSelectOptions($fieldName)
	{
		return $this->_selectOptions[$fieldName];
	}

	/**
	 * Returns content of a field cast to specified type.
	 * String is default and it don't need to be defined.
	 * @return unknown_type
	 */
	public function getCastValue($fieldName,$value)
	{
		if (!isset($this->_typeValue[$fieldName]))
		{
			return $value;
		}
		else
		{
			switch($this->_typeValue[$fieldName])
			{
				case self::INT_TYPE:
					return (int) $value;
					break;
				case self::FLOAT_TYPE:
					return (float) $value;
					break;
				case self::BOOLEAN_TYPE:
					return (boolean) $value;
					break;
				case self::ARRAY_TYPE:
					return (array) $value;
					break;
				case self::OBJECT_TYPE:
					return (object) $value;
					break;
				default:
					return $value;
			}
		}
	}

	/**
	 * Return primary key
	 * @param $dados
	 * @return unknown_type
	 */
	public function getFieldKey()
	{
		return $this->_fieldKey;
	}

	/**
	 * Returns table field names
	 * to be used to modify and remove records.
	 * @return unknown_type
	 */
	public function getFieldNames()
	{
		return $this->_fieldNames;
	}

	public function getFieldOptions($fieldName)
		{
		if (!isset($this->_fieldOptions[$fieldName]))
			return false;

		return $this->_fieldOptions[$fieldName];
		}

	/**
	 * Returns default search field.
	 * @return unknown_type
	 */
	public function getSearchField()
	{
		return $this->_searchField;
	}

	/**
	 * Returns default field to sorting.
	 * @return unknown_type
	 */
	public function getOrderField()
	{
		return $this->_orderField;
	}

	/**
	 * Forces relationships in a array with model data.
	 * Must be implemented.
	 * @return unknown_type
	 */
	public function setRelationships(array &$records)
	{

	}

	/**
	 * Indicates if a field is locked to edit
	 * @param $fieldName
	 * @return unknown_type
	 */
	public function isLocked($fieldName)
	{
		return in_array($fieldName,$this->_lockedFields);
	}
	/**
	 *
	 * Adiciona um campo locked em tempo de execu��o
	 * @param String $fieldName
	 * @return void
	 */

	public function addLockedField($fieldName)
		{
		$this->_lockedFields[]=$fieldName;
		}
	/**
	 * Assembles a custom SQL SELECT statement
	 * @param string $where
	 * @param string $order
	 * @param string $limit
	 * @return Zend_Db_Table_Rowset
	 */
	public function getCustomSelect($where,$order,$limit)
	{
		$select = $this->select();
		if ($where !== null)
		{
			$select->where($where);
		}
		$select->order($order);
		$select->limit($limit);
		return $select;
	}
	/**
	 * Retorna um array contendo os dados da chave passada
	 * @param string $key
	 * @return array
	 */
	public function getData($key)
		{
		$result = $this->fetchRow($this->_fieldKey.'=\''.$key.'\'');
		if(!$result)
			return false;

		return $result->toArray();
		}
	/**
	 * Retorna um array contendo os dados do cliente passado
	 * @param string $key
	 * @return array
	 */
	public function getDataByCliId($cli_id)
		{
		$result = $this->fetchRow('cli_id=\''.$cli_id.'\'');
		if(!$result)
			return false;

		return $result->toArray();
		}
	/**
	 * Returns a array with a data rowset
	 * @param unknown_type $select
	 * @return unknown_type
	 */
	public function fetchAllAsArray($select)
	{
		if ($select instanceof Zend_Db_Select)
		{
			return $this->getAdapter()->fetchAll($select);
		}
		if ($select instanceof Zend_Db_Table_Select)
		{
			$rowSet = $this->fetchAll($select);
			return $rowSet->toArray();
		}
	}
	/**
	 * Retorna o pr�ximo ID da chave prim�ria da tabela
	 * @param String $where : cli_id='integer' //Chave prim�ria principal
	 * @return Integer
	 */
  public function nextID($where="")
    {
    $select = $this->getAdapter()->select();
    $select->from(self::$this->_name,array("max({$this->_fieldKey}) AS MAX"));
    if($where!="")
      $select->where($where);
    $record = $this->fetchAllAsArray($select);

    return $record[0]["MAX"]+1;
    }

  /**
   * Valida e retorna a data independente se for no formato YYYY-MM-DD ou DD/MM/YYYY
   * para o formato YYYY-MM-DD para que seja inserida no banco de forma correta.
   *
   *  Caso a data seja inválida irá retornar 0000-00-00
   *
   * @param Date $date
   * @return Date
   */
  function dateOnDB($date)
    {
    $arrTemp = explode("/",$date);
    if(count($arrTemp) < 3)
      $arrTemp = explode("-",$date);
    else
      //Altera a ordem do array caso a data passada seja padrão PT-BR
      $arrTemp = array($arrTemp[2],$arrTemp[1],$arrTemp[0]);

    if(count($arrTemp) == 3 && checkdate($arrTemp[1], $arrTemp[2], $arrTemp[0]))
      $date = "{$arrTemp[0]}-{$arrTemp[1]}-{$arrTemp[2]}";
    else
      $date = "0000-00-00";

    return $date;
    }
  /**
   * Retorna o nome da tabela.
   */
  function getName()
    {
    return self::$this->_name;
    }
}