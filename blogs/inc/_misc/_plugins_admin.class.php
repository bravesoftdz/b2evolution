<?php
/**
 * This file implements the {@link Plugins_admin} class, which gets used for administrative
 * handling of the {@link Plugin Plugins}.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2006 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2006 by Daniel HAHLER - {@link http://daniel.hahler.de/}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 *
 * {@internal Open Source relicensing agreement:
 * Daniel HAHLER grants Francois PLANQUE the right to license
 * Daniel HAHLER's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * @author blueyed: Daniel HAHLER
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


/**
 * A Plugins object that loads all Plugins, not just the enabled ones. This is needed for the backoffice plugin management.
 *
 * @package evocore
 */
class Plugins_admin extends Plugins
{
	/**
	 * Load all plugins (not just enabled ones).
	 */
	var $sql_load_plugins_table = '
			SELECT plug_ID, plug_priority, plug_classname, plug_code, plug_name, plug_shortdesc, plug_apply_rendering, plug_status, plug_version, plug_spam_weight
			  FROM T_plugins
			 ORDER BY plug_priority, plug_classname';

	/**
	 * @var boolean Gets used in base class
	 * @static
	 */
	var $is_admin_class = true;


	/**
	 * Discover and register all available plugins below {@link $plugins_path}.
	 */
	function discover()
	{
		global $Debuglog, $Timer;

		$Timer->resume('plugins_discover');

		$Debuglog->add( 'Discovering plugins...', 'plugins' );

		$Timer->resume('plugins_discover::get_filenames');
		$plugin_files = get_filenames( $this->plugins_path, true, false );
		$Timer->pause('plugins_discover::get_filenames');

		foreach( $plugin_files as $path )
		{
			if( ! preg_match( '~/_([^/]+)\.plugin\.php$~', $path, $match ) && is_file( $path ) )
			{
				continue;
			}
			$classname = $match[1].'_plugin';

			if( substr( dirname($path), 0, 1 ) == '_' )
			{ // Skip plugins which are in a directory that starts with an underscore ("_")
				continue;
			}

			if( $this->get_by_classname($classname) )
			{
				$Debuglog->add( 'Skipping duplicate plugin (classname '.$classname.')!', array('error', 'plugins') );
				continue;
			}

			// TODO: check for parse errors before, e.g. through /htsrc/async.php..?!

			$this->register( $classname, 0, -1, NULL, $path ); // auto-generate negative ID; will return string on error.
		}

		$Timer->pause('plugins_discover');
	}


	/**
	 * Get a list of methods that are supported as events out of the Plugin's
	 * source file.
	 *
	 * @todo Extend to get list of defined classes and global functions and check this list before sourcing/including a Plugin! (prevent fatal error)
	 *
	 * @return array
	 */
	function get_registered_events( $Plugin )
	{
		global $Timer, $Debuglog;

		$Timer->resume( 'plugins_detect_events' );

		$plugin_class_methods = array();

		if( ! is_readable($Plugin->classfile_path) )
		{
			$Debuglog->add( 'get_registered_events(): "'.$Plugin->classfile_path.'" is not readable.', array('plugins', 'error') );
			return array();
		}

		$classfile_contents = @file_get_contents( $Plugin->classfile_path );
		if( ! is_string($classfile_contents) )
		{
			$Debuglog->add( 'get_registered_events(): "'.$Plugin->classfile_path.'" could not get read.', array('plugins', 'error') );
			return array();
		}

		// TODO: allow optional Plugin callback to get list of methods. Like Plugin::GetRegisteredEvents().

		if( preg_match_all( '~^\s*function\s+(\w+)~mi', $classfile_contents, $matches ) )
		{
			$plugin_class_methods = $matches[1];
		}
		else
		{
			$Debuglog->add( 'No functions found in file "'.$Plugin->classfile_path.'".', array('plugins', 'error') );
			return array();
		}

		$supported_events = $this->get_supported_events();
		$supported_events = array_keys($supported_events);
		$verified_events = array_intersect( $plugin_class_methods, $supported_events );

		$Timer->pause( 'plugins_detect_events' );

		// TODO: Report, when difference in $events_verified and what getRegisteredEvents() returned
		return $verified_events;
	}


