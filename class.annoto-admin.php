<?php

/**
 * Class AnnotoAdmin
 */
class AnnotoAdmin {
    private $annoto_options;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'annoto_add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'annoto_page_init' ) );
    }

    public function annoto_add_plugin_page() {
        add_options_page(
            __( 'Annoto', 'annoto' ), // page_title
            __( 'Annoto', 'annoto' ), // menu_title
            'manage_options', // capability
            'annoto-key-config', // menu_slug
            array( $this, 'annoto_create_admin_page' ) // function
        );
    }

    public function annoto_create_admin_page() {
        $this->annoto_options = get_option( ANNOTO_SETTING_KEY_NAME ); ?>

        <div class="wrap">
            <h2>Annoto settings</h2>
            <p></p>
            <?php  settings_errors('', false, true); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields( 'annoto_options' );
                do_settings_sections( 'annoto-admin' );
                submit_button();
                ?>
            </form>
        </div>
    <?php }

    public function annoto_page_init() {
        $settings = Annoto::$annotoDefaultSettings;
        
        register_setting(
            'annoto_options', // option_group
            ANNOTO_SETTING_KEY_NAME, // option_name
            array( $this, 'sanitize_option_annoto_settings' ) // sanitize_callback
        );

        add_settings_section(
            'annoto_setting_section',
            '',
            array( $this, 'annoto_section_info' ),
            'annoto-admin'
        );

        foreach ($settings as $setting) {
            add_settings_field(
                $setting['name'],
                $setting['desc'],
                array( $this, 'render_cb' ),
                'annoto-admin',
                'annoto_setting_section',
                ['id' => $setting['name'], 'type' => $setting['type']]
            );
        }
    }

    public function sanitize_option_annoto_settings($input) {

        $sanitary_values = array();
        if ( isset( $input['api-key'] ) ) {
            $sanitary_values['api-key'] = sanitize_text_field( $input['api-key'] );
        }

        if ( isset( $input['sso-secret'] ) ) {
            $sanitary_values['sso-secret'] = sanitize_text_field( $input['sso-secret'] );
        }

        if ( isset( $input['scripturl'] ) ) {
           if ( esc_url_raw($input['scripturl']) !== $input['scripturl'] ) {
               $message = __('The URL has been saved but may not be valid, please check it.');
               add_settings_error(ANNOTO_SETTING_KEY_NAME, ANNOTO_SETTING_KEY_NAME, $message);
           }
            $sanitary_values['scripturl'] = esc_url_raw( $input['scripturl'] );
        }

        if ( isset( $input['deploymentDomain'] ) ) {
            $sanitary_values['deploymentDomain'] =  $input['deploymentDomain'] ;
        }

        if ( isset( $input['locale'] ) ) {
            $sanitary_values['locale'] =  $input['locale'] ;
        }

        return $sanitary_values;
    }

    public function annoto_section_info() {

    }

    public function render_cb($attr) {

        switch ($attr['type']) {
            case 'input':
                $options = isset( $this->annoto_options[$attr['id']] ) ? esc_attr( $this->annoto_options[$attr['id']] ) : '';
                printf(
                    '<input class="regular-text" type="text" name="annoto_settings['.$attr['id'].']" id="'.$attr['id'].'" value="%s">',
                    $options
                );
                break;
            case 'checkbox':
                $options = isset( $this->annoto_options['locale'] ) ? esc_attr( $this->annoto_options['locale']) : 0;
                printf('<input class="regular-text" type="checkbox" name="annoto_settings['.$attr['id'].']" id="'. $attr['id'] .'" value="1"'. checked( 1, $options, false ));
                break;
            case 'select':
                $options = isset( $this->annoto_options['deploymentDomain'] ) ? esc_attr( $this->annoto_options['deploymentDomain']) : 'euregion';
                ?>
                <select name="annoto_settings[deploymentDomain]" id="domain">
                    <option value = "euregion" <?php selected( $options, 'euregion' ) ?>>EU region</option>
                    <option value = "usregion" <?php selected( $options, 'usregion' ) ?>>US region</option>
                    <option value = "custom" <?php selected( $options, 'custom' ) ?>>Custom</option>
                </select>
                <?php
                break;
        }
    }
}
