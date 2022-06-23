<?php
/*
    Plugin Name: WP Chunks
    Description: A plugin that create a component engine to Wordpress.
    Version: 0.0.1
    Auhtor: ale@alemacedo.com
    Author URI: https://github.com/aledebarba
*/
require_once __DIR__ . '/vendor/autoload.php';  // PHP Sass Compiler
require_once __DIR__ . '/classes/plugin.php';   // WP Chunks Plugin
require_once __DIR__ . '/classes/component-core-options.php'; 
require_once __DIR__ . '/classes/component-element.php'; 
require_once __DIR__ . '/classes/component.php';
require_once __DIR__ . '/functions/auxiliary.php';
require_once __DIR__ . '/functions/sass-compiler.php';


// create plugin instance
$wpChunksPlugin = new WPChunksPlugin();
$components_core_options = new Components_Core_Options( $wpChunksPlugin->getOption('folderPath'), $wpChunksPlugin->getOption('filePrefix'));
?>