<?php
/*
Plugin Name: img-responsive for Bootstrap
Plugin URI: http://www.webfwd.co.uk/
Description: Automatically add img-responsive class to all post and page content.
Version: 1.0
Author: Webforward
Author URI: http://www.webfwd.co.uk/
License: GPL

Copyright 2016 WEBFWD LTD (email : support@mailerplugin.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

GNU General Public License: http://www.gnu.org/licenses/gpl.html
*/

// Plugin Activation
function bsir_install() {
    global $wpdb;

    //    INSTALL CODE

    $plver = bsir_get_version();
    $dbver = get_option('bsir_version');

    if ($dbver > $plver) {
        deactivate_plugins(basename(__FILE__));
        wp_die("Sorry, The plugin version you are installing is older than the database you have installed, please install plugin version " . $dbver . " or newer.");
    }

    if ($dbver <= '1.0') {} // Nothing to do
    update_option('bsir_version', $plver);


}

register_activation_hook(__FILE__, 'bsir_install');

// Plugin Deactivation
function bsir_uninstall() {
    //global $wpdb;
}

register_deactivation_hook(__FILE__, 'bsir_uninstall');

// Admin Menu
function bsir_menu() {
    // No menu required
}

add_action('admin_menu', 'bsir_menu');

function bsir_get_version() {
    $plugin_data = get_plugin_data(__FILE__);
    $plugin_version = $plugin_data['Version'];
    return $plugin_version;
}

/////////////

function bsir_add_image_responsive_class($content) {
    global $post;
    $pattern ="/<img(.*?)class=\"(.*?)\"(.*?)>/i";
    $replacement = '<img$1class="$2 img-responsive"$3>';
    $content = preg_replace($pattern, $replacement, $content);
    return $content;
}
add_filter('the_content', 'bsir_add_image_responsive_class');