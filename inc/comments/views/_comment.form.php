<?php
/**
 * This file implements the Comment form.
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

/**
 * @var Blog
 */
global $Blog;
/**
 * @var Comment
 */
global $edited_Comment;
/**
 *
 */
global $Plugins;

global $mode, $month, $tab, $redirect_to, $comment_content;

$Form = new Form( NULL, 'comment_checkchanges', 'post' );

$link_attribs = array( 'style' => 'margin-left:3ex', 'class' => 'btn btn-sm btn-default action_icon' ); // Avoid misclicks by all means!
if( $current_User->check_perm( 'blog_post!draft', 'edit', false, $Blog->ID ) )
{
	$Form->global_icon( T_( 'Post as a quote' ), 'elevate', '?ctrl=comments&amp;action=elevate&amp;type=quote&amp;comment_ID='.$edited_Comment->ID.'&amp;'.url_crumb('comment'),
				T_( 'Post as a quote' ), 4, 3, $link_attribs, 'elevate' );
	$Form->global_icon( T_( 'Post as original user' ), 'elevate', '?ctrl=comments&amp;action=elevate&amp;type=original&amp;comment_ID='.$edited_Comment->ID.'&amp;'.url_crumb('comment'),
				T_( 'Post as original user' ), 4, 3, $link_attribs, 'elevate' );
}

$delete_url = '?ctrl=comments&amp;action=delete&amp;comment_ID='.$edited_Comment->ID.'&amp;'.url_crumb('comment');
if( $edited_Comment->status == 'trash' )
{
	$delete_title = T_('Delete this comment');
	$delete_text = T_('delete');
	$link_attribs['onclick'] = 'return confirm(\''.TS_('You are about to delete this comment!\\nThis cannot be undone!').'\')';
}
else
{
	$delete_title = T_('Recycle this comment');
	$delete_text = T_('recycle');
}
$Form->global_icon( $delete_title, 'recycle', $delete_url, $delete_text, 4, 3, $link_attribs );

$Form->global_icon( T_('Cancel editing!'), 'close', str_replace( '&', '&amp;', $redirect_to), T_('cancel'), 4, 1, $link_attribs );

$Form->begin_form( 'eform' );

$Form->add_crumb( 'comment' );
$Form->hidden( 'ctrl', 'comments' );
$Form->hidden( 'redirect_to', $redirect_to );
$Form->hidden( 'comment_ID', $edited_Comment->ID );
?>

<div class="row">

<div class="left_col col-md-9">


	<?php
	$Form->begin_fieldset( T_('Comment contents').get_manual_link( 'editing-comments' ) );

	echo '<div class="row">';
		echo '<div class="col-md-7 col-sm-12">';

		$comment_Item = & $edited_Comment->get_Item();
		$Form->info( T_('In response to'), $comment_Item->get_title( array(
				'link_type'  => 'admin_view',
				'max_length' => '30'
			) ) );

		echo '</div>';
		echo '<div class="col-md-5 col-sm-12">';

		$Blog_owner_User = & $Blog->get_owner_User();
		if( ( $Blog_owner_User->ID == $current_User->ID ) || $current_User->check_perm( 'blog_admin', 'edit', false, $Blog->ID ) )
		{	// User has permission to change comment's post, because user is the owner of the current blog, or user has admin full access permission for current blog
			$Form->text_input( 'moveto_post', $comment_Item->ID, 20, T_('Move to post ID'), '', array( 'maxlength' => 100, 'style' => 'width:25%' ) );
		}

		echo '</div>';
	echo '</div>';

	echo '<div class="row">';
		echo '<div class="col-md-7 col-sm-12">';

		if( $Blog->get_setting( 'threaded_comments' ) )
		{	// Display a reply comment ID only when this feature is enabled in blog settings:
			$Form->text_input( 'in_reply_to_cmt_ID', $edited_Comment->in_reply_to_cmt_ID, 10, T_('In reply to comment ID'), T_('(leave blank for normal comments)') );
		}

		if( $edited_Comment->get_author_User() )
		{	// This comment has been created by member
			if( $current_User->check_perm( 'users', 'edit' ) )
			{	// Allow to change an author if current user has a permission:
				$Form->username( 'comment_author_login', $edited_Comment->get_author_User(), T_('Author'), '' );
			}
			else
			{	// Current user has no permission to edit a comment author
				$Form->info( T_('Author'), $edited_Comment->get_author( array(
						'before'    => '',
						'link_to'   => '',
						'link_text' => 'name',
					) ) );
			}
		}
		else
		{	// This is not a member comment
			$Form->text_input( 'newcomment_author', $edited_Comment->author, 20, T_('Author'), '', array( 'maxlength' => 100, 'style' => 'width:100%' ) );
			$Form->text_input( 'newcomment_author_email', $edited_Comment->author_email, 20, T_('Email'), '', array( 'maxlength' => 255, 'style' => 'width:100%' ) );
			$Form->checkbox( 'comment_allow_msgform', $edited_Comment->allow_msgform, T_('Allow contact'), T_('If checked, the comment author can be contacted through a form that will send him en email.') );
			$Form->text_input( 'newcomment_author_url', $edited_Comment->author_url, 20, T_('Website URL'), '', array( 'maxlength' => 255, 'style' => 'width:100%' ) );
		}

		echo '</div>';
	echo '</div>';
	?>

	<div class="edit_toolbars">
	<?php // --------------------------- TOOLBARS ------------------------------------
		// CALL PLUGINS NOW:
		$Plugins->trigger_event( 'DisplayCommentToolbar', array( 'Comment' => & $edited_Comment, 'Item' => & $comment_Item ) );
	?>
	</div>

	<?php // ---------------------------- TEXTAREA -------------------------------------
	$content = $comment_content;
	$Form->fieldstart = '<div class="edit_area">';
	$Form->fieldend = "</div>\n";
	$Form->textarea_input( 'content', $content, 16, '', array( 'cols' => 40 , 'id' => 'commentform_post_content', 'class' => 'autocomplete_usernames' ) );
	$Form->fieldstart = '<div class="tile">';
	$Form->fieldend = '</div>';
	?>
	<script type="text/javascript">
		<!--
		// This is for toolbar plugins
		var b2evoCanvas = document.getElementById('commentform_post_content');
		//-->
	</script>

	<div class="edit_actions">

	<?php
	// ---------- DELETE ----------
	if( $edited_Comment->status == 'trash' && ( $action == 'edit' || $action == 'update_edit' ) )
	{ // Editing comment
		// Display delete button if user has permission to:
		$edited_Comment->delete_link( ' ', ' ', '#', '#', 'DeleteButton btn btn-danger', true );
	}

	// CALL PLUGINS NOW:
	$Plugins->trigger_event( 'AdminDisplayEditorButton', array( 'target_type' => 'Comment', 'edit_layout' => NULL ) );

	echo '<div class="pull-right">';
	echo_comment_buttons( $Form, $edited_Comment );
	echo '</div>';

	?>
	</div>

	<?php
	$Form->end_fieldset();

	// -------------------------- ATTACHMENTS/LINKS --------------------------
	if( isset($GLOBALS['files_Module']) )
	{
		load_class( 'links/model/_linkcomment.class.php', 'LinkComment' );
		$LinkOwner = new LinkComment( $edited_Comment );
		attachment_iframe( $Form, $LinkOwner );
	}

	// ####################### PLUGIN FIELDSETS #########################

	$Plugins->trigger_event( 'AdminDisplayCommentFormFieldset', array( 'Form' => & $Form, 'Comment' => & $edited_Comment, 'edit_layout' => NULL ) );
	?>

