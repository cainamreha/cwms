<?php
namespace Concise\Events\Admin;

use Symfony\Component\EventDispatcher\Event;


##################################
###  CoreEventListener-Events  ###
##################################

// AdminCoreEvents

final class AdminCoreEvents extends Event
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"admin"	=> array(
									'global.register_head_files'		=> array('onRegisterHeadFiles', 0),
									'global.get_rightbar_contents'		=> array('onGetRightbarContents', 0)
								)
							);

	/**
	 * Listener path
	 *
	 * @array	string	$path path
	 * @access	public
	 */
	public static $path = 'inc/classes/Admin/events';

} // Ende class
