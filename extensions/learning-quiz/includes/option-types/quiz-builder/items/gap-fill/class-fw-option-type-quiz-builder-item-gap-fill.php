<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Quiz_Builder_Item_Gap_Fill extends FW_Option_Type_Quiz_Builder_Item {
	/**
	 * @var FW_Extension_Learning_Quiz
	 */
	private $parent = null;

	public function _init() {
		$this->parent = fw()->extensions->get( 'learning-quiz' );

		$this->set_options( array(
			'text-before'    => array(
				'type'  => 'text',
				'attr'  => array(
					'class'       => 'learning-quiz-gap-fill-text gap',
					'placeholder' => __( 'Text before gap', 'fw' )
				),
				'label' => __( 'Text before gap', 'fw' ),
				'desc'  => false,
			),
			'correct-answer' => array(
				'type'  => 'text',
				'attr'  => array(
					'class'       => 'learning-quiz-gap-fill-text before',
					'placeholder' => __( 'Gap', 'fw' )
				),
				'label' => __( 'Gap', 'fw' ),
				'desc'  => false,
			),
			'text-after'     => array(
				'type'  => 'text',
				'attr'  => array(
					'class'       => 'learning-quiz-gap-fill-text after',
					'placeholder' => __( 'Text after gap', 'fw' )
				),
				'label' => __( 'Text after gap', 'fw' ),
				'desc'  => false,
			)
		) );
	}

	public function get_type() {
		return 'gap-fill';
	}

	public function get_thumbnails() {
		$image = $this->parent->get_declared_URI( '/includes/option-types/' . $this->get_builder_type() . '/items/' . $this->get_type() . '/static/images/icon.png' );

		return array(
			array(
				'html' =>
					'<div class="quiz-item-type-icon-title" data-hover-tip="' . __( 'Creates a',
						'fw' ) . ' ' . __( 'Gap Fill', 'fw' ) . ' ' . __( 'item', 'fw' ) . '">' .
					'<span><img src="' . $image . '"><br/>' .
					__( 'Gap Fill', 'fw' ) . '</span>' .
					'</div>'
			)
		);
	}

	public function enqueue_static() {

		wp_enqueue_style(
			'fw-builder-' . $this->get_builder_type() . '-item-' . $this->get_type(),
			$this->parent->get_declared_URI( '/includes/option-types/' . $this->get_builder_type() . '/items/' . $this->get_type() . '/static/css/styles.css' )
		);

		wp_enqueue_script(
			'fw-builder-' . $this->get_builder_type() . '-item-' . $this->get_type(),
			$this->parent->get_declared_URI( '/includes/option-types/' . $this->get_builder_type() . '/items/' . $this->get_type() . '/static/js/scripts.js' ),
			array(
				'fw-events',
			),
			fw()->manifest->get_version(),
			true
		);

		wp_localize_script(
			'fw-builder-' . $this->get_builder_type() . '-item-' . $this->get_type(),
			'fw_quiz_builder_item_type_gap_fill',
			array(
				'l10n'     => array(
					'label'      => __( 'Label', 'fw' ),
					'item_title' => __( 'Add/Edit Question', 'fw' ),
					'edit'       => __( 'Edit', 'fw' ),
					'name'       => __( 'Gap _____ Fill', 'fw' ),
					'delete'     => __( 'Delete', 'fw' ),
					'edit_label' => __( 'Edit Label', 'fw' ),
					'validator'  => array(
						'empty_question' => __( 'The question label is empty', 'fw' ),
						'invalid_points' => __( 'Invalid mark point number', 'fw' ),
						'empty_form'     => sprintf(
							__( 'At least one of the fields ( %s or %s ) has to ve filled with text', 'fw' ),
							__( 'Text before gap', 'fw' ),
							__( 'Text after gap', 'fw' )
						),
					)
				),
				'options'  => $this->get_options(),
				'defaults' => array(
					'type'    => $this->get_type(),
					'width'   => '1-2',
					'options' => fw_get_options_values_from_input( $this->get_options(), array() )
				)
			)
		);

		fw()->backend->enqueue_options_static( $this->get_options() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_value_from_attributes( $attributes ) {
		return $attributes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render( array $item, $input_value ) {
		if ( empty( $item['options']['text-before'] ) && empty( $item['options']['correct-answer'] ) && empty( $item['options']['text-after'] ) ) {
			return '';
		}
		// prepare attributes
		{
			$attr = array(
				'name' => $item['shortcode'],
				'id'   => 'id-' . fw_unique_increment(),
			);
		}

		return fw_render_view(
			$this->locate_path( '/views/view.php', dirname( __FILE__ ) . '/views/view.php' ),
			array(
				'item'      => $item,
				'type'      => $this->get_type(),
				'attr'      => $attr,
				'max_width' => 12,
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process_item( array $item, $input_value ) {
		$response = new FW_Quiz_Question_Process_Response();
		$response->set_question( $item['options']['question'] );
		$response->set_correct_answer( $item['options']['correct-answer'] );
		$response->set_current_answer( $input_value );
		$response->set_max_percentage( (float) $item['options']['points'] );

		$item['options']['correct-answer'] = trim( $item['options']['correct-answer'] );
		$item['options']['correct-answer'] = strtolower( $item['options']['correct-answer'] );
		preg_replace( '/\s{1,}/i', ' ', $item['options']['correct-answer'] );

		$input_value = trim( $input_value );
		$input_value = strtolower( $input_value );
		preg_replace( '/\s{1,}/i', ' ', $input_value );

		if ( $input_value == $item['options']['correct-answer'] ) {
			$response->set_current_percentage( $item['options']['points'] );
		}

		return $response;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate_item( $item ) {

		if (
			! isset( $item['text-before'] ) ||
			! isset( $item['text-after'] ) ||
			( empty( $item['text-before'] ) && empty( $item['text-after'] ) )
		) {
			return false;
		}

		return true;
	}
}

FW_Option_Type_Builder::register_item_type( 'FW_Option_Type_Quiz_Builder_Item_Gap_Fill' );