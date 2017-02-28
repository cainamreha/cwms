<?php
namespace Concise\Events\Adminmain;

use Symfony\Component\EventDispatcher\Event;
use Concise\ContentsEngine;
use Concise\Admin_Main;



##############################
###  EventListener-Klasse  ###
##############################

// GlobalCoreEventsListener

class GlobalCoreEventsListener extends Admin_Main
{	
	
	public function __construct()
	{
		
	}
	
	// onRegisterHeadFiles
	public function onRegisterHeadFiles(Event $event)
	{
	
		$event->cssFiles[]	= 'system/inc/admintasks/main/css/adminMain.min.css';
		
		return true;
	
	}
	
	// onGetMainContents
	public function onGetMainContents(Event $event)
	{
		
		$output	= "";
		
		$output	.= '
		<nav class="quick-menu">
		  <input type="checkbox" href="#" class="quick-menu-open" name="quick-menu-open" id="quick-menu-open" />
		  <label class="quick-menu-open-button" for="quick-menu-open">
			<span class="cc-admin-icons cc-icons cc-icon-rocket"></span>
			<span class="hamburger hamburger-1"></span>
			<span class="hamburger hamburger-2"></span>
			<span class="hamburger hamburger-3"></span>
		  </label>  
		  <a href="' . ADMIN_HTTP_ROOT . '?task=new" class="quick-menu-item" title="{s_nav:adminnew}" data-ajax="true"> <span class="cc-admin-icons cc-icons cc-icon-plus"></span> </a>
		  <a href="' . ADMIN_HTTP_ROOT . '?task=tpl" class="quick-menu-item" title="{s_nav:admintpl}" data-ajax="true"> <span class="cc-admin-icons cc-icons cc-icon-leaf"></span> </a>
		  <a href="' . ADMIN_HTTP_ROOT . '?task=modules&type=planner" class="quick-menu-item" title="{s_nav:adminplanner}" data-ajax="true"> <span class="cc-admin-icons cc-icons cc-icon-planner"></span> </a>
		  <a href="' . ADMIN_HTTP_ROOT . '?task=modules&type=articles" class="quick-menu-item" title="{s_nav:adminarticles}" data-ajax="true"> <span class="cc-admin-icons cc-icons cc-icon-articles"></span> </a>
		  <a href="' . ADMIN_HTTP_ROOT . '?task=modules&type=news" class="quick-menu-item" title="{s_nav:adminnews}" data-ajax="true"> <span class="cc-admin-icons cc-icons cc-icon-news"></span> </a>
		  <a href="' . ADMIN_HTTP_ROOT . '?task=edit" class="quick-menu-item" title="{s_nav:adminedit}" data-ajax="true"> <span class="cc-admin-icons cc-icons cc-icon-pencil"></span> </a>
		</nav>
		<!-- filters -->
		<svg xmlns="http://www.w3.org/2000/svg" version="1.1">
			<defs>
			  <filter id="shadowed-goo">				  
				  <feGaussianBlur in="SourceGraphic" result="blur" stdDeviation="10" />
				  <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="goo" />
				  <feGaussianBlur in="goo" stdDeviation="3" result="shadow" />
				  <feColorMatrix in="shadow" mode="matrix" values="0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 1 -0.2" result="shadow" />
				  <feOffset in="shadow" dx="1" dy="1" result="shadow" />
				  <feComposite in2="shadow" in="goo" result="goo" />
				  <feComposite in2="goo" in="SourceGraphic" result="mix" />
			  </filter>
			  <filter id="goo">
				  <feGaussianBlur in="SourceGraphic" result="blur" stdDeviation="10" />
				  <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="goo" />
				  <feComposite in2="goo" in="SourceGraphic" result="mix" />
			  </filter>
			</defs>
		</svg>' . PHP_EOL;

		$event->addOutput($output);
		
		return $output;

	}
	
	// onGetRightbarContents
	public function onGetRightbarContents(Event $event)
	{
	
		
	
	}

} // Ende class
