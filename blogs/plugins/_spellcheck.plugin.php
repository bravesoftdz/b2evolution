<?php
/**
 * This file implements the Test plugin for b2evolution
 *
 * This plugin responds to virtually all possible plugin events :P
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2004 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package plugins
 */
if( !defined('DB_USER') ) die( 'Please, do not access this page directly.' );


/**
 * @package plugins
 */
class spellcheck_plugin extends Plugin
{
	var $code = 'cafeSpell';
	var $name = 'Spellchecker';
	var $priority = 50;
	var $apply_when = 'opt-out';
	var $apply_to_html = true;
	var $apply_to_xml = true;
	var $short_desc;
	var $long_desc;

	var $useSpellcheckOnThisPage = false; // So far we have not requested it on this page.

	/**
	 * Should be toolbar be displayed?
	 */
	var $display = true;

	/**
	 * Constructor
	 *
	 * {@internal spellcheck_plugin::spellcheck_plugin(-)}}
	 */
	function spellcheck_plugin()
	{
		$this->short_desc = T_('Simple Spellchecker (English only)');
		$this->long_desc = T_('This plugins calls a simple online spellchecker.');
	}


 	/**
	 * Display an editor button
	 *
	 * {@internal Plugin::DisplayEditorButton(-)}}
	 *
	 * @param array Associative array of parameters
	 * @return boolean did we display a toolbar?
	 */
	function DisplayEditorButton( & $params )
	{
		// This means we are using the spellchecker on this page!
 		$this->useSpellcheckOnThisPage = true;
	 	?>
		<input type="button" value="<?php echo T_('Spellcheck') ?>" onclick="DoSpell('post','content','');" />
		<?php
		return true;
	}


	/**
	 * Called when ending the admin html head section
	 *
	 * {@internal Plugin::AdminEndHtmlHead(-)}}
	 *
	 * @param array Associative array of parameters
	 * @return boolean did we do something?
	 */
	function AdminEndHtmlHead( & $params )
	{
		global $admin_tab, $admin_url;

		if( $admin_tab != 'new' )
		{	// We won't need the spellchecker
			return false;
		}

		?>
		<script type="text/javascript" language="javascript">
			<!--
			function DoSpell(formname, subject, body)
			{
				document.SPELLDATA.formname.value=formname
				document.SPELLDATA.subjectname.value=subject
				document.SPELLDATA.messagebodyname.value=body
				document.SPELLDATA.companyID.value="custom\\http://cafelog.com"
				document.SPELLDATA.language.value=1033
				document.SPELLDATA.opener.value="<?php echo $admin_url ?>sproxy.php"
				document.SPELLDATA.formaction.value="http://www.spellchecker.com/spell/startspelling.asp "
				window.open("<?php echo $admin_url ?>b2spell.php","Spell","toolbar=no,directories=no,location=yes,resizable=yes,width=620,height=400,top=100,left=100")
			}
			// End -->
		</script>
		<?php

		return true;
	}


	/**
	 * Called right after displaying the admin page footer
	 *
	 * {@internal Plugin::AdminAfterPageFooter(-)}}
	 *
	 * @param array Associative array of parameters
	 * @return boolean did we do something?
	 */
	function AdminAfterPageFooter( & $params )
	{
	 	if( ! $this->useSpellcheckOnThisPage )
		{ // no spellcheck on this page, no need for this...
	 		return false;
		}
		?>
    <!-- this is for the spellchecker -->
		<form action="" name="SPELLDATA"><div>
		<input name="formname" type="hidden" value="" />
		<input name="messagebodyname" type="hidden" value="" />
		<input name="subjectname" type="hidden" value="" />
		<input name="companyID" type="hidden" value="" />
		<input name="language" type="hidden" value="" />
		<input name="opener" type="hidden" value="" />
		<input name="formaction" type="hidden" value="" />
		</div></form>
		<?php
		return true;
	}


	/**
	 * Perform rendering
	 *
	 * {@internal spellcheck_plugin::render(-)}}
	 *
	 * @param string content to render (by reference) / rendered content
	 * @param string Output format, see {@link format_to_output()}
	 * @return boolean true if we can render something for the required output format
	 */
	function render( & $content, $format )
	{
		if( ! parent::render( $content, $format ) )
		{	// We cannot render the required format
			return false;
		}

	}
}
?>