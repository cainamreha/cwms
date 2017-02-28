<?php
namespace Concise\Events\Listmenu;



##################################
###  CoreEventListener-Events  ###
##################################

// ListmenuCoreEvents

final class ListmenuCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"listmenu"	=> array(
									// menu
									'listmenu.get_menu_head'				=> array('onGetMenuHead', 0)
								)
							);

	/**
	 * Listener path
	 *
	 * @array	string	$path path
	 * @access	public
	 */
	public static $path = 'inc/classes/Modules/events';

} // Ende class
