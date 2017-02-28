<?php
namespace Concise\Events\Fe;



##################################
###  CoreEventListener-Events  ###
##################################

// FeCoreEvents

final class FeCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"fe"	=> array(
									// global
									'global.register_head_files_fe'		=>	array('onRegisterHeadFilesFE', 0),
									'global.add_head_code_fe'			=>	array(
																				array('onAddHeadCodeFEPre', 10),
																				array('onAddHeadCodeFEMid', 5),
																				array('onAddHeadCodeFEPost', 0)
																			),
									'global.extend_styles_fe'			=>	array('onExtendStylesFE', 0),
									'global.extend_element_fe'			=>	array('onExtendElementFE', 0)
								)
							);

	/**
	 * Listener path
	 *
	 * @array	string	$path path
	 * @access	public
	 */
	public static $path = 'inc/classes/Contents/events';

} // Ende class
