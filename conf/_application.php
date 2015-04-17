<?php
/**
 * This is b2evolution's application config file.
 *
 * @package conf
 */
if( !defined('EVO_CONFIG_LOADED') ) die( 'Please, do not access this page directly.' );


$app_name = 'b2evolution';
$app_shortname = 'b2evo';

/**
 * The version of the application.
 * Note: This has to be compatible with {@link http://us2.php.net/en/version-compare}.
 * @global string
 */
$app_version = '6.3.0-alpha';

/**
 * Release date (ISO)
 * @global string
 */
$app_date = '2015-04-16';

/**
 * Long version string for checking differences
 */
$app_version_long = $app_version.'-'.$app_date;

/**
 * This is used to check if the database is up to date.
 *
 * This will be incrememented by 100 with each change in {@link upgrade_b2evo_tables()}
 * in order to leave space for maintenance releases.
 *
 * {@internal Before changing this in CVS, it should be discussed! }}
 */
$new_db_version = 11380;

/**
 * Minimum PHP version required for b2evolution to function properly. It will contain each module own minimum PHP version as well.
 * @global array
 */
$required_php_version = array( 'application' => '5.0' );

/**
 * Minimum MYSQL version required for b2evolution to function properly. It will contain each module own minimum MYSQL version as well.
 * @global array
 */
$required_mysql_version = array( 'application' => '5.0.3' );

/**
 * Is displayed on the login screen:
 */
$app_footer_text = '<a href="http://b2evolution.net/" title="visit b2evolution\'s website"><strong>b2evolution '.$app_version.'</strong></a>
		&ndash;
		<a href="http://b2evolution.net/about/gnu-gpl-license" class="nobr">GPL License</a>';

$copyright_text = '<span class="nobr">&copy;2003-2015 by <a href="http://fplanque.net/">Fran&ccedil;ois</a> <a href="http://fplanque.com/">Planque</a> &amp; <a href="http://b2evolution.net/about/about-us">others</a>.</span>';

/**
 * Modules to load
 *
 * This is most useful when extending evoCore with features beyond what b2evolution does and when those features do not
 * fit nicely into a plugin, mostly when they are too large or too complex.
 *
 * Note: a long term goal is to be able to disable some b2evolution feature sets that would not be needed. This should
 * however only be used for large enough feature sets to make it worth the trouble. NO MICROMANAGING here.
 * Try commenting out the 'collections' module to revert to pretty much just evocore.
 */
$modules = array(
		'_core',
		'collections',  // TODO: installer won't work without this module
		'files',
		'sessions',
		'messaging',
		'maintenance',
	);

/* Overrides */

$app_footer_text = sprintf('<a href="https://duckduckgo.com/?q=!+%1$s" title="Viziti la TTT-ejon de %1$s"><b>%1$s %2$s</b></a>
		&#183;
		<a href="http://www.esperanto.mv.ru/Cetero/gpl.html" class="nobr">la Ĝenerala Publika Permesilo de GNU</a>', $app_name, $app_version);

$copyright_text = sprintf('<span class="nobr">Kopirajto &#169; de 2003 ĝis %d de <a href="http://fplanque.net/" hreflang="fr">François Planque</a> kaj <a href="http://b2evolution.net/about/about-us" hreflang="en">aliuloj</a>. Plibonigoj kaj plivastigoj estas faritaj de <a href="https://github.com/keithbowes/b2evolution/graphs/contributors" hreflang="en" title="Kontribuintoj en Github">mutaj aliaj</a>.</span>', date('Y'));

?>
