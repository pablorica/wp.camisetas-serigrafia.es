<?php
/**
 * Landing blocks (ACF PRO): one block per section of the CPT Easy page.
 *
 * The design (markup + CSS + JS) lives in the plugin, so editors only change texts,
 * images and lists in the block sidebar and can't break the layout.
 *
 *   blocks/<name>/block.json  → block registration (ACF block, rendered by render.php)
 *   blocks/<name>/render.php  → markup
 *   self::schema()            → ACF fields of each block (registered in PHP, versioned in git)
 *   includes/landing-defaults.php → current texts, used by `wp cpt-easy landing <page-id>`
 *
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

class CPT_Easy_Blocks {

	const NS       = 'cpt-easy';
	const CATEGORY = 'cpt-easy';
	const BLOCKS   = array( 'hero', 'precios', 'opiniones', 'pedido', 'categorias', 'faq' );

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_assets' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
		add_action( 'acf/include_fields', array( __CLASS__, 'register_fields' ) );
		add_filter( 'block_categories_all', array( __CLASS__, 'block_category' ) );
		add_action( 'wp_head', array( __CLASS__, 'preload_fonts' ), 1 );
	}

	public static function register_assets(): void {
		$css = 'assets/landing.css';
		$js  = 'assets/landing.js';
		wp_register_style( 'cpt-easy-landing', CPT_EASY_URL . $css, array(), (string) filemtime( CPT_EASY_PATH . $css ) );
		wp_register_script(
			'cpt-easy-landing',
			CPT_EASY_URL . $js,
			array(),
			(string) filemtime( CPT_EASY_PATH . $js ),
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	public static function register_blocks(): void {
		if ( ! function_exists( 'acf_register_block_type' ) ) {
			return; // ACF PRO not active.
		}
		foreach ( self::BLOCKS as $name ) {
			register_block_type( CPT_EASY_PATH . 'blocks/' . $name );
		}
	}

	public static function block_category( $categories ) {
		array_unshift(
			$categories,
			array(
				'slug'  => self::CATEGORY,
				'title' => __( 'CPT Easy', 'cpt-easy' ),
				'icon'  => null,
			)
		);
		return $categories;
	}

	/** The webfonts are used above the fold: preload them on pages that use the hero block. */
	public static function preload_fonts(): void {
		if ( ! is_singular() || ! has_block( self::NS . '/hero', get_queried_object_id() ) ) {
			return;
		}
		foreach ( array( 'cpt-namaku.woff', 'cpt-dejavu-sans-book.woff2', 'cpt-dejavu-sans-bold.woff2' ) as $font ) {
			$type = str_ends_with( $font, 'woff2' ) ? 'font/woff2' : 'font/woff';
			printf(
				'<link rel="preload" href="%s" as="font" type="%s" crossorigin>' . "\n",
				esc_url( content_url( 'uploads/fonts/' . $font ) ),
				esc_attr( $type )
			);
		}
	}

	/* ---------------------------------------------------------------------
	 * Fields
	 * ------------------------------------------------------------------ */

	/**
	 * Fields per block: [ name, type, label, extra args ]. Repeaters list their sub fields in 'sub'.
	 *
	 * @return array<string, array>
	 */
	public static function schema(): array {
		$text     = static fn( $name, $label, $args = array() ) => array( $name, 'text', $label, $args );
		$textarea = static fn( $name, $label, $rows = 2, $args = array() ) => array( $name, 'textarea', $label, $args + array( 'rows' => $rows, 'new_lines' => '' ) );

		$cards = array();
		foreach ( CPT_Easy_Pricing::QUALITIES as $q ) {
			$k       = strtolower( $q );
			$cards[] = array( "{$k}_tab", 'tab', $q, array() );
			$cards[] = $textarea( "{$k}_texto", __( 'Descripción', 'cpt-easy' ) );
			$cards[] = $text( "{$k}_etiqueta", __( 'Etiqueta (opcional)', 'cpt-easy' ), array( 'instructions' => __( 'Ej.: «Más vendido». Vacío = sin etiqueta.', 'cpt-easy' ) ) );
			$cards[] = $text( "{$k}_boton", __( 'Texto del botón', 'cpt-easy' ) );
		}

		return array(
			'hero'       => array(
				$text( 'titulo', __( 'Título', 'cpt-easy' ), array( 'instructions' => __( 'Usa | para partir la línea.', 'cpt-easy' ) ) ),
				$text( 'destacado', __( 'Texto destacado del título', 'cpt-easy' ), array( 'instructions' => __( 'Va al final del título, subrayado en amarillo.', 'cpt-easy' ) ) ),
				array( 'imagen', 'image', __( 'Imagen', 'cpt-easy' ), array( 'instructions' => __( 'Imagen vertical a la derecha (PNG/WebP con fondo transparente).', 'cpt-easy' ) ) ),
				$text( 'banda', __( 'Franja', 'cpt-easy' ) ),
				array(
					'pasos',
					'repeater',
					__( 'Pasos', 'cpt-easy' ),
					array(
						'max'    => 4,
						'button' => __( 'Añadir paso', 'cpt-easy' ),
						'sub'    => array( $text( 'titulo', __( 'Título', 'cpt-easy' ) ), $textarea( 'texto', __( 'Texto', 'cpt-easy' ) ) ),
					),
				),
				$text( 'incluido_titulo', __( 'Título «todo incluido»', 'cpt-easy' ) ),
				$text( 'incluido_texto', __( 'Texto «todo incluido»', 'cpt-easy' ) ),
				array(
					'ventajas',
					'repeater',
					__( 'Ventajas (etiquetas amarillas)', 'cpt-easy' ),
					array(
						'button' => __( 'Añadir ventaja', 'cpt-easy' ),
						'sub'    => array( $text( 'titulo', __( 'Título', 'cpt-easy' ) ), $text( 'texto', __( 'Texto', 'cpt-easy' ) ) ),
					),
				),
			),
			'precios'    => array_merge(
				array(
					array( 'general_tab', 'tab', __( 'General', 'cpt-easy' ), array() ),
					$text( 'titulo', __( 'Título', 'cpt-easy' ) ),
					$textarea( 'subtitulo', __( 'Subtítulo', 'cpt-easy' ) ),
					$text( 'nota', __( 'Nota bajo las tarjetas', 'cpt-easy' ), array( 'instructions' => __( 'Se añade solo «Pedidos desde N unidades.» con el mínimo del producto.', 'cpt-easy' ) ) ),
					array( 'precios_info', 'message', __( 'Precios', 'cpt-easy' ), array( 'message' => __( 'Los precios de las tarjetas salen del producto CPT Easy (Productos → CPT Easy → Variaciones → «precios por cantidad»).', 'cpt-easy' ) ) ),
				),
				$cards
			),
			'opiniones'  => array(
				$text( 'titulo', __( 'Título', 'cpt-easy' ) ),
				$text( 'nota', __( 'Nota media en Google', 'cpt-easy' ), array( 'instructions' => __( 'Ej.: 5,0', 'cpt-easy' ) ) ),
				array( 'total', 'number', __( 'Número de reseñas en Google', 'cpt-easy' ), array( 'min' => 0 ) ),
				array( 'enlace', 'url', __( 'Enlace a las reseñas de Google', 'cpt-easy' ), array() ),
				array(
					'resenas',
					'repeater',
					__( 'Reseñas', 'cpt-easy' ),
					array(
						'button'    => __( 'Añadir reseña', 'cpt-easy' ),
						'collapsed' => 'autor',
						'sub'       => array(
							$textarea( 'texto', __( 'Texto', 'cpt-easy' ), 4 ),
							$text( 'autor', __( 'Autor', 'cpt-easy' ) ),
							$text( 'fecha', __( 'Fecha', 'cpt-easy' ), array( 'instructions' => __( 'Ej.: 14-09-2022', 'cpt-easy' ) ) ),
							array( 'estrellas', 'number', __( 'Estrellas', 'cpt-easy' ), array( 'min' => 1, 'max' => 5, 'default_value' => 5 ) ),
						),
					),
				),
			),
			'pedido'     => array(
				$text( 'titulo', __( 'Título', 'cpt-easy' ) ),
				$textarea( 'subtitulo', __( 'Subtítulo', 'cpt-easy' ) ),
				$text( 'caja_titulo', __( 'Título del formulario', 'cpt-easy' ) ),
				$textarea( 'info', __( 'Texto informativo bajo el formulario', 'cpt-easy' ), 5 ),
				array( 'pedido_info', 'message', __( 'Formulario', 'cpt-easy' ), array( 'message' => __( 'El formulario lo genera el plugin CPT Easy: crea el pedido y lleva al cliente a la página de pago.', 'cpt-easy' ) ) ),
			),
			'categorias' => array(
				$text( 'titulo', __( 'Título', 'cpt-easy' ) ),
				$textarea( 'subtitulo', __( 'Subtítulo', 'cpt-easy' ) ),
				array( 'mostrar_total', 'true_false', __( 'Mostrar el número de productos', 'cpt-easy' ), array( 'ui' => 1, 'default_value' => 1 ) ),
				array(
					'categorias',
					'repeater',
					__( 'Categorías', 'cpt-easy' ),
					array(
						'button' => __( 'Añadir categoría', 'cpt-easy' ),
						'layout' => 'block',
						'sub'    => array(
							array( 'categoria', 'taxonomy', __( 'Categoría', 'cpt-easy' ), array( 'taxonomy' => 'product_cat', 'field_type' => 'select', 'return_format' => 'id', 'add_term' => 0, 'save_terms' => 0, 'load_terms' => 0 ) ),
							array( 'imagen', 'image', __( 'Imagen', 'cpt-easy' ), array( 'instructions' => __( 'Cuadrada, 400 × 400.', 'cpt-easy' ) ) ),
							$text( 'texto', __( 'Texto (opcional)', 'cpt-easy' ), array( 'instructions' => __( 'Vacío = nombre de la categoría.', 'cpt-easy' ) ) ),
						),
					),
				),
			),
			'faq'        => array(
				$text( 'titulo', __( 'Título', 'cpt-easy' ) ),
				array(
					'preguntas',
					'repeater',
					__( 'Preguntas', 'cpt-easy' ),
					array(
						'button'    => __( 'Añadir pregunta', 'cpt-easy' ),
						'collapsed' => 'pregunta',
						'sub'       => array( $text( 'pregunta', __( 'Pregunta', 'cpt-easy' ) ), $textarea( 'respuesta', __( 'Respuesta', 'cpt-easy' ), 4, array( 'new_lines' => 'wpautop' ) ) ),
					),
				),
			),
		);
	}

	public static function field_key( string $block, string $name, string $parent = '' ): string {
		return 'field_cpte_' . $block . '_' . ( $parent ? $parent . '_' : '' ) . $name;
	}

	private static function acf_field( string $block, array $def, string $parent = '' ): array {
		[ $name, $type, $label, $args ] = $def + array( 3 => array() );

		$field = array(
			'key'   => self::field_key( $block, $name, $parent ),
			'name'  => $name,
			'label' => $label,
			'type'  => $type,
		);
		foreach ( array( 'instructions', 'rows', 'new_lines', 'min', 'max', 'default_value', 'message', 'taxonomy', 'field_type', 'return_format', 'add_term', 'save_terms', 'load_terms', 'ui', 'layout' ) as $opt ) {
			if ( array_key_exists( $opt, $args ) ) {
				$field[ $opt ] = $args[ $opt ];
			}
		}
		if ( 'image' === $type ) {
			$field += array(
				'return_format' => 'id',
				'preview_size'  => 'medium',
				'library'       => 'all',
			);
		}
		if ( 'tab' === $type ) {
			$field['placement'] = 'top';
		}
		if ( 'repeater' === $type ) {
			$field['layout']       = $args['layout'] ?? 'row';
			$field['button_label'] = $args['button'] ?? '';
			$field['min']          = 0;
			$field['max']          = $args['max'] ?? 0;
			if ( ! empty( $args['collapsed'] ) ) {
				$field['collapsed'] = self::field_key( $block, $args['collapsed'], $name );
			}
			$field['sub_fields'] = array_map( fn( $sub ) => self::acf_field( $block, $sub, $name ), $args['sub'] );
		}
		return $field;
	}

	public static function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		foreach ( self::schema() as $block => $fields ) {
			acf_add_local_field_group(
				array(
					'key'      => 'group_cpte_' . $block,
					'title'    => 'CPT Easy · ' . ucfirst( $block ),
					'fields'   => array_map( fn( $def ) => self::acf_field( $block, $def ), $fields ),
					'location' => array(
						array(
							array(
								'param'    => 'block',
								'operator' => '==',
								'value'    => self::NS . '/' . $block,
							),
						),
					),
				)
			);
		}
	}

	/* ---------------------------------------------------------------------
	 * Page builder (used by `wp cpt-easy landing`)
	 * ------------------------------------------------------------------ */

	/**
	 * Turns natural data ( 'pasos' => [ [ 'titulo' => …, 'texto' => … ], … ] ) into the flat
	 * ACF block "data" attribute ( 'pasos' => 2, 'pasos_0_titulo' => …, '_pasos_0_titulo' => field key ).
	 */
	public static function flatten( string $block, array $values ): array {
		$data = array();
		foreach ( self::schema()[ $block ] as $def ) {
			[ $name, $type ] = $def;
			if ( in_array( $type, array( 'tab', 'message' ), true ) || ! array_key_exists( $name, $values ) ) {
				continue;
			}
			if ( 'repeater' === $type ) {
				$rows                 = array_values( (array) $values[ $name ] );
				$data[ $name ]        = count( $rows );
				$data[ '_' . $name ]  = self::field_key( $block, $name );
				foreach ( $rows as $i => $row ) {
					foreach ( $def[3]['sub'] as $sub ) {
						$sub_name = $sub[0];
						if ( array_key_exists( $sub_name, $row ) ) {
							$data[ "{$name}_{$i}_{$sub_name}" ]   = $row[ $sub_name ];
							$data[ "_{$name}_{$i}_{$sub_name}" ] = self::field_key( $block, $sub_name, $name );
						}
					}
				}
				continue;
			}
			$data[ $name ]       = $values[ $name ];
			$data[ '_' . $name ] = self::field_key( $block, $name );
		}
		return $data;
	}

	/** Serialized block markup for the whole landing. */
	public static function page_content( array $sections ): string {
		$out = array();
		foreach ( $sections as $block => $values ) {
			$name  = self::NS . '/' . $block;
			$out[] = serialize_block(
				array(
					'blockName'    => $name,
					'attrs'        => array(
						'name' => $name,
						'data' => self::flatten( $block, $values ),
						'mode' => 'preview',
					),
					'innerBlocks'  => array(),
					'innerHTML'    => '',
					'innerContent' => array(),
				)
			);
		}
		return implode( "\n\n", $out ) . "\n";
	}
}
