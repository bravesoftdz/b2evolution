<?php
/**
 * This file implements the UI controller for settings management.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2015 by Francois Planque - {@link http://fplanque.com/}
 * Parts of this file are copyright (c)2004-2006 by Daniel HAHLER - {@link http://thequod.de/contact}.
 *
 * @package admin
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

// Check minimum permission:
$current_User->check_perm( 'options', 'view', true );

// Memorize this as the last "tab" used in the Blog Settings:
$UserSettings->set( 'pref_glob_settings_tab', $ctrl );
$UserSettings->dbupdate();

$AdminUI->set_path( 'options', 'general' );

param( 'action', 'string' );

switch( $action )
{
	case 'update':
		// UPDATE general settings:

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'globalsettings' );

		// Check permission:
		$current_User->check_perm( 'options', 'edit', true );

		// fp> Restore defaults has been removed because it's extra maintenance work and no real benefit to the user.

		// Online help
		param( 'webhelp_enabled', 'integer', 0 );
		$Settings->set( 'webhelp_enabled', $webhelp_enabled );

		if( ! $Messages->has_errors() )
		{
			$Settings->dbupdate();
			$Messages->add( T_( 'General settings updated.' ), 'success' );
			// Redirect so that a reload doesn't write to the DB twice:
			header_redirect( '?ctrl=gensettings', 303 ); // Will EXIT
			// We have EXITed already at this point!!
		}
		break;

	case 'update_tools':
		// UPDATE general settings from tools:

		// Check that this action request is not a CSRF hacked request:
		$Session->assert_received_crumb( 'globalsettings' );

		// Check permission:
		$current_User->check_perm( 'options', 'edit', true );

		// Lock system
		if( $current_User->check_perm( 'users', 'edit' ) )
		{
			$system_lock = param( 'system_lock', 'integer', 0 );
			if( $Settings->get( 'system_lock' ) && ( ! $system_lock ) && ( ! $Messages->has_errors() ) && ( 1 == $Messages->count() ) )
			{ // System lock was turned off and there was no error, remove the warning about the system lock
				$Messages->clear();
			}
			$Settings->set( 'system_lock', $system_lock );
		}

		if( ! $Messages->has_errors() )
		{
			$Settings->dbupdate();
			$Messages->add( T_('Site settings updated.'), 'success' );
		}

		// Redirect so that a reload doesn't write to the DB twice:
		header_redirect( '?ctrl=tools', 303 ); // Will EXIT
		// We have EXITed already at this point!!
		break;
}


$AdminUI->breadcrumbpath_init( false );
$AdminUI->breadcrumbpath_add( T_('System'), $admin_url.'?ctrl=system',
		T_('Global settings are shared between all blogs; see Blog settings for more granular settings.') );
$AdminUI->breadcrumbpath_add( T_('General'), $admin_url.'?ctrl=gensettings' );

// Display <html><head>...</head> section! (Note: should be done early if actions do not redirect)
$AdminUI->disp_html_head();

// Display title, menu, messages, etc. (Note: messages MUST be displayed AFTER the actions)
$AdminUI->disp_body_top();

// Begin payload block:
$AdminUI->disp_payload_begin();

// Display VIEW:
$AdminUI->disp_view( 'settings/views/_settings_general.form.php' );

// End payload block:
$AdminUI->disp_payload_end();

// Display body bottom, debug info and close </html>:
$AdminUI->disp_global_footer();

?>
