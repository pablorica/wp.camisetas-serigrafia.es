<?php
/**
 * CPT Easy · Pedido (order form).
 *
 * @var array $block      ACF block.
 * @var bool  $is_preview True in the editor.
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="cpt-s cpt-pedido" id="pedido" aria-labelledby="cpt-pedido-t">
	<div class="cpt-in">
		<h2 class="cpt-h2" id="cpt-pedido-t"><?php echo esc_html( get_field( 'titulo' ) ); ?></h2>
		<?php if ( get_field( 'subtitulo' ) ) : ?>
			<p class="cpt-sub"><?php echo esc_html( get_field( 'subtitulo' ) ); ?></p>
		<?php endif; ?>
		<div class="cpt-formbox">
			<?php if ( get_field( 'caja_titulo' ) ) : ?>
				<h3><?php echo esc_html( get_field( 'caja_titulo' ) ); ?></h3>
			<?php endif; ?>
			<div<?php echo ! empty( $is_preview ) ? ' inert' : ''; ?>>
				<?php echo CPT_Easy_Form::render(); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
			</div>
			<?php if ( get_field( 'info' ) ) : ?>
				<p class="cpt-info"><?php echo esc_html( get_field( 'info' ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
