<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$dir = dirname( __FILE__ );
require $dir . '/true-false/class-fw-option-type-quiz-builder-item-true-false.php';
require $dir . '/single-choice/class-fw-option-type-quiz-builder-item-single-choice.php';
require $dir . '/multiple-choice/class-fw-option-type-quiz-builder-item-multiple-choice.php';
require $dir . '/gap-fill/class-fw-option-type-quiz-builder-item-gap-fill.php';