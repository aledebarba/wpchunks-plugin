<?php 
/**
 * 
 * Component class 
 * 
 * this class works as syntax sugar for the components system
 * 
 * @param string $name
 * @param array $params
 * @return void
 * @example Component::D("simple-card", "Jonh Doe", "Code Master", "https://www.google.com");
 * @example Component::J("modal", "This information is important", "Close");
 * @example Component::react("taksList");
 *
 */ 
class Component {
 
    // PHP components -------------------------------------------------------
    public static function php($name, ...$params) {
        global $components_core_options;        
        $componentName = $name."/src/index.php";
        $componentStyles = get_stylesheet_directory().'/'.$components_core_options->getRoot().'/'.$name."/src/".$components_core_options->getStyleName();
        $componentPath = get_stylesheet_directory()."/".$components_core_options->getRoot()."/".$componentName;   

        if( file_exists($componentStyles) ) { 
            require_once($componentStyles); 
        }
        if ( file_exists($componentPath) ) {
            $components_core_options->setCurrentComponentName($name);
            $components_core_options->setCurrentComponentPath();
            include_once ( $componentPath );
        } else { // show error
            Component::error("Could not find PHP component: $name");
        }
    }
    public static function P($name, ...$params) { // sugar for php
        Component::php($name, $params);
    }

    // Javascript components -------------------------------------------------
    public static function JS($name, $deps = [], $infooter = true) {
        global $components_core_options;
        $chunk = $components_core_options;

        $path = $chunk->getSrcPath($name, "index.js");
        $url  = $chunk->getSrcUrl($name, "index.js");
        
        $handle = "wpchunck-".$name;
        $stylePath = $chunk->getSrcPath($name, "style.php");
        $ver = uniqid();
        
        if ( file_exists($path) ) {
            // include style file
            if (file_exists($stylePath)) {
                require_once ($stylePath);
            }
            //output entry point html element
            echo "<div class='$name' wpchunk-$name='true'></div>";
            //register script
            add_action("wp_enqueue_scripts", function() use ($handle, $url, $deps, $ver, $infooter, $stylePath) {
                wp_register_script( $handle, $url, $deps, $ver, $infooter );
                wp_enqueue_script( $handle );
            }, 100);
            do_action("wp_enqueue_scripts");
        } else {
            Component::error("Could not find Javascript component: $name");
        }
    }

    public static function J($name, $deps = [], $infooter = true) { // sugar for JS 
        Component::JS($name, $deps, $infooter);
    }

    // React components -------------------------------------------------------
    public static function react($name) {
        global $components_core_options;
        $chunk = $components_core_options;
        
        $path = $chunk->getBuildPath($name, "index.js");
        $url  = $chunk->getBuildUrl($name, "index.js");

        $handle = "wpchunk-".$name;
        $componentPath = $path;
        $stylePath = $chunk->getBuildPath($name, "index.css");
        $styleUrl  = $chunk->getBuildUrl($name, "index.css");
        $styleHandle = "wpchunk-$name-styles";
        $ver = uniqid();
        $deps = ['wp-element', 'wp-editor'];
        $infooter = true;

        if ( file_exists($componentPath) ) {
            echo <<<HTML
                <div class="wpchunk-$name" data-wpchunk="$name" wpchunk-$name="true" ${$chunk->getReactKey($name)}></div>
            HTML;
            
            if ( false == wp_script_is( $handle, 'enqueued' ) ) {
                add_action("wp_enqueue_scripts", function() use ($handle, $url, $deps, $ver, $infooter, $styleHandle, $styleUrl, $stylePath) {
                    wp_register_script( $handle, $url, $deps, $ver, $infooter );
                    wp_enqueue_script( $handle );
                    if (file_exists($stylePath)) {
                        wp_register_style( $styleHandle, $styleUrl, [], $ver, "screen" );
                        wp_enqueue_style( $styleHandle );
                    }
                }, 100);                
                do_action("wp_enqueue_scripts");
            }
        } else {
            Component::error("Could not find React component: $name");
        }
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
            <div style="
                color: white; 
                font-size: 18px; 
                font-weight: bold; 
                font-family: sans-serif;
                padding: 8px;
                margin: 8px 0px;
                background-color: #3d3d3d;
                border-left: 32px solid red;
                display: inline-block;
                position: relative;
                z-index: 100000;
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
}
?>