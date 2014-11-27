<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * @var string $items_html
 */
?>
<div class="quiz-builder-form">
	<?php echo $items_html ?>
	<div style="clear: both"></div>
</div>
<div class="submit">
	<input type="submit" value="<?php _e( 'Submit', 'fw' ) ?>"/>
</div>