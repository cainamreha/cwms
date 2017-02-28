<?php
namespace Concise\Events\Adminedit;



##################################
###  CoreEventListener-Events  ###
##################################

// AdmineditCoreEvents

final class AdmineditCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"adminedit"	=> array(
									// global
									'global.register_head_files'		=> array('onRegisterHeadFiles', 100),
									'global.get_rightbar_contents'		=> array('onGetRightbarContents', 100),
									// edit
									'edit.eval_edit_post'				=> array('onEvalEditPost', 100),
									'edit.get_settings_fields'			=> array('onGetSettingsFields', 100),
									'edit.get_styles_fields'			=> array(
																			array('onGetStylesFieldsPre', 10),
																			array('onGetStylesFieldsMid', 5),
																			array('onGetStylesFieldsPost', 0)
																		)
								)
							);

	/**
	 * Listener path
	 *
	 * @array	string	$path path
	 * @access	public
	 */
	public static $path = 'system/inc/admintasks/edit/events';

} // Ende class
