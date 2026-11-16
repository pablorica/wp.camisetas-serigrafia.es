<?php
/**
 * CPT Easy · Preguntas frecuentes.
 *
 * @var array $block ACF block.
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

$preguntas = array_filter( (array) get_field( 'preguntas' ), static fn( $p ) => ! empty( $p['pregunta'] ) );
?>
<section class="cpt-s cpt-faq" id="preguntas-frecuentes" aria-labelledby="cpt-faq-t">
	<div class="cpt-in">
		<h2 class="cpt-h2" id="cpt-faq-t"><?php echo esc_html( get_field( 'titulo' ) ); ?></h2>
		<?php if ( $preguntas ) : ?>
			<div class="cpt-faq-list">
				<?php foreach ( $preguntas as $p ) : ?>
					<details>
						<summary><h3><?php echo esc_html( $p['pregunta'] ); ?></h3></summary>
						<?php echo wp_kses_post( $p['respuesta'] ?? '' ); ?>
					</details>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
