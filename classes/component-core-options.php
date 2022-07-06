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
        return "key='${$this->reactKeys[$name]}'";
    }
    public function getPackageJson($name) {
        return get_stylesheet_directory()."/".$this->getRoot()."/".$this->prefix.$name."/package.json";
    }
    public function componentExist($name) {
        return file_exists(get_stylesheet_directory()."/".$this->getRoot()."/".$this->prefix.$name."/package.json");
    }
}

function component_core_setup($root, $styleName, $prefix) {
    global $components_core_options;
    $components_core_options->setRoot($root);
    $components_core_options->setStyleName($styleName);
    $components_core_options->setPrefix($prefix);
 }

 ?>