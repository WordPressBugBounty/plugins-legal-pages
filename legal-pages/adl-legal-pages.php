<?php
/*
Plugin Name: Legal Pages
Plugin URI: https://wpwax.com/product/legal-pages-pro
Description: A very useful plugin to generate legal pages for your websites/ business. It is simple, easy and elegant to use. It comes with ready-made templates which gives you even better experience creating legal pages with ease. You can customize the page template too.
Version: 1.4.4
Author: wpWax
Author URI: https://wpwax.com
License: GPLv2 or later
Text Domain: legal-pages
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2016 wpwax.
*/

// Make sure we don't expose any info if called directly

defined('ABSPATH') || die( 'Cheating? Direct access is not allowed !!!' );
if ( !defined('ADL_LP_BASE') ) { define('ADL_LP_BASE', plugin_basename( __FILE__ )); }
if ( ! defined( 'WPLP_URL' ) ) { define( 'WPLP_URL', plugin_dir_url( __FILE__ ) ); }

// Load plugin config
require_once 'config.php';
// main plugin class
require_once 'main.php';


if ( class_exists( 'Adl_Legal_Pages' ) ) { // Instantiate the plugin class
    global $ADL_LP;
    $ADL_LP = new Adl_Legal_Pages();
    $ADL_LP->check_req_php_version();
    $ADL_LP->warn_if_unsupported_wp();
    register_activation_hook(__FILE__, array($ADL_LP, 'prepare_plugin'));
    register_deactivation_hook(__FILE__, array($ADL_LP, 'remove_plugin_data'));
    $ADL_LP->init();
}
