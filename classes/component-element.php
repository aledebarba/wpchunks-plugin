<?php 
/**
 * 
 * This class provides extended functions for PHP components
 * 
 * Besides the fact the component_core framework runs without using this Class
 * more advanced resourses can be included when this class is invoked.
 * This class also automatically enqueue the component javascript file <component-name>.js if it exists.
 * 
 * @param none
 * @return class ComponentElement
 * @example $myNewComponent = new ComponentElement();
 * 
 */
class ComponentElement {
    
    private $root = "";
    private $path = "";
    private $name = "";
    private $jspath = "";
    private $version = "";
    private $scriptInfo = array();

    function __construct($ver="", $deps=[], $infooter=true) {
        global $components_core_options;
        // save the component data
        $this->version = $ver;
        $this->path = $components_core_options->getCurrentComponentPath();
        $this->name = $components_core_options->getCurrentComponentName();
        $this->root = $components_core_options->getRoot().'-'.$this->name;
        $this->jspath = $this->path.$this->$name.".js";
        
        $uid = uniqid();
        $handle = $this->name."_js_".$uid;
        $src = $this->jspath;
        $ver = $this->version."_$uid";

        if ( file_exists($this->jspath) ) {
            add_action('wp_enqueue_scripts', function() use ($handle, $src, $deps, $ver, $infooter) {
                wp_register_script( $handle, $src, $deps, $ver, $infooter );
                wp_enqueue_script( $handle );
            }, 100);
            do_action("wp_enqueue_scripts");
        }
    }
}
?>