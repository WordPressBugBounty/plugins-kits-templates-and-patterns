<?php
/**
 * GetBowtied Import Demo Content setup
 *
 * @package GetBowtied_Import_Demo_Content
 * @since   1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main GetBowtied Import Demo Content Class.
 *
 * @class GetBowtied_Import_Demo_Content
 */
final class GetBowtied_Import_Demo_Content {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '1.0';

	/**
	 * Theme single instance of this class.
	 *
	 * @var object
	 */
	protected static $_instance = null;

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.4
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'getbowtied-kits-templates-and-patterns' ), '1.4' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.4
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'getbowtied-kits-templates-and-patterns' ), '1.4' );
	}

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		$this->define_constants();
		$this->init_hooks();

		do_action( 'getbowtied_demo_importer_loaded' );
	}

	/**
	 * Define GETBOWTIED_IDC Constants.
	 */
	private function define_constants() {
		$upload_dir = wp_upload_dir( null, false );

		$this->define( 'GETBOWTIED_IDC_ABSPATH', dirname( GETBOWTIED_IDC_PLUGIN_FILE ) . '/' );
		$this->define( 'GETBOWTIED_IDC_PLUGIN_BASENAME', plugin_basename( GETBOWTIED_IDC_PLUGIN_FILE ) );
		$this->define( 'GETBOWTIED_IDC_VERSION', $this->version );
		$this->define( 'GETBOWTIED_IDC_DEMO_DIR', $upload_dir['basedir'] . '/getbowtied-kits-templates-and-patterns/' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Register activation hook.
		register_activation_hook( GETBOWTIED_IDC_PLUGIN_FILE, array( $this, 'install' ) );

		$this->includes();

		add_filter( 'plugin_action_links_' . GETBOWTIED_IDC_PLUGIN_BASENAME, array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Include required core files.
	 */
	private function includes() {
		include_once GETBOWTIED_IDC_ABSPATH . 'includes/class-kits-templates-and-patterns.php';
		include_once GETBOWTIED_IDC_ABSPATH . 'includes/functions-kits-templates-and-patterns.php';
		//include_once GETBOWTIED_IDC_ABSPATH . 'includes/gbt-third-party/setup.php';
	}

	/**
	 * Install GETBOWTIED Demo Importer.
	 */
	public function install() {
		$files = array(
			array(
				'base'    => GETBOWTIED_IDC_DEMO_DIR,
				'file'    => 'index.html',
				'content' => '',
			),
		);

		// Bypass if filesystem is read-only and/or non-standard upload system is used.
		if ( ! is_blog_installed() || apply_filters( 'getbowtied_demo_importer_install_skip_create_files', false ) ) {
			return;
		}

		// Install files and folders.
		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
					fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
				}
			}
		}

		// Redirect to demo importer page.
		set_transient( '_getbowtied_demo_importer_activation_redirect', 1, 30 );
	}

	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/getbowtied-kits-templates-and-patterns/getbowtied-kits-templates-and-patterns-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/getbowtied-kits-templates-and-patterns-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'getbowtied-kits-templates-and-patterns' );

		unload_textdomain( 'getbowtied-kits-templates-and-patterns' );
		load_textdomain( 'getbowtied-kits-templates-and-patterns', WP_LANG_DIR . '/getbowtied-kits-templates-and-patterns/getbowtied-kits-templates-and-patterns-' . $locale . '.mo' );
		load_plugin_textdomain( 'getbowtied-kits-templates-and-patterns', false, plugin_basename( dirname( GETBOWTIED_IDC_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', GETBOWTIED_IDC_PLUGIN_FILE ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( GETBOWTIED_IDC_PLUGIN_FILE ) );
	}

	/**
	 * Display action links in the Plugins list table.
	 *
	 * @param  array $actions Plugin Action links.
	 * @return array
	 */
	public function plugin_action_links( $actions ) {
		$new_actions = array(
			'importer' => '<a href="' . admin_url( 'themes.php?page=kits-templates-and-patterns' ) . '" aria-label="' . esc_attr( __( 'Settings', 'getbowtied-kits-templates-and-patterns' ) ) . '">' . __( 'Settings', 'getbowtied-kits-templates-and-patterns' ) . '</a>',
		);

		return array_merge( $new_actions, $actions );
	}

	/**
	 * Display row meta in the Plugins list table.
	 *
	 * @param  array  $plugin_meta Plugin Row Meta.
	 * @param  string $plugin_file Plugin Row Meta.
	 * @return array
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( GETBOWTIED_IDC_PLUGIN_BASENAME === $plugin_file ) {
			$new_plugin_meta = array(
				'docs'    => '<a href="' . esc_url( apply_filters( 'getbowtied_demo_importer_docs_url', 'https://www.getbowtied.com/documentation/' ) ) . '" title="' . esc_attr( __( 'View Documentation', 'getbowtied-kits-templates-and-patterns' ) ) . '">' . __( 'Documentation', 'getbowtied-kits-templates-and-patterns' ) . '</a>'
			);

			return array_merge( $plugin_meta, $new_plugin_meta );
		}

		return (array) $plugin_meta;
	}

}
