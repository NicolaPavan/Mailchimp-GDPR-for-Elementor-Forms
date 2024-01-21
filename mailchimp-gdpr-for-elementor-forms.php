<?php
/**
 * Plugin Name: Mailchimp GDPR for Elementor Forms
 * Plugin URI: https://nicolapavan.it
 * Description: [BETA] add a custom checkbox to GDPR complaint form in Elementor
 * Version: 1.0.0
 * Author: Nicola Pavan
 * Author URI: https://nicolapavan.it/
 * Text Domain: mailchimp-gdpr-for-elementor-forms
 * License: GPL2+
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Checks to see if Elementor Pro is installed. Returns admin error and deactivates plugin if Elementor Pro is not installed.
 *
 * @return void
 */
function ebfef_check_for_elementor(){
    if (!function_exists('is_plugin_active')) {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    if( !is_plugin_active( 'elementor-pro/elementor-pro.php' ) ){
        add_action( 'admin_notices', 'ebfef_fail_load' );
        deactivate_plugins( plugin_basename( __FILE__) );
    }
}
add_action( 'plugins_loaded', 'ebfef_check_for_elementor' );

/**
 * Admin error message if Elementor Pro is not installed
 *
 * @return void
 */
function ebfef_fail_load() {
    load_plugin_textdomain( 'elementor-forms-mailchimp-gdpr' );

    $message = '<div class="error">';
    $message .= '<h3>' . esc_html__( 'Elementor Pro plugin is missing.', 'elementor-forms-mailchimp-gdpr' ) . '</h3>';
    $message .= '<p>' . esc_html__( 'Please to install and active the Elementor Pro plugin for Mailchimp GDPR for Elementor Forms to work!.', 'elementor-forms-mailchimp-gdpr' ) . '</p>';
    $message .= '</div>';
    echo $message;
}

// Add a frontEnd description in Elementor builderform section

function elementor_forms_Mailchimp_GDPR_control( $field ) {
    $field->add_control(
        'mailchimp-gdpr',
        [
            'label' => __( 'mailchimp-gdpr', 'elementor-forms-mailchimp-gdpr' ),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'description' => __( 'enable if there is a Mailchimp subscription is optional <br/> ADD a id = mailchimp-gdpr" '),
        ]
    );
}
add_action( 'elementor/element/form/section_mailchimp/before_section_end', 'elementor_forms_Mailchimp_GDPR_control' );


use ElementorPro\Modules\Forms\Module as Form_Module;
use ElementorPro\Modules\Forms\Registrars\Form_Actions_Registrar;

function disable_actions(array $actions_to_disable)
    {
        /** @var Form_Module $module */
        $module = Form_Module::instance();
        $actions = $module->actions_registrar->get();
        foreach ($actions_to_disable as $a) {
            unset($actions[$a]);
        }
        $module->actions_registrar = new class($actions) extends Form_Actions_Registrar
        {
            private $override_items;
            public function __construct($items)
            {
                $this->override_items = $items;
            }
            public function get($id = null)
            {
                if (!$id) {
                    return $this->override_items;
                }
                return isset($this->override_items[$id]) ? $this->override_items[$id] : null;
            }
        };
    }

$backend_mailchimp_check;
$print;
$user_checkbox_state;

function check_mailchimp_checkbox( $record, $ajax_handler ) {
$settings = $record->get( 'form_settings' );
$GLOBALS["backend_mailchimp_check"] = $settings['mailchimp-gdpr'];
$raw_fields = $record->get( 'fields' );
    $fields = [];
    foreach ( $raw_fields as $id => $field ) {
        $fields[ $id ] = $field['value'];
    }
	$GLOBALS['user_checkbox_state'] = $fields['mailchimp_gdpr'];

$GLOBALS['print']=$user_checkbox_state;

}

add_action( 'elementor_pro/forms/validation', 'check_mailchimp_checkbox', 10, 3 );



add_action( 'elementor_pro/forms/process', function() {
if ($GLOBALS['backend_mailchimp_check'] == 'yes' && $GLOBALS['user_checkbox_state'] != 'on' ){
	disable_actions(['mailchimp']);
}
},  10, 2 );


	
add_action( 'elementor_pro/forms/new_record', function( $record, $handler ) {

	$output['result'] = $GLOBALS['backend_mailchimp_check'] == 'yes' && $GLOBALS['user_checkbox_state'] != 'on' ;
	$handler->add_response_data( true, $output);
		
	
}, 10, 2 );

