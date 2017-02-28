<?php
namespace Concise\Events\Planner;



##################################
###  CoreEventListener-Events  ###
##################################

// PlannerCoreEvents

final class PlannerCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"planner"	=> array(
									// list
									'list.get_data_details'				=> array('onGetDataDetails', 0),
									// detail
									'detail.get_data_details'			=> array('onGetDataDetails', 0),
									'detail.assign_data_details'		=> array('onAssignDataDetails', 0)
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
