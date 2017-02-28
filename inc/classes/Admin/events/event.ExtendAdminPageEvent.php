<?php
namespace Concise\Events\Admin;

use Symfony\Component\EventDispatcher\Event;


/**
 * Klasse ExtendAdminPageEvent
 *
 * Erweitert Adminbereich
 * 
 * 
 */

class ExtendAdminPageEvent extends event
{

	public $DB				= null;
	public $o_lng			= null;
	public $o_admin			= null;
	public $lang			= "";
	public $adminTask		= "";
	public $adminType		= "";
	public $editId			= "";
	public $cssFiles		= array();
	public $scriptFiles		= array();
	public $scriptCode		= array();
	public $html5			= false;
	protected $output		= "";
	
	public function __construct($DB, $o_lng, $admin, $adminTask, $adminType)
    {
	
		$this->DB			= $DB;
		$this->o_lng		= $o_lng;
		$this->o_admin		= $admin;
		$this->lang			= $this->o_lng->lang;
		$this->adminTask	= $adminTask;
		$this->adminType	= $adminType;
		$this->editId		= $admin->editId;
		
    }
 
	public function __get($name)
    {
	
		if(!isset($name))
			return ""; // Überladung, falls neue Vars bei Aufruf durch Plugin
	
	}

    public function getOutput($reset = false)
    {

		$output		= $this->output;
		
		if($reset)
			$this->output = "";
		
		return $output;
	
	}
 
    public function setOutput($out)
    {

		return $this->output	= $out;
	
	}
 
    public function addOutput($out)
    {

		return $this->output	.= $out;
	
	}
 
    public function getResult()
    {

		return $this->result;
	
	}
 
    public function setResult($res)
    {

		return $this->result	= $res;
	
	}

}
