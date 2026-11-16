<?php
/**
 * CPT Easy · Precios. The rows come from the CPT Easy product variations.
 *
 * @var array $block ACF block.
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

$mods = array(
	'Basic'   => 'b',
	'Medium'  => 'm',
	'Premium' => 'p',
);
$nota = trim( (string) get_field( 'nota' ) );
/* translators: %d: minimum quantity */
$nota = trim( $nota . ' ' . sprintf( __( 'Pedidos desde %d unidades.', 'cpt-easy' ), CPT_Easy_Pricing::min_qty() ) );
?>
<section class="cpt-s cpt-precios" id="precios" aria-labelledby="cpt-precios-t">
	<div class="cpt-in">
		<h2 class="cpt-h2" id="cpt-precios-t"><?php echo esc_html( get_field( 'titulo' ) ); ?></h2>
		<?php if ( get_field( 'subtitulo' ) ) : ?>
			<p class="cpt-sub"><?php echo esc_html( get_field( 'subtitulo' ) ); ?></p>
		<?php endif; ?>

		<div class="cpt-toggle" role="group" aria-label="<?php esc_attr_e( 'Caras impresas', 'cpt-easy' ); ?>">
			<button type="button" aria-pressed="true" data-caras="1"><?php esc_html_e( 'Impresión 1 cara', 'cpt-easy' ); ?></button>
			<button type="button" aria-pressed="false" data-caras="2"><?php esc_html_e( 'Impresión 2 caras', 'cpt-easy' ); ?></button>
		</div>

		<div class="cpt-cards">
			<?php
			foreach ( $mods as $quality => $mod ) :
				$k     = strtolower( $quality );
				$tag   = (string) get_field( "{$k}_etiqueta" );
				$boton = (string) get_field( "{$k}_boton" ) ?: sprintf( /* translators: %s: quality */ __( 'Elegir %s', 'cpt-easy' ), $quality );
				?>
				<article class="cpt-card cpt-card--<?php echo esc_attr( $mod ); ?>">
					<?php if ( $tag ) : ?>
						<span class="cpt-tag"><?php echo esc_html( $tag ); ?></span>
					<?php endif; ?>
					<h3><?php echo esc_html( $quality ); ?></h3>
					<p><?php echo esc_html( get_field( "{$k}_texto" ) ); ?></p>
					<?php echo CPT_Easy_Landing::price_rows( array( 'calidad' => $quality ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
					<a class="cpt-btn" href="#pedido" data-cpt-calidad="<?php echo esc_attr( $quality ); ?>"><?php echo esc_html( $boton ); ?></a>
				</article>
			<?php endforeach; ?>
		</div>

		<p class="cpt-note"><?php echo esc_html( $nota ); ?></p>
	</div>
</section>
