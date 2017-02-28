<?php
namespace Concise\Events\Adminnews;



##################################
###  CoreEventListener-Events  ###
##################################

// AdminnewsCoreEvents

final class AdminnewsCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"adminnews"	=> array(
									// data cat
									'cat.eval_cat_post'				=> array('onEvalCatPost', 0),
									'cat.make_cat_dbstring'			=> array('onMakeCatDbstring', 0),
									'cat.reset_cat_attributes'		=> array('onResetCatAttributes', 0),
									'cat.get_cat_fields'			=> array(
																		array('onGetCatFieldsMid', 10),
																		array('onGetCatFieldsPost', 0)
																	),
									'cat.get_goeditcat_fields'		=> array('onGetGoeditcatFields', 0),
									// data
									'data.eval_data_post'			=> array('onEvalDataPost', 0),
									'data.eval_newdata_post'		=> array('onEvalNewdataPost', 0),
									'data.get_data_attributes'		=> array('onGetDataAttributes', 0)
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
