<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var int $id - Course Id
 * @var string $title - Input name
 * @var string $status - Course status
 * @var string $author - Author ID
 * @var string $name - Input name
 */

?>

<div class="course-item">
	<input type="checkbox" name="<?php echo $name ?>" id="<?php echo $name . '-' . $id ?>" value="<?php echo $id?>"/>
	<label for="<?php echo $name . '-' . $id ?>"><?php echo get_the_title( $id )?> - <?php echo $status ?></label>
</div>