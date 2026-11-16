<?php
/**
 * CPT Easy · Categorías (shop categories grid).
 *
 * @var array $block ACF block.
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

$mostrar_total = (bool) get_field( 'mostrar_total' );
$items         = array();
foreach ( (array) get_field( 'categorias' ) as $row ) {
	$term = ! empty( $row['categoria'] ) ? get_term( (int) $row['categoria'], 'product_cat' ) : null;
	if ( ! $term || is_wp_error( $term ) ) {
		continue;
	}
	$label = trim( (string) ( $row['texto'] ?? '' ) ) ?: $term->name;
	if ( $mostrar_total ) {
		$label .= ' (' . (int) $term->count . ')';
	}
	$items[] = array(
		'url'   => get_term_link( $term ),
		'img'   => (int) ( $row['imagen'] ?? 0 ),
		'label' => $label,
	);
}
?>
<section class="cpt-s cpt-cats" id="catalogo" aria-labelledby="cpt-cats-t">
	<div class="cpt-in">
		<h2 class="cpt-h2" id="cpt-cats-t"><?php echo esc_html( get_field( 'titulo' ) ); ?></h2>
		<?php if ( get_field( 'subtitulo' ) ) : ?>
			<p class="cpt-sub"><?php echo esc_html( get_field( 'subtitulo' ) ); ?></p>
		<?php endif; ?>
		<?php if ( $items ) : ?>
			<ul class="cpt-grid">
				<?php foreach ( $items as $item ) : ?>
					<li>
						<a href="<?php echo esc_url( $item['url'] ); ?>">
							<?php
							if ( $item['img'] ) {
								echo wp_get_attachment_image( $item['img'], 'medium_large', false, array( 'decoding' => 'async' ) );
							}
							?>
							<span><?php echo esc_html( $item['label'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
