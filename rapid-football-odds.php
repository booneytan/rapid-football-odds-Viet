<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/AcePointer/rapid-football-odds
 * @since             1.0.0
 * @package           Rapid_Football_Odds
 *
 * @wordpress-plugin
 * Plugin Name:       Rapid Football Odds Viet
 * Plugin URI:        https://github.com/AcePointer/rapid-football-odds
 * Description:       An API plugin to call for the odds and livescore. Fully customised to comply with the needs of SEO Microsites.
 * Version:           1.4.1
 * Author:            AP Dev Team
 * Author URI:        https://github.com/AcePointer/rapid-football-odds
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rapid-football-odds
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'RAPID_FOOTBALL_ODDS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rapid-football-odds-activator.php
 */
function activate_rapid_football_odds() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rapid-football-odds-activator.php';
	Rapid_Football_Odds_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rapid-football-odds-deactivator.php
 */
function deactivate_rapid_football_odds() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rapid-football-odds-deactivator.php';
	Rapid_Football_Odds_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_rapid_football_odds' );
register_deactivation_hook( __FILE__, 'deactivate_rapid_football_odds' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rapid-football-odds.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rapid_football_odds() {

	$plugin = new Rapid_Football_Odds();
	$plugin->run();

}
run_rapid_football_odds();