</div>

<div class="right_col col-md-3">

<?php
	// ####################### RATING #########################
	if( $comment_Item->can_rate()
		|| !empty( $edited_Comment->rating ) )
	{	// Rating is editable
		$Form->begin_fieldset( T_('Rating') );

		echo '<p>';
		$edited_Comment->rating_input( array( 'reset' => true ) );
		echo '</p>';

		$Form->end_fieldset();
	}
	else
	{
		$Form->hidden( 'comment_rating', 0 );
	}

	// ####################### ADVANCED PROPERTIES #########################
	if( $current_User->check_perm( 'blog_edit_ts', 'edit', false, $Blog->ID ) )
	{ // ------------------------------------ TIME STAMP -------------------------------------
		$Form->begin_fieldset( T_('Date & Time') );

		$Form->switch_layout( 'fieldset' );

		echo '<div id="commentform_edit_timestamp">';
		$Form->date_input( 'comment_issue_date', $edited_Comment->date, T_('Date'), array( 'add_date_format_note' => true ) );
		$Form->time( 'comment_issue_time', $edited_Comment->date, T_('Time') );
		echo '</div>';

		$Form->switch_layout( NULL );

		$Form->end_fieldset();
	}

	// ####################### LINKS #########################
	$Form->begin_fieldset( T_('Links') );
		echo '<p>';
		$Form->checkbox_basic_input( 'comment_nofollow', $edited_Comment->nofollow, T_('Nofollow website URL') );
		// TODO: apply to all links  -- note: see basic antispam plugin that does this for x hours
		echo '</p>';
	$Form->end_fieldset();


	// ####################### FEEDBACK INFO #########################
	$Form->begin_fieldset( T_('Feedback info') );
?>

	<p><strong><?php echo T_('Type') ?>:</strong> <?php echo $edited_Comment->type; ?></p>
	<p><strong><?php echo T_('IP address') ?>:</strong> <?php
		// Display IP address and allow plugins to filter it, e.g. the DNSBL plugin will add a link to check the IP:
		echo $Plugins->get_trigger_event( 'FilterIpAddress', array('format'=>'htmlbody', 'data'=>$edited_Comment->author_IP), 'data' ); ?>
		<?php $edited_Comment->ip_country(); ?>
	</p>
	<p><strong><?php echo T_('Spam Karma') ?>:</strong> <?php $edited_Comment->spam_karma(); ?></p>

	<?php
	$Form->end_fieldset();

	// ####################### TEXT RENDERERS #########################
	global $Plugins;
	$Form->begin_fieldset( T_('Text Renderers'), array( 'id' => 'itemform_renderers' ) );
	$edited_Comment->renderer_checkboxes();
	$Form->end_fieldset();
	?>
</div>

<div class="clear"></div>

</div>

<?php
$Form->end_form();

// JS code for status dropdown select button
echo_status_dropdown_button_js( 'comment' );
?>