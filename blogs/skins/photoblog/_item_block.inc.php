<?php
/**
 * This is the template that displays the item block
 *
 * This file is not meant to be called directly.
 * It is meant to be called by an include in the main.page.php template (or other templates)
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2009 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package evoskins
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

global $Item;

// Default params:
$params = array_merge( array(
		'feature_block'          => false,
		'item_class'             => 'bPost',
		'item_status_class'      => 'bPost',
		'content_mode'           => 'full', // We want regular "full" content, even in category browsing: i-e no excerpt or thumbnail
		'image_size'	           =>	'', // Image is handled separately
		'url_link_text_template' => '', // link will be displayed (except player if podcast)
	), $params );

?>

<div id="<?php $Item->anchor_id() ?>" class="<?php $Item->div_classes( $params ) ?>" lang="<?php $Item->lang() ?>">

	<?php
		$Item->locale_temp_switch(); // Temporarily switch to post locale (useful for multilingual blogs)
	?>

	<?php
		// Display images that are linked to this post:
		$Item->images( array(
				'before' =>              '<div class="bImages">',
				'before_image' =>        '<div class="image_block">',
				'before_image_legend' => '<div class="image_legend">',
				'after_image_legend' =>  '</div>',
				'after_image' =>         '</div>',
				'after' =>               '</div>',
				'image_size' =>          'fit-720x500',
				/* Comment the above line to use the default image size
				 * (fit-720x500). Possible values for the image_size
				 * parameter are:
				 * fit-720x500, fit-640x480, fit-520x390, fit-400x320,
				 * fit-320x320, fit-160x160, fit-160x120, fit-80x80,
				 * crop-80x80, crop-64x64, crop-48x48, crop-32x32,
				 * crop-15x15
				 * See also the $thumbnail_sizes array in conf/_advanced.php.
				 */
				'files_position' =>      '',
			) );
	?>


	<div class="bDetails">

		<div class="bSmallHead">

			<?php
				// Link to comments, trackbacks, etc.:
				$Item->feedback_link( array(
								'type' => 'feedbacks',
								'link_before' => '<div class="action_right">',
								'link_after' => '</div>',
								'link_text_zero' => get_icon( 'nocomment' ),
								'link_text_one' => get_icon( 'comments' ),
								'link_text_more' => get_icon( 'comments' ),
								'link_title' => '#',
								'use_popup' => true,
							) );

				$Item->permanent_link( array(
						'before'    => '<div class="action_right">',
						'after'     => '</div>',
						'text' => T_('Permalink'),
					) );
			?>

			<?php
				$Item->edit_link( array( // Link to backoffice for editing
						'before'    => '<div class="action_right">',
						'after'     => '</div>',
						'text'      => T_('Edit...'),
            'title'     => T_('Edit title/description...'),
					) );
			?>

			<h3 class="bTitle"><?php $Item->title(); ?></h3>

			<?php
				$Item->issue_date( array(
						'before'      => '<span class="timestamp">',
						'after'       => '</span>',
						'date_format' => locale_datefmt().' H:i',
					) );
			?>

		</div>

		<?php
			// ---------------------- POST CONTENT INCLUDED HERE ----------------------
			skin_include( '_item_content.inc.php', $params );
			// Note: You can customize the default item feedback by copying the generic
			// /skins/_item_feedback.inc.php file into the current skin folder.
			// -------------------------- END OF POST CONTENT -------------------------
		?>

		<div class="bSmallPrint">
		<?php
			$Item->author( array(
					'before'    => T_('By').' ',
					'after'     => ' &bull; ',
				) );
		?>

		<?php
			$Item->categories( array(
				'before'          => T_('Albums').': ',
				'after'           => ' ',
				'include_main'    => true,
				'include_other'   => true,
				'include_external'=> true,
				'link_categories' => true,
			) );
		?>

		<?php
			// List all tags attached to this post:
			$Item->tags( array(
					'before' =>         ' &bull; '.T_('Tags').': ',
					'after' =>          ' ',
					'separator' =>      ', ',
				) );
		?>

		<?php
			// URL link, if the post has one:
			$Item->url_link( array(
					'before'        => ' &bull; '.T_('Link').': ',
					'after'         => ' ',
					'text_template' => '$url$',
					'url_template'  => '$url$',
					'target'        => '',
					'podcast'       => false,        // DO NOT display mp3 player if post type is podcast
				) );
		?>

		</div>
	</div>

	<?php
		// ------------------ FEEDBACK (COMMENTS/TRACKBACKS) INCLUDED HERE ------------------
		skin_include( '_item_feedback.inc.php', array(
				'before_section_title' => '<h4>',
				'after_section_title'  => '</h4>',
			) );
		// Note: You can customize the default item feedback by copying the generic
		// /skins/_item_feedback.inc.php file into the current skin folder.
		// ---------------------- END OF FEEDBACK (COMMENTS/TRACKBACKS) ---------------------
	?>

	<?php
		locale_restore_previous();	// Restore previous locale (Blog locale)
	?>

</div>
<?php
/*
 * $Log$
 * Revision 1.3  2009/10/11 03:00:11  blueyed
 * Add "position" and "order" properties to attachments.
 * Position can be "teaser" or "aftermore" for now.
 * Order defines the sorting of attachments.
 * Needs testing and refinement. Upgrade might work already, be careful!
 *
 * Revision 1.2  2009/06/28 17:37:54  tblue246
 * Adding list of possible values for the image_size param to Photoblog skin to make life easier for new/unexperienced users.
 *
 * Revision 1.1  2009/05/23 14:12:42  fplanque
 * All default skins now support featured posts and intro posts.
 *
 */
?>
