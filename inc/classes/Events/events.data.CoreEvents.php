<?php
namespace Concise\Events\Data;



##################################
###  CoreEventListener-Events  ###
##################################

// DataCoreEvents

final class DataCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"data"	=> array(
									// list
									'list.get_data_details'				=> array('onGetDataDetails', 0),
									// detail
									'detail.get_data_details'			=> array('onGetDataDetails', 10),
									'detail.assign_data_details'		=> array('onAssignDataDetails', 0),
									'detail.assign_data_objects'		=> array('onAssignDataObjects', 0)
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
