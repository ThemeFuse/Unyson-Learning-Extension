<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Learning_Grading_Quiz_Review {

	/**
	 * @var FW_Quiz_Question_Process_Response[]
	 */
	private $questions = array();

	/**
	 * @var int
	 */
	private $accumulated = 0;

	private $minimum_pass_mark = 0;

	private $status = '';

	/**
	 * @var string
	 */
	private $time = null;

	public function __construct( array $args ) {
		foreach ( $args as $key => $arg ) {
			switch ( $key ) {
				case 'questions' :
					$this->add_questions( $arg );
					break;
				case 'accumulated' :
					$this->add_accumulated( $arg );
					break;
				case 'minimum-pass-mark' :
					$this->add_minimum_pass_mark( $arg );
					break;
				case 'status' :
					$this->add_status( $arg );
					break;
				case 'time' :
					$this->add_time( $arg );
					break;
			}
		}
	}

	public function add_questions( array $questions ) {
		foreach ( $questions as $id => $question ) {
			if ( ! $question instanceof FW_Quiz_Question_Process_Response ) {
				continue;
			}

			$this->questions[ $id ] = $question;
		}
	}

	public function add_accumulated( $number ) {
		if ( ! is_numeric( $number ) ) {
			return;
		}

		$this->accumulated = ( ( float ) $number > 0 ) ? ( float ) $number : (int) $number;
	}

	public function add_minimum_pass_mark( $number ) {
		if ( ! is_numeric( $number ) ) {
			return;
		}

		$this->minimum_pass_mark = ( ( float ) $number > 0 ) ? ( float ) $number : (int) $number;
	}

	public function add_status( $status ) {

		if (
			empty( $status )
			|| ! is_string( $status )
			|| (
				$status != 'pending'
				&& $status != 'passed'
				&& $status != 'filed'
			)
		) {
			return;
		}

		$this->status = $status;
	}

	public function add_time( $time ) {
		$this->time = date( 'Y-m-d H:i:s', $time );
	}

	/**
	 * @return FW_Quiz_Question_Process_Response[]
	 */
	public function get_questions() {
		return $this->questions;
	}

	public function get_accumulated() {
		return $this->accumulated;
	}

	public function get_minimum_pass_mark() {
		return $this->minimum_pass_mark;
	}

	public function get_status() {

		return $this->status;
	}

	public function get_time() {
		return $this->time;
	}
}