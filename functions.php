<?php

/* Standard underscores functions
--------------------------------------------------------------------------------------*/

add_action( 'after_setup_theme', 'caweb_standard_setup' );

if ( ! function_exists( 'caweb_standard_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function caweb_standard_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on CAWeb Standard, use a find and replace
		 * to change 'caweb-standard' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'caweb-standard', get_template_directory() . '/languages' );

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support( 'html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		) );

		// Add theme support for selective refresh for widgets.
		add_theme_support( 'customize-selective-refresh-widgets' );
	}
endif;

/* Autoload function files
--------------------------------------------------------------------------------------*/

add_action( 'after_setup_theme', 'caweb_autoload' );

function caweb_autoload() {
	$function_path = pathinfo( __FILE__ );

	foreach ( glob( $function_path['dirname'] . '/inc/*.php' ) as $file ) {
		require_once $file;
	}
}


/* Remove admin bar for non admins
--------------------------------------------------------------------------------------*/

add_action('after_setup_theme', 'remove_admin_bar');

function remove_admin_bar() {
	if (!current_user_can('administrator') && !is_admin()) {
	  show_admin_bar(false);
	}

	show_admin_bar(false);
}


/* Gravity form submit buttons
--------------------------------------------------------------------------------------*/

add_filter("gform_submit_button_2", "form_submit_button", 10, 2);

function form_submit_button($button, $form){
    return "<div class='button-container single-button'><button class='button button-primary' id='gform_submit_button_{$form['id']}'><span>Save</button></div>";
}

add_filter("gform_submit_button_1", "form_submit_button_register", 10, 2);

function form_submit_button_register($button, $form){
    return "<div class='button-container single-button'><button class='button button-primary' id='gform_submit_button_{$form['id']}'><span>Signup</button></div>";
}


/* Gravity form update profile
--------------------------------------------------------------------------------------*/

add_filter( 'gform_pre_submission_filter_2', 'dw_show_confirmation_and_form' );
function dw_show_confirmation_and_form( $form ) {
	$shortcode = '[gravityform id="' . $form['id'] . '" title="false" description="false"]';

	if ( array_key_exists( 'confirmations', $form ) ) {
		foreach ( $form['confirmations'] as $key => $confirmation ) {
			$form['confirmations'][ $key ]['message'] = $shortcode . '<div class="confirmation-message animated fadeOut delay-2s">' . $form['confirmations'][ $key ]['message'] . '</div>';
		}
	}

	return $form;
}

/* Gravity forms login after signup
--------------------------------------------------------------------------------------*/

add_action( 'gform_user_registered', 'vc_gf_registration_autologin',  10, 4 );

function vc_gf_registration_autologin( $user_id, $user_config, $entry, $password ) {
	$user = get_userdata( $user_id );
	$user_login = $user->user_login;
	$user_password = $password;

	wp_signon( array(
	'user_login' => $user_login,
	'user_password' =>  $user_password,
	'remember' => false

	) );
}


/* Custom login
--------------------------------------------------------------------------------------*/

/* Main redirection of the default login page */

function redirect_login_page() {
	$login_page  = home_url('/login/');
	$page_viewed = basename($_SERVER['REQUEST_URI']);

	if($page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
		wp_redirect($login_page);
		exit;
	}
}
add_action('init','redirect_login_page');

/* Where to go if a login failed */
function custom_login_failed() {
	$login_page  = home_url('/login/');
	wp_redirect($login_page . '?login=failed');
	exit;
}
add_action('wp_login_failed', 'custom_login_failed');

/* Where to go if any of the fields were empty */
function verify_user_pass($user, $username, $password) {
	$login_page  = home_url('/login/');
	if($username == "" || $password == "") {
		wp_redirect($login_page . "?login=empty");
		exit;
	}
}
add_filter('authenticate', 'verify_user_pass', 1, 3);

/* What to do on logout */
function logout_redirect() {
	$login_page  = home_url('/login/');
	wp_redirect($login_page . "?login=false");
	exit;
}
add_action('wp_logout','logout_redirect');


/* If user is subscriber role, send them to the dashboard on login
--------------------------------------------------------------------------------------*/

function my_login_redirect( $redirect_to, $request, $user ) {
    //is there a user to check?
    global $user;

    if ( isset( $user->roles ) && is_array( $user->roles ) ) {

        if ( in_array( 'subscriber', $user->roles ) ) {
            // redirect them to the default place
            $data_login = get_page_by_title('Dashboard');

            return get_permalink($data_login->ID);
        } else {
        	$data_login = get_page_by_title('Dashboard');
            //return admin_url();
            return get_permalink($data_login->ID);
        }
    } else {
        return $redirect_to;
    }
}

add_filter( 'login_redirect', 'my_login_redirect', 10, 3 );

