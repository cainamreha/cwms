<?php
namespace Concise\Events;

use Symfony\Component\EventDispatcher\Event;


/**
 * Klasse UserAuthentificationEvent
 *
 * Erweitert Head code
 * 
 * 
 */

class UserAuthentificationEvent extends event
{

	public $DB				= null;
	public $o_lng			= null;
	protected $loginUser	= false;
	protected $redirectUser	= false;
	protected $userDetails	= array();
	protected $checkScript	= "";
	protected $output		= "";
	
	public function __construct($DB, $o_lng)
    {
	
		$this->DB			= $DB;
		$this->o_lng		= $o_lng;
		
    }
 
	public function __get($name)
    {
	
		if(!isset($name))
			return ""; // Überladung, falls neue Vars bei Aufruf durch Plugin
	
	}

    public function getUserDetails($reset = false)
    {

		$output		= $this->userDetails;
		
		if($reset)
			$this->userDetails = "";
		
		return $output;
	
	}

    public function setUserDetails($userDetails)
    {

		$this->userDetails = $userDetails;		
		return true;
	
	}
 
    public function checkLoginUser()
    {

		return $this->loginUser;
	
	}

    public function setLoginUser($loginUser)
    {

		$this->loginUser = $loginUser;		
		return true;
	
	}
 
    public function getRedirectUser()
    {

		return $this->redirectUser;
	
	}
 
    public function setRedirectUser($redirectUser)
    {

		$this->redirectUser = $redirectUser;		
		return true;
	
	}
 
    public function getCheckScript()
    {

		return $this->checkScript;
	
	}
 
    public function setCheckScript($script)
    {

		$this->checkScript = $script;		
		return true;
	
	}
 
    public function getOutput()
    {

		return $this->output;
	
	}
 
    public function setOutput($output)
    {

		$this->output = $output;		
		return true;
	
	}

}
