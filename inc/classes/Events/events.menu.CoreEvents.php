<?php
namespace Concise\Events\Menu;



##################################
###  CoreEventListener-Events  ###
##################################

// MenuCoreEvents

final class MenuCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"menu"	=> array(
									// menu
									'menu.get_menu_head'				=> array('onGetMenuHead', 0)
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
