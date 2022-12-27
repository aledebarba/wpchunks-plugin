<?php 
/**
 * Target sintax;
 * 
 * Import::function('functionName')->from('path-to-component-file');
 * @example
 * Import::component('hambugerMenu')->from('components/hambuger-menu');                    // no file name = index 
 * Import::component('responsiveVideo')->from('components/react-videos/full-width.jsx');   // last name = file name where to find the function 
 * Import::component('wpMenu')->from('utilities/wp-menu/wp-menu.php');                     // if file is php try to find the function
 * Import::component('wpFooterMenu')->from('utilities/wp-menu/wp-menu.php');               // 
 * Import::component('fontAwesome')->fromPlugin('font-awesome-chunk/font-awesome');        //  
 * Import::component('newsTicker')->fromPlugin('news-package/news-ticker');
 * Import::component('newsColumn')->fromPlugin('news-package/news-column');                // Various components at same plugin
 */
class Component { 
    // PHP components -------------------------------------------------------
    public static function php($name, $params) {
        global $components_core_options; 
        $componentName = $name."/src/index.php";
        $componentRoot = get_stylesheet_directory()."/".$components_core_options->getRoot()."/".$name."/src/";  
        $componentPath = $componentRoot."index.php";
        $componentStyles = $componentRoot."index.scss";   
        
        if ( isset( $components_core_options->components[$name] ) ) {
            include( $componentPath );
            return true;
        } else {
            $components_core_options->setComponent($name, $componentPath);
        }
        if( file_exists($componentStyles) ) { 
            sassFile("index.scss", $name, $componentRoot); 
        }
        if ( file_exists($componentPath) ) {
            include ( $componentPath );
        } else { // show error
            Component::error("Could not find PHP component: $name");
        }
    }

    public static function jsbundle($name, $params=[], $deps = [], $infooter = true) {
        global $components_core_options;
        $components_core_options->componentStore[$name]['instance'] += 1;
        
        $path           = $components_core_options->getBuildPath($name, "index.js");
        $url            = $components_core_options->getBuildUrl ($name, "index.js");
        $handle         = "wpchunk-".$name; // ex. wpchunk-hello-world
        $globalVars     = str_replace("-", "_", $handle); // ex: wpchunk_hello_world 
        $componentPath  = $path;
        $stylePath      = $components_core_options->getBuildPath($name, "index.css");
        $styleUrl       = $components_core_options->getBuildUrl($name, "index.css");
        $styleHandle    = "wpchunk-$name-styles";
        $ver            = uniqid();
        $instance       = $components_core_options->componentStore[$name]['instance'];
        
        if ( file_exists($componentPath) ) {            
            $html = <<<HTML
                <div class="wpchunk-$name" 
                     data-wpchunk="$name" 
                     wpchunk-$name="true" 
                     data-instance="$instance"                    
                ></div>
            HTML;
            // mount js code
            $params     = json_encode($params);
            $paramsVar  = str_replace("-", "_", $name);
            $nonce      = wp_create_nonce( $name.$instance );
            $adminUrl   = admin_url('admin-ajax.php');
            $jsCode     = <<<JAVASCRIPT
                {$paramsVar}[{$instance}] = {
                    'ajaxUrl'   : "$adminUrl",
                    'ajaxNonce' : "$nonce",
                    'params'    : $params
                };          
            JAVASCRIPT; 
            echo $html;
            
            if ( wp_script_is( $handle, 'enqueued' ) == false ) {
                $components_core_options->setComponent($name, $componentPath);                
                add_action("wp_enqueue_scripts", function() use ($handle, $url, $deps, $ver, $infooter, $styleHandle, $styleUrl, $stylePath, $globalVars, $params) {
                    wp_register_script( $handle, $url, $deps, $ver, $infooter );
                    wp_enqueue_script( $handle );
                    if (file_exists($stylePath)) {
                        wp_register_style( $styleHandle, $styleUrl, [], $ver, "screen" );
                        wp_enqueue_style( $styleHandle );
                    }
                }, 1);      
                do_action("wp_enqueue_scripts");    
                // init global params array
                $jsCode = "var $paramsVar = [];".$jsCode;
            }
            wp_add_inline_script( $handle, $jsCode, 'before' );
        } else {
            Component::error("Could not find component: $name");
        }
    }
    
    

