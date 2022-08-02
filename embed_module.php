<?php
/**
 * WPChunks Module
 * @version 0.0.1;
 * @author Alexandre CMC Souza <ale@alemacedo.com>
 * 
 * This file is part of the WPChunks System.
 * use this file if you want to include the component system as a module
 * inside the theme, bypassing the plugin activate/deactivate and
 * configutarios menus.
 * 
 * To activate it, include this file inside the theme's functions.php file.
 * with the require_once() function.
 * 
 * @example require_once(__DIR__ . '/wpchunks/wpchunks-module.php');
 * 
 */
require_once __DIR__ . '/vendor/autoload.php';  // PHP Sass Compiler
require_once __DIR__ . '/classes/component-core-options.php'; 
require_once __DIR__ . '/classes/component-element.php'; 
require_once __DIR__ . '/classes/component.php';
require_once __DIR__ . '/functions/auxiliary.php';
require_once __DIR__ . '/functions/sass-compiler.php';

// create module with default values
$defaultFolder = "components";
$filePrefix = "";
$components_core_options = new Components_Core_Options( $defaultFolder, $filePrefix );
?>