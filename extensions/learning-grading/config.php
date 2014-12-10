<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

// Learning instructor role name
$cfg['user-name'] = __( 'Instructor', 'fw' );

// Add other capabilities for the learning instructor. The required capabilities are similar to author
$cfg['user-capabilities'] = array();