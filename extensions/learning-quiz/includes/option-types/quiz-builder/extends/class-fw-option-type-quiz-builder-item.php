<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Extend this class to create items for form-builder option type
 */
abstract class FW_Option_Type_Quiz_Builder_Item extends FW_Option_Type_Builder_Item {

	private $options = array(
		'quiz-general-options'  => array(),
		'quiz-specific-options' => array(),
	);

	final public function get_builder_type() {
		return 'quiz-builder';
	}

	/**
	 * Render item html for frontend form
	 *
	 * @param array $item Attributes from Backbone JSON
	 * @param mixed|null $input_value
	 *
	 * @return string HTML
	 */
	abstract public function render( array $item, $input_value );

	/**
	 * Process item on frontend form submit
	 *
	 * @param array $item Attributes from Backbone JSON
	 * @param mixed|null $input_value
	 *
	 * @return FW_Quiz_Question_Process_Response
	 */
	abstract public function process_item( array $item, $input_value );

	/**
	 * Return item options that was set before using set_options() method,
	 * The options will be merged with default items options: Question and Percentage mark
	 *
	 * @return array
	 */
	final public function get_options() {
		$this->options['quiz-general-options'] = array(
			'type'    => 'group',
			'options' => array(
				'question' => array(
					'type'  => 'text',
					'label' => __( 'Question', 'fw' ),
					'attr'  => array(
						'placeholder' => __( 'Type the question...', 'fw' )
					),
				),
				'points'   => array(
					'type'  => 'text',
					'label' => __( 'Points', 'fw' ),
					'value' => 10
				)
			)
		);

		if ( empty( $this->options['quiz-specific-options'] ) ) {
			unset( $this->options['quiz-specific-options'] );
		}

		return $this->options;
	}

	/**
	 * Set item options
	 *
	 * @param array $options
	 */
	protected function set_options( array $options ) {
		if ( ! empty( $options ) ) {
			$this->options['quiz-specific-options'] = array(
				'type'    => 'group',
				'options' => $options
			);
		}
	}

	/**
	 * Search relative path in '/extensions/learning-quiz/includes/option-types/{builder_type}/items/{item_type}/'
	 *
	 * @param string $rel_path
	 * @param string $default_path Used if no path found
	 *
	 * @return false|string
	 */
	final protected function locate_path( $rel_path, $default_path ) {
		if ( $path = fw()->extensions->get( 'learning-quiz' )->locate_path( '/' . $this->get_builder_type() . '/items/' . $this->get_type() . $rel_path ) ) {
			return $path;
		} else {
			return $default_path;
		}
	}

	/**
	 * Validate the quiz item
	 *
	 * @param array $item
	 *
	 * @return bool
	 */
	public function validate_item( $item ) {
		return true;
	}
}
