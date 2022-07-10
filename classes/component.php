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
            $components_core_options->setCurrentComponentName($name);
            $components_core_options->setCurrentComponentPath();
            include ( $componentPath );
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
    public static function react($name, ...$params) {
        global $components_core_options;
        $chunk = $components_core_options;
        
        $path = $chunk->getBuildPath($name, "index.js");
        $url  = $chunk->getBuildUrl($name, "index.js");

        $handle = "wpchunk-".$name; // ex. wpchunk-hello-world
        $handle_global_vars = str_replace("-", "_", $handle); // ex: wpchunk_hello_world 
        $componentPath = $path;
        $stylePath = $chunk->getBuildPath($name, "index.css");
        $styleUrl  = $chunk->getBuildUrl($name, "index.css");
        $styleHandle = "wpchunk-$name-styles";
        $ver = uniqid();
        $deps = ['wp-element', 'wp-editor'];
        $infooter = true;
        
        if ( file_exists($componentPath) ) {
            $output = <<<HTML
                <div class="wpchunk-$name" data-wpchunk="$name" wpchunk-$name="true" {$chunk->getReactKey($name)}></div>
            HTML;
            echo $output;
            
            if ( false == wp_script_is( $handle, 'enqueued' ) ) {
                add_action("wp_enqueue_scripts", function() use ($handle, $url, $deps, $ver, $infooter, $styleHandle, $styleUrl, $stylePath, $handle_global_vars, $params) {
                    wp_register_script( $handle, $url, $deps, $ver, $infooter );
                    wp_enqueue_script( $handle );
                    wp_localize_script( $handle, $handle_global_vars, array(
                        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                        'ajaxNonce' => wp_create_nonce( 'wpchunk-nonce' ),
                        'params' => $params
                    ));
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
}

function chunk($name, ...$params) {
    global $components_core_options;  
    $options = $components_core_options;

    if ( ! $options->componentExist($name) ) {
        
        Component::error(<<<HTML
            Component
            <span style='
                font-family: "Courier New", Courier, "Lucida Sans Typewriter", "Lucida Typewriter", monospace; 
                color: gold;'>
                $name
            </span>not found. Please create a new component first.
            HTML);
        return false;

    }

    $package = $options->getPackageJson($name);
    
    if ( file_exists($package) ) {
        $package = json_decode(file_get_contents($package));
        if (isset($package->wpchunk)) {
            $type = $package->wpchunk;
            if ($type == "react") {
                Component::react($name, $params);
            } else if ($type == "vue") {
                Component::error("not supported yet");
            } else if ($type == "php") {
                Component::php($name, $params);
            } else if ($type == "js") {
                Component::JS($name, $params);
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