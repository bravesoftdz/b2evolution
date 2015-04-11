<?php
/**
 * This file implements the UI view for the widgets params form.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2015 by Francois Planque - {@link http://fplanque.com/}.
 *
 * @package admin
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

load_funcs('plugins/_plugin.funcs.php');

/**
 * @var ComponentWidget
 */
global $edited_ComponentWidget;

// Determine if we are creating or updating...
$creating = is_create_action( $action );

$Form = new Form( NULL, 'form' );

// Manual link
$manual_url = preg_replace( '/[^a-z0-9]/', '-', strtolower( $edited_ComponentWidget->get_name() ) ).'-widget';
$Form->global_icon( T_('View manual'), 'manual', get_manual_url( $manual_url ), '', 3, 2, array( 'target' => '_blank' ) );
// Close link
$Form->global_icon( T_('Cancel editing!'), 'close', regenerate_url( 'action' ), '', 3, 2, array( 'class' => 'action_icon close_link' ) );

$Form->begin_form( 'fform', sprintf( $creating ?  T_('New widget %s in %s') : T_('Edit widget %s in %s'), $edited_ComponentWidget->get_name(), $edited_ComponentWidget->get( 'sco_name' ) ) );

	$Form->add_crumb( 'widget' );
	$Form->hidden( 'action', $creating ? 'create' : 'update' );
	$Form->hidden( 'wi_ID', $edited_ComponentWidget->ID );
	$Form->hiddens_by_key( get_memorized( 'action' ) );

// Display properties:
$Form->begin_fieldset( T_('Properties') );
	$Form->info( T_('Widget type'), $edited_ComponentWidget->get_name() );
	$Form->info( T_('Description'), $edited_ComponentWidget->get_desc() );
$Form->end_fieldset();


// Display (editable) parameters:
$Form->begin_fieldset( T_('Params') );

	//$params = $edited_ComponentWidget->get_params();

	// Loop through all widget params:
	foreach( $edited_ComponentWidget->get_param_definitions( $tmp_params = array('for_editing'=>true) ) as $l_name => $l_meta )
	{
		// Display field:
		autoform_display_field( $l_name, $l_meta, $Form, 'Widget', $edited_ComponentWidget );
	}

$Form->end_fieldset();


// dh> TODO: allow the widget to display information, e.g. the coll_category_list
//       widget could say which blogs it affects. (Maybe this would be useful
//       for all even, so a default info field(set)).
//       Does a callback make sense? Then we should have a action hook too, to
//       catch any params/settings maybe? Although this could be done in the
//       same hook in most cases probably. (dh)

$Form->end_form( array( array( 'submit', 'submit', ( $creating ? T_('Record') : T_('Save Changes!') ), 'SaveButton' ) ) );

?>
