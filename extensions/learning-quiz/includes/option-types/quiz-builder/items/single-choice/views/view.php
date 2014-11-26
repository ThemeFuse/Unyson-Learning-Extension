<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}
/**
 * @var array $item
 * @var string $type
 * @var array $attr
 * @var int $max_width
 */

$options = $item['options'];

$choice_options   = $item['options']['wrong-answers'];
$choice_options[] = $item['options']['correct-answer'];
shuffle( $choice_options );
?>
<div class="quiz-item field-radio <?php echo esc_attr( fw_ext_builder_get_item_width( 'quiz-builder',
	$item['width'] . '/frontend_class' ) ) ?>">
	<label><?php echo $item['number'] . ') ' . fw_htmlspecialchars( $item['options']['question'] ) ?></label>

	<div class="inputs">
		<?php $counter = 1;
		foreach ( $choice_options as $option ) : ?>
			<input type="radio" value="<?php echo esc_attr( $option ) ?>"
			       id="<?php echo esc_attr( $attr['id'] ) . $counter ?>"
			       name="<?php echo esc_attr( $attr['name'] ) ?>"/>
			<label
				for="<?php echo esc_attr( $attr['id'] ) . $counter ++ ?>"><?php echo esc_attr( $option ) ?></label>
			<br/>
		<?php endforeach ?>
	</div>
</div>