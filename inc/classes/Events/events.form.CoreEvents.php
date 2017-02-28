<?php
namespace Concise\Events\Form;



##################################
###  CoreEventListener-Events  ###
##################################

// FormCoreEvents

final class FormCoreEvents
{

	/**
	 * Listener events
	 *
	 * @array	array	$events [scope,event,method,priority]
	 * @access	public
	 */
	public static $events = array(
								"form"	=> array(
									// FormGenerator
									'form.create_form_field'	=> array('onCreateFormfield', 0),
									'form.check_form_field'		=> array('onCheckFormfield', 0),
									'form.check_field_types'	=> array('onCheckFieldTypes', 0),
									'form.build_data_array'		=> array(
																		array('onBuildDataArrayPre', 10),
																		array('onBuildDataArrayMid', 5),
																		array('onBuildDataArrayPost', 0)
																	),
									'form.get_extra_data'		=> array('onAddExtraData', 0),
									// PDFMaker
									'pdf.make_pdf'				=> array('onMakePdf', 0)
								)
							);

	/**
	 * Listener path
	 *
	 * @array	string	$path path
	 * @access	public
	 */
	public static $path = 'inc/classes/Forms/events';

} // Ende class
