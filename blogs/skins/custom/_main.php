<?php
/**
 * This is the main template. It displays the blog.
 *
 * However this file is not meant to be called directly.
 * It is meant to be called automagically by b2evolution.
 * To display a blog, the easiest way is to call index.php?blog=#
 * where # is the number of your blog.
 *
 * This file is part of the b2evolution project - {@link http://b2evolution.net/}
 *
 * @copyright (c)2003-2004 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2005 by Jason Edgecombe.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 * {@internal
 * b2evolution is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * b2evolution is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with b2evolution; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * Jason EDGECOMBE grants Fran�ois PLANQUE the right to license
 * Jason EDGECOMBE's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evoskins
 * @subpackage custom
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author fplanque: Fran�ois PLANQUE - {@link http://fplanque.net/}
 * @author cafelog (team)
 * @author edgester: Jason EDGECOMBE (personal contributions, not for hire)
 *
 * @version $Id$
 */
if( !defined('DB_USER') ) die( 'Please, do not access this page directly.' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php locale_lang() ?>" lang="<?php locale_lang() ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php locale_charset() ?>" />
<title><?php
	$Blog->disp('name', 'htmlhead');
	single_cat_title( ' - ', 'htmlhead' );
	single_month_title( ' - ', 'htmlhead' );
	single_post_title( ' - ', 'htmlhead' );
	arcdir_title( ' - ', 'htmlhead' );
	last_comments_title( ' - ', 'htmlhead' );
	stats_title( ' - ', 'htmlhead' );
	msgform_title( ' - ', 'htmlhead' );
	profile_title( ' - ', 'htmlhead' );
?>
</title>
<base href="<?php skinbase(); /* Base URL for this skin. You need this to fix relative links! */ ?>" />
<meta name="description" content="<?php $Blog->disp( 'shortdesc', 'htmlattr' ); ?>" />
<meta name="keywords" content="<?php $Blog->disp( 'keywords', 'htmlattr' ); ?>" />
<meta name="generator" content="b2evolution <?php echo $app_version ?>" /> <!-- Please leave this for stats -->
<link rel="alternate" type="text/xml" title="RDF" href="<?php $Blog->disp( 'rdf_url', 'raw' ) ?>" />
<link rel="alternate" type="text/xml" title="RSS .92" href="<?php $Blog->disp( 'rss_url', 'raw' ) ?>" />
<link rel="alternate" type="text/xml" title="RSS 2.0" href="<?php $Blog->disp( 'rss2_url', 'raw' ) ?>" />
<link rel="alternate" type="application/atom+xml" title="Atom" href="<?php $Blog->disp( 'atom_url', 'raw' ) ?>" />
<link rel="pingback" href="<?php $Blog->disp( 'pingback_url', 'raw' ) ?>" />
<link rel="stylesheet" href="custom.css" type="text/css" />
<?php
/* Add the html for user and blog-specified stylesheets
   All stylesheets will be included if the blog settings allow it
   and the files exist. CSS rules say that the latter style sheets can
   override earlier stylesheets.
 */
if ( ( $Blog->allowblogcss == 1 )
     && ( file_exists( $Blog->get( "mediadir" ) . "customstyle.css" ) )
     )
    {
      echo '<link rel="stylesheet" href="'
        . $Blog->get( "mediaurl" )
        .  'customstyle.css" type="text/css" />' . "\n";
    }
/* check for a user-specified stylesheet */
/* TODO Fix the following if statement
   FIXME The if statement below doesn't work
*/
if ( ( $Blog->allowusercss == 1 )
     && ( file_exists( $current_User->getMediaDir() . "customstyle.css" ) )
     )
    {
      echo '<link rel="stylesheet" href="'
        . $current_User->getMediaUrl()
        .  'customstyle.css" type="text/css" />' . "\n";
    }
?>
</head>
<body>
<div id="wrapper">

<?php
	/**
	 * --------------------------- BLOG LIST INCLUDED HERE -----------------------------
	 */
	require( dirname(__FILE__).'/_bloglist.php' );
	// ----------------------------- END OF BLOG LIST ---------------------------- ?>

<div class="pageHeader">

<h1 id="pageTitle"><?php $Blog->disp( 'name', 'htmlbody' ) ?></h1>

<div class="pageSubTitle"><?php $Blog->disp( 'tagline', 'htmlbody' ) ?></div>

</div>

<div class="bPosts">
<h2><?php
	single_cat_title();
	single_month_title();
	single_post_title();
	arcdir_title();
	last_comments_title();
	stats_title();
	profile_title();
	msgform_title();
?></h2>

<!-- =================================== START OF MAIN AREA =================================== -->

<?php // ------------------------------------ START OF POSTS ----------------------------------------
	if( isset($MainList) ) $MainList->display_if_empty(); // Display message if no post

	if( isset($MainList) ) while( $Item = $MainList->get_item() )
	{
		$MainList->date_if_changed();
	?>
	<div class="bPost" lang="<?php $Item->lang() ?>">
		<?php
			locale_temp_switch( $Item->locale ); // Temporarily switch to post locale
			$Item->anchor(); // Anchor for permalinks to refer to
		?>
		<div class="bSmallHead">
		<a href="<?php $Item->permalink() ?>" title="<?php echo T_('Permanent link to full entry') ?>"><img src="img/icon_minipost.gif" alt="<?php echo T_('Permalink') ?>" width="12" height="9" class="middle" /></a>
		<?php
			$Item->issue_time();
			echo ', by ';
			$Item->Author->prefered_name();
			$Item->msgform_link( $Blog->get('msgformurl') );
			echo ', ';
			$Item->wordcount();
			echo ' ', T_('words');
			echo ', ';
			$Item->views();
			echo ' '.T_('views');
			echo ' &nbsp; ';
			locale_flag( $Item->locale, 'h10px' );
			echo '<br /> ', T_('Categories'), ': ';
			$Item->categories();
		?>
		</div>
		<h3 class="bTitle"><?php $Item->title(); ?></h3>
		<div class="bText">
			<?php $Item->content(); ?>
			<?php link_pages() ?>
		</div>
		<div class="bSmallPrint">
			<span class="bIcons">
				<a href="<?php $Item->permalink() ?>" title="<?php echo T_('Permanent link to full entry') ?>"><img src="<?php imgbase(); ?>chain_link.gif" alt="<?php echo T_('Permalink') ?>" width="14" height="14" class="middle" /></a>
			</span>

			<?php $Item->feedback_link( 'comments' ) // Link to comments ?>
			<?php $Item->feedback_link( 'trackbacks', ' &bull; ' ) // Link to trackbacks ?>
			<?php $Item->feedback_link( 'pingbacks', ' &bull; ' ) // Link to trackbacks ?>

			<?php $Item->edit_link( ' &bull; ' ) // Link to backoffice for editing ?>

			<?php $Item->trackback_rdf() // trackback autodiscovery information ?>
		</div>
			<?php // ------------- START OF INCLUDE FOR COMMENTS, TRACKBACK, PINGBACK, ETC. -------------
			$disp_comments = 1;					// Display the comments if requested
			$disp_comment_form = 1;			// Display the comments form if comments requested
			$disp_trackbacks = 1;				// Display the trackbacks if requested

			$disp_trackback_url = 1;		// Display the trackbal URL if trackbacks requested
			$disp_pingbacks = 1;				// Display the pingbacks if requested
			require( dirname(__FILE__).'/_feedback.php' );
			// ---------------- END OF INCLUDE FOR COMMENTS, TRACKBACK, PINGBACK, ETC. ----------------

			locale_restore_previous();	// Restore previous locale (Blog locale)
			?>
	</div>
	<?php
	} // ---------------------------------- END OF POSTS ------------------------------------
	?>

	<p class="center"><strong>
		<?php posts_nav_link(); ?>
		<?php
			// previous_post( '<p class="center">%</p>' );
			// next_post( '<p class="center">%</p>' );
		?>
	</strong></p>

<?php // ---------------- START OF INCLUDES FOR LAST COMMENTS, STATS ETC. ----------------
	switch( $disp )
	{
		case 'comments':
			// this includes the last comments if requested:
			require( dirname(__FILE__).'/_lastcomments.php' );
			break;

		case 'stats':
			// this includes the statistics if requested:
			require( dirname(__FILE__).'/_stats.php');
			break;

		case 'arcdir':
			// this includes the archive directory if requested
			require( dirname(__FILE__).'/_arcdir.php');
			break;

		case 'profile':
			// this includes the profile form if requested
			require( dirname(__FILE__).'/_profile.php');
			break;

		case 'msgform':
			// this includes the email form if requested
			require( dirname(__FILE__).'/_msgform.php');
			break;

	}
// ------------------- END OF INCLUDES FOR LAST COMMENTS, STATS ETC. ------------------- ?>
</div>
<!-- =================================== START OF SIDEBAR =================================== -->

<div class="bSideBar">

	<div class="bSideItem">
		<h3><?php $Blog->disp( 'name', 'htmlbody' ) ?></h3>
		<p><?php $Blog->disp( 'longdesc', 'htmlbody' ); ?></p>
		<p class="center"><strong><?php
			posts_nav_link( ' | ',
											/* TRANS: previous page (of posts) */ '< '.T_('Previous'),
											/* TRANS: next page (of posts) */ T_('Next').' >' );
			?></strong></p>
		<!--?php next_post(); // activate this if you want a link to the next post in single page mode ?-->
		<!--?php previous_post(); // activate this if you want a link to the previous post in single page mode ?-->
		<ul>
			<li><a href="<?php $Blog->disp( 'staticurl', 'raw' ) ?>"><strong><?php echo T_('Recently') ?></strong></a> <span class="dimmed"><?php echo T_('(cached)') ?></span></li>
			<li><a href="<?php $Blog->disp( 'dynurl', 'raw' ) ?>"><strong><?php echo T_('Recently') ?></strong></a> <span class="dimmed"><?php echo T_('(no cache)') ?></span></li>
		</ul>
		<?php // -------------------------- CALENDAR INCLUDED HERE -----------------------------
			require( dirname(__FILE__).'/_calendar.php' );
			// -------------------------------- END OF CALENDAR ---------------------------------- ?>
		<ul>
			<li><a href="<?php $Blog->disp( 'lastcommentsurl', 'raw' ) ?>"><strong><?php echo T_('Last comments') ?></strong></a></li>
			<li><a href="<?php $Blog->disp( 'blogstatsurl', 'raw' ) ?>"><strong><?php echo T_('Some viewing statistics') ?></strong></a></li>
		</ul>
	</div>

	<div class="bSideItem">
		<h3 class="sideItemTitle"><?php echo T_('Search') ?></h3>
		<?php form_formstart( $Blog->dget( 'blogurl', 'raw' ), 'search', 'SearchForm' ) ?>
			<p><input type="text" name="s" size="30" value="<?php echo htmlspecialchars($s) ?>" class="SearchField" /><br />
			<input type="radio" name="sentence" value="AND" id="sentAND" <?php if( $sentence=='AND' ) echo 'checked="checked" ' ?>/><label for="sentAND"><?php echo T_('All Words') ?></label><br />
			<input type="radio" name="sentence" value="OR" id="sentOR" <?php if( $sentence=='OR' ) echo 'checked="checked" ' ?>/><label for="sentOR"><?php echo T_('Some Word') ?></label><br />
			<input type="radio" name="sentence" value="sentence" id="sentence" <?php if( $sentence=='sentence' ) echo 'checked="checked" ' ?>/><label for="sentence"><?php echo T_('Entire phrase') ?></label></p>
			<input type="submit" name="submit" class="submit" value="<?php echo T_('Search') ?>" />
		</form>
	</div>

	<div class="bSideItem">
		<h3><?php echo T_('Categories') ?></h3>
		<?php form_formstart( $Blog->dget( 'blogurl', 'raw' ) ) ?>
		<?php // -------------------------- CATEGORIES INCLUDED HERE -----------------------------
			require( dirname(__FILE__).'/_categories.php' );
			// -------------------------------- END OF CATEGORIES ---------------------------------- ?>
		<br />
		<input type="submit" class="submit" value="<?php echo T_('Get selection') ?>" />
		</form>
	</div>

	<div class="bSideItem">
		<h3><?php echo T_('Archives') ?></h3>
		<?php // -------------------------- ARCHIVES INCLUDED HERE -----------------------------
			// Call the Archives plugin:
			$Plugins->call_by_code( 'evo_Arch', array() );
			// -------------------------------- END OF ARCHIVES ---------------------------------- ?>
	</div>

	<?php if( ! $Blog->get('force_skin') )
	{	// Skin switching is allowed for this blog: ?>
		<div class="bSideItem">
			<h3><?php echo T_('Choose skin') ?></h3>
			<ul>
				<?php // ------------------------------- START OF SKIN LIST -------------------------------
				for( skin_list_start(); skin_list_next(); ) { ?>
					<li><a href="<?php skin_change_url() ?>"><?php skin_list_iteminfo( 'name', 'htmlbody' ) ?></a></li>
				<?php } // ------------------------------ END OF SKIN LIST ------------------------------ ?>
			</ul>
		</div>
	<?php } ?>

	<?php if( $disp != 'stats' )
	{ ?>
	<div class="bSideItem">
		<h3><?php echo T_('Recent Referers') ?></h3>
			<?php refererList(5, 'global', 0, 0, 'no', '', ($blog > 1) ? $blog : ''); ?>
			<ul>
				<?php if( count( $res_stats ) ) foreach( $res_stats as $row_stats ) { ?>
					<li><a href="<?php stats_referer() ?>"><?php stats_basedomain() ?></a></li>
				<?php } // End stat loop ?>
				<li><a href="<?php $Blog->disp( 'blogstatsurl', 'raw' ) ?>"><?php echo T_('more...') ?></a></li>
			</ul>
		<br />
		<h3><?php echo T_('Top Referers') ?></h3>
			<?php refererList(5, 'global', 0, 0, 'no', 'dom_name', ($blog > 1) ? $blog : ''); ?>
			<ul>
				<?php if( count( $res_stats ) ) foreach( $res_stats as $row_stats ) { ?>
					<li><a href="<?php stats_referer() ?>"><?php stats_basedomain() ?></a></li>
				<?php } // End stat loop ?>
				<li><a href="<?php $Blog->disp( 'blogstatsurl', 'raw' ) ?>"><?php echo T_('more...') ?></a></li>
			</ul>
	</div>

	<?php } ?>


	<?php // -------------------------- LINKBLOG INCLUDED HERE -----------------------------
		require( dirname(__FILE__).'/_linkblog.php' );
		// -------------------------------- END OF LINKBLOG ---------------------------------- ?>


	<div class="bSideItem">
		<h3><?php echo T_('Misc') ?></h3>
		<ul>
			<?php
				user_login_link( '<li>', '</li>' );
				user_register_link( '<li>', '</li>' );
				user_admin_link( '<li>', '</li>' );
				user_profile_link( '<li>', '</li>' );
				user_logout_link( '<li>', '</li>' );
			?>
		</ul>
	</div>

	<div class="bSideItem">
		<h3><?php echo T_('Syndicate this blog') ?> <img src="../../img/xml.gif" alt="XML" width="36" height="14" class="middle" /></h3>
			<ul>
				<li>
					RSS 0.92:
					<a href="<?php $Blog->disp( 'rss_url', 'raw' ) ?>"><?php echo T_('Posts') ?></a>,
					<a href="<?php $Blog->disp( 'comments_rss_url', 'raw' ) ?>"><?php echo T_('Comments') ?></a>
				</li>
				<li>
					RSS 1.0:
					<a href="<?php $Blog->disp( 'rdf_url', 'raw' ) ?>"><?php echo T_('Posts') ?></a>,
					<a href="<?php $Blog->disp( 'comments_rdf_url', 'raw' ) ?>"><?php echo T_('Comments') ?></a>
				</li>
				<li>
					RSS 2.0:
					<a href="<?php $Blog->disp( 'rss2_url', 'raw' ) ?>"><?php echo T_('Posts') ?></a>,
					<a href="<?php $Blog->disp( 'comments_rss2_url', 'raw' ) ?>"><?php echo T_('Comments') ?></a>
				</li>
				<li>
					Atom:
					<a href="<?php $Blog->disp( 'atom_url', 'raw' ) ?>"><?php echo T_('Posts') ?></a>,
					<a href="<?php $Blog->disp( 'comments_atom_url', 'raw' ) ?>"><?php echo T_('Comments') ?></a>
				</li>
			</ul>
			<a href="http://fplanque.net/Blog/devblog/2004/01/10/p456" title="External - English"><?php echo T_('What is RSS?') ?></a>
	</div>


	<div class="bSideItem">
		<h3 class="sideItemTitle"><?php echo T_('Who\'s Online?') ?></h3>
		<?php
			$Sessions->displayOnliners();
		?>
	</div>


	<p class="center">powered by<br />
	<a href="http://b2evolution.net/" title="b2evolution home"><img src="../../img/b2evolution_logo_80.gif" alt="b2evolution" width="80" height="17" border="0" class="middle" /></a></p>

</div>
<div id="pageFooter">
	<p class="baseline">
		Original template design by <a href="http://fplanque.net/">Fran�ois PLANQUE</a>.
	</p>
	<div class="center">
		<a href="http://validator.w3.org/check/referer"><img style="border:0;width:88px;height:31px" src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0!" class="middle" /></a>

		<a href="http://jigsaw.w3.org/css-validator/"><img style="border:0;width:88px;height:31px" src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS!" class="middle" /></a>

		<a href="http://feedvalidator.org/check.cgi?url=<?php $Blog->disp( 'rss2_url', 'raw' ) ?>"><img src="../../img/valid-rss.png" alt="Valid RSS!" style="border:0;width:88px;height:31px" class="middle" /></a>

		<a href="http://feedvalidator.org/check.cgi?url=<?php $Blog->disp( 'atom_url', 'raw' ) ?>"><img src="../../img/valid-atom.png" alt="Valid Atom!" style="border:0;width:88px;height:31px" class="middle" /></a>
	</div>

	<?php
		$Hit->log();	// log the hit on this page
		debug_info(); // output debug info if requested
	?>
</div>
</div>
</body>
</html>