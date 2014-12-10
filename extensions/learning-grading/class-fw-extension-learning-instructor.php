<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Extension_Learning_Instructor extends FW_Extension {

	/**
	 * @var string
	 */
	private $user_role = 'learning-instructor';

	/**
	 * @var string
	 */
	private $user_name = 'Instructor';

	/**
	 * @var array
	 */
	private $user_capabilities = array(
		'read',
		'publish_posts',
		'edit_published_posts',
		'edit_posts',
		'delete_published_posts',
		'delete_posts',
		'upload_files',
	);

	/**
	 * @var FW_Extension_Learning
	 */
	private $learning = null;

	/**
	 * @internal
	 */
	public function _init() {

		$user_require = $this->get_config( 'user-require' );

		if ( $user_require === false ) {
			return;
		}

		$this->learning = fw()->extensions->get( 'learning' );

		$this->define_role();
		$this->register_role();
	}

	private function define_role() {
		$name         = $this->get_config( 'user-name' );
		$capabilities = $this->get_config( 'user-capabilities' );

		if ( ! empty( $name ) && is_string( $name ) ) {
			$this->user_name = $name;
		}
		if ( ! empty( $capabilities ) && is_array( $capabilities ) ) {
			$this->user_capabilities = array_unique( array_merge( $this->user_capabilities, $capabilities ) );
		}
	}

	private function register_role() {
		add_role( $this->user_role, $this->user_name, $this->user_capabilities );

		$role = get_role( $this->user_role );

		foreach ( $this->user_capabilities as $cap ) {
			$role->add_cap( $cap );
		}
	}
}