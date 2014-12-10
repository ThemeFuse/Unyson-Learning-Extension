<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Default form builder
 * Other form types may define and use new form builders
 */
class FW_Option_Type_Quiz_Builder extends FW_Option_Type_Builder {
	/**
	 * @var FW_Extension_Learning_Quiz
	 */
	private $parent;

	public function get_type() {
		return 'quiz-builder';
	}

	/**
	 * @internal
	 */
	protected function _init() {
		$this->parent = fw()->extensions->get( 'learning-quiz' );
		$dir          = dirname( __FILE__ );

		require $dir . '/extends/class-fw-option-type-quiz-builder-item.php';
		require $dir . '/items/form-builder-items.php';
	}

	/**
	 * @param FW_Option_Type_Builder_Item $item_type_instance
	 *
	 * @return bool
	 */
	protected function item_type_is_valid( $item_type_instance ) {
		return is_subclass_of( $item_type_instance, 'FW_Option_Type_Quiz_Builder_Item' );
	}

	/**
	 * @internal
	 * {@inheritdoc}
	 */
	protected function _enqueue_static( $id, $option, $data ) {
		parent::_enqueue_static( $id, $option, $data );

		wp_enqueue_style(
			'fw-builder-' . $this->get_type(),
			$this->parent->get_declared_URI( '/includes/option-types/' . $this->get_type() . '/static/css/styles.css' ),
			array( 'fw' )
		);
		wp_enqueue_script(
			'fw-builder-' . $this->get_type(),
			$this->parent->get_declared_URI( '/includes/option-types/' . $this->get_type() . '/static/js/helpers.js' ),
			array( 'fw' ),
			fw()->manifest->get_version(),
			true
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_value_from_items( $items ) {
		static $recursion_level = 0;

		/** prevent duplicate shortcodes */
		static $found_shortcodes = array();

		/**
		 * @var FW_Option_Type_Builder_Item[] $item_types
		 */
		$item_types = $this->get_item_types();

		$fixed_items = array();

		foreach ( $items as $item_attributes ) {
			if ( ! isset( $item_attributes['type'] ) || ! isset( $item_types[ $item_attributes['type'] ] ) ) {
				// invalid item type
				continue;
			}

			$fixed_item_attributes = $item_types[ $item_attributes['type'] ]->get_value_from_attributes( $item_attributes );

			// check if required attribute is set and it is unique
			{
				if (
					empty( $fixed_item_attributes['shortcode'] )
					||
					isset( $found_shortcodes[ $fixed_item_attributes['shortcode'] ] )
				) {
					$fixed_item_attributes['shortcode'] = sanitize_key(
						str_replace( '-', '_', $item_attributes['type'] ) . '_' . substr( fw_rand_md5(), 0, 7 )
					);
				}

				$found_shortcodes[ $fixed_item_attributes['shortcode'] ] = true;
			}

			if ( isset( $fixed_item_attributes['_items'] ) ) {
				// item leaved _items key, this means that it has/accepts items in it

				$recursion_level ++;

				$fixed_item_attributes['_items'] = $this->get_value_from_items( $fixed_item_attributes['_items'] );

				$recursion_level --;
			}

			$fixed_items[] = $fixed_item_attributes;

			unset( $fixed_item_attributes );
		}

		/**
		 * this will be real return (not inside a recursion)
		 * make some clean up
		 */
		if ( ! $recursion_level ) {
			$found_shortcodes = array();
		}

		return $fixed_items;
	}

	/**
	 * Generate html form for frontend from builder items
	 *
	 * @param array $items Builder array value json decoded
	 * @param array $input_values {shortcode => value} Usually values from _POST
	 *
	 * @return string HTML
	 */
	public function frontend_render( array $items, array $input_values ) {
		return fw_render_view(
			$this->locate_path( '/views/form.php', dirname( __FILE__ ) . '/views/form.php' ),
			array(
				'items_html' => $this->render_items( $items, $input_values )
			)
		);
	}

	/**
	 * Loop through each item and ask to validate the post answer
	 *
	 * @param array $items
	 * @param array $input_values {shortcode => value} Usually values from _POST
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function process_answers( array $items, array $input_values, $post_id ) {
		/**
		 * @var FW_Option_Type_Learning_Quiz_Builder_Item[] $item_types
		 */
		$item_types = $this->get_item_types();

		$process_response = array();

		foreach ( $items as $item ) {
			if ( ! isset( $item_types[ $item['type'] ] ) ) {
				trigger_error( 'Invalid form item type: ' . $item['type'], E_USER_WARNING );
				continue;
			}

			$input_value = isset( $input_values[ $item['shortcode'] ] ) ? $input_values[ $item['shortcode'] ] : null;

			$response = $item_types[ $item['type'] ]->process_item( $item, $input_value );

			if ( $response ) {
				$process_response[ $item['shortcode'] ] = $response;
			}

			if ( isset( $item['_items'] ) ) {
				$sub_responses = $this->process_answers( $item['_items'], $input_values, $post_id );

				if ( ! empty( $sub_responses ) ) {
					$process_response = array_merge( $process_response, $sub_responses );
				}
			}
		}

		return $process_response;
	}

	/**
	 * Form items value after submit and successful validation
	 *
	 * @param array $items
	 * @param array $input_values {shortcode => value} Usually values from _POST
	 *
	 * @return array
	 */
	public function frontend_get_value_from_items( array $items, array $input_values ) {
		/**
		 * @var FW_Option_Type_Builder_Item[] $item_types
		 */
		$item_types = $this->get_item_types();

		$values = array();

		foreach ( $items as $item ) {
			if ( ! isset( $item_types[ $item['type'] ] ) ) {
				trigger_error( 'Invalid form item type: ' . $item['type'], E_USER_WARNING );
				continue;
			}

			if ( isset( $values[ $item['shortcode'] ] ) ) {
				trigger_error( 'Form item duplicate shortcode: ' . $item['shortcode'], E_USER_WARNING );
			}

			$values[ $item['shortcode'] ] = isset( $input_values[ $item['shortcode'] ] ) ? $input_values[ $item['shortcode'] ] : null;

			if ( isset( $item['_items'] ) ) {
				$sub_values = $this->frontend_get_value_from_items( $item['_items'], $input_values );

				if ( ! empty( $sub_values ) ) {
					$values = array_merge( $values, $sub_values );
				}
			}
		}

		return $values;
	}

	/**
	 * Render items
	 *
	 * This method can be used recursive by items that has another items inside
	 *
	 * @param array $items
	 * @param array $input_values
	 *
	 * @return string
	 */
	public function render_items( array $items, array $input_values ) {
		/**
		 * @var FW_Option_Type_Quiz_Builder_Item[] $item_types
		 */
		$item_types = $this->get_item_types();

		wp_enqueue_style( $this->get_type() . '-styles',
			$this->parent->locate_URI( '/includes/option-types/' . $this->get_type() . '/static/css/style.css' ),
			array(),
			fw()->manifest->get_version()
		);

		$html = '';

		foreach ( $items as $key => $item ) {
			if ( ! isset( $item_types[ $item['type'] ] ) ) {
				trigger_error( 'Invalid form item type: ' . $item['type'], E_USER_WARNING );
				continue;
			}

			$input_value = isset( $input_values[ $item['shortcode'] ] ) ? $input_values[ $item['shortcode'] ] : null;

			$item['number'] = ++ $key;

			$html .= $item_types[ $item['type'] ]->render( $item, $input_value );
		}

		return $html;
	}

	/**
	 * @return FW_Option_Type_Quiz_Builder_Item[]
	 */
	public function get_items() {
		return $this->get_item_types();
	}

	/**
	 * Search relative path in '/extensions/learning-quiz/includes/options-types/{builder_type}/'
	 *
	 * @param string $rel_path
	 * @param string $default_path Used if no path found
	 *
	 * @return false|string
	 */
	private function locate_path( $rel_path, $default_path ) {
		if ( $path = fw()->extensions->get( 'learning-quiz' )->locate_path( '/' . $this->get_type() . $rel_path ) ) {
			return $path;
		} else {
			return $default_path;
		}
	}
}

FW_Option_Type::register( 'FW_Option_Type_Quiz_Builder' );

/**
 * Class FW_Learning_Quiz_Question_Process_Response
 */
class FW_Quiz_Question_Process_Response {
	/**
	 * @var string
	 */
	private $question = '';

