<?php
/*
Plugin Name: Cool Kids Network
Description: A WordPress plugin for the Cool Kids Network proof of concept.
Version: 1.0
Author: Jovan Kitanovic
*/

// Enqueue frontend styles
add_action('wp_enqueue_scripts', 'ckn_enqueue_styles');
function ckn_enqueue_styles() {
    wp_enqueue_style('ckn-style', plugins_url('assets/styles.css', __FILE__));
    // Add wp-admin ajax url to be available in JS file
    wp_localize_script('ckn-script', 'ajaxurl', admin_url('admin-ajax.php'));
}

// Enqueue frontend scripts
add_action('wp_enqueue_scripts', 'ckn_enqueue_scripts');
function ckn_enqueue_scripts() {
    wp_enqueue_script('ckn-script', plugins_url('assets/script.js', __FILE__), ['jquery'], null, true);
}

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/class-character.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-rest-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-views.php';

// Initialize REST API routes
add_action('rest_api_init', function() {
    $api = new CKN_REST_API();
    $api->register_routes();
});

// Register activation hook to set up roles and database
register_activation_hook(__FILE__, 'ckn_activate_plugin');

function ckn_activate_plugin() {
    // Add custom roles
    add_role('cool_kid', 'Cool Kid', [
        'read' => true, 
        'view_own_character' => true,
    ]);
    add_role('cooler_kid', 'Cooler Kid', [
        'read' => true, 
        'view_own_character' => true,
        'view_other_characters' => true,
    ]);
    add_role('coolest_kid', 'Coolest Kid', [
        'read' => true, 
        'view_own_character' => true,
        'view_other_characters' => true,
        'view_email_and_role' => true,
    ]);

    // Create custom database for character data
    global $wpdb;
    $table_name = $wpdb->prefix . 'ckn_characters';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        first_name varchar(50) NOT NULL,
        last_name varchar(50) NOT NULL,
        country varchar(50) NOT NULL,
        role varchar(20) NOT NULL DEFAULT 'Cool Kid',
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}