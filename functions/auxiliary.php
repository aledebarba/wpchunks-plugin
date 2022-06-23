<?php 
/**
 * 
 * 
 * Auxiliary function pair get/the
 *
 * @return string url of the current component
 * 
 */
function get_current_component_path() {
    global $components_core_options;
    return $components_core_options->getCurrentComponentPath();
}
function the_current_component_path() {
    echo get_current_component_path();
}
?>