	/**
	 * Install a plugin into DB.
	 *
	 * @param string Classname of the plugin to install
	 * @param string Initial DB Status of the plugin ("enabled", "disabled", "needs_config", "broken")
	 * @param string|NULL Optional classfile path, if not default (used for tests).
	 * @return string|Plugin The installed Plugin (perhaps with $install_dep_notes set) or a string in case of error.
	 */
	function & install( $classname, $plug_status = 'enabled', $classfile_path = NULL )
	{
		global $DB, $Debuglog;

		$this->load_plugins_table();

		// Register the plugin:
		$Plugin = & $this->register( $classname, 0, -1, NULL, $classfile_path ); // Auto-generates negative ID; New ID will be set a few lines below

		if( is_string($Plugin) )
		{ // return error message from register()
			return $Plugin;
		}

		if( isset($Plugin->number_of_installs)
		    && ( $this->count_regs( $Plugin->classname ) >= $Plugin->number_of_installs ) )
		{
			$this->unregister( $Plugin );
			$r = T_('The plugin cannot be installed again.');
			return $r;
		}

		$install_return = $Plugin->BeforeInstall();
		if( $install_return !== true )
		{
			$this->unregister( $Plugin );
			$r = T_('The installation of the plugin failed.');
			if( is_string($install_return) )
			{
				$r .= '<br />'.$install_return;
			}
			return $r;
		}

		// Dependencies:
		if( $this->is_admin_class )
		{ // We must check dependencies against installed Plugins ($Plugins)
			global $Plugins;
			$dep_msgs = $Plugins->validate_dependencies( $Plugin, 'enable' );
		}
		else
		{
			$dep_msgs = $this->validate_dependencies( $Plugin, 'enable' );
		}
		if( ! empty( $dep_msgs['error'] ) )
		{ // required dependencies
			$this->unregister( $Plugin );
			$r = T_('Some plugin dependencies are not fulfilled:').' <ul><li>'.implode( '</li><li>', $dep_msgs['error'] ).'</li></ul>';
			return $r;
		}

		// All OK, install:
		if( empty($Plugin->code) )
		{
			$Plugin->code = NULL;
		}

		$Plugin->status = $plug_status;

		// Record into DB
		$DB->begin();

		$DB->query( '
				INSERT INTO T_plugins( plug_classname, plug_priority, plug_code, plug_apply_rendering, plug_version, plug_status )
				VALUES( "'.$classname.'", '.$Plugin->priority.', '.$DB->quote($Plugin->code).', '.$DB->quote($Plugin->apply_rendering).', '.$DB->quote($Plugin->version).', '.$DB->quote($Plugin->status).' ) ' );

		// Unset auto-generated ID info
		unset( $this->index_ID_Plugins[ $Plugin->ID ] );
		$key = array_search( $Plugin->ID, $this->sorted_IDs );

		// New ID:
		$Plugin->ID = $DB->insert_id;
		$this->index_ID_Plugins[ $Plugin->ID ] = & $Plugin;
		$this->index_ID_rows[ $Plugin->ID ] = array(
				'plug_ID' => $Plugin->ID,
				'plug_priority' => $Plugin->priority,
				'plug_classname' => $Plugin->classname,
				'plug_code' => $Plugin->code,
				'plug_apply_rendering' => $Plugin->apply_rendering,
				'plug_status' => $Plugin->status,
				'plug_version' => $Plugin->version,
			);
		$this->sorted_IDs[$key] = $Plugin->ID;

		$this->save_events( $Plugin );

		$DB->commit();

		$Debuglog->add( 'Installed plugin: '.$Plugin->name.' ID: '.$Plugin->ID, 'plugins' );

		if( ! empty($dep_msgs['note']) )
		{ // Add dependency notes
			$Plugin->install_dep_notes = $dep_msgs['note'];
		}

		// Do the stuff that we've skipped in register method at the beginning:

		$this->init_settings( $Plugin );

		$tmp_params = array('db_row' => $this->index_ID_rows[$Plugin->ID], 'is_installed' => false);

		if( $Plugin->PluginInit( $tmp_params ) === false && ! $this->is_admin_class )
		{
			$Debuglog->add( 'Unregistered plugin, because PluginInit returned false.', 'plugins' );
			$this->unregister( $Plugin );
			$Plugin = '';
		}

		if( ! defined('EVO_IS_INSTALLING') || ! EVO_IS_INSTALLING )
		{ // do not sort, if we're installing/upgrading.. instantiating Plugins might cause a fatal error!
			$this->sort();
		}

		return $Plugin;
	}


	/**
	 * Save the events that the plugin provides into DB, while removing obsolete
	 * entries (that the plugin does not register anymore).
	 *
	 * @param Plugin Plugin to save events for
	 * @param array|NULL List of events to save as enabled for the Plugin.
	 *              By default all provided events get saved as enabled. Pass array() to discover only new ones.
	 * @param array List of events to save as disabled for the Plugin.
	 *              By default, no events get disabled. Disabling an event takes priority over enabling.
	 * @return boolean True, if events have changed, false if not.
	 */
	function save_events( $Plugin, $enable_events = NULL, $disable_events = NULL )
	{
		global $DB, $Debuglog;

		$r = false;

		$saved_events = array();
		foreach( $DB->get_results( '
				SELECT pevt_event, pevt_enabled
				  FROM T_pluginevents
				 WHERE pevt_plug_ID = '.$Plugin->ID ) as $l_row )
		{
			$saved_events[$l_row->pevt_event] = $l_row->pevt_enabled;
		}
		$available_events = $this->get_registered_events( $Plugin );
		$obsolete_events = array_diff( array_keys($saved_events), $available_events );

		if( is_null( $enable_events ) )
		{ // Enable all events:
			$enable_events = $available_events;
		}
		if( is_null( $disable_events ) )
		{
			$disable_events = array();
		}
		if( $disable_events )
		{ // Remove events to be disabled from enabled ones:
			$enable_events = array_diff( $enable_events, $disable_events );
		}

		// New discovered events:
		$discovered_events = array_diff( $available_events, array_keys($saved_events), $enable_events, $disable_events );


		// Delete obsolete events from DB:
		if( $obsolete_events && $DB->query( '
				DELETE FROM T_pluginevents
				WHERE pevt_plug_ID = '.$Plugin->ID.'
					AND pevt_event IN ( "'.implode( '", "', $obsolete_events ).'" )' ) )
		{
			$r = true;
		}

		if( $discovered_events )
		{
			$DB->query( '
				INSERT INTO T_pluginevents( pevt_plug_ID, pevt_event, pevt_enabled )
				VALUES ( '.$Plugin->ID.', "'.implode( '", 1 ), ('.$Plugin->ID.', "', $discovered_events ).'", 1 )' );
			$r = true;

			$Debuglog->add( 'Discovered events ['.implode( ', ', $discovered_events ).'] for Plugin '.$Plugin->name, 'plugins' );
		}

		$new_events_enabled = array();
		if( $enable_events )
		{
			foreach( $enable_events as $l_event )
			{
				if( ! isset( $saved_events[$l_event] ) || ! $saved_events[$l_event] )
				{ // Event not saved yet or not enabled
					$new_events_enabled[] = $l_event;
				}
			}
			if( $new_events_enabled )
			{
				$DB->query( '
					REPLACE INTO T_pluginevents( pevt_plug_ID, pevt_event, pevt_enabled )
					VALUES ( '.$Plugin->ID.', "'.implode( '", 1 ), ('.$Plugin->ID.', "', $new_events_enabled ).'", 1 )' );
				$r = true;
			}
			$Debuglog->add( 'Enabled events ['.implode( ', ', $new_events_enabled ).'] for Plugin '.$Plugin->name, 'plugins' );
		}

		$new_events_disabled = array();
		if( $disable_events )
		{
			foreach( $disable_events as $l_event )
			{
				if( ! isset( $saved_events[$l_event] ) || $saved_events[$l_event] )
				{ // Event not saved yet or enabled
					$new_events_disabled[] = $l_event;
				}
			}
			if( $new_events_disabled )
			{
				$DB->query( '
					REPLACE INTO T_pluginevents( pevt_plug_ID, pevt_event, pevt_enabled )
					VALUES ( '.$Plugin->ID.', "'.implode( '", 0 ), ('.$Plugin->ID.', "', $new_events_disabled ).'", 0 )' );
				$r = true;
			}
			$Debuglog->add( 'Disabled events ['.implode( ', ', $new_events_disabled ).'] for Plugin '.$Plugin->name, 'plugins' );
		}

		if( $r )
		{ // Something has changed: Reload event index
			foreach( array_merge($obsolete_events, $discovered_events, $new_events_enabled, $new_events_disabled) as $event )
			{
				if( strpos($event, 'RenderItemAs') === 0 )
				{ // Clear pre-rendered content cache, if RenderItemAs* events have been added or removed:
					$DB->query( 'DELETE FROM T_item__prerendering WHERE 1' );
					$ItemCache = & get_Cache( 'ItemCache' );
					$ItemCache->clear();
					break;
				}
			}

			$this->load_events();
		}

		return $r;
	}


	/**
	 * Set the status of an event for a given Plugin.
	 *
	 * @return boolean True, if status has changed; false if not
	 */
	function set_event_status( $plugin_ID, $plugin_event, $enabled )
	{
		global $DB;

		$enabled = $enabled ? 1 : 0;

		$DB->query( '
			UPDATE T_pluginevents
			   SET pevt_enabled = '.$enabled.'
			 WHERE pevt_plug_ID = '.$plugin_ID.'
			   AND pevt_event = "'.$plugin_event.'"' );

		if( $DB->rows_affected )
		{
			$this->load_events();

			if( strpos($plugin_event, 'RenderItemAs') === 0 )
			{ // Clear pre-rendered content cache, if RenderItemAs* events have been added or removed:
				$DB->query( 'DELETE FROM T_item__prerendering WHERE 1' );
				$ItemCache = & get_Cache( 'ItemCache' );
				$ItemCache->clear();
				break;
			}

			return true;
		}

		return false;
	}


	/**
	 * Sort the list of plugins.
	 *
	 * WARNING: do NOT sort by anything else than priority unless you're handling a list of NOT-YET-INSTALLED plugins!
	 *
	 * @param string Order: 'priority' (default), 'name'
	 */
	function sort( $order = 'priority' )
	{
		$this->load_plugins_table();

		foreach( $this->sorted_IDs as $k => $plugin_ID )
		{ // Instantiate every plugin, so invalid ones do not get unregistered during sorting (crashes PHP, because $sorted_IDs gets changed etc)
			if( ! $this->get_by_ID( $plugin_ID ) )
			{
				unset($this->sorted_IDs[$k]);
			}
		}

		switch( $order )
		{
			case 'name':
				usort( $this->sorted_IDs, array( & $this, 'sort_Plugin_name') );
				break;

			case 'group':
				usort( $this->sorted_IDs, array( & $this, 'sort_Plugin_group') );
				break;

			default:
				// Sort array by priority:
				usort( $this->sorted_IDs, array( & $this, 'sort_Plugin_priority') );
		}

		$this->current_idx = 0;
	}

	/**
	 * Callback function to sort plugins by priority (and classname, if they have same priority).
	 */
	function sort_Plugin_priority( & $a_ID, & $b_ID )
	{
		$a_Plugin = & $this->get_by_ID( $a_ID );
		$b_Plugin = & $this->get_by_ID( $b_ID );

		$r = $a_Plugin->priority - $b_Plugin->priority;

		if( $r == 0 )
		{
			$r = strcasecmp( $a_Plugin->classname, $b_Plugin->classname );
		}

		return $r;
	}

	/**
	 * Callback function to sort plugins by name.
	 *
	 * WARNING: do NOT sort by anything else than priority unless you're handling a list of NOT-YET-INSTALLED plugins
	 */
	function sort_Plugin_name( & $a_ID, & $b_ID )
	{
		$a_Plugin = & $this->get_by_ID( $a_ID );
		$b_Plugin = & $this->get_by_ID( $b_ID );

		return strcasecmp( $a_Plugin->name, $b_Plugin->name );
	}


	/**
	 * Callback function to sort plugins by group, sub-group and name.
	 *
	 * Those, which have a group get sorted above the ones without one.
	 *
	 * WARNING: do NOT sort by anything else than priority unless you're handling a list of NOT-YET-INSTALLED plugins
	 */
	function sort_Plugin_group( & $a_ID, & $b_ID )
	{
		$a_Plugin = & $this->get_by_ID( $a_ID );
		$b_Plugin = & $this->get_by_ID( $b_ID );

		// first check if both have a group (-1: only A has a group; 1: only B has a group; 0: both have a group or no group):
		$r = (int)empty($a_Plugin->group) - (int)empty($b_Plugin->group);
		if( $r != 0 )
		{
			return $r;
		}

		// Compare Group
		$r = strcasecmp( $a_Plugin->group, $b_Plugin->group );
		if( $r != 0 )
		{
			return $r;
		}

		// Compare Sub Group
		$r = strcasecmp( $a_Plugin->sub_group, $b_Plugin->sub_group );
		if( $r != 0 )
		{
			return $r;
		}

		// Compare Name
		return strcasecmp( $a_Plugin->name, $b_Plugin->name );
	}


}


/* {{{ Revision log:
 * $Log$
 * Revision 1.5  2006/12/01 19:16:00  blueyed
 * Moved Plugins::get_registered_events() to Plugins_admin class
 *
 * Revision 1.4  2006/12/01 18:18:22  blueyed
 * Moved Plugins::save_events() to Plugins_admin class
 *
 * Revision 1.3  2006/12/01 02:03:04  blueyed
 * Moved Plugins::set_event_status() to Plugins_admin
 *
 * Revision 1.2  2006/11/30 05:57:54  blueyed
 * Moved Plugins::install() and sort() galore to Plugins_admin
 *
 * Revision 1.1  2006/11/30 05:43:40  blueyed
 * Moved Plugins::discover() to Plugins_admin::discover(); Renamed Plugins_no_DB to Plugins_admin_no_DB (and deriving from Plugins_admin)
 *
 * }}}
 */
?>