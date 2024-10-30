<?php
/**
 * Uninstall Script
 *
 * This file is executed when the plugin is uninstalled (deleted) from WordPress.
 * It is responsible for cleaning up any data or settings created by the plugin.
 *
 * @package WPRobo/CryptoCart-Lite
 */

// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

// Add uninstallation logic here.
// For example, remove database tables, options, or any other data created by the plugin.

// Sample code to remove plugin options from the database (replace 'your_option_name' with the actual option name).

// @phpcs:Squiz.Commenting.InlineComment.InvalidEndChar ignored
// delete_option( 'your_option_name' );

// Note: Be cautious with uninstallation, as this file is triggered when the user decides to remove the plugin,
// and data removed here will not be recoverable unless you have a backup mechanism.

// Exit with a success message.
