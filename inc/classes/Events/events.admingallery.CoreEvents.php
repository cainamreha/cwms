<?php
namespace Concise\Events\Admingallery;



##################################
###  CoreEventListener-Events  ###
##################################

// AdmingalleryCoreEvents

final class AdmingalleryCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"admingallery"	=> array(
									// global
									'global.register_head_files'		=> array('onRegisterHeadFiles', 0),
									'global.get_rightbar_contents'		=> array('onGetRightbarContents', 0),
								)
							);

	/**
	 * Listener path
	 *
	 * @array	string	$path path
	 * @access	public
	 */
	public static $path = 'system/inc/admintasks/modules/events';

} // Ende class
