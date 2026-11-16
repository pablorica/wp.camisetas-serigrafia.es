<?php
/**
 * Contents of the CPT Easy landing (from the HTML prototype, 2026-11-16).
 * Used by `wp cpt-easy landing <page-id>` to build the page with blocks. After that,
 * the texts live in the page and editors change them in the block sidebar.
 *
 * Images are upload paths and categories are product_cat slugs: the command turns them into IDs.
 *
 * @package cpt-easy
 */

defined( 'ABSPATH' ) || exit;

return array(
	'hero'       => array(
		'titulo'          => 'Pide tus camisetas personalizadas | con nuestro sistema',
		'destacado'       => 'CPT EASY',
		'imagen'          => '/wp-content/uploads/2026/10/cpt-bulldog-camiseta-personalizada-hero.webp',
		'banda'           => 'Haz tu pedido en solo 3 minutos en 3 sencillos pasos',
		'pasos'           => array(
			array(
				'titulo' => 'Elige tu calidad',
				'texto'  => 'Basic, Medium o Premium. Tú decides cuánto quieres invertir.',
			),
			array(
				'titulo' => 'Datos de tu pedido',
				'texto'  => 'Rellena el formulario de pedido y dale al botón “Enviar mi pedido”.',
			),
			array(
				'titulo' => 'Confirma el pago y elige tu modelo',
				'texto'  => 'En cuanto recibamos tu pago te enviamos las fichas de los modelos más vendidos de tu grupo para que elijas el que más te guste.',
			),
		),
		'incluido_titulo' => 'Todo incluido, sin letra pequeña',
		'incluido_texto'  => 'Lo que otras webs cobran aparte, aquí ya está en el precio.',
		'ventajas'        => array(
			array(
				'titulo' => 'DTF todo color',
				'texto'  => 'Fotos y degradados, sin límite de colores de tinta.',
			),
			array(
				'titulo' => 'Hasta A4',
				'texto'  => 'Tamaño de impresión incluido.',
			),
			array(
				'titulo' => 'Todos los colores',
				'texto'  => 'De camiseta, sin recargo.',
			),
			array(
				'titulo' => 'Nos ocupamos',
				'texto'  => 'Te guiamos en la elección del modelo.',
			),
			array(
				'titulo' => 'Entrega y pedido',
				'texto'  => 'Plazo de entrega 15 días. Pedido mínimo 10 uds.',
			),
		),
	),

	'precios'    => array(
		'titulo'          => 'Tres calidades. Un precio claro.',
		'subtitulo'       => 'Cuantas más camisetas, menos pagas por cada una. Impresión incluida en todos los precios.',
		'nota'            => 'Precios por camiseta, IVA no incluido.',
		'basic_texto'     => 'Para pocas puestas, despedidas, manifestaciones y festivales.',
		'basic_etiqueta'  => '',
		'basic_boton'     => 'Elegir Basic',
		'medium_texto'    => 'Excelente calidad-precio. El grupo más vendido.',
		'medium_etiqueta' => 'Más vendido',
		'medium_boton'    => 'Elegir Medium',
		'premium_texto'   => 'Gramajes altos, algodón orgánico y peinado. La gama superior.',
		'premium_etiqueta' => '',
		'premium_boton'   => 'Elegir Premium',
	),

	'opiniones'  => array(
		'titulo'  => 'Mejor que hablen nuestros clientes',
		'nota'    => '5,0',
		'total'   => 13,
		'enlace'  => 'https://search.google.com/local/reviews?placeid=ChIJVYx__b5-YQ0ROg2pwXKntn4',
		// Reseñas reales de la ficha de Google (Camisetas Para Todos, Banyeres de Mariola), copiadas el 29-10-2026.
		'resenas' => array(
			array( 'autor' => 'Javier Puig Miró', 'fecha' => '14-09-2022', 'estrellas' => 5, 'texto' => 'Hemos hecho un pedido de 80 camisetas, de tallas, colores y modelos diferentes. Todas han quedado perfectas. En una había un pequeño fallo y Sergio nos aviso y la repitió. Era un detalle pequeño en la impresión que no nos hubiésemos dado ni cuenta. Se agradece encontrar esta honestidad, la verdad. ¡¡Repetiremos seguro!!' ),
			array( 'autor' => 'Jose Luis Miralles', 'fecha' => '12-05-2022', 'estrellas' => 5, 'texto' => 'Muy buen trato, rapideza y buena calidad y precio!' ),
			array( 'autor' => 'Virgimf Muñoz', 'fecha' => '09-11-2021', 'estrellas' => 5, 'texto' => 'Las camisetas están perfectamente adaptadas al diseño que mandamos. Totalmente recomendable 😉. Nos han mandado el pedido super rápido y con calidad.' ),
			array( 'autor' => 'C. García', 'fecha' => '19-10-2021', 'estrellas' => 5, 'texto' => 'Muy profesionales. Sergio muy amable explicando todo. Gracias! Me han transmitido mucha confianza, gracias!' ),
			array( 'autor' => 'Noelia Olid Heredero', 'fecha' => '16-06-2021', 'estrellas' => 5, 'texto' => 'Todo perfecto. La serigrafía, preciosa. Y el algodón de las camisetas de gran calidad. Sin duda 100% recomendable!' ),
			array( 'autor' => 'Pachi Sanchez Villar', 'fecha' => '12-06-2021', 'estrellas' => 5, 'texto' => 'Hemos quedado muy contentas con su trabajo! Queríamos personalizar unas camisetas, pero no lo teníamos muy claro y les pedimos ayuda. Sergio nos atendió estupendamente. Una vez tuvimos las cosas claras, hicimos el pedido desde su página web que, por cierto, funciona muy bien. Cuando recibieron nuestro “diseño”, lo adaptaron y nos mandaron un par de opciones para elegir la que más nos gustara. Así lo hicimos y, a pesar de lo que indican en su página web, del tiempo máximo de espera, como nos urgía un poco, se comprometieron a tenerlo en plazo y… así fue! En menos de dos semanas desde el inicio del proceso, tuvimos las camisetas en casa y con un resultado (tanto de diseño como económico) muy bueno.' ),
			array( 'autor' => 'Paco Ruiz Romero', 'fecha' => '06-05-2021', 'estrellas' => 5, 'texto' => 'Buenisima la experiencia con ellos para hacer unas camisetas personalizadas. Son muy amables, el trato fue genial y todo fue muy rapido.' ),
			array( 'autor' => 'Yaiza Pintado Ballester', 'fecha' => '05-12-2020', 'estrellas' => 5, 'texto' => 'Servicio excepcional, el pedido ha llegado justo a tiempo y la calidad de las camisetas y el serigrafiado es brutal. Amabilidad en el servicio y revisión de archivos pre-impresión (no en todos los sitios web te ofrecen esta posibilidad). ¡Sin duda repetiré con ellos!' ),
			array( 'autor' => 'Diego Redondo', 'fecha' => '16-10-2020', 'estrellas' => 5, 'texto' => 'Todo perfecto muy contento empresa muy seria. La recomiendo' ),
			array( 'autor' => 'Mariuki', 'fecha' => '15-10-2020', 'estrellas' => 5, 'texto' => 'todo correcto y me enviaron dos prendas más de las que pedí ✌✨' ),
		),
	),

	'pedido'     => array(
		'titulo'      => 'Haz tu pedido ahora',
		'subtitulo'   => 'Rellena esto y listo. Al enviarlo verás tu pedido y cómo pagarlo.',
		'caja_titulo' => 'Formulario de pedido',
		'info'        => 'Al enviar el formulario verás el resumen de tu pedido y podrás pagarlo por transferencia bancaria (pronto sumaremos más métodos de pago). También te lo enviamos por email. En cuanto recibamos el pago, te enviaremos las fichas de los modelos más vendidos del grupo elegido y, si lo necesitas, te asesoramos en la elección según tus necesidades. Plazo de entrega 15 días a partir de cuando realices el pago.',
	),

	'categorias' => array(
		'titulo'        => 'Explora por categoría',
		'subtitulo'     => 'Si prefieres el sistema tradicional, aquí tienes nuestras categorías con todos nuestros productos.',
		'mostrar_total' => 1,
		'categorias'    => array(
			array( 'categoria' => 'camisetas', 'texto' => 'Camisetas personalizadas', 'imagen' => '/wp-content/uploads/2026/10/home-cat-camisetas-personalizadas.webp' ),
			array( 'categoria' => 'polos', 'texto' => 'Polos corporativos', 'imagen' => '/wp-content/uploads/2026/10/home-cat-polos-corporativos.webp' ),
			array( 'categoria' => 'sudaderas', 'texto' => 'Sudaderas con logo', 'imagen' => '/wp-content/uploads/2026/10/home-cat-sudaderas-con-logo.webp' ),
			array( 'categoria' => 'deporte', 'texto' => 'Ropa de deporte personalizada', 'imagen' => '/wp-content/uploads/2026/10/home-cat-ropa-deporte-personalizada.webp' ),
			array( 'categoria' => 'laboral', 'texto' => 'Ropa laboral con logo', 'imagen' => '/wp-content/uploads/2026/10/home-cat-ropa-laboral-con-logo.webp' ),
			array( 'categoria' => 'invierno', 'texto' => 'Ropa laboral de invierno', 'imagen' => '/wp-content/uploads/2026/10/home-cat-ropa-laboral-invierno.webp' ),
			array( 'categoria' => 'accesorios-complementos', 'texto' => 'Bolsas y mochilas corporativas', 'imagen' => '/wp-content/uploads/2026/10/home-cat-bolsas-mochilas-corporativas.webp' ),
			array( 'categoria' => 'calzado', 'texto' => 'Calzado laboral', 'imagen' => '/wp-content/uploads/2026/10/home-cat-calzado-laboral.webp' ),
		),
	),

	'faq'        => array(
		'titulo'    => 'Dudas frecuentes',
		'preguntas' => array(
			array( 'pregunta' => '¿Qué diferencia hay entre Basic, Medium y Premium?', 'respuesta' => 'La calidad de la camiseta. Basic es la opción económica, pensada para pocas puestas: despedidas, manifestaciones o festivales. Medium es el equilibrio entre calidad y precio, y la que más se pide. Premium son prendas de gramaje alto, de algodón orgánico y peinado. La impresión es la misma en las tres.' ),
			array( 'pregunta' => '¿Tengo que elegir técnica o tamaño de impresión?', 'respuesta' => 'No. Imprimimos en DTF a todo color, así que puedes usar fotos y degradados sin pagar más por colores de tinta. El tamaño de impresión llega hasta A4 y ya está incluido. Solo tienes que decidir si quieres impresión en una cara o en dos.' ),
			array( 'pregunta' => '¿Cómo se paga el pedido?', 'respuesta' => 'Por transferencia bancaria. Al enviar el formulario verás los datos para pagar y también te los enviamos por email. Pronto sumaremos más métodos de pago.' ),
			array( 'pregunta' => '¿Cómo elijo el modelo exacto de camiseta?', 'respuesta' => 'En cuanto recibimos el pago te mandamos las fichas de los modelos más vendidos del grupo que has elegido (Basic, Medium o Premium). Eliges el que más te guste y, si tienes dudas, te ayudamos a decidir según el uso que le vayas a dar.' ),
			array( 'pregunta' => '¿Los precios llevan IVA?', 'respuesta' => 'No, los precios de la tabla son sin IVA. En el formulario de pedido verás el total con el IVA (21 %) incluido antes de enviarlo.' ),
			array( 'pregunta' => '¿Qué diferencia hay entre el sistema de pedido rápido CPT EASY y el sistema de pedido tradicional?', 'respuesta' => 'CPT EASY es para pedidos de 10 a 100 camisetas con precio cerrado: eliges calidad, cantidad y si imprimes una o dos caras, y la impresión DTF a todo color hasta A4 ya va incluida. En el catálogo tradicional tienes todos nuestros productos (polos, sudaderas, ropa laboral, bolsas…) y puedes elegir el modelo exacto desde el principio.' ),
		),
	),
);
