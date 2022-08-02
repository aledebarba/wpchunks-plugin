<?php
/**
 * 
 * 
 * class Components_Core_Options keep the options for components
 * naming, loadind, basic information gathering, etc.
 * 
 * 
 */
class Components_Core_Options {

    private $currentComponentName;
    private $currentComponentPath;
    private $components = [];
    public  $componentStore = [];
    public  $shortCodeStore = [];
    private $cssControl = array();
    private $root;
    private $styleName;
    private $prefix;
    private $reactBuildPath = "build";
    private $reactKeys = array();

    function __construct($root, $prefix) {
        // load component styles
        $this->root = $root;
        $this->prefix = $prefix;
        $this->styleName = "style.php";
    }

    public function setComponent($name, $path) { $this->components[$name] = $path; } 
    
    public function getComponent($name) {
        if ( isset( $this->components[$name] ) ) {
            return $this->components[$name];
        } else {
            return false;
        }
    }
    public function setRoot($root) { $this->root = $root; }
    public function setStyleName($styleName) { $this->styleName = $styleName; }
    public function getRoot() { return $this->root; }
    public function getStyleName() { return $this->styleName; }
    public function getCurrentComponentPath() { return $this->currentComponentPath; }
    public function getCurrentComponentName() { return $this->currentComponentName; }
    public function setCurrentComponentName($currentComponentName) { $this->currentComponentName = $currentComponentName; }
    public function getPrefix() { return $this->prefix; }
    public function setPrefix($prefix) { $this->prefix = $prefix; }
    public function setCurrentComponentPath() {
        $this->currentComponentPath = get_stylesheet_directory_uri().$this->root."-".$this->currentComponentName."/src/";
    }
    public function getSrc($name, $fileName = "") {
        return $this->getRoot()."/".$this->prefix.$name."/src/".$fileName;
    }
    public function getBuild($name, $fileName = "") {
        return $this->getRoot()."/".$this->prefix.$name."/build/".$fileName;
    }
    public function getSrcPath($name, $fileName) {
        return get_stylesheet_directory()."/".$this->GetSrc($name, $fileName);
    }
    public function getSrcUrl($name, $fileName) {
        return get_stylesheet_directory_uri()."/".$this->GetSrc($name, $fileName);
    }
    public function getBuildPath($name, $fileName) {
        return get_stylesheet_directory()."/".$this->GetBuild($name, $fileName);
    }
    public function getBuildUrl($name, $fileName) {
        return get_stylesheet_directory_uri()."/".$this->GetBuild($name, $fileName);
    }    
    public function getReactKey($name) {
        if (!isset($this->reactKeys[$name])) {
            $this->reactKeys[$name] = 1;
        } else {
            $this->reactKeys[$name]++;
        }
        return "key='".$this->reactKeys[$name]."'";
    }
    public function getPackageJson($name) {
        return get_stylesheet_directory()."/".$this->getRoot()."/".$this->prefix.$name."/package.json";
    }
    public function componentExist($name) {
        return file_exists(get_stylesheet_directory()."/".$this->getRoot()."/".$this->prefix.$name."/package.json");
    }
    public function isStyled($name) {
            return isset($this->cssControl[$name]);
    }
    public function setStyle($name, $path) {
        $this->cssControl[$name] = $path;
    }
    public function getStyle($name) {
        return $this->cssControl[$name];
    }
    public function getCssControl() {
        return $this->cssControl;
    }
    public function addComponent($fnName, $path) {
        $name = strrchr($path, "/") !== false ? substr( strrchr($path, "/"), 1) : $path;        
        if( isset($this->componentStore[$name]) ) {
            return false; // fails gracefully if component already exists
        }
        // add component to store and create the function
        $this->componentStore[$name] = ['function'=>$fnName, 'path' => $path, 'instance' => 0];
        $package = $path."/package.json";
        
        if (file_exists($package)) {
            // create function
            $package = json_decode(file_get_contents($package));
            if(isset($package->wpchunk)) {
                $type = $package->wpchunk;
                
                if($type == "react") {
                    $fnBody = "function $fnName(){ 
                        global \$components_core_options;
                        \$components_core_options->setCurrentComponentName('$fnName');
                        return call_user_func( 
                            'Component::jsbundle', 
                            '$name', func_get_args(), ['wp-element', 'wp-editor'], true ); 
                        }";
                    // eval function
                    eval($fnBody);
                }

                if($type == "jsbundle") {
                    $fnBody = "function $fnName(){ 
                        global \$components_core_options;
                        \$components_core_options->setCurrentComponentName('$fnName');
                        return call_user_func( 
                            'Component::jsbundle', 
                            '$name', func_get_args() ); 
                        }";
                    // eval function
                    eval($fnBody);
                }

                if($type == "javascript") {
                    $fnBody = "function $fnName(){ 
                        global \$components_core_options;
                        \$components_core_options->setCurrentComponentName('$fnName');
                        return call_user_func( 
                            'Component::javascript', 
                            '$name', func_get_args() ); 
                        }";
                    // eval function
                    eval($fnBody);
                }

