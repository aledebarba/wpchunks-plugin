<?php 

class WPChunksPlugin {
    
    private $pluginId            = "wpchunks_";
    private $adminPageTitle      = "WP Chunks";
    private $adminPageMenuTitle  = "WPChunks";
    private $adminPageSlug       = "wpchunks-page-settings";
    private $adminPageCapability = "manage_options";
    private $adminPageIcon       = "dashicons-drumstick";
    private $adminPagePosition   = "80";
    private $fieldsGroup         = ""; // don't change here
    private $currentSection      = 0; // don't change here
    private $currentSectionId    = ""; // dont change here
    private $currentFieldId      = ""; // dont change here
    private $coreOptions         = null;

    function __construct() {
        $this->fieldsGroup = $this->pluginId . "options";        
        add_action('admin_menu', array($this, 'createAdminPage'));
        add_action('admin_init', array($this, 'adminPageOptions'));
    }

    function createAdminPage() {
        add_menu_page(
            $this->adminPageTitle,
            $this->adminPageMenuTitle,
            $this->adminPageCapability,
            $this->adminPageSlug,
            function(){ /* html output */ ?> 
                <div class="wrap">
                    <h1>WP Chunks</h1>
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
        $this->addSection("Files Options");
        $this->addField("folderPath", "Default Folder Path", "text", "the components folder inside your theme folder (default: components)");
        $this->addField("filePrefix", "Default File Prefix", "text", "the component folder prefix (default: component)");
        $this->addField("jsElement", "Add HTML element to JS component as entry point", "checkbox", "if wpchunk should create a html element to js component use as entry point");
    }

    function defaultSectionCallBack() {?>
        <p>Configurations options for <?php echo $this->adminPageTitle; ?>.</p>
    <?php }

    function folderPath() {
        $fieldName = $this->pluginId . "field_" . __FUNCTION__;
        ?>        
        <input type="text" name="<?php echo __FUNCTION__;?>" value="<?php echo get_option($fieldName); ?>" />
        <br/>
        <small>&nbsp;after ... wp-content / themes / &lt;your theme folder name&gt; / </small>
    <?php }

    function filePrefix() {
        $fieldName = $this->pluginId . "field_" . __FUNCTION__;
        ?>
        <input type="text" name="<?php echo $fieldName; ?>" value="<?php echo get_option($fieldName); ?>" />
    <?php }

    function jsElement() {
        $fieldName = $this->pluginId . "field_" . __FUNCTION__;
        ?>
        <input type="checkbox" name="<?php echo $fieldName; ?>" <?php echo get_option($fieldName) == true ? "checked" : ""; ?>/>
    <?php }

    public function get($key) {
        return $this[$key];
    }
    public function set($key, $value) {
        $this[$key] = $value;
    }
    public function getSetting($key) {
        $fieldName = $this->pluginId . "field_" . $key;
        return get_option($fieldName);
    }
    public function getOption($key) {
        $fieldName = $this->pluginId . "field_" . $key;
        return get_option($fieldName);
    }
}
?>