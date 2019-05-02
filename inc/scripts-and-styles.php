<?php

/* Remove style.css from wp_head so we can make overwrites
--------------------------------------------------------------------------------------*/

remove_action('wp_head', 'wp_print_styles');


/* Enqueue styles
--------------------------------------------------------------------------------------*/
add_action( 'wp_enqueue_scripts', 'caweb_styles', 99 );

function caweb_styles() {

	wp_dequeue_style( 'et-builder-modules-style' );

	$general_settings = get_field('general_settings', 'option');
	$color_scheme = $general_settings['color_scheme'];

	wp_enqueue_style( 'global-styles', get_template_directory_uri() . '/styles/cagov.core.min.css', [], '5.5.0', 'all' );

	if ($color_scheme) {
		wp_enqueue_style( $color_scheme . '-theme', get_template_directory_uri() . '/color-schemes/colorscheme-' . $color_scheme . '.min.css', [], '5.5.0', 'all' );
	} else {
		wp_enqueue_style( 'oceanside-theme', get_template_directory_uri() . '/color-schemes/colorscheme-oceanside.min.css', [], '5.5.0', 'all' );
	}

	if (is_page_template('page-search.php')) {
		//wp_enqueue_style( 'google-search', 'http://www.google.com/cse/style/look/default.css', [], '1.0.0', 'all' );
	}

	wp_enqueue_style( 'caweb-standard', get_stylesheet_uri() );

	// custom uploaded stylesheets
	if( have_rows('upload_css', 'options') ):
		while( have_rows('upload_css', 'option') ): the_row();
			wp_enqueue_style( 'custom-stylesheet-' . get_row_index(), get_sub_field('stylesheets'), [], '1.0.0', 'all' );
		endwhile;
	endif;

}


/* Enqueue scripts
--------------------------------------------------------------------------------------*/

add_action( 'wp_enqueue_scripts', 'caweb_scripts' );

function caweb_scripts() {

	$utility_settings = get_field('utility_header', 'option');

	$geolocator = isset($utility_settings['enable_geo_locator']) ? $utility_settings['enable_geo_locator'] : false;

	if ($geolocator) {
		wp_enqueue_script( 'geolocator', get_template_directory_uri() . '/js/libs/geolocator.js', [], '1.0.0', true );
	}

	// wp_enqueue_script( 'custom-js', get_template_directory_uri() . '/js/custom.js', [], '1.0.0', true );
	wp_enqueue_script( 'custom-js', get_template_directory_uri() . '/app.js', ['jquery'], '1.0.0', true );

	if( have_rows('custom_uploads', 'options') ):
		while( have_rows('custom_uploads', 'option') ): the_row();
			wp_enqueue_script( 'custom-js-' . get_row_index(), get_sub_field('javascript_files'), ['jquery'], '1.0.0', true );
		endwhile;
	endif;

}