    public static function javascript($name, $params = [], $deps = [], $infooter = true) {
        global $components_core_options;
        $components_core_options->componentStore[$name]['instance'] += 1;
        
        $path           = $components_core_options->getSrcPath($name, "index.js");
        $url            = $components_core_options->getSrcUrl ($name, "index.js");
        $handle         = "wpchunk-".$name; // ex. wpchunk-hello-world
        $globalVars     = str_replace("-", "_", $handle); // ex: wpchunk_hello_world 
        $componentPath  = $path;
        $stylePath      = $components_core_options->getSrcPath($name, "index.scss");
        $styleUrl       = $components_core_options->getSrcUrl($name, "index.scss");
        $styleHandle    = "wpchunk-$name-styles";
        $ver            = uniqid();
        $deps           = [];
        $infooter       = true;
        $instance       = $components_core_options->componentStore[$name]['instance'];
        
        if ( file_exists($path) ) {
            $html = <<<HTML
                <div class="wpchunk-$name" 
                     data-wpchunk="$name" 
                     wpchunk-$name="true" 
                     data-instance="$instance"                    
                ></div>
            HTML;
            // mount js code
            $params     = json_encode($params);
            $paramsVar  = str_replace("-", "_", $name);
            $nonce      = wp_create_nonce( $name.$instance );
            $adminUrl   = admin_url('admin-ajax.php');
            $jsCode     = <<<JAVASCRIPT
                {$paramsVar}[{$instance}] = {
                    'ajaxUrl'   : "$adminUrl",
                    'ajaxNonce' : "$nonce",
                    'params'    : $params
                };          
            JAVASCRIPT; 
            echo $html;

            if ( wp_script_is( $handle, 'enqueued' ) == false ) {
                $components_core_options->setComponent($name, $componentPath);   
                $componentRoot = get_stylesheet_directory()."/".$components_core_options->getRoot()."/".$name."/src/";              
                add_action("wp_enqueue_scripts", function() use ($handle, $url, $deps, $ver, $infooter, $styleHandle, $styleUrl, $stylePath, $globalVars, $params, $componentRoot) {
                    wp_register_script( $handle, $url, $deps, $ver, $infooter );
                    wp_enqueue_script( $handle );                    
                }, 1);      
                do_action("wp_enqueue_scripts");   
                if (file_exists($stylePath)) {
                    sassFile("index.scss", $name, $componentRoot); 
                } 
                // init global params array
                $jsCode = "var $paramsVar = [];".$jsCode;
            }
            wp_add_inline_script( $handle, $jsCode, 'before' );
        } else {
            Component::error("Could not find component: $name");
        }        
    }

    // React components -------------------------------------------------------
    public static function react($name, $params) {
        global $components_core_options;
        $path           = $components_core_options->getBuildPath($name, "index.js");
        $url            = $components_core_options->getBuildUrl ($name, "index.js");
        $handle         = "wpchunk-".$name; // ex. wpchunk-hello-world
        $globalVars     = str_replace("-", "_", $handle); // ex: wpchunk_hello_world 
        $componentPath  = $path;
        $stylePath      = $components_core_options->getBuildPath($name, "index.css");
        $styleUrl       = $components_core_options->getBuildUrl($name, "index.css");
        $styleHandle    = "wpchunk-$name-styles";
        $ver            = uniqid();
        $deps           = ['wp-element', 'wp-editor'];
        $infooter       = true;
        $instance       = $components_core_options->componentStore[$name]['instance'];
        
        if ( file_exists($componentPath) ) {            
            $html = <<<HTML
                <div class="wpchunk-$name" 
                     data-wpchunk="$name" 
                     wpchunk-$name="true" 
                     data-instance="$instance"                    
                ></div>
            HTML;
            // mount js code
            $params     = json_encode($params);
            $paramsVar  = str_replace("-", "_", $name);
            $nonce      = wp_create_nonce( $name.$instance );
            $adminUrl   = admin_url('admin-ajax.php');
            $jsCode     = <<<JAVASCRIPT
                {$paramsVar}[{$instance}] = {
                    'ajaxUrl'   : "$adminUrl",
                    'ajaxNonce' : "$nonce",
                    'params'    : $params
                };          
            JAVASCRIPT; 
            echo $html;
            
            if ( wp_script_is( $handle, 'enqueued' ) == false ) {
                $components_core_options->setComponent($name, $componentPath);                
                add_action("wp_enqueue_scripts", function() use ($handle, $url, $deps, $ver, $infooter, $styleHandle, $styleUrl, $stylePath, $globalVars, $params) {
                    wp_register_script( $handle, $url, $deps, $ver, $infooter );
                    wp_enqueue_script( $handle );
                    if (file_exists($stylePath)) {
                        wp_register_style( $styleHandle, $styleUrl, [], $ver, "screen" );
                        wp_enqueue_style( $styleHandle );
                    }
                }, 1);      
                do_action("wp_enqueue_scripts");    
                // init global params array
                $jsCode = "var $paramsVar = [];".$jsCode;
            }
            wp_add_inline_script( $handle, $jsCode, 'before' );
        } else {
            Component::error("Could not find React component: $name");
        }
    }

