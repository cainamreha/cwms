<?php
namespace Concise\Events\Admincampaigns;



##################################
###  CoreEventListener-Events  ###
##################################

// AdmincampaignsCoreEvents

final class AdmincampaignsCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"admincampaigns"	=> array(
									// global
									'global.register_head_files'		=> array('onRegisterHeadFiles', 0),
									'global.get_rightbar_contents'		=> array('onGetRightbarContents', 100)
								)
							);

	/**
	 * Listener path
	 *
	 * @array	string	$path path
	 * @access	public
	 */
	public static $path = 'system/inc/admintasks/campaigns/events';

} // Ende class
