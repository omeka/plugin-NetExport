<?php
/**
 * Main plugin script
 *
 * Main script for the plugin, sets up hooks and filters to the Omeka API.
 *
 * TODO: Status indicator on browse page.
 * TODO: Reports should be stored using Omeka_Storage.
 * @package Reports
 * @author Center for History and New Media
 * @copyright Copyright 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

if (!defined('NETEXPORT_PLUGIN_DIRECTORY')) {
    define('NETEXPORT_PLUGIN_DIRECTORY', dirname(__FILE__));

    define('NETEXPORT_GENERATOR_DIRECTORY', NETEXPORT_PLUGIN_DIRECTORY .
                                          '/models/NetExport/Generator');
}

add_plugin_hook('install', 'netexport_install');
add_plugin_hook('uninstall', 'netexport_uninstall');
add_plugin_hook('define_acl', 'netexport_define_acl');
add_filter('admin_navigation_main', 'netexport_admin_navigation_main');

require_once dirname(__FILE__) . '/hook-definitions.php';
