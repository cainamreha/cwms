<?php
namespace Concise\Events\Adminplanner;



##################################
###  CoreEventListener-Events  ###
##################################

// AdminplannerCoreEvents

final class AdminplannerCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"adminplanner"	=> array(
									// data
									'data.eval_data_post'			=> array('onEvalDataPost', 0),
									'data.eval_newdata_post'		=> array('onEvalNewdataPost', 0),
									'data.get_data_attributes'		=> array('onGetDataAttributes', 0),
									'data.get_data_fields'			=> array('onGetDataFieldsPre', 10),
									'data.get_newdata_fields'		=> array('onGetNewdataFieldsPre', 10)
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
