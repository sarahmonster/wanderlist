<?php
/*
  Plugin Name: Wanderlist
  Plugin URI: http://triggersandsparks.com/wanderlist
  Description: A WordPress plugin for tracking the places you've visited. Display them on a map, brag about your country-count, and show fun stats! Because everyone loves stats, right?
  Version: 0.1.0
  Author: sarah semark
  Author URI: http://triggersandsparks.com
  License: GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /languages
  Text Domain: wanderlist
/*

/**
 * All code that needs to be run once the plugin is activated.
 */
require plugin_dir_path( __FILE__ ) . 'includes/wanderlist-activator.php';

register_activation_hook( __FILE__, 'wanderlist_activate' );

/**
 * Core code available across both admin and public views.
 */
require plugin_dir_path( __FILE__ ) . 'includes/wanderlist.php';

/**
 * Code that filters our post loops.
 */
require plugin_dir_path( __FILE__ ) . 'includes/public/wanderlist-filters.php';

/**
 * Code to manipulate and display dates.
 */
require plugin_dir_path( __FILE__ ) . 'includes/public/wanderlist-dates.php';

/**
 * Code that deals with country functions.
 */
require plugin_dir_path( __FILE__ ) . 'includes/public/wanderlist-countries.php';

/**
 * Public-facing code.
 */
require plugin_dir_path( __FILE__ ) . 'includes/public/wanderlist-public.php';

/**
 * Template-related code.
 */
require plugin_dir_path( __FILE__ ) . 'includes/public/wanderlist-templates.php';

/**
 * Shortcodes!
 */
require plugin_dir_path( __FILE__ ) . 'includes/public/wanderlist-shortcodes.php';

/**
 * Admin-side code.
 */
require plugin_dir_path( __FILE__ ) . 'includes/admin/wanderlist-admin.php';