    public static function P($name, ...$params) { // sugar for php
        Component::php($name, $params);
    }

    // Javascript components -------------------------------------------------
    public static function JS($name, $deps = [], $infooter = true) {
        global $components_core_options;
        $path           = $components_core_options->getBuildPath($name, "index.js");
        $url            = $components_core_options->getBuildUrl ($name, "index.js");
        $handle         = "wpchunk-".$name; // ex. wpchunk-hello-world
        $globalVars     = str_replace("-", "_", $handle); // ex: wpchunk_hello_world 
        $componentPath  = $path;
        $stylePath      = $components_core_options->getBuildPath($name, "index.css");
        $styleUrl       = $components_core_options->getBuildUrl($name, "index.css");
        $styleHandle    = "wpchunk-$name-styles";
        $ver            = uniqid();
        $deps           = [];
        $infooter       = true;
        $instance       = $components_core_options->componentStore[$name]['instance'];
        
        if ( file_exists($path) ) {
            //output entry point html element
            echo "<div class='$name' wpchunk-$name='true'></div>";
            //register script
            add_action("wp_enqueue_scripts", function() use ($handle, $url, $deps, $ver, $infooter, $stylePath, $styleHandle, $instance) {
                wp_register_script( $handle, $url, $deps, $ver, $infooter );
                wp_enqueue_script( $handle );
            }, 100);
            do_action("wp_enqueue_scripts");
        } else {
            Component::error("Could not find Javascript component: $name");
        }
    }

    //
    public static function J($name, $deps = [], $infooter = true) { // sugar for JS 
        Component::JS($name, $deps, $infooter);
    }
    public static function D($name, ...$params) { // sugar for react
        Component::react($name, $params);
    }
    public static function R($name, ...$params) { // sugar for react
        Component::react($name, $params);
    }
    public static function V($name, ...$params) { // sugar for vue
        Component::react($name, $params);
    }
    public static function error($message, $throw = false) {
        echo <<<HTML
            <div style="color: white; 
                font-size: 18px; font-weight: bold; font-family: sans-serif;
                padding: 16px; margin: 8px 0px;
                background-color: #3d3d3d;
                border-left: 32px solid red;
                display: block; z-index: 100000;
                width: fit-content;
                ">
                $message
            </div>
        HTML;
        if ($throw) {
            throw new Exception($message);
            die();
        }
    }

    public static function create() { return new ComponentElement(); }
    
    public static function reactComponentCycle( $name, $params=[] ){
        global $components_core_options;
        $components_core_options->componentStore[$name]['instance'] += 1;
        $path = $components_core_options->componentStore[$name]['path'];
        Component::react($name, $params);
    }

    public static function javascriptComponentCycle( $name, $params=[] ){
        global $components_core_options;
        $components_core_options->componentStore[$name]['instance'] += 1;
        $path = $components_core_options->componentStore[$name]['path'];
        Component::JS($name, $params);
    }


}

function chunk($name, ...$params) {
    global $components_core_options;  

    if ( !$components_core_options->componentExist($name) ) {
        Component::error(<<<HTML
            Component
            <span style='
                font-family: "Courier New", Courier, "Lucida Sans Typewriter", "Lucida Typewriter", monospace; 
                color: gold;'>
                $name
            </span>not found. Please create a new component first.
            HTML);
            throw new Exception('Component not found');
        return false;
    }

    $package = $components_core_options ->getPackageJson($name);
    
    if ( file_exists($package) ) {        
        $package = json_decode(file_get_contents($package));
        if (isset($package->wpchunk)) {
            $type = $package->wpchunk;
            if ($type == "react") {
                Component::reactComponentCycle($name, $params);
            } else if ($type == "vue") {
                Component::error("not supported yet");
            } else if ($type == "php") {
                Component::php($name, $params);
            } else if ($type == "javascript") {
                if( !isset($components_core_options->componentStore[$name])) {
                    $components_core_options->componentStore[$name] = ['instance' => 0];
                }
                Component::javascript($name, $params);
            } else if ($type == "jsbundle") {
                if( !isset($components_core_options->componentStore[$name])) {
                    $components_core_options->componentStore[$name] = ['instance' => 0];
                }
                Component::jsbundle($name, $params);
            } else {
                Component::error("Unknown component type: $type");
            }
        } else {
            Component::error("Could not find component type in package.json");
        }
    } else {
        Component::error("Could not find package.json");
    }    
}


?>