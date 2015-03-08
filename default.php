<?php
/**
 * This is the main public interface file!
 *
 * This file is NOT mandatory. You can delete it if you want.
 * You can also replace the contents of this file with contents similar to the contents
 * of a_stub.php, a_noskin.php, multiblogs.php, etc.
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/gnu-gpl-license}
 * @copyright (c)2003-2015 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evoskins
 * @subpackage noskin
 */

/**
 * First thing: Do the minimal initializations required for b2evo:
 */
require_once dirname(__FILE__).'/conf/_config.php';

/**
 * Check this: we are requiring _main.inc.php INSTEAD of _blog_main.inc.php because we are not
 * trying to initialize any particular blog
 */
require_once $inc_path.'_main.inc.php';

load_funcs('skins/_skin.funcs.php');


// --------------------- PAGE LEVEL CACHING SUPPORT ---------------------
// Note: This is totally optional. General caching must be enabled in Global settings, otherwise this will do nothing.
// Delete this block if you don't care about page level caching. Don't forget to delete the matching section at the end of the page.
load_class( '_core/model/_pagecache.class.php', 'PageCache' );
$PageCache = new PageCache( NULL );
// Check for cached content & Start caching if needed:
if( ! $PageCache->check() )
{	// Cache miss, we have to generate:
	// --------------------- PAGE LEVEL CACHING SUPPORT ---------------------


// Add CSS:
require_css( 'basic_styles.css', 'rsc_url' ); // the REAL basic styles
require_css( 'basic.css', 'rsc_url' ); // Basic styles
require_css( 'evo_distrib_2.css', 'rsc_url' );

add_js_for_toolbar();		// Registers all the javascripts needed by the toolbar menu

headers_content_mightcache( 'text/html' );		// In most situations, you do NOT want to cache dynamic content!
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php locale_lang() ?>" lang="<?php locale_lang() ?>"><!-- InstanceBegin template="/Templates/evo_distrib_2.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
	<!-- InstanceBeginEditable name="doctitle" -->
	<title>b2evolution - Default Page</title>
	<!-- InstanceEndEditable -->
	<meta name="viewport" content="width = 750" />
	<meta name="robots" content="noindex, follow" />
	<?php include_headlines() /* Add javascript and css files included by plugins and skin */ ?>
	<!-- InstanceBeginEditable name="head" -->
	<base href="<?php echo $baseurl ?>" />
	<!-- InstanceEndEditable -->
	<!-- InstanceParam name="lang" type="text" value="&lt;?php locale_lang() ?&gt;" -->
</head>

<body<?php skin_body_attrs(); ?>>
	<!-- InstanceBeginEditable name="BodyHead" -->
	<?php
	// ---------------------------- TOOLBAR INCLUDED HERE ----------------------------
	require $skins_path.'_toolbar.inc.php';
	// ------------------------------- END OF TOOLBAR --------------------------------

	echo "\n";
	if( show_toolbar() )
	{
		echo '<div id="skin_wrapper" class="skin_wrapper_loggedin">';
	}
	else
	{
		echo '<div id="skin_wrapper" class="skin_wrapper_anonymous">';
	}
	echo "\n";
	?>
	<!-- Start of skin_wrapper -->
	<!-- InstanceEndEditable -->

	<div class="wrapper1">
	<div class="wrapper2">
		<span class="version_top"><!-- InstanceBeginEditable name="Version" --><?php echo T_('Default page') ?><!-- InstanceEndEditable --></span>

		<a href="http://b2evolution.net/" target="_blank"><img src="rsc/img/distrib/b2evolution-logo.gif" alt="b2evolution" width="237" height="92" /></a>

		<div class="menu_top"><!-- InstanceBeginEditable name="MenuTop" -->
			<span class="floatright"><a href="<?php echo $baseurl; ?>"><?php echo T_('Home'); ?></a>
			<?php
				if( is_logged_in() )
				{
					if( $current_User->check_perm( 'admin', 'restricted' ) &&
					    $current_User->check_status( 'can_access_admin' ) )
					{ // User must has an access to backoffice
						echo ' &#8226; <a href="'.$admin_url.'">'.T_('Admin').'</a>';
					}
				}
				else
				{ // User is not logged in yet
					echo ' &#8226; <a href="'.$admin_url.'">'.T_('Log in').'</a>';
				}
			?>
			</span>
			&#160;
		<!-- InstanceEndEditable --></div>

		<!-- InstanceBeginEditable name="Main" -->
		<?php
		/**
		 * @var BlogCache
		 */
		$BlogCache = & get_BlogCache();
		$BlogCache->load_all();

		if( $pagenow == 'index.php' || count( $BlogCache->cache ) == 0 )
		{	// This page is actually included by the index.html page OR there are no blogs
			?>
			<div class="block1">
			<div class="block2">
			<div class="block3">

				<h1><?php echo T_('Welcome to b2evolution') ?></h1>

				<?php
					messages( array(
							'block_start' => '<div class="action_messages">',
							'block_end'   => '</div>',
						) );

					if( count( $BlogCache->cache ) == 0 )
					{	// There is no blog on this system!
						echo '<p><strong>'.T_('b2evolution is installed and ready but you haven\'t created any content collection on this system yet.').'</strong></p>';

						if( is_logged_in() && $current_User->check_perm( 'blogs', 'create' ) )
						{ // Display this link only for users who can create blog
							echo '<p><a href="'.$admin_url.'?ctrl=collections&amp;action=new">'.T_( 'Create a first collection' ).' &raquo;</a></p>';
						}
					}
					else
					{
						echo '<p><strong>'.T_('You have successfully installed b2evolution.').'</strong></p>';

						echo '<p>'.T_('You haven\'t set a default collection yet. Thus, you see this default page.').'</p>';

						if( is_logged_in() && $current_User->check_perm( 'blogs', 'create' ) )
						{ // Display this link only for users who can create blog
						?>
						<p><a href="<?php echo $admin_url ?>?ctrl=collections&amp;tab=site_settings"><?php echo T_( 'Set a default collection' ) ?> &raquo;</a></p>
						<?php
						}
					}
					?>
			</div>
			</div>
			</div>
			<?php
		}

		if( count( $BlogCache->cache ) )
		{	// There are blogs on this system!
		?>

		<div class="block1">
		<div class="block2">
		<div class="block3">

	<h2><?php echo T_('Collections on this system') ?></h2>

	<ul>
	<?php // --------------------------- BLOG LIST -----------------------------
		for( $l_Blog = & $BlogCache->get_first();
					! is_null( $l_Blog );
					 $l_Blog = & $BlogCache->get_next() )
		{ # by uncommenting the following lines you can hide some blogs
			// if( $curr_blog_ID == 2 ) continue; // Hide blog 2...
			echo '<li><strong>';
			printf( T_('Blog #%d'), $l_Blog->ID );
			echo ': <a href="'.$l_Blog->gen_blogurl().'" title="'.$l_Blog->dget( 'shortdesc', 'htmlattr' ).'">';
			$l_Blog->disp( 'name' );
			echo '</a></strong>';
			echo '</li>';
		}
		// ---------------------------------- END OF BLOG LIST ---------------------------------
		?>
	</ul>

		<?php
		if( is_logged_in() && $current_User->check_perm( 'blogs', 'create' ) )
		{ // Display this link only for users who can create blog
			echo '<p><a href="'.$admin_url.'?ctrl=collections&amp;action=new">'.T_( 'Add a new collection' ).' &raquo;</a></p>';
		}
		?>
		</div>
		</div>
		</div>

	<?php
	}
?>

<!-- InstanceEndEditable -->
	</div>

	<div class="body_fade_out">

	<div class="menu_bottom"><!-- InstanceBeginEditable name="MenuBottom" -->Powered by <a href="http://b2evolution.net/" target="_blank">b2evolution</a> &bull; <a href="<?php echo get_manual_url( NULL ); ?>" target="_blank">Manual</a> &bull; <a href="http://forums.b2evolution.net/" target="_blank">Forums</a>
		<!-- InstanceEndEditable --></div>

	<div class="copyright"><!-- InstanceBeginEditable name="CopyrightTail" -->
		<a href="contact.php"><?php echo T_('Contact the admin') ?></a>
		<?php
			credits( array(
					'list_start'  => ' &middot; ',
					'list_end'    => ' ',
					'separator'   => ' &middot; ',
					'item_start'  => ' ',
					'item_end'    => ' ',
				) );
		?>
		<!-- InstanceEndEditable --></div>

	</div>
	</div>

	<!-- InstanceBeginEditable name="BodyFoot" -->
	<!-- End of skin_wrapper -->
	</div>
	<!-- InstanceEndEditable -->
</body>
<!-- InstanceEnd --></html>
<?php
	// --------------------- PAGE LEVEL CACHING SUPPORT ---------------------
	// Save collected cached data if needed:
	$PageCache->end_collect();
}
// --------------------- PAGE LEVEL CACHING SUPPORT ---------------------
?>
