<?php
namespace Concise\Events\Adminmain;



##################################
###  CoreEventListener-Events  ###
##################################

// AdminmainCoreEvents

final class AdminmainCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"adminmain"	=> array(
									// global
									'global.register_head_files'		=> array('onRegisterHeadFiles', 0),
									'global.get_main_contents'			=> array('onGetMainContents', 0),
									'global.get_rightbar_contents'		=> array('onGetRightbarContents', 100)
								)
							);

	/**
	 * Listener path
	 *
	 * @array	string	$path path
	 * @access	public
	 */
	public static $path = 'system/inc/admintasks/main/events';

} // Ende class
