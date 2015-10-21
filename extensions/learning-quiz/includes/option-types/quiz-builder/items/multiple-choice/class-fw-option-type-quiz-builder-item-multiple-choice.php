<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Option_Type_Quiz_Builder_Item_Multiple_Choice extends FW_Option_Type_Quiz_Builder_Item {
	/**
	 * @var FW_Extension_Learning_Quiz
	 */
	private $parent = null;

	public function _init() {
		$this->parent = fw()->extensions->get( 'learning-quiz' );

		$this->set_options( array(
			'correct-answers-group' => array(
				'type'    => 'group',
				'options' => array(
					'correct-answers' => array(
						'type'   => 'addable-option',
						'label'  => __( 'Correct answers', 'fw' ),
						'desc'   => __( 'Add correct answer variants', 'fw' ),
						'option' => array(
							'attr' => array(
								'placeholder' => __( 'Set Correct Answer', 'fw' )
							),
							'type' => 'text',
						),
					)
				)
			),
			'wrong-answers'         => array(
				'type'   => 'addable-option',
				'attr'   => array( 'class' => 'custom-class', 'data-foo' => 'bar' ),
				'label'  => __( 'Wrong answers', 'fw' ),
				'desc'   => __( 'Add wrong answer variants', 'fw' ),
				'option' => array(
					'attr' => array(
						'placeholder' => __( 'Set Wrong Answer', 'fw' )
					),
					'type' => 'text',
				),
			)
		) );
	}

	public function get_type() {
		return 'multiple-choice';
	}

	public function get_thumbnails() {
		$image = $this->parent->get_declared_URI( '/includes/option-types/' . $this->get_builder_type() . '/items/' . $this->get_type() . '/static/images/icon.png' );

		return array(
			array(
				'html' =>
					'<div class="quiz-item-type-icon-title" data-hover-tip="' . __( 'Creates a',
						'fw' ) . ' ' . __( 'Multiple Choice', 'fw' ) . ' ' . __( 'item', 'fw' ) . '">' .
					'<span><img src="' . $image . '"><br/>' .
					__( 'Multiple Choice', 'fw' ) . '</span>' .
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
			'fw_quiz_builder_item_type_multiple_choice',
			array(
				'l10n'     => array(
					'label'      => __( 'Label', 'fw' ),
					'item_title' => __( 'Add/Edit Question', 'fw' ),
					'edit'       => __( 'Edit', 'fw' ),
					'delete'     => __( 'Delete', 'fw' ),
					'name'       => __( 'Multiple Choice', 'fw' ),
					'more_items' => __( 'More', 'fw' ),
					'close'      => __( 'Close', 'fw' ),
					'edit_label' => __( 'Edit Label', 'fw' ),
					'validator'  => array(
						'empty_question' => __( 'The question label is empty', 'fw' ),
						'invalid_points' => __( 'Invalid mark point number', 'fw' ),
						'empty_form'     => __( 'There needs to be at least one correct answer', 'fw' ),
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
		// prepare attributes
		{
			$attr = array(
				'name' => $item['shortcode'] . '[]',
				'id'   => 'id-' . fw_unique_increment(),
			);
		}

		if ( empty( $item['options']['correct-answers'] ) ) {
			return '';
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
		$response->set_correct_answer( $item['options']['correct-answers'] );
		$response->set_current_answer( $input_value );
		$response->set_max_percentage( (float) $item['options']['points'] );

		$percent_per_answer = $item['options']['points'] / count( $item['options']['correct-answers'] );

		if ( empty( $input_value ) ) {
			return $response;
		}

		$count = 0;
		foreach ( $input_value as $answer ) {
			if ( in_array( $answer, $item['options']['correct-answers'] ) ) {
				$count += $percent_per_answer;
			} else {
				$count -= $percent_per_answer;
			}
		}

		if ( $count < 0 ) {
			$count = 0;
		}

		$response->set_current_percentage( $count );

		return $response;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate_item( $item ) {
		if ( ! isset( $item['correct-answers'] ) || empty( $item['correct-answers'] ) ) {
			return false;
		}

		return true;
	}
}

FW_Option_Type_Builder::register_item_type( 'FW_Option_Type_Quiz_Builder_Item_Multiple_Choice' );