                if($type == "php") {
                    $fnBody = "function $fnName(){ 
                        global \$components_core_options;
                        \$components_core_options->setCurrentComponentName('$fnName');
                        return call_user_func( 
                            'Component::php', 
                            '$name', func_get_args() ); 
                        }";
                    // eval function
                    eval($fnBody);
                }

                
            }
        }
    }
}

/**
 * 
 * This class set the syntax for importing components
 * The import syntax mimic the import syntax in Javascript libs, but also controls the loading of components. 
 * It also generates a callable function, in PHP code, that can expose arguments to the component. 
 * It also exposes data from the component itself, and pass nonces and callbacks to ajax calls.  
 * @param string $name The name of the function that represents the component inside PHP code - it must be a valid PHP function name.
 * 
 * @return object $this with the following methods: from(), fromPath(), fromLib()
 * 
 * @method from() - import a component from the default folder of the theme
 * @method fromPath() - import a component from a custom path under the theme folder
 * @method fromLib() - import a component from a custom folder inside a plugin
 * 
 * 
 * @example Import::component('userCard')->from('user-card'); - create the function userCard() from component inside <default-folder>/user-card
 * @example Import::component('userCard')->fromPath('my/custom/path/user-card'); - create the function userCard() from component inside <custom-path>/user-card
 * @example Import::component('userCard')->fromLib('custom-lib-name/user-card'); - create the function userCard() from component inside wordpress/wp-content/plugins/<custom-lib-name>/user-card
 */
class Import {

    public  $functionName = "";
    private $path = "";
    private $defaultPath = "components";
    private $components = array();

    function __construct($name) {
        $this->functionName = $name;
    }

    public static function component($name) {
        return new Import($name);
    }

    
    function from($folder) {
        global $components_core_options;
        $this->path = get_stylesheet_directory()."/{$this->defaultPath}/$folder";
        $components_core_options->addComponent($this->functionName, $this->path);
    }

    function fromPath($pathName) {
        $this->path = STYLESHEETPATH."/$pathName";
    }
    function fromLib($pluginName) {
        $this->path = PLUGINDIR."/$pluginName";
    }    

    function checkComponent($name) {
        global $components_core_options;
        $componentFolder = self::getFolderName($name);
        if ($components_core_options->componentExist($folder)) {
            // already loaded or declared
            return;
        }
    }

    /**
     * Return the component folder name from a path
     * @param string $name
     * @return string $foldername
     */
    public static function getFolderName($name) {
        if( strrchr('/', $name) == false ) {
            return $name;
        }
        return substr(strrchr('/', $name), 1);
    }

}
class customFunctions {
    private static $store = [];
    private static $maker = "";
    private static $declaration = '
        function %s() {
            return call_user_func_array(
                %s::get(__FUNCTION__),
                func_get_args()
            );
        }
    ';

    private static function safeName($name) {
        // extra safety against bad function names
        $name = preg_replace('/[^a-zA-Z0-9_]/',"",$name);
        $name = substr($name,0,64);
        return $name;
    }
    public static function add($name,$func) {
        // prepares a new function for make()
        $name = self::safeName($name);
        self::$store[$name] = $func;
        self::$maker = sprintf( self::$declaration, $name,__CLASS__ );
        eval(self::$maker);
    }

    public static function get($name) {  
        // returns a stored callable
        return self::$store[$name];
    }

    public static function make() {  
        // returns a string with all declarations
        return self::$maker;
    }

}


function component_core_setup($root, $styleName, $prefix) {
    global $components_core_options;
    $components_core_options->setRoot($root);
    $components_core_options->setStyleName($styleName);
    $components_core_options->setPrefix($prefix);
 }

function chunks_location($root, $styleName, $prefix) {
    component_core_setup($root, $styleName, $prefix);
}

function wpchunks_location($root, $styleName, $prefix) {
    component_core_setup($root, $styleName, $prefix);
}

 ?>