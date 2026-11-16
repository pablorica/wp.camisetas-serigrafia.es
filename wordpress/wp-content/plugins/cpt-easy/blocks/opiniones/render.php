<?php
/**
 * CPT Easy · Opiniones (Google reviews carousel).
 *
 * @var array $block ACF block.
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

$resenas = array_values( array_filter( (array) get_field( 'resenas' ), static fn( $r ) => ! empty( $r['texto'] ) ) );
$enlace  = (string) get_field( 'enlace' );
$nota    = (string) get_field( 'nota' );
$total   = (int) get_field( 'total' );
$n       = count( $resenas );

$stars    = static fn( int $s ) => str_repeat( '★', max( 1, min( 5, $s ) ) );
$initials = static function ( string $name ): string {
	$parts = preg_split( '/\s+/', trim( $name ) );
	$ini   = mb_substr( $parts[0] ?? '', 0, 1 ) . mb_substr( $parts[1] ?? '', 0, 1 );
	return mb_strtoupper( $ini );
};
$google = '<svg class="cpt-g" viewBox="0 0 48 48" aria-hidden="true" focusable="false"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.2 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.8 1.2 8 3l5.7-5.7C34 6.1 29.3 4 24 4 13 4 4 13 4 24s9 20 20 20 20-9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="m6.3 14.7 6.6 4.8C14.7 15.1 19 12 24 12c3.1 0 5.8 1.2 8 3l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2c-2 1.5-4.5 2.4-7.2 2.4-5.2 0-9.6-3.3-11.3-7.9l-6.5 5C9.5 39.6 16.2 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.2-4.1 5.6l6.2 5.2C37 39.2 44 34 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>';
?>
<section class="cpt-s cpt-opi" aria-labelledby="cpt-opi-t">
	<div class="cpt-in">
		<h2 class="cpt-h2s" id="cpt-opi-t"><?php echo esc_html( get_field( 'titulo' ) ); ?></h2>

		<?php if ( $enlace && ( $nota || $total ) ) : ?>
			<a class="cpt-gbadge" href="<?php echo esc_url( $enlace ); ?>" target="_blank" rel="noopener">
				<?php echo $google; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG. ?>
				<span>
					<?php if ( $nota ) : ?>
						<b><?php echo esc_html( $nota ); ?></b> <span class="cpt-stars" aria-hidden="true">★★★★★</span>
					<?php endif; ?>
					<?php if ( $total ) : ?>
						<?php /* translators: %d: number of reviews */ echo esc_html( sprintf( _n( '%d reseña en Google', '%d reseñas en Google', $total, 'cpt-easy' ), $total ) ); ?>
					<?php endif; ?>
				</span>
			</a>
		<?php endif; ?>

		<?php if ( $n ) : ?>
			<div class="cpt-slider">
				<div class="cpt-view">
					<div class="cpt-track">
						<?php
						foreach ( $resenas as $i => $r ) :
							$texto  = (string) $r['texto'];
							$autor  = (string) ( $r['autor'] ?? '' );
							$fecha  = (string) ( $r['fecha'] ?? '' );
							$num    = (int) ( $r['estrellas'] ?? 5 );
							$long   = mb_strlen( $texto ) > 220 ? ' cpt-quote--long' : '';
							?>
							<figure class="cpt-slide">
								<blockquote class="cpt-quote<?php echo esc_attr( $long ); ?>">
									<span class="cpt-qm cpt-qm--o" aria-hidden="true">“</span><span class="cpt-stars" role="img" aria-label="<?php /* translators: %d: stars */ echo esc_attr( sprintf( __( '%d de 5 estrellas', 'cpt-easy' ), $num ) ); ?>"><?php echo esc_html( $stars( $num ) ); ?></span><?php echo esc_html( $texto ); ?><span class="cpt-qm cpt-qm--c" aria-hidden="true">”</span>
								</blockquote>
								<figcaption class="cpt-who">
									<span class="cpt-ava" aria-hidden="true"><?php echo esc_html( $initials( $autor ) ); ?></span>
									<span><?php echo esc_html( trim( mb_strtoupper( $autor ) . ( $fecha ? ' | ' . $fecha : '' ) ) ); ?></span>
									<?php echo $google; // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<span class="cpt-sr"><?php esc_html_e( 'Reseña publicada en Google', 'cpt-easy' ); ?></span>
								</figcaption>
							</figure>
						<?php endforeach; ?>
					</div>
				</div>
				<button type="button" class="cpt-arr cpt-arr--prev" aria-label="<?php esc_attr_e( 'Reseña anterior', 'cpt-easy' ); ?>"></button><button type="button" class="cpt-arr cpt-arr--next" aria-label="<?php esc_attr_e( 'Reseña siguiente', 'cpt-easy' ); ?>"></button>
				<div class="cpt-dots" role="tablist" aria-label="<?php esc_attr_e( 'Elegir reseña', 'cpt-easy' ); ?>">
					<?php for ( $i = 0; $i < $n; $i++ ) : ?>
						<button type="button" class="cpt-dot" aria-label="<?php /* translators: 1: index 2: total */ echo esc_attr( sprintf( __( 'Reseña %1$d de %2$d', 'cpt-easy' ), $i + 1, $n ) ); ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?>></button>
					<?php endfor; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $enlace ) : ?>
			<p class="cpt-gnote"><a href="<?php echo esc_url( $enlace ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Ver todas las reseñas en Google', 'cpt-easy' ); ?></a></p>
		<?php endif; ?>
	</div>
</section>
