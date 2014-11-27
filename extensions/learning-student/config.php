<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

// Define if the learning extension will use the student user.
$cfg['user-require'] = true;

// Learning student role name
$cfg['user-name'] = __( 'Student', 'fw' );

// Add other capabilities for the learning student. The required one is 'read' that is added already
$cfg['user-capabilities'] = array();

// Require that user can pass the lessons only in order, or can access/pass them in random order
$cfg['lessons-in-order'] = true;

// Require that user can pass the lessons only in order, or can access/pass them in random order
$cfg['enable-flash-messages'] = true;