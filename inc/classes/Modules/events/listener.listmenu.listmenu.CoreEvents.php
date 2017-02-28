<?php
namespace Concise\Events\Listmenu;

use Symfony\Component\EventDispatcher\Event;
use Concise\ContentsEngine;
use Concise\Modules;



##############################
###  EventListener-Klasse  ###
##############################

// ListmenuCoreEventsListener

class ListmenuCoreEventsListener
{	

	// onGetMenuHead
	public function onGetMenuHead(Event $event)
	{	
	
		// Menu head
		$menuOutput	= "";
		$uniqueID	= uniqid();

		// Toggle for mobile navigation, targeting the <ul>		
		// Falls Bootstrap
		if($event->framework == "bootstrap") {
		
			$event->hasChildClass	.=	" {t_class:subnav}";
			$event->dropdownSub		=	" {t_class:ddsubmenu}";
			$event->dropdownClass	=	"{t_class:ddmenu}";
			$event->dropdownExt		=	'<a href="#" class="{t_class:ddtoggle}" data-context="navbar" data-toggle="dropdown" role="button" aria-expanded="false">{menutitle} <span class="{caret}"></span></a>' . PHP_EOL;
			$event->dropdownOpen	=	'<ul class="' . $event->dropdownClass . '">' . PHP_EOL;
			$event->navbarClose		=	'</ul>' . PHP_EOL;
			
			if($event->navClose !== false)
				$event->navClose		=	'</div>' . PHP_EOL .
											'</div>' . PHP_EOL;

			// Falls HTML5, nav-Tag einfügen
			if(!$event->html5) {
				$menuOutput .=	'<div id="' . $event->menuType . 'Nav" class="' . $event->navClass . '" role="navigation">' . PHP_EOL;
				if($event->navClose !== false)
					$event->navClose	.=	'</div>' . PHP_EOL;
			}
			
			// Container
			$menuOutput .=	'<div class="{t_class:container' . ($event->menuFixed ? '' : 'fl') . '}">' . PHP_EOL;
			
			// Navbar header
			if($event->collapsibleMenu
			|| $event->menuLogo
			) {
			
				$menuOutput .=	'<div class="navbar-header">' . PHP_EOL;
				
				if($event->collapsibleMenu)
					$menuOutput .=	'<button type="button" class="{t_class:navtoggle}" data-context="navbar" data-toggle="collapse" data-target="#navbar-collapse-' . $event->menuType . 'Nav-' . $uniqueID . '">' . PHP_EOL .
									'<span class="sr-only">Toggle navigation</span>' . PHP_EOL .
									'<span class="icon-bar"></span>' . PHP_EOL .
									'<span class="icon-bar"></span>' . PHP_EOL .
									'<span class="icon-bar"></span>' . PHP_EOL .
									'</button>' . PHP_EOL;
				
				// Ggf. Logo einfügen
				if($event->menuLogo
				&& file_exists(PROJECT_DOC_ROOT . '/' . CC_SITE_LOGO)
				) {
				
					$menuOutput .=	'<a class="navbar-brand" href="' . $event->indexPageUrl . '">' .
									'<img class="navbar-brand-logo" src="' . PROJECT_HTTP_ROOT . '/' . CC_SITE_LOGO . '" alt="Logo ' . str_replace(array("http://", "https://", "www."), "", PROJECT_HTTP_ROOT) . '" />' .
									'</a>' . PHP_EOL;
				}

				$menuOutput .=	'</div>' . PHP_EOL;
			}
			
			if($event->collapsibleMenu)
				$menuOutput .=	'<div class="collapse navbar-collapse" id="navbar-collapse-' . $event->menuType . 'Nav-' . $uniqueID . '">' . PHP_EOL;
			else
				$menuOutput .=	'<div class="navbar-expanded" id="navbar-expanded-' . $event->menuType . 'Nav">' . PHP_EOL;
			
			// Lang menu
			if($event->langMenu
			&& $event->langDiv != ""
			)
				$menuOutput .=	$event->langDiv;
			
			$menuOutput .=	'<ul id="' . $event->menuType . '_menu" class="' . $event->navbarClass . ($event->menuAlign ? ' {t_class:nav' . ($event->menuAlign == 2 ? 'rgt' : ($event->menuAlign == 3 ? 'cen' : 'lft')) . '}' : '') . '">' . PHP_EOL;  // Ausgabe des Menues mit Listen-id = Tabellenname
		}
		
		// Falls Foundation
		if($event->framework == "foundation") {
		
			$event->hasChildClass		.=	" has-dropdown" . ($event->groupSubmenuItems ? ' not-click' : '');
			$event->dropdownClass		=	"dropdown";
			$event->dropdownOpen		=	'<ul class="' . $event->dropdownClass . '">'.PHP_EOL;
			$event->navbarClose			=	'</ul>' . PHP_EOL;
			
			if($event->navClose !== false)
				$event->navClose			=	'</section>' . PHP_EOL;

			// Falls nicht HTML5, div als nav-Tag einfügen
			if(!$event->html5) {
				$menuOutput .=	'<div id="' . $event->menuType . 'Nav" class="' . $event->navClass . '" role="navigation" data-topbar>' . PHP_EOL;
				if($event->navClose !== false)
					$event->navClose		.=	'</div>' . PHP_EOL;
			}
			
			// Navbar header
			if($event->collapsibleMenu
			|| $event->menuLogo
			) {
			
				$menuOutput .=	'<ul class="title-area">' . PHP_EOL;
				
				// Ggf. Logo einfügen
				if($event->menuLogo
				&& file_exists(PROJECT_DOC_ROOT . '/' . CC_SITE_LOGO)
				) {
				
					$menuOutput .=	'<li class="name">' . PHP_EOL;
									#'<h1><a href="#">My Site</a></h1>' . PHP_EOL .
					$menuOutput .=	'<a class="navbar-brand" href="' . $event->indexPageUrl . '">' .
									'<img class="navbar-brand-logo" src="' . PROJECT_HTTP_ROOT . '/' . CC_SITE_LOGO . '" alt="Logo ' . str_replace(array("http://", "https://", "www."), "", PROJECT_HTTP_ROOT) . '" />' .
									'</a>' . PHP_EOL;
					$menuOutput .=	'</li>' . PHP_EOL;
				}
			
				if($event->collapsibleMenu)
					$menuOutput .=	'<li class="{t_class:navtoggle}"><a href="#"><span>&nbsp;</span></a></li>' . PHP_EOL;
				
				$menuOutput .=	'</ul>' . PHP_EOL;
			}
			
			// Lang menu
			if($event->langMenu
			&& $event->langDiv != ""
			)
				$menuOutput .=	$event->langDiv;
			
			$menuOutput .=	'<section class="' . $event->navbarClass . '">' . PHP_EOL;
			$menuOutput .=	'<ul class="right">' . PHP_EOL;  // Ausgabe des Menues mit Listen-id = Tabellenname
		}
		
		// Falls Gumby
		if($event->framework == "gumby") {
		
			$event->dropdownClass	=	"dropdown";
			$event->dropdownOpen	=	PHP_EOL . '<' . $event->dropdownTag . ' class="' . $event->dropdownClass . '">' . PHP_EOL . '<ul>'.PHP_EOL;
			$event->dropdownClose	=	'</ul></' . $event->dropdownTag . '>'.PHP_EOL;
			$event->navbarClose		=	'</ul>' . PHP_EOL;
			
			// Falls nicht HTML5, div als nav-Tag einfügen
			if(!$event->html5) {
				if($event->navClose !== false)
					$event->navClose	=	'</div>' . PHP_EOL;
				$menuOutput .=	'<div id="' . $event->menuType . 'Nav" class="' . $event->navClass . '" role="navigation">' . PHP_EOL;
			}
			
			// Navbar header
			if($event->collapsibleMenu
			|| $event->menuLogo
			) {
				
				// Ggf. Logo einfügen
				if($event->menuLogo
				&& file_exists(PROJECT_DOC_ROOT . '/' . CC_SITE_LOGO)
				) {
				
					$menuOutput .=	'<a class="navbar-brand" href="' . $event->indexPageUrl . '">' .
									'<img class="navbar-brand-logo" src="' . PROJECT_HTTP_ROOT . '/' . CC_SITE_LOGO . '" alt="Logo ' . str_replace(array("http://", "https://", "www."), "", PROJECT_HTTP_ROOT) . '" />' .
									'</a>' . PHP_EOL;
				}
			
				if($event->collapsibleMenu)
					$menuOutput .=	'<a class="{t_class:navtoggle}" gumby-trigger="#' . $event->menuType . 'Nav > ul" href="#"><i class="icon-menu"></i></a>' . PHP_EOL;
			}
			
			// Lang menu
			if($event->langMenu
			&& $event->langDiv != ""
			)
				$menuOutput .=	$event->langDiv;
			
			$menuOutput .=	'<ul id="' . $event->menuType . '_menu" class="' . $event->navbarClass . '">' . PHP_EOL;  // Ausgabe des Menues mit Listen-id = Tabellenname
		}
		
		$event->setOutput($menuOutput, true);
		
		return $menuOutput;
	
	}

} // Ende class
