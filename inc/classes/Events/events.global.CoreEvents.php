<?php
namespace Concise\Events;



##################################
###  CoreEventListener-Events  ###
##################################

// GlobalCoreEvents

final class GlobalCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"global"	=> array(
									'global.add_head_code'				=>	array(
																				array('onAddHeadCodePre', 10),
																				array('onAddHeadCodeMid', 5),
																				array('onAddHeadCodePost', 0)
																			),
									'global.register_head_files'		=>	array('onRegisterHeadFiles', 0)
								)
							);

	/**
	 * Listener path
	 *
	 * @array	string	$path path
	 * @access	public
	 */
	public static $path = 'inc/classes/Events';

} // Ende class
