<?php
/**
 * Plugin Name: Custom Background Per Page
 * Plugin URI: http://itspiders.net
 * Description: Easily add background color/Image on each page/post . It works on any theme that supports the WordPress <code>custom-background</code> feature.
 * Version: 2.0
 * Author: Abdul Haleem
 * Author URI: http://itspiders.net
 * License: GPLv2 or later
 */

final class ITS_Custom_Background {
	private static $instance;
	private $directory_path;
	private $directory_uri;
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'setup' ), 1 );

		add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

		add_action( 'plugins_loaded', array( $this, 'includes' ), 3 );

		add_action( 'plugins_loaded', array( $this, 'admin' ), 4 );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts' ), 5 );

		add_action( 'init', array( $this, 'post_type_support' ) );

		register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );
	}

	public function setup() {

		$this->directory_path = trailingslashit( plugin_dir_path( __FILE__ ) );
		$this->directory_uri  = trailingslashit( plugin_dir_url(  __FILE__ ) );
	}

	
	public function includes() {

		if ( !is_admin() )
			require_once( "{$this->directory_path}inc/class-custom-backgrounds-filter.php" );
	}

	
	public function i18n() {

		load_plugin_textdomain( 'custom-background-per-page', false, 'custom-background-per-page/languages' );
	}

	public function admin() {

		if ( is_admin() )
			require_once( "{$this->directory_path}admin/class-custom-backgrounds-admin.php" );
	}


	public function post_type_support() {
		add_post_type_support( 'post', 'custom-background' );
		add_post_type_support( 'page', 'custom-background' );
	}

	public function admin_register_scripts() {

		wp_register_script(
			'custom-background-per-page',
			"{$this->directory_uri}js/custom-backgrounds.min.js",
			array( 'wp-color-picker', 'media-views' ),
			'20130926',
			true
		);
	}

	public static function activation() {

		$role = get_role( 'administrator' );

		if ( !empty( $role ) )
			$role->add_cap( 'itcb_edit_background' );
	}

	public static function get_instance() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
}

ITS_Custom_Background::get_instance();

?>
