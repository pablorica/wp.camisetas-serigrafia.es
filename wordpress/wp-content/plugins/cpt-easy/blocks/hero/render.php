<?php
/**
 * CPT Easy · Hero.
 *
 * @var array $block ACF block.
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

$titulo    = (string) get_field( 'titulo' );
$destacado = (string) get_field( 'destacado' );
$imagen    = (int) get_field( 'imagen' );
$banda     = (string) get_field( 'banda' );
$pasos     = (array) get_field( 'pasos' );
$ventajas  = (array) get_field( 'ventajas' );
?>
<section class="cpt-s cpt-hero" id="inicio" aria-labelledby="cpt-h1">
	<?php
	if ( $imagen ) {
		echo wp_get_attachment_image(
			$imagen,
			'full',
			false,
			array(
				'class'         => 'cpt-dog',
				'fetchpriority' => 'high',
				'decoding'      => 'async',
				'loading'       => false,
			)
		);
	}
	?>
	<div class="cpt-in">
		<h1 class="cpt-h1" id="cpt-h1">
			<?php echo implode( '<br> ', array_map( 'esc_html', array_map( 'trim', explode( '|', $titulo ) ) ) ); ?>
			<?php if ( $destacado ) : ?>
				<mark><?php echo esc_html( $destacado ); ?></mark>
			<?php endif; ?>
		</h1>
	</div>

	<?php if ( $banda ) : ?>
		<div class="cpt-band"><div class="cpt-in"><h2><?php echo esc_html( $banda ); ?></h2></div></div>
	<?php endif; ?>

	<div class="cpt-in">
		<?php if ( $pasos ) : ?>
			<ol class="cpt-steps">
				<?php foreach ( $pasos as $i => $paso ) : ?>
					<li>
						<span class="cpt-num" aria-hidden="true"><?php echo (int) $i + 1; ?></span>
						<h3><?php echo esc_html( $paso['titulo'] ?? '' ); ?></h3>
						<p><?php echo esc_html( $paso['texto'] ?? '' ); ?></p>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>

		<div class="cpt-incl">
			<?php if ( get_field( 'incluido_titulo' ) ) : ?>
				<h2><?php echo esc_html( get_field( 'incluido_titulo' ) ); ?></h2>
			<?php endif; ?>
			<?php if ( get_field( 'incluido_texto' ) ) : ?>
				<p><?php echo esc_html( get_field( 'incluido_texto' ) ); ?></p>
			<?php endif; ?>
			<?php if ( $ventajas ) : ?>
				<ul class="cpt-chips">
					<?php foreach ( $ventajas as $v ) : ?>
						<li><h3><?php echo esc_html( $v['titulo'] ?? '' ); ?></h3><p><?php echo esc_html( $v['texto'] ?? '' ); ?></p></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</div>
</section>