	/**
	 * @var int|float|string|array
	 */
	private $correct_answer = array();

	/**
	 * @var int|float|string|array
	 */
	private $current_answer = array();

	/**
	 * @var float|int
	 */
	private $max_percentage = 0;

	/**
	 * @var float|int
	 */
	private $current_percentage = 0;

	/**
	 * @var string
	 */
	private $comments = '';

	/**
	 * @param string $question
	 * @param int|float|string|array $correct_answer
	 * @param int|float|string|array $current_answer
	 * @param int|float $max_percentage
	 * @param int|float $current_percentage
	 * @param string $comments
	 */
	public function __construct(
		$question = '',
		$correct_answer = array(),
		$current_answer = array(),
		$max_percentage = 0,
		$current_percentage = 0,
		$comments = ''
	) {
		if ( is_string( $question ) ) {
			$this->question = $question;
		}

		$this->current_answer = $current_answer;

		$this->correct_answer = $correct_answer;

		if ( is_float( $max_percentage ) || is_int( $max_percentage ) ) {
			$this->max_percentage = $max_percentage;
		}

		if ( is_float( $current_percentage ) || is_int( $current_percentage ) ) {
			$this->current_percentage = $current_percentage;
		}

		if ( is_string( $comments ) ) {
			$this->comments = $comments;
		}
	}

	/**
	 * @param string $question
	 */
	public function set_question( $question ) {
		if ( is_string( $question ) ) {
			$this->question = $question;
		}
	}

	/**
	 * @param int|float|string|array $correct_answer
	 */
	public function set_correct_answer( $correct_answer ) {
		$this->correct_answer = $correct_answer;
	}

	/**
	 * @param int|float|string|array $current_answer
	 */
	public function set_current_answer( $current_answer ) {
		$this->current_answer = $current_answer;
	}

	/**
	 * @param int|float $max_percentage
	 */
	public function set_max_percentage( $max_percentage ) {
		if ( is_numeric( $max_percentage ) ) {
			$this->max_percentage = $max_percentage;
		}
	}

	/**
	 * @param int|float $current_percentage
	 */
	public function set_current_percentage( $current_percentage ) {
		if ( is_numeric( $current_percentage ) ) {
			$this->current_percentage = $current_percentage;
		}
	}

	/**
	 * @param string $comments
	 */
	public function set_comments( $comments ) {
		if ( is_string( $comments ) ) {
			$this->comments = $comments;
		}
	}

	/**
	 * @return string
	 */
	public function get_question() {
		return $this->question;
	}

	/**
	 * @return int|float|string|array
	 */
	public function get_correct_answer() {
		return $this->correct_answer;
	}

	/**
	 * @return int|float|string|array
	 */
	public function get_current_answer() {
		return $this->current_answer;
	}

	/**
	 * @return float|int
	 */
	public function get_max_percentage() {
		return $this->max_percentage;
	}

	/**
	 * @return float|int
	 */
	public function get_current_percentage() {
		return $this->current_percentage;
	}

	/**
	 * @return string
	 */
	public function get_comments() {
		return $this->comments;
	}
}