<?php
/**
 * This file implements the ItemList class.
 *
 * This is the object handling item/post/article lists.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2004 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2004 by Daniel HAHLER - {@link http://thequod.de/contact}.
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
 * }}
 *
 * {@internal
 * Daniel HAHLER grants Fran�ois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author blueyed: Daniel HAHLER.
 * @author fplanque: Fran�ois PLANQUE.
 * @author jupiterx: Jordan RUNNING.
 *
 * @version $Id$
 */
if( !defined('DB_USER') ) die( 'Please, do not access this page directly.' );

/**
 * Includes:
 */
require_once dirname(__FILE__).'/_dataobjectlist.class.php';
require_once dirname(__FILE__).'/_item.class.php';
require_once dirname(__FILE__).'/_item.funcs.php';

function cat_req( $parent_cat_ID, $level )
{
	global $cat_array;
	// echo "[$parent_cat_ID] ";
	if( ! in_array( $parent_cat_ID, $cat_array ) )
	{ // Not already visited
		$cat_array[] = $parent_cat_ID;
	}
	else
	{
		// echo "STOP! ALREADY VISITED THIS ONE!";
		return -1;		// STOP going through that branch
	}
}

function cat_req_dummy() {}

/**
 * Item List Class
 *
 * @package evocore
 */
class ItemList extends DataObjectList
{
	var $objType;

	var $preview;
	var $blog;
	var $p;
	var $what_to_show;
	var $result_num_rows;			// Number of rows in result set
	var $postIDlist;
	var $postIDarray;

	var $total_num_posts;			// Total number of posts
	var $max_paged;						// Max page number for paged display

	var $group_by_cat;

	var $limitdate_start;     // UNIX timestamp
	var $limitdate_end;       // UNIX timestamp

	// Used in looping
	var $row_num;							// Current row
	var $row;									// Current row
	var $main_cat;						// Current main category
	var $previous_main_cat;		// Previous one
	/**
	 * @access private
	 */
	var $last_Item;

	/**
	 * @access private
	 */
	var $last_displayed_date = '';

	var $show_statuses;
	var $cat;
	var $catsel;
	var $timestamp_min;
	var $timestamp_max;

	var $dbcols;

	/**
	 * Constructor
	 *
	 * {@internal ItemList::ItemList(-)}}
	 *
	 * @param integer Blog ID
	 * @param array show_statuses
	 * @param mixed Specific post number to display
	 * @param mixed YearMonth(Day) to display
	 * @param mixed Week number. Note: uses MySQL's week numbering and MySQL default if applicable.
   * In MySQL < 4.0, WEEK() uses mode 0: Week starts on Sunday;
   * Value range is 0 to 53; week 1 is the first week that starts in this year
	 * @param mixed List of cats to restrict to
	 * @param array Array of cats to restrict to
	 * @param mixed List of authors to restrict to
	 * @param string sort order can be either ASC or DESC
	 * @param string space separated list of fields to order by. Possible list elements are:
	 *               author issue_date mod_date status locale content title urltitle url ctageory
	 *               wordcount comments
	 * @param mixed # of posts to display on the page
	 * @param mixed List page number in paged display
	 * @param mixed Start results at this position
	 * @param mixed End results at this position
	 * @param string Search string
	 * @param mixed Search for sentence or for words
	 * @param mixed Require exact match of title or contents
	 * @param boolean Is this preview
	 * @param mixed
	 * @param mixed
	 * @param mixed Do not show posts before this timestamp, can be 'now'
	 * @param mixed Do not show posts after this timestamp, can be 'now'
	 * @param string urltitle of post to display
	 * @param
	 * @param string Name of the DB table
	 * @param string Prefix of fields in the table
	 * @param string Name of the ID field (including prefix)
	 */
	function ItemList(
		$blog = 1,
		$show_statuses = array(),
		$p = '',															// Specific post number to display
		$m = '',															// YearMonth(Day) to display
		$w = -1,															// Week number
		$cat = '',														// List of cats to restrict to
		$catsel = array(),										// Array of cats to restrict to
		$author = '',													// List of authors to restrict to
		$order = '',													// ASC or DESC
		$orderby = '',												// list of fields to order by
		$posts = '',													// # of posts to display on the page
		$paged = '',													// List page number in paged display
		$poststart = '',											// Start results at this position
		$postend = '',												// End results at this position
		$s = '',															// Search string
		$sentence = '',												// Search for sentence or for words
		$exact = '',													// Require exact match of title or contents
		$preview = 0,													// Is this preview
		$default_posts_per_page = '',
		$init_what_to_show = '',
		$timestamp_min = '',									// Do not show posts before this timestamp
		$timestamp_max = 'now',								// Do not show posts after this timestamp
		$title = '',													// urltitle of post to display
		$objType = 'Item',
		$dbtable = 'T_posts',
		$dbprefix = 'post_',
		$dbIDname = 'ID' )
	{
		global $DB, $object_def;
		global $cache_categories;
		global $cat_array; // communication with recursive callback funcs
		global $Settings;

		// Call parent constructor:
		parent::DataObjectList( $dbtable, $dbprefix, $dbIDname );

		// Object type handled by this list
		$this->objType = $objType;

		$this->preview = $preview;
		$this->blog = $blog;
		$this->p = $p;

		$this->show_statuses = $show_statuses;
		$this->cat = $cat;
		$this->catsel = $catsel;
		$this->timestamp_min = $timestamp_min;
		$this->timestamp_max = $timestamp_max;

		if( !empty($posts) )
			$posts_per_page = $posts;
		elseif( !empty($default_posts_per_page) )
			$posts_per_page = $default_posts_per_page;
		else
			$posts_per_page = $Settings->get('posts_per_page');
		$this->posts_per_page = $posts_per_page;

		$what_to_show = (empty($init_what_to_show)) ? $Settings->get('what_to_show') : $init_what_to_show;
		$this->what_to_show = $what_to_show;

		// First let's clear some variables
		$whichcat = '';
		$whichauthor = '';
		$result = '';
		$where = '';
		$limits = '';
		$distinct = '';

		// WE ARE GOING TO CONSTRUCT THE "AND" CLOSE
		// THIS IS GOING TO LAST FOR MANY MANY LINES...

		// if a month is specified in the querystring, load that month
		if( $m != '' )
		{
			$m = '' . intval($m);
			$where .= ' AND YEAR('.$dbprefix.'datestart)=' . substr($m,0,4);
			if( strlen($m) > 5 )
				$where .= ' AND MONTH('.$dbprefix.'datestart)=' . substr($m,4,2);
			if( strlen($m) > 7 )
				$where .= ' AND DAYOFMONTH('.$dbprefix.'datestart)=' . substr($m,6,2);
			if( strlen($m) > 9 )
				$where .= ' AND HOUR('.$dbprefix.'datestart)=' . substr($m,8,2);
			if( strlen($m) > 11 )
				$where .= ' AND MINUTE('.$dbprefix.'datestart)=' . substr($m,10,2);
			if( strlen($m) > 13 )
				$where .= ' AND SECOND('.$dbprefix.'datestart)=' . substr($m,12,2);
		}

		// If a week number is specified
		if( !empty($w) && ($w>=0) ) // Note: week # can be 0
		{
			$where .= ' AND WEEK('.$dbprefix.'datestart)='.intval($w);
		}

		// if a post number is specified, load that post
		if( ($p != '') && ($p != 'all') )
		{
			$p = intval($p);
			$where .= ' AND ID = '. $p;
		}

		// if a post urltitle is specified, load that post
		if( !empty( $title ) )
		{
			$where .= ' AND '.$dbprefix."urltitle = '$title'";
		}

		/*
		 * ----------------------------------------------------
		 * Search stuff:
		 * ----------------------------------------------------
		 */
		if( !empty($s) )
		{
			$search = ' AND (';
			if( $exact ) // We want exact match of title or contents
				$n = '';
			else // The words/sentence are/is to be included in in the title or the contents
				$n = '%';
			if( ($sentence == '1') or ($sentence == 'sentence') )
			{ // Sentence search
				$s = $DB->escape(trim($s));
				$search .= '('.$dbprefix.'title LIKE \''. $n. $s. $n. '\') OR ('.$dbprefix.'content LIKE \''. $n. $s. $n.'\')';
			}
			else
			{ // Word search
				if( strtoupper( $sentence ) == 'OR' )
					$swords = 'OR';
				else
					$swords = 'AND';

				// puts spaces instead of commas
				$s = preg_replace('/, +/', '', $s);
				$s = str_replace(',', ' ', $s);
				$s = str_replace('"', ' ', $s);
				$s = trim($s);
				$s_array = explode(' ',$s);
				$join = '';
				for ( $i = 0; $i < count($s_array); $i++)
				{
					$search .= ' '. $join. ' ( ('.$dbprefix.'title LIKE \''. $n. $DB->escape($s_array[$i]). $n. '\')
																	OR ('.$dbprefix.'content LIKE \''. $n. $DB->escape($s_array[$i]). $n.'\') ) ';
					$join = $swords;
				}
			}

			$search .= ')';

			//echo $search;
		}
		else
		{
			$search = '';
		}

		/*
		 * ----------------------------------------------------
		 * Category stuff:
		 * ----------------------------------------------------
		 */
		$eq = 'IN'; // default

		$cat_array = array();		// this is a global var

		// Check for cat string (which will be handled recursively)
		if( ! ((empty($cat)) || ($cat == 'all') || ($cat == '0')) )
		{ // specified a category string:
			$cat = str_replace(',', ' ', $cat);
			if( strstr($cat, '-') )
			{ // We want to exclude cats
				$eq = 'NOT IN';
				$cats = explode('-', $cat);
				$req_cat_array = explode(' ', $cats[1]);
			}
			else
			{ // We want to include cats
				$req_cat_array = explode(' ', $cat);
			}

			// Getting required sub-categories:
			// and add everything to cat array
			// ----------------- START RECURSIVE CAT LIST ----------------
			cat_query( false );	// make sure the caches are loaded
			foreach( $req_cat_array as $cat_ID )
			{ // run recursively through the cats
				settype( $cat_ID, 'integer' ); // make sure
				if( ! in_array( $cat_ID, $cat_array ) )
				{ // Not already in list
					$cat_array[] = $cat_ID;
					cat_children( $cache_categories, ($blog==1)?0:$blog, $cat_ID, 'cat_req_dummy', 'cat_req',
												'cat_req_dummy', 'cat_req_dummy', 1 );
				}
			}
			// ----------------- END RECURSIVE CAT LIST ----------------
		}

		// Add explicit selections:
		if( ! empty( $catsel ))
		{
			// echo "Explicit selections!<br />";
			$cat_array = array_merge( $cat_array, $catsel );
			array_unique( $cat_array );
		}

		if( empty($cat_array) )
		{
			$whichcat='';
		}
		else
		{
			$whichcat .= ' AND postcat_cat_ID '. $eq.' ('.implode(",", $cat_array). ') ';
			// echo $whichcat;
		}

		/*
		 * ----------------------------------------------------
		 * Author stuff:
		 * ----------------------------------------------------
		 */
		if((empty($author)) || ($author == 'all'))
		{
			$whichauthor='';
		}
		elseif (intval($author))
		{
			$author = intval($author);
			if (stristr($author, '-'))
			{
				$eq = '!=';
				$andor = 'AND';
				$author = explode('-', $author);
				$author = $author[1];
			} else {
				$eq = '=';
				$andor = 'OR';
			}
			$author_array = explode(' ', $author);
			$whichauthor .= ' AND '.$dbprefix.'creator_user_ID '. $eq.' '. $author_array[0];
			for ($i = 1; $i < (count($author_array)); $i = $i + 1) {
				$whichauthor .= ' '. $andor.' '.$dbprefix.'creator_user_ID '. $eq.' '. $author_array[$i];
			}
		}

		$where .= $search. $whichcat . $whichauthor;


		/*
		 * ----------------------------------------------------
		 * order by stuff
		 * ----------------------------------------------------
		 */
		if( (!empty($order)) && ((strtoupper($order) != 'ASC') && (strtoupper($order) != 'DESC')))
		{
			$order='DESC';
		}

		if(empty($orderby))
		{
			$orderby = 'datestart '. $order;
		}
		else
		{
			$orderby_array = explode(' ',$orderby);
			$orderby = $orderby_array[0]. ' '. $order;
			if (count($orderby_array)>1)
			{
				for($i = 1; $i < (count($orderby_array)); $i++)
				{
					$orderby .= ', '.$dbprefix.$orderby_array[$i]. ' '. $order;
				}
			}
		}


		/*
		 * ----------------------------------------------------
		 * Limits:
		 * ----------------------------------------------------
		 */
		if( !empty($poststart) )
		{ // When in backoffice: always paged
			// echo 'POSTSTART-POSTEND ';
			if( $postend < $poststart )
			{
				$postend = $poststart + $posts_per_page - 1;
			}
			if ($what_to_show == 'posts' || $what_to_show == 'paged')
			{
				$posts = $postend - $poststart + 1;
				$limits = ' LIMIT '. ($poststart-1). ','. $posts;
			}
			elseif ($what_to_show == 'days')
			{
				$posts = $postend - $poststart + 1;
				// echo 'days=',$posts;
				$lastpostdate = $this->get_lastpostdate();
				$lastpostdate = mysql2date('Y-m-d 23:59:59',$lastpostdate);
				// echo $lastpostdate;
				$lastpostdate = mysql2date('U',$lastpostdate);
				$this->limitdate_end = $lastpostdate - (($poststart -1) * 86400);
				$this->limitdate_start = $lastpostdate+1 - (($postend) * 86400);
				$where .= ' AND '.$dbprefix.'datestart >= \''. date( 'Y-m-d H:i:s', $this->limitdate_start )
									.'\' AND '.$dbprefix.'datestart <= \''. date('Y-m-d H:i:s', $this->limitdate_end) . '\'';

			}
		}
		elseif( !empty($m) )
		{ // no restriction if we request a month... some permalinks may point to the archive!
			// echo 'ARCHIVE - no limits';
			$limits = '';
		}
		elseif ($what_to_show == 'posts')
		{
			// echo 'LIMIT POSTS!';
			$limits = ' LIMIT '. $posts_per_page;
		}
		elseif( $what_to_show == 'paged' )
		{
			// echo 'PAGED';
			$pgstrt = '';
			if ($paged)
			{
				$pgstrt = (intval($paged) -1) * $posts_per_page. ', ';
			}
			$limits = 'LIMIT '. $pgstrt.$posts_per_page;
		}
		elseif( $what_to_show == 'days' )
		{
			// echo 'LIMIT DAYS ';
			if( !empty($p) || !empty($title) || !empty($s) || !empty($cat) || !empty($author) )
			{ // We are in DAYS mode but we can't restrict on these!
				$limits = '';
			}
			else
			{
				$lastpostdate = $this->get_lastpostdate();
				$lastpostdate = mysql2date('Y-m-d 00:00:00',$lastpostdate);
				$lastpostdate = mysql2date('U',$lastpostdate);
				$otherdate = date('Y-m-d H:i:s', ($lastpostdate - (($posts_per_page-1) * 86400)));
				$where .= ' AND '.$dbprefix.'datestart > \''. $otherdate.'\'';
			}
		}
		/* else
		{
			echo 'DEFAULT - NO LIMIT';
		}*/

		/*
		 * ----------------------------------------------------
		 *	Restrict to the statuses we want to show:
		 * ----------------------------------------------------
		 */
		$where .= ' AND ' . statuses_where_clause( $show_statuses, $dbprefix );

		/*
		 * ----------------------------------------------------
		 * Time limits:
		 * ----------------------------------------------------
		 */
		if( $timestamp_min == 'now' )
		{
			// echo 'hide past';
			$timestamp_min = time();
		}
		if( !empty($timestamp_min) )
		{ // Hide posts before
			// echo 'hide before '.$timestamp_min;
			$date_min = date('Y-m-d H:i:s', $timestamp_min + ($Settings->get('time_difference') * 3600) );
			$where .= ' AND '.$dbprefix.'datestart >= \''. $date_min.'\'';
		}

		if( $timestamp_max == 'now' )
		{
			// echo 'hide future';
			$timestamp_max = time();
		}
		if( !empty($timestamp_max) )
		{ // Hide posts after
			// echo 'after';
			$date_max = date('Y-m-d H:i:s', $timestamp_max + ($Settings->get('time_difference') * 3600) );
			$where .= ' AND '.$dbprefix.'datestart <= \''. $date_max.'\'';
		}

		$this->request = 'SELECT DISTINCT '.implode( ', ', $object_def[ $this->objType]['db_cols'] )
										.' FROM ('.$dbtable.' INNER JOIN T_postcats ON '.$dbIDname.' = postcat_post_ID)
														INNER JOIN T_categories ON postcat_cat_ID = cat_ID ';

		if( $blog == 1 )
		{ // Special case: we aggregate all cats from all blogs
			$this->request .= 'WHERE 1 ';
		}
		else
		{
			$this->request .= 'WHERE cat_blog_ID = '. $blog;
		}

		$this->request .= $where. ' ORDER BY '.$dbprefix.$orderby.' '.$limits;
		// echo '<br />where=',$where;

		if ($preview)
		{	// PREVIEW MODE:
			$this->request = $this->preview_request();
		}

		//echo $this->request;
		$this->result_rows = $DB->get_results( $this->request, OBJECT, 'Item List (Main|Lastpostdate) Query' );

		$this->result_num_rows = $DB->num_rows;
		// echo $this->result_num_rows, ' items';

		// Make a list of posts for future queries!
		// Also make arrays...
		$this->postIDlist = "";
		$this->postIDarray = array();
		if( count( $this->result_rows ) ) foreach( $this->result_rows as $myrow )
		{
			array_unshift( $this->postIDarray, $myrow->$dbIDname );	// new row at beginning
		}
		if( !empty($this->postIDarray) )
		{
			$this->postIDlist = implode( ',', $this->postIDarray );
		}
		// echo "postlist:". $this->postIDlist;

		// Initialize loop stuff:
		$this->restart();
	}

	// dummy mysql query for the preview
	function preview_request()
	{
		// we need globals for the param function
		global $preview_userid, $preview_date, $post_status, $post_locale, $content,
						$post_title, $post_url, $post_category, $post_views, $edit_date,
						$aa, $mm, $jj, $hh, $mn, $ss, $renderers;
		global $DB, $localtimenow, $Messages;

		$id = 0;
		param( 'preview_userid', 'integer', true );
		param( 'post_status', 'string', true );
		param( 'post_locale', 'string', true );
		param( 'content', 'html', true );
		param( 'post_title', 'html', true );
		param( 'post_url', 'string', true );
		param( 'post_category', 'integer', true );
		param( 'post_views', 'integer', 0 );
		param( 'renderers', 'array', array() );

		$post_title = format_to_post( $post_title, 0 );
		$content = format_to_post( $content );
		$post_renderers = implode( '.', $renderers );

		param( 'aa', 'integer', 2000 );
		param( 'mm', 'integer', 1 );
		param( 'jj', 'integer', 1 );
		param( 'hh', 'integer', 20 );
		param( 'mn', 'integer', 30 );
		param( 'ss', 'integer', 0 );
		$jj = ($jj > 31) ? 31 : $jj;
		$hh = ($hh > 23) ? $hh - 24 : $hh;
		$mn = ($mn > 59) ? $mn - 60 : $mn;
		$ss = ($ss > 59) ? $ss - 60 : $ss;
		$post_date = date('Y-m-d H:i:s', mktime( $hh, $mn, $ss, $mm, $jj, $aa ) );


		if( $errcontent = $Messages->display( T_('Invalid post, please correct these errors:'), '', false ) )
		{
			$content = $errcontent;
		}

		// little funky fix for IEwin, rawk on that code
		global $is_winIE;
		if (($is_winIE) && (!isset($IEWin_bookmarklet_fix)))
		{
			$content =	preg_replace('/\%u([0-9A-F]{4,4})/e',	 "'&#'.base_convert('\\1',16,10). ';'", $content);
		}

		return "SELECT
										0 AS ID,
										$preview_userid AS ".$this->dbprefix."creator_user_ID,
										'$post_date' AS ".$this->dbprefix."datestart,
										'$post_date' AS ".$this->dbprefix."datemodified,
										'".$DB->escape($post_status)."' AS ".$this->dbprefix."status,
										'".$DB->escape($post_locale)."' AS ".$this->dbprefix."locale,
										'".$DB->escape($content)."' AS ".$this->dbprefix."content,
										'".$DB->escape($post_title)."' AS ".$this->dbprefix."title,
										NULL AS ".$this->dbprefix."urltitle,
										'".$DB->escape($post_url)."' AS ".$this->dbprefix."url,
										$post_category AS ".$this->dbprefix."main_cat_ID,
										$post_views AS ".$this->dbprefix."views,
										'' AS ".$this->dbprefix."flags,
										".bpost_count_words( $content )." AS ".$this->dbprefix."wordcount,
										'open' AS ".$this->dbprefix."comments,
										'".$DB->escape( $post_renderers )."' AS ".$this->dbprefix.'renderers';
	}


	/**
	 * {@internal ItemList::get_lastpostdate(-)}}
	 */
	function get_lastpostdate()
	{
		global $localtimenow, $postdata;

		// echo 'getting last post date';
		$LastPostList = & new ItemList( $this->blog, $this->show_statuses, '', '', '', $this->cat, $this->catsel,
																		 '', 'DESC', 'datestart', 1, '','', '', '', '', '', '', 1, 'posts',
																		 $this->timestamp_min, $this->timestamp_max, '', $this->objType,
																		 $this->dbtablename, $this->dbprefix, $this->dbIDname );

		if( $LastItem = $LastPostList->get_item() )
		{
			// echo 'we have a last item';
			$last_postdata = $LastPostList->get_postdata();	// will set $postdata;
			$lastpostdate = $postdata['Date'];
		}
		else
		{
			// echo 'we have no last item';
			$lastpostdate = date('Y-m-d H:i:s', $localtimenow);
		}
		// echo $lastpostdate;
		return($lastpostdate);
	}


	/*
	 * ItemList->restart(-)
	 */
	function restart()
	{
		// Set variables for future:
		global $previousday;		// Should be a member var
		$previousday = '';
		$this->row_num = 0;
		$this->main_cat = '';
		$this->group_by_cat = false;
	}


	/*
	 * ItemList->get_max_paged(-)
	 *
	 * return maximum page number for paged display
	 */
	function get_max_paged()
	{
		if( empty($this->max_paged) )
		{ // Not already cached:
			$this->calc_max();
		}
		//echo 'max paged= ', $this->max_paged;
		return $this->max_paged;
	}


	/**
	 * Template function: display last mod date (datetime) of Item
	 *
	 * {@internal ItemList::mod_date(-) }}
	 *
	 * @param string date/time format: leave empty to use locale default date format
	 * @param boolean true if you want GMT
	 */
	function mod_date( $format = '', $useGM = false )
	{
		$mod_date_timestamp = 0;
		foreach( $this->result_rows as $loop_row )
		{ // Go through whole list
			$m = $loop_row->post_datemodified;
			$loop_mod_date = mktime(substr($m,11,2),substr($m,14,2),substr($m,17,2),substr($m,5,2),substr($m,8,2),substr($m,0,4));
			if( $loop_mod_date > $mod_date_timestamp )
				$mod_date_timestamp = $loop_mod_date;
		}

		if( empty($format) )
			echo date_i18n( locale_datefmt(), $mod_date_timestamp, $useGM );
		else
			echo date_i18n( $format, $mod_date_timestamp, $useGM );
	}


	/*
	 * ItemList->get_total_num_posts(-)
	 *
	 * return total number of posts
	 */
	function get_total_num_posts()
	{
		if( empty($this->total_num_posts) )
		{ // Not already cached:
			$this->calc_max();
		}
		return $this->total_num_posts;
	}


	/*
	 * Private ItemList->calc_max(-)
	 *
	 * @todo use COUNT(*)
	 */
	function calc_max()
	{
		global $DB;

		if( $this->preview )
			return 1;	// 1 row in preview mode

		$nxt_request = $this->request;
		if( $pos = strpos(strtoupper($this->request), 'LIMIT'))
		{ // Remove the limit form the request
			$nxt_request = substr($this->request, 0, $pos);
		}
		//echo $nxt_request;

		$DB->query( $nxt_request );
		$this->total_num_posts = $DB->num_rows;
		$this->max_paged = intval( ($this->total_num_posts-1) / max($this->posts_per_page, $this->result_num_rows)) +1;
		if( $this->max_paged < 1 )
			$this->max_paged =1;
	}


	/**
	 * {@internal ItemList::get_category_group()}}
	 */
	function get_category_group()
	{
		global $row;

		$this->group_by_cat = true;

		if( ($this->row_num > $this->result_num_rows) || ($this->result_num_rows == 0) )
		{ // We are at the the end!
			// echo 'END';
			return false;
		}

		if( $this->row_num == 0 )
		{ // We need to initialize
			$this->row = & $this->result_rows[0];
			$row = $this->row;
			$this->row_num = 1;
			$this->get_postdata();
		}

		// Memorize main cat
		$this->main_cat = $this->row->post_main_cat_ID;

		// Go back now so that the fetch row doesn't skip one!
		$this->row_num --;

		#already done in get_postdata: $this->last_Item = new Item( $this->row ); // COPY !
		return $this->last_Item;
	}


	/**
	 * {@internal ItemList::get_item()}}
	 */
	function get_item( )
	{
		global $row;
		if( $this->row_num >= $this->result_num_rows )
		{ // We would pass the end!
			$this->row_num++;
			return false;
		}
		$this->row = & $this->result_rows[$this->row_num];
		$row = $this->row;
		// echo '<p>accessing row['. $this->row_num. ']:',$this->row->post_title,'</p>';
		$this->row_num++;
		$this->get_postdata();


		if( $this->group_by_cat && ($this->main_cat != $this->row->post_main_cat_ID) )
		{ // Category change
			// echo '<p>CAT CHANGE!</p>';
			return false;
		}

		#already done in get_postdata: $this->last_Item = new Item( $this->row ); // COPY !
		return $this->last_Item;
	}


	/**
	 * {@internal ItemList::get_postdata(-)}}
	 *
	 * Init postdata
	 */
	function get_postdata()
	{
		global $id, $postdata, $authordata, $day, $page, $pages, $multipage, $more, $numpages;
		global $pagenow, $current_User;

		$this->last_Item = new $this->objType( $this->row, $this->dbtablename, $this->dbprefix, $this->dbIDname ); // COPY!!

		$id = $this->last_Item->ID;
		// echo 'starting ',$current_Item->title;
		$postdata = array (
				'ID'         => $this->last_Item->ID,
				'Author_ID'  => $this->last_Item->Author->ID,
				'Date'       => $this->last_Item->issue_date,
				'Status'     => $this->last_Item->status,
				'Locale'     => $this->last_Item->locale,
				'Content'    => $this->last_Item->content,
				'Title'      => $this->last_Item->title,
				'Url'        => $this->last_Item->url,
				'Category'   => $this->last_Item->main_cat_ID,
				'Flags'      => explode( ',', $this->last_Item->flags ),
				'Wordcount'  => $this->last_Item->wordcount,
				'views'      => $this->last_Item->views,
				'comments'   => $this->last_Item->comments
			);

		// echo ' title: ',$postdata['Title'];
		$authordata = get_userdata($postdata['Author_ID']);
		$day = mysql2date('d.m.y',$postdata['Date']);
		$currentmonth = mysql2date('m',$postdata['Date']);
		$numpages = 1;
		if( !$page )
			$page = 1;
		if( isset($p) )
			$more = 1;
		$content = $postdata['Content'];
		if( preg_match('/<!--nextpage-->/', $postdata['Content']) )
		{
			if( $page > 1 )
				$more = 1;
			$multipage = 1;
			$content = $postdata['Content'];
			$content = str_replace("\n<!--nextpage-->\n", '<!--nextpage-->', $content);
			$content = str_replace("\n<!--nextpage-->", '<!--nextpage-->', $content);
			$content = str_replace("<!--nextpage-->\n", '<!--nextpage-->', $content);
			$pages = explode('<!--nextpage-->', $content);
			$numpages = count($pages);
		}
		else
		{
			$pages[0] = $postdata['Content'];
			$multipage = 0;
		}
	}


	/**
	 * Template function: Display the date if it has changed since last call
	 *
	 * {@internal ItemList::date_if_changed(-) }}
	 *
	 * @param string string to display before the date (if changed)
	 * @param string string to display after the date (if changed)
	 * @param string date/time format: leave empty to use locale default time format
	 */
	function date_if_changed( $before='<h2>', $after='</h2>', $format='' )
	{
		$current_item_date = $this->last_Item->get( 'issue_date' );
		if($format=='')
		{
			$current_item_date = mysql2date( locale_datefmt(), $current_item_date );
		}
		else
		{
			$current_item_date = mysql2date( $format, $current_item_date );
		}

		if( $current_item_date != $this->last_displayed_date )
		{
			$this->last_displayed_date = $current_item_date;

			echo $before;
			echo $current_item_date;
			echo $after;
		}
	}

	/**
	 * Template function: display message if list is empty
	 *
	 * {@internal ItemList::display_if_empty(-) }}
	 *
	 * @param string String to display if list is empty
   * @return true if empty
	 */
	function display_if_empty( $message = '' )
	{
		if( empty($message) )
		{	// Default message:
			$message = T_('Sorry, there is no post to display...');
		}

		return parent::display_if_empty( $message );
	}
}

/*
 * $Log$
 * Revision 1.11  2004/12/20 19:49:24  fplanque
 * cleanup & factoring
 *
 * Revision 1.10  2004/12/17 20:38:52  fplanque
 * started extending item/post capabilities (extra status, type)
 *
 * Revision 1.9  2004/12/15 20:50:34  fplanque
 * heavy refactoring
 * suppressed $use_cache and $sleep_after_edit
 * code cleanup
 *
 * Revision 1.8  2004/12/14 21:01:06  fplanque
 * minor fixes
 *
 * Revision 1.7  2004/12/14 18:32:15  fplanque
 * quick optimizations
 *
 * Revision 1.6  2004/12/13 21:29:58  fplanque
 * refactoring
 *
 * Revision 1.5  2004/12/10 19:45:55  fplanque
 * refactoring
 *
 * Revision 1.4  2004/12/09 21:21:20  fplanque
 * introduced foreign key support
 *
 * Revision 1.3  2004/11/09 00:25:12  blueyed
 * minor translation changes (+MySQL spelling :/)
 *
 * Revision 1.2  2004/10/14 18:31:25  blueyed
 * granting copyright
 *
 * Revision 1.1  2004/10/13 22:46:32  fplanque
 * renamed [b2]evocore/*
 *
 * Revision 1.62  2004/10/12 16:12:18  fplanque
 * Edited code documentation.
 *
 * Revision 1.4  2003/8/22 22:12:30  jupiterx
 * Added wordcount functionality
 */
?>