<?php
/**
 * This file is part of b2evolution - {@link http://b2evolution.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2009-2014 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2009 by The Evo Factory - {@link http://www.evofactory.com/}.
 *
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 *
 * {@internal Open Source relicensing agreement:
 * The Evo Factory grants Francois PLANQUE the right to license
 * The Evo Factory's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package messaging
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author efy-asimo: Attila Simo.
 *
 * @version $Id: _msg_settings.form.php 8274 2015-02-17 01:29:58Z fplanque $
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * @var Plugins
 */
global $Plugins;

$current_User->check_perm( 'options', 'edit', true );

global $Settings;

$BlogCache = & get_BlogCache();

$Form = new Form( NULL, 'msg_settings' );

$Form->begin_form( 'fform', '' );

	$Form->add_crumb( 'msgsettings' );
	$Form->hidden( 'ctrl', 'msgsettings' );
	$Form->hidden( 'action', 'update' );

$Form->begin_fieldset( T_( 'General settings' ) );

	// set link to options 
	$messages_link_to = $Settings->get( 'messages_link_to' );
	$admin_selected = ( $messages_link_to == 'admin' ) ? 'selected="selected"' : '';
	$link_to_options = '<option value="admin" '.$admin_selected.'>admin</option>';
	$link_to_options .= $BlogCache->get_option_list( $messages_link_to );
	$Form->select_input_options( 'messages_link_to', $link_to_options, T_( 'Messaging emails link to' ) );

	$Form->checkbox( 'allow_html_message', $Settings->get( 'allow_html_message' ),
						T_( 'Allow HTML' ), T_( 'Check to allow HTML in messages.' ).' ('.T_('HTML code will pass several sanitization filters.').')' );

$Form->end_fieldset();

$Form->begin_fieldset( T_( 'Welcome message after account activation' ) );

	$Form->checkbox_input( 'welcomepm_enabled', $Settings->get( 'welcomepm_enabled' ), T_('Send Welcome PM'), array( 'note' => T_('Check to automatically send a welcome message to users when they activate their account.' ) ) );

	$UserCache = & get_UserCache();
	$User = $UserCache->get_by_login( $Settings->get( 'welcomepm_from' ) );
	if( !$User )
	{	// Use login of the current user if user login is incorrect
		$User = $current_User;
	}
	$Form->username( 'welcomepm_from', $User, T_('From'), T_('User login.') );

	$Form->text_input( 'welcomepm_title', $Settings->get( 'welcomepm_title' ), 58, T_('Title'), '', array( 'maxlength' => 5000 ) );

	$Form->textarea_input( 'welcomepm_message', $Settings->get( 'welcomepm_message' ), 15, T_('Message'), array( 'cols' => 45 ) );

$Form->end_fieldset();

$Form->begin_fieldset( T_( 'Info message to reporters after account deletion' ) );

	$Form->checkbox_input( 'reportpm_enabled', $Settings->get( 'reportpm_enabled' ), /* TRANS: Send a Private Message to reporters when an account is deleted by a moderator */ T_('Send delete notification'), array( 'note' => T_('Check to allow sending a message to users who have reported an account whenever that account is deleted by a moderator.' ) ) );

	$UserCache = & get_UserCache();
	$User = $UserCache->get_by_login( $Settings->get( 'reportpm_from' ) );
	if( !$User )
	{	// Use login of the current user if user login is incorrect
		$User = $current_User;
	}
	$Form->username( 'reportpm_from', $User, T_('From'), T_('User login.') );

	$Form->text_input( 'reportpm_title', $Settings->get( 'reportpm_title' ), 58, T_('Title'), '', array( 'maxlength' => 5000 ) );

	$Form->textarea_input( 'reportpm_message', $Settings->get( 'reportpm_message' ), 15, T_('Message'), array( 'cols' => 45 ) );

$Form->end_fieldset();

// -------- Display plugins settings -------- //

load_funcs('plugins/_plugin.funcs.php');

$plugins_settings_content = '';
$Plugins->restart();
while( $loop_Plugin = & $Plugins->get_next() )
{
	// We use output buffers here to display the fieldset only if there's content in there
	ob_start();

	$plugin_settings = $loop_Plugin->get_msg_setting_definitions( $tmp_params = array( 'for_editing' => true ) );
	if( is_array( $plugin_settings ) )
	{
		foreach( $plugin_settings as $l_name => $l_meta )
		{
			// Display form field for this setting:
			autoform_display_field( $l_name, $l_meta, $Form, 'MsgSettings', $loop_Plugin );
		}
	}

	$plugins_settings_content .= ob_get_contents();

	ob_end_clean();
}

if( !empty( $plugins_settings_content ) )
{ // Display fieldset only when at least one renderer plugin exists
	$Form->begin_fieldset( T_( 'Renderer plugins settings' ) );

	echo $plugins_settings_content;

	$Form->end_fieldset();
}
// -------- End of Display plugins settings -------- //

$Form->buttons( array( array( 'submit', 'submit', T_('Save Changes!'), 'SaveButton' ) ) );

$Form->end_form();

?>
