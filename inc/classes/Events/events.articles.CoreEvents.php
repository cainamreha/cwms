<?php
namespace Concise\Events\Articles;



##################################
###  CoreEventListener-Events  ###
##################################

// ArticlesCoreEvents

final class ArticlesCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"articles"	=> array(
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
