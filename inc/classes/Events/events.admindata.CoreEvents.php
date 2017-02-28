<?php
namespace Concise\Events\Admindata;



##################################
###  CoreEventListener-Events  ###
##################################

// AdmindataCoreEvents

final class AdmindataCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"admindata"	=> array(
									// global
									'global.register_head_files'		=> array('onRegisterHeadFiles', 0),
									'global.get_rightbar_contents'		=> array('onGetRightbarContents', 100),
									// data cat
									'cat.get_cat_fields'			=> array(
																		array('onGetCatFieldsMid', 5),
																		array('onGetCatFieldsPost', 0)
																	),
									'cat.get_goeditcat_fields'		=> array('onGetGoeditcatFields', 0),
									// data
									'data.eval_data_post'			=> array('onEvalDataPost', 0),
									'data.eval_newdata_post'		=> array('onEvalNewdataPost', 0),
									'data.get_data_attributes'		=> array('onGetDataAttributes', 0),
									'data.get_data_fields'			=> array(
																		array('onGetDataFieldsPre', 10),
																		array('onGetDataFieldsMid', 5),
																		array('onGetDataFieldsPost', 0)
																	),
									'data.get_newdata_fields'		=> array(
																		array('onGetNewdataFieldsPre', 10),
																		array('onGetNewdataFieldsMid', 5),
																		array('onGetNewdataFieldsPost', 0)
																	),
									'data.get_dataheader_fields'	=> array('onGetDataheaderFields', 0),
									'data.get_dataheader_listfields'=> array('onGetDataheaderListfields', 0)
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
