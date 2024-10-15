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