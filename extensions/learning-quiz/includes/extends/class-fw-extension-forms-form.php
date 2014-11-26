<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
return;

/**
 * Forms sub extensions should extend this class
 */
abstract class FW_Extension_Quiz_Form extends FW_Extension {
	/**
	 * @return string
	 */
	abstract public function get_form_type();

	/**
	 * @return string
	 */
	abstract public function get_form_type_title();

	/**
	 * Options for edit form page
	 * @return array
	 */
	abstract public function get_form_options();

	/**
	 * Specify which builder option type this form type is using (in options)
	 * @return string
	 */
	abstract public function get_form_builder_type();

	/**
	 * Return value of the option type $this->get_form_builder_type() used in options
	 *
	 * @param int $form_id Post id
	 *
	 * @return array
	 */
	abstract public function get_form_builder_value( $form_id );

	/**
	 * Do something with form items values on frontend form submit after successful validation
	 *
	 * @param $form_id
	 * @param array $shortcodes_values {shortcode => form_value}
	 * @param array $shortcodes_items {shortcode => item_data}
	 */
	abstract public function process_frontend_form_submit( $form_id, $shortcodes_values, $shortcodes_items );
}