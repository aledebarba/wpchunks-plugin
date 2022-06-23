<?php 

class AdminMenuPageItem {
    
    private $moduleId            = "filterOptions";
    private $adminPageTitle      = "Filters Options";
    private $adminPageDescription= "Setup options to control de filter functions.";
    private $adminPageMenuTitle  = "Filter Options";
    private $adminPageSlug       = "admin-menu-filter-options";
    private $adminPageCapability = "manage_options"; // user capabilities to access this filter
    private $adminPageIcon       = "dashicons-list-view"; // https://developer.wordpress.org/resource/dashicons/
    private $adminPagePosition   = "80"; // position of item in the main Wordpress menu
    private $fieldsGroup         = ""; // don't change here
    private $currentSection      = 0; // don't change here
    private $currentSectionId    = ""; // dont change here
    private $currentFieldId      = ""; // dont change here
    private $coreOptions         = null;
    private $sections            = array(
        //<callback> => <title> 
        "filterTags" => "Filter Tags Options",
        "filterCategories" => "Filter Categories Remap",
        "filterFunctions" => "Filter Functions Options"
    );
    
    function __construct() {
        $this->fieldsGroup = $this->pluginId . "options";        
        add_action('admin_menu', array($this, 'createAdminPage'));
        add_action('admin_init', array($this, 'adminPageOptions'));
    }

    function createAdminPage() { // setup the admin page and basic html
        add_menu_page( 
            $this->adminPageTitle,
            $this->adminPageMenuTitle,
            $this->adminPageCapability,
            $this->adminPageSlug,
            function(){ /* *************** html output ******************** */ ?> 
                <div class="wrap">
                    <h1><?php echo $this->adminPageTitle; ?></h1>
                    <p><?php echo $this->adminPageDescription; ?></p>
                    <?php settings_errors(); ?>
                    <form action='options.php' method="POST">
                        <?php
                            settings_fields($this->fieldsGroup);
                            do_settings_sections($this->adminPageSlug);
                            submit_button();
                        ?>
                    </form>
                </div>
            <?php },
            $this->adminPageIcon,
            $this->adminPagePosition
        );
    }

    function addSection($title = null, $callback = "defaultSectionCallback") {
        $this->currentSection++;
        $this->currentSectionId = $this->pluginId . "section_" . $this->currentSection;
        add_settings_section($this->currentSectionId, $title, array($this, $callback), $this->adminPageSlug);
    }

    function addField( $fieldName, $fieldTitle, $fieldType = "text", $small="" ){
        $section = $this->pluginId . "section_" . $this->currentSection;
        $fieldId = $this->pluginId . "field_" . $fieldName;
        $this->currentFieldId = $fieldId;
        add_settings_field(
            $fieldId, 
            $fieldTitle, 
            function() use( $section, $fieldId, $fieldType, $small ){ /* html output */ 
                $value = get_option($fieldId) ?? "";
                switch( $fieldType ){
                    case "text": 
                        echo <<<HTML
                            <input type="text" name="$fieldId" value="$value" /> 
                            <small style="display: block;">$small</small>
                        HTML;
                        break;
                    case "textarea": 
                        echo <<<HTML
                            <textarea name="$fieldId">$value</textarea>
                            <small style="display: block;">$small</small>
                        HTML;
                        break;
                    case "checkbox":  ?>
                        <input type="checkbox" name=<?php echo $fieldId; ?> value="1" <?php checked(get_option($fieldId), '1'); ?>/> 
                        <small style="display: block;"><?php echo $small; ?></small>
                        <?php break;
                }
            }, 
            $this->adminPageSlug, 
            $section);
        $type = array("text" => "string", "textarea" => "string", "checkbox" => "boolean")[$fieldType];
        register_setting( $this->fieldsGroup, $fieldId, array('type' => $type, 'sanitize_callback' => 'sanitize_text_field') );
    }

    function adminPageOptions() {
        foreach( $this->sections as $callback => $title ){
            $this->addSection($title);
            call_user_func($callback); // adds fields 
        }
    }

    function folderPath() {
        $fieldName = $this->pluginId . "field_" . __FUNCTION__;
        ?>        
        <input type="text" name="<?php echo __FUNCTION__;?>" value="<?php echo get_option($fieldName); ?>" />
        <br/>
        <small>&nbsp;after ... wp-content / themes / &lt;your theme folder name&gt; / </small>
    <?php }

}

$moduleAdminMenuPageItem = new ModuleAdminMenuPageItem();

function filterTags() {
    $res = "";
    return $res;
}

function filterCategories() {
    $res = "";
    return $res;
}

function filterFunctions() {
    $res = "";
    return $res;
}
?>