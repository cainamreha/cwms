<?php
namespace Concise\Events;

use Symfony\Component\EventDispatcher\Event;


/**
 * Klasse CreateFormFieldEvent
 *
 * Generiert Formularfelder je nach Typ
 * 
 * 
 */

class CreateFormFieldEvent extends Event
{

	public $fieldOutput			= "";
	public $field				= "";
	public $tablename			= "";
	public $configArray			= array();
	public $attribute			= array();
	public $attributename		= "";
	public $nullAllowed			= "";
	public $required			= false;
	public $fieldType			= "";
	public $fieldName			= "";
	public $fieldVal			= "";
	public $fieldSel			= "";
	public $dateExt				= "";
	public $fieldMaxLen			= "";
	public $fieldTitle			= "";
	public $fieldClass			= "";
	public $prevField			= "";
	public $prevValue			= "";
	public $recipients			= array();
	public $formInputArrayDB	= array();
	public $formInputArray		= array();
	public $result				= "";
	
	public function __construct()
    {
		
    }
 
    public function getFieldOutput()
    {

		return $this->fieldOutput;
	
	}
 
    public function getFieldName()
    {

		return $this->fieldName;
	
	}
 
    public function getFieldVal()
    {

		return $this->fieldVal;
	
	}
 
    public function getPrevField()
    {

		return $this->prevField;
	
	}
 
    public function getPrevVal()
    {

		return $this->prevValue;
	
	}
 
    public function setResult($res)
    {

		return $this->result	= $res;
	
	}
 
    public function getResult()
    {

		return $this->result;
	
	}

}
