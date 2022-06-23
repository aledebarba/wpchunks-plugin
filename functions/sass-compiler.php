<?php
/**
 * 
 * Insert CSS code compiled from scss string
 *        this function also ensures that the compiled css is inserted only once
 *        or a minimum amount of times

 * @example sass("<style>...scss code...</style>", "css-code-cards");
 * @example sass("scss code", "css-code-cards");
 * 
 * @param string $sasscode the uncompiled scss code
 * @param string $id the id of the element to insert the compiled css
 * @return void
 */

use ScssPhp\ScssPhp\Compiler;
$css__control__rule_insertion = [];

function sass($sasscode, $id = "") {
    $id = $id == "" ? "css-contrel-".uniqid() : $id;
    if(isset($css__control__rule_insertion)) {
        if (isset($css__control__rule_insertion[$id])) {
            return;
        }
    } else {
        $id = $id || $id!=="" ? $id : "css__control__uid_".uniqid(); 
        $css__control__rule_insertion[$id] = $id;
    }

    // remove the <style> tag if it exists
    $sasscode = str_replace("<style>", "", $sasscode); 
    $sasscode = str_replace("</style>", "", $sasscode);

    //start compiling
    $compiler = new Compiler();
    $compiledCSS = $compiler->compileString($sasscode)->getCss();

    add_action('wp_head', function($css) use ($compiledCSS, $id) {
        echo "<style type='text/css' media='screen' generator='php_sass_compiler' id='$id'>$compiledCSS</style>";
    }, 1);
    do_action('wp_head');
}
?>