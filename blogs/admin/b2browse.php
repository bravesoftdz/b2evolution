<?php
/**
 * This file implements the UI controller for the browsing posts.
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2004 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package admin
 */

/**
 * Includes:
 */
require_once (dirname(__FILE__). '/_header.php');

$itemTypeCache = & new DataObjectCache( 'Element', true, 'T_posttypes', 'ptyp_', 'ptyp_ID' );
$itemStatusCache = & new DataObjectCache( 'Element', true, 'T_poststatuses', 'pst_', 'pst_ID' );

$admin_tab = 'edit';
$admin_pagetitle = $admin_pagetitle_titlearea = T_('Browse blog:');
param( 'blog', 'integer', 0 );

if( ($blog == 0) && $current_User->check_perm( 'blog_ismember', 1, false, $default_to_blog ) )
{ // Default blog is a valid choice
	$blog = $default_to_blog;
}
// ---------------------------------- START OF BLOG LIST ----------------------------------
$blogListButtons = '';

for( $curr_blog_ID = blog_list_start();
			$curr_blog_ID != false;
			$curr_blog_ID = blog_list_next() )
	{
		if( ! $current_User->check_perm( 'blog_ismember', 1, false, $curr_blog_ID ) )
		{ // Current user is not a member of this blog...
			continue;
		}
		if( $blog == 0 )
		{ // If no selected blog yet, select this one:
			$blog = $curr_blog_ID;
		}
		if( $curr_blog_ID == $blog )
		{ // This is the blog being displayed on this page
			$blogListButtons .= '<a href="'.$pagenow.'?blog='.$curr_blog_ID.'" class="CurrentBlog">'
				.blog_list_iteminfo( 'shortname', false ).'</a> ';
			$admin_pagetitle .= ' '.blog_list_iteminfo( 'shortname', false );
		}
		else
		{ // This is another blog
			$blogListButtons .= '<a href="'.$pagenow.'?blog='.$curr_blog_ID.'" class="OtherBlog">'
				.blog_list_iteminfo( 'shortname', false ).'</a> ';
		}
	} // --------------------------------- END OF BLOG LIST ---------------------------------


	require (dirname(__FILE__). '/_menutop.php');

	if( $blog == 0 )
	{ // No blog could be selected
		?>
		<div class="panelblock">
		<?php printf( T_('Since you\'re a newcomer, you\'ll have to wait for an admin to authorize you to post. You can also <a %s>e-mail the admin</a> to ask for a promotion. When you\'re promoted, just reload this page and you\'ll be able to blog. :)'), 'href="mailto:'. $admin_email. '?subject=b2-promotion"' ); ?>
		</div>
		<?php
	}
	else
	{ // We could select a blog:
		$Blog = Blog_get_by_ID( $blog ); /* TMP: */ $blogparams = get_blogparams_by_ID( $blog );

		// Check permission:
		$current_User->check_perm( 'blog_ismember', 1, true, $blog );

		// Show the posts:
		$add_item_url = 'b2edit.php?blog='.$blog;
		$edit_item_url = 'b2edit.php?action=edit&amp;post=';
		$delete_item_url = 'edit_actions.php?action=delete&amp;post=';
		$objType = 'Item';
		$dbtable = 'T_posts';
		$dbprefix = 'post_';
		$dbIDname = 'ID';
		require dirname(__FILE__). '/_edit_showposts.php';
	}

	require( dirname(__FILE__). '/_footer.php' );
?>