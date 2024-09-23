<?php
/**
 * GetBowtied Import Demo Content.
 *
 * @package GetBowtied_Import_Demo_Content\Classes
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * GetBowtied_Import_Demo_Setup Class.
 */
class GetBowtied_Import_Demo_Setup {

	/**
	 * Demo packages.
	 *
	 * @var array
	 */
	public $demo_packages;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'setup' ), 5 );
		add_action( 'init', array( $this, 'includes' ) );

		// Add Demo Importer menu.
		if ( apply_filters( 'getbowtied_show_demo_importer_page', true ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
			add_action( 'admin_head', array( $this, 'add_menu_classes' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		// Help Tabs.
		if ( apply_filters( 'getbowtied_demo_importer_enable_admin_help_tab', true ) ) {
			add_action( 'current_screen', array( $this, 'add_help_tabs' ), 50 );
		}

		// Footer rating text.
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 1 );

		// Disable WooCommerce setup wizard.
		add_action( 'current_screen', array( $this, 'woocommerce_disable_setup_wizard' ) );

		// AJAX Events to query demo, import demo and update rating footer.
		add_action( 'wp_ajax_query-demos', array( $this, 'ajax_query_demos' ) );
		add_action( 'wp_ajax_import-demo', array( $this, 'ajax_import_demo' ) );
		add_action( 'wp_ajax_footer-text-rated', array( $this, 'ajax_footer_text_rated' ) );

		// Update custom nav menu items and elementor.
		add_action( 'getbowtied_ajax_demo_imported', array( $this, 'update_nav_menu_items' ) );
		add_action( 'getbowtied_ajax_demo_imported', array( $this, 'update_elementor_data' ), 10, 2 );

		// Update widget and customizer demo import settings data.
		add_filter( 'getbowtied_widget_demo_import_settings', array( $this, 'update_widget_data' ), 10, 4 );
		add_filter( 'getbowtied_customizer_demo_import_settings', array( $this, 'update_customizer_data' ), 10, 2 );

		// Refresh demos.
		add_action( 'admin_init', array( $this, 'refresh_demo_lists' ) );
	}

	/**
	 * Demo importer setup.
	 */
	public function setup() {
		$this->demo_packages = $this->get_demo_packages();
	}

	/**
	 * Include required core files.
	 */
	public function includes() {
		include_once dirname( __FILE__ ) . '/importers/class-widget-importer.php';
		include_once dirname( __FILE__ ) . '/importers/class-customizer-importer.php';
		include_once dirname( __FILE__ ) . '/admin/class-kits-templates-and-patterns-status.php';
	}

	/**
	 * Get demo packages.
	 *
	 * @return array of objects
	 */
	private function get_demo_packages() {

		$packages = get_transient( 'getbowtied_demo_importer_packages' );
		$template = strtolower( str_replace( '-pro', '', get_option( 'template' ) ) );

		if ( false === $packages || ( isset( $packages->slug ) && $template !== $packages->slug ) ) {
			
			//$raw_packages = wp_safe_remote_get( plugin_dir_url( __DIR__ ) . "_imports/{$template}.json" );
			//$raw_packages = wp_safe_remote_get( plugin_dir_url( __DIR__ ) . "_imports/demos.json" );
			
			$raw_packages = wp_safe_remote_get( "https://getbowtied.github.io/imports/kits-templates-and-patterns/demos.json" );

			if ( ! is_wp_error( $raw_packages ) ) {
				$packages = json_decode( wp_remote_retrieve_body( $raw_packages ) );

				if ( $packages ) {
					set_transient( 'getbowtied_demo_importer_packages', $packages, WEEK_IN_SECONDS );
				}
			}
		}

		return apply_filters( 'getbowtied_demo_importer_packages_' . $template, $packages );
	}

	/**
	 * Get the import file path.
	 *
	 * @param  string $filename File name.
	 * @return string The import file path.
	 */
	private function get_import_file_path( $filename ) {
		return trailingslashit( GETBOWTIED_IDC_DEMO_DIR ) . sanitize_file_name( $filename );
	}

	/**
	 * Add menu item.
	 */
	public function admin_menu() {
		add_theme_page( __( 'Kits, Templates and Patterns', 'getbowtied-kits-templates-and-patterns' ), __( 'Kits, Templates and Patterns', 'getbowtied-kits-templates-and-patterns' ), 'switch_themes', 'kits-templates-and-patterns', array( $this, 'demo_importer' ) );
		//add_theme_page( __( 'Import Demo Content Status', 'getbowtied-kits-templates-and-patterns' ), __( 'Kits, Templates and Patterns Status', 'getbowtied-kits-templates-and-patterns' ), 'switch_themes', 'kits-templates-and-patterns-status', array( $this, 'status_menu' ) );
	}

	/**
	 * Adds the class to the menu.
	 */
	public function add_menu_classes() {
		global $submenu;

		if ( isset( $submenu['themes.php'] ) ) {
			$submenu_class = 'kits-templates-and-patterns hide-if-no-js';

			// Add menu classes if user has access.
			if ( apply_filters( 'getbowtied_demo_importer_include_class_in_menu', true ) ) {
				foreach ( $submenu['themes.php'] as $order => $menu_item ) {
					if ( 0 === strpos( $menu_item[0], _x( 'Demo Importer', 'Admin menu name', 'getbowtied-kits-templates-and-patterns' ) ) ) {
						$submenu['themes.php'][ $order ][4] = empty( $menu_item[4] ) ? $submenu_class : $menu_item[4] . ' ' . $submenu_class;
						break;
					}
				}
			}
		}
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue_scripts() {
		$screen      = get_current_screen();
		$screen_id   = $screen ? $screen->id : '';
		$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$assets_path = getbowtied_import_demo_content()->plugin_url() . '/assets/';

		// Register admin styles.
		wp_register_style( 'jquery-confirm', $assets_path . 'css/jquery-confirm/jquery-confirm.css', array(), GETBOWTIED_IDC_VERSION );
		wp_register_style( 'getbowtied-kits-templates-and-patterns', $assets_path . 'css/kits-templates-and-patterns.css', array( 'jquery-confirm' ), GETBOWTIED_IDC_VERSION );

		// Register and enqueue admin notice files.
		wp_register_style( 'getbowtied-kits-templates-and-patterns-notice', getbowtied_import_demo_content()->plugin_url() . '/includes/admin/assets/css/notice.css', array(), GETBOWTIED_IDC_VERSION );
		wp_enqueue_style( 'getbowtied-kits-templates-and-patterns-notice' );
		wp_register_script( 'getbowtied-kits-templates-and-patterns-notice', getbowtied_import_demo_content()->plugin_url() . '/includes/admin/assets/js/notice.js', array( 'jquery' ), GETBOWTIED_IDC_VERSION, true );
		wp_enqueue_script( 'getbowtied-kits-templates-and-patterns-notice' );

		// Add RTL support for admin styles.
		wp_style_add_data( 'getbowtied-kits-templates-and-patterns', 'rtl', 'replace' );

		// Register admin scripts.
		wp_register_script( 'jquery-tiptip', $assets_path . 'js/jquery-tiptip/jquery.tipTip' . $suffix . '.js', array( 'jquery' ), '1.3', true );
		wp_register_script( 'jquery-confirm', $assets_path . 'js/jquery-confirm/jquery-confirm' . $suffix . '.js', array( 'jquery' ), GETBOWTIED_IDC_VERSION, true );
		wp_register_script( 'getbowtied-demo-updates', $assets_path . 'js/admin/demo-updates' . $suffix . '.js', array( 'jquery', 'updates', 'wp-i18n' ), GETBOWTIED_IDC_VERSION, true );
		wp_register_script( 'getbowtied-kits-templates-and-patterns', $assets_path . 'js/admin/kits-templates-and-patterns' . $suffix . '.js', array( 'jquery', 'jquery-tiptip', 'wp-backbone', 'wp-a11y', 'getbowtied-demo-updates', 'jquery-confirm' ), GETBOWTIED_IDC_VERSION, true );

		wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');

		// Demo Importer appearance page.
		if ( 'appearance_page_kits-templates-and-patterns' === $screen_id ) {
			wp_enqueue_style( 'getbowtied-kits-templates-and-patterns' );
			wp_enqueue_script( 'getbowtied-kits-templates-and-patterns' );

			wp_localize_script(
				'getbowtied-kits-templates-and-patterns',
				'_demoImporterSettings',
				array(
					'demos'    => $this->ajax_query_demos( true ),
					'settings' => array(
						'isNew'         => false,
						'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
						'adminUrl'      => wp_parse_url( self_admin_url(), PHP_URL_PATH ),
						'suggestURI'    => apply_filters( 'getbowtied_demo_importer_suggest_new', 'https://getbowtied.com/contact/' ),
						'confirmImport' => sprintf(
							/* translators: Before import warning texts */
							__( '<strong>When you import demo data, your website will look just like the theme demo, making it easier to tweak the content instead of starting from scratch. But, before you import, here are some things to keep in mind:</strong> %1$s %2$s %3$s %4$s %5$s %6$s', 'getbowtied-kits-templates-and-patterns' ),
							'<ul><li><i class="fa-regular fa-hand-point-right"></i> ' . __( 'It’s not recommended to import this demo onto a website where you’ve already added your own content.', 'getbowtied-kits-templates-and-patterns' ) . '</li>',
							'<li><i class="fa-regular fa-hand-point-right"></i> ' . __( 'For an exact replica of the theme demo, import the demo on a fresh WordPress install.', 'getbowtied-kits-templates-and-patterns' ) . '</li>',
							'<li><i class="fa-regular fa-hand-point-right"></i> ' . __( 'The import process includes installing and activating required plugins to set up the theme demo on your site.', 'getbowtied-kits-templates-and-patterns' ) . '</li>',
							'<li><i class="fa-regular fa-hand-point-right"></i> ' . __( 'Copyright images will be swapped out with placeholder images.', 'getbowtied-kits-templates-and-patterns' ) . '</li>',
							'<li><i class="fa-regular fa-hand-point-right"></i> ' . __( 'Your existing posts, pages, attachments, and other data won’t be deleted or changed.', 'getbowtied-kits-templates-and-patterns' ) . '</li>',
							'<li><i class="fa-regular fa-hand-point-right"></i> ' . __( 'The import might take a bit of time, so be patient while it sets up the theme demo.', 'getbowtied-kits-templates-and-patterns' ) . '</li></ul>'
						),
					),
					'l10n'     => array(
						'search'              => __( 'Search Demos', 'getbowtied-kits-templates-and-patterns' ),
						'searchPlaceholder'   => __( 'Search demos...', 'getbowtied-kits-templates-and-patterns' ), // placeholder (no ellipsis)
						/* translators: %s: support forums URL */
						'error'               => sprintf( __( 'An unexpected error occurred. Something may be wrong with GetBowtied demo server&#8217;s configuration. If you continue to have problems, please try the <a href="%s">support forums</a>.', 'getbowtied-kits-templates-and-patterns' ), 'https://wordpress.org/support/plugin/kits-templates-and-patterns' ),
						'tryAgain'            => __( 'Try Again', 'getbowtied-kits-templates-and-patterns' ),
						'suggestNew'          => __( 'Please suggest us!', 'getbowtied-kits-templates-and-patterns' ),
						/* translators: %d: Number of demos. */
						'demosFound'          => __( 'Number of Demos found: %d', 'getbowtied-kits-templates-and-patterns' ),
						'noDemosFound'        => __( 'No demos found. Try a different search.', 'getbowtied-kits-templates-and-patterns' ),
						'collapseSidebar'     => __( 'Collapse Sidebar', 'getbowtied-kits-templates-and-patterns' ),
						'expandSidebar'       => __( 'Expand Sidebar', 'getbowtied-kits-templates-and-patterns' ),
						/* translators: accessibility text */
						'selectFeatureFilter' => __( 'Select one or more Demo features to filter by', 'getbowtied-kits-templates-and-patterns' ),
						'confirmMsg'          => __( 'Ready to Go? Import Now!', 'getbowtied-kits-templates-and-patterns' ),
					),
				)
			);

			// For translation of strings within scripts.
			wp_set_script_translations( 'getbowtied-demo-updates', 'getbowtied-kits-templates-and-patterns' );
		}
	}

	/**
	 * Change the admin footer text.
	 *
	 * @param  string $footer_text
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $footer_text;
		}

		$current_screen = get_current_screen();

		// Check to make sure we're on a GetBowtied Import Demo Content admin page.
		if ( isset( $current_screen->id ) && apply_filters( 'getbowtied_demo_importer_display_admin_footer_text', in_array( $current_screen->id, array( 'appearance_page_kits-templates-and-patterns' ) ) ) ) {
			// Change the footer text.
			if ( ! get_option( 'getbowtied_demo_importer_admin_footer_text_rated' ) ) {
				$footer_text = sprintf(
					/* translators: 1: GetBowtied Import Demo Content 2: five stars */
					__( 'If the import worked well, please consider leaving a %s rating. Thanks in advance!', 'getbowtied-kits-templates-and-patterns' ),
					'<a href="https://wordpress.org/support/plugin/kits-templates-and-patterns/reviews?rate=5#new-post" target="_blank" class="getbowtied-kits-templates-and-patterns-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'getbowtied-kits-templates-and-patterns' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
				);
			} else {
				$footer_text = __( 'Thank you for importing with Kits, Templates and Patterns.', 'getbowtied-kits-templates-and-patterns' );
			}
		}

		return $footer_text;
	}

	/**
	 * Add Contextual help tabs.
	 */
	public function add_help_tabs() {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, array( 'appearance_page_kits-templates-and-patterns' ) ) ) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id'      => 'getbowtied_demo_importer_support_tab',
				'title'   => __( 'Help &amp; Support', 'getbowtied-kits-templates-and-patterns' ),
				'content' =>
					'<h2>' . __( 'Help &amp; Support', 'getbowtied-kits-templates-and-patterns' ) . '</h2>' .
				'<p>' . sprintf(
					/* translators: %s: Documentation URL */
					__( 'Should you need help understanding, using, or extending GetBowtied Import Demo Content, <a href="%s">please read our documentation</a>. You will find all kinds of resources including snippets, tutorials and much more.', 'getbowtied-kits-templates-and-patterns' ),
					'https://getbowtied.com/docs/getbowtied-kits-templates-and-patterns/'
				) . '</p>' .
				'<p>' . sprintf(
					/* translators: 1: WP support URL. 2: GETBOWTIED support URL  */
					__( 'For further assistance with GetBowtied Import Demo Content core you can use the <a href="%1$s">community forum</a>. If you need help with premium themes sold by GetBowtied, please <a href="%2$s">use our customer support</a>.', 'getbowtied-kits-templates-and-patterns' ),
					'https://wordpress.org/support/plugin/kits-templates-and-patterns',
					'https://getbowtied.com/support/'
				) . '</p>' .
					'<p><a href="https://wordpress.org/support/plugin/kits-templates-and-patterns" class="button button-primary">' . __( 'Community forum', 'getbowtied-kits-templates-and-patterns' ) . '</a> <a href="https://getbowtied.com/support/" class="button">' . __( 'GetBowtied Support', 'getbowtied-kits-templates-and-patterns' ) . '</a></p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'getbowtied_demo_importer_bugs_tab',
				'title'   => __( 'Found a bug?', 'getbowtied-kits-templates-and-patterns' ),
				'content' =>
					'<h2>' . __( 'Found a bug?', 'getbowtied-kits-templates-and-patterns' ) . '</h2>' .
					'<p>' . sprintf(
						/* translators: %s: GitHub links */
						__( 'If you find a bug within GetBowtied Import Demo Content you can create a ticket via <a href="%1$s">Github issues</a>. Ensure you read the <a href="%2$s">contribution guide</a> prior to submitting your report. To help us solve your issue, please be as descriptive as possible.', 'getbowtied-kits-templates-and-patterns' ),
						'https://github.com/getbowtied/getbowtied-kits-templates-and-patterns/issues?state=open',
						'https://github.com/getbowtied/getbowtied-kits-templates-and-patterns/blob/master/.github/CONTRIBUTING.md'
					) . '</p>' .
					'<p><a href="https://github.com/getbowtied/getbowtied-kits-templates-and-patterns/issues?state=open" class="button button-primary">' . __( 'Report a bug', 'getbowtied-kits-templates-and-patterns' ) . '</a></p>',

			)
		);

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'getbowtied-kits-templates-and-patterns' ) . '</strong></p>' .
			'<p><a href="https://getbowtied.com/kits-templates-and-patterns/" target="_blank">' . __( 'About Demo Importer', 'getbowtied-kits-templates-and-patterns' ) . '</a></p>' .
			'<p><a href="https://wordpress.org/plugins/getbowtied-kits-templates-and-patterns/" target="_blank">' . __( 'WordPress.org project', 'getbowtied-kits-templates-and-patterns' ) . '</a></p>' .
			'<p><a href="https://github.com/getbowtied/getbowtied-kits-templates-and-patterns" target="_blank">' . __( 'Github project', 'getbowtied-kits-templates-and-patterns' ) . '</a></p>' .
			'<p><a href="https://getbowtied.com/wordpress-themes/" target="_blank">' . __( 'Official themes', 'getbowtied-kits-templates-and-patterns' ) . '</a></p>' .
			'<p><a href="https://getbowtied.com/plugins/" target="_blank">' . __( 'Official plugins', 'getbowtied-kits-templates-and-patterns' ) . '</a></p>'
		);
	}

	/**
	 * Disable the WooCommerce Setup Wizard on `GetBowtied Import Demo Content` page only.
	 */
	public function woocommerce_disable_setup_wizard() {

		$screen = get_current_screen();

		if ( 'appearance_page_kits-templates-and-patterns' === $screen->id ) {
			add_filter( 'woocommerce_enable_setup_wizard', '__return_false', 1 );
		}

	}

	/**
	 * Demo Importer page output.
	 */
	public function demo_importer() {
		include_once dirname( __FILE__ ) . '/admin/views/html-admin-page-importer.php';
	}

	/**
	 * Demo Importer status page output.
	 */
	public function status_menu() {
		include_once dirname( __FILE__ ) . '/admin/views/html-admin-page-status.php';
	}

	/**
	 * Check for GetBowtied All Themes Plan
	 *
	 * @return bool
	 */
	public function getbowtied_is_all_themes_plan() {

		if ( is_plugin_active( 'getbowtied-all-themes-plan/getbowtied-all-themes-plan.php' ) ) {
			return true;
		}

		return false;

	}

	/**
	 * Ajax handler for getting demos from github.
	 */
	public function ajax_query_demos( $return = true ) {
		$prepared_demos        = array();
		$current_template      = get_option( 'template' );
		$current_theme_name    = wp_get_theme()->get( 'Name' );
		$current_theme_version = wp_get_theme()->get( 'Version' );
		$is_pro_theme_demo     = strpos( $current_template, '-pro' ) !== false;
		$demo_activated_id     = get_option( 'getbowtied_demo_importer_activated_id' );
		$available_packages    = $this->demo_packages;

		// Condition if child theme is being used.
		if ( is_child_theme() ) {
			$current_theme_name    = wp_get_theme()->parent()->get( 'Name' );
			$current_theme_version = wp_get_theme()->parent()->get( 'Version' );
		}

		/**
		 * Filters demo data before it is prepared for JavaScript.
		 *
		 * @param array      $prepared_demos     An associative array of demo data. Default empty array.
		 * @param null|array $available_packages An array of demo package config to prepare, if any.
		 * @param string     $demo_activated_id  The current demo activated id.
		 */
		$prepared_demos = (array) apply_filters( 'getbowtied_demo_importer_pre_prepare_demos_for_js', array(), $available_packages, $demo_activated_id );

		if ( ! empty( $prepared_demos ) ) {
			return $prepared_demos;
		}

		if ( ! $return ) {
			$request = wp_parse_args(
				wp_unslash( $_REQUEST['request'] ),
				array(
					'browse' => 'all',
				)
			);
		} else {
			$request = array(
				'browse' => 'all',
			);
		}

		if ( isset( $available_packages->demos ) ) {
			foreach ( $available_packages->demos as $package_slug => $package_data ) {
				$plugins_list   = isset( $package_data->plugins_list ) ? $package_data->plugins_list : array();
				//$screenshot_url = plugin_dir_url( __DIR__ ) . "_imports/screenshots/{$available_packages->slug}/{$package_slug}/screenshot.png";
				$screenshot_url = "https://getbowtied.github.io/imports/kits-templates-and-patterns/screenshots/{$package_data->theme_slug}/{$package_slug}/screenshot.png";

				if ( isset( $request['browse'], $package_data->category ) && ! in_array( $request['browse'], $package_data->category, true ) ) {
					continue;
				}

				if ( isset( $request['builder'], $package_data->pagebuilder ) && ! in_array( $request['builder'], $package_data->pagebuilder, true ) ) {
					continue;
				}

				// Plugins status.
				foreach ( $plugins_list as $plugin => $plugin_data ) {
					$plugin_data->is_active = is_plugin_active( $plugin_data->slug );

					// Looks like a plugin is installed, but not active.
					if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin ) ) {
						$plugins = get_plugins( '/' . $plugin );
						if ( ! empty( $plugins ) ) {
							$plugin_data->is_install = true;
						}
					} else {
						$plugin_data->is_install = false;
					}
				}

				// Get the required theme versions.
				$required_version_installed           = false;
				$required_version                     = false;
				
				if ( isset( $package_data->minimum_version ) && is_object( $package_data->minimum_version ) ) {
					foreach ( $package_data->minimum_version as $theme => $minimum_version ) {
						if ( $current_template === $theme && version_compare( $minimum_version, $current_theme_version, '>' ) ) {
							$required_version_installed = true;

							if ( $current_template === $theme ) {
								$required_version = $minimum_version;
							}
						}
					}
				}

				// For required message.
				$required_message = false;
				
				if ( $required_version ) {
					$required_message = sprintf(
						esc_html__( 'This demo requires %1$s version of %2$s theme to get imported', 'getbowtied-kits-templates-and-patterns' ),
						$required_version,
						$current_theme_name
					);
				}

				// Prepare all demos.
				$prepared_demos[ $package_slug ] = array(
					'slug'              => $package_slug,
					'name'              => $package_data->title,
					'theme'             => $is_pro_theme_demo ? sprintf( esc_html__( '%s Pro', 'getbowtied-kits-templates-and-patterns' ), $package_data->theme_name ) : $package_data->theme_name,
					'isPro'             => $is_pro_theme_demo ? false : isset( $package_data->isPro ),
					'isPremium'         => isset( $package_data->isPremium ),
					'isAllThemePlan'    => $this->getbowtied_is_all_themes_plan() ? false : isset( $package_data->isAllThemePlan ),
					'active'            => $package_slug === $demo_activated_id,
					'author'            => isset( $package_data->author ) ? $package_data->author : __( 'GetBowtied', 'getbowtied-kits-templates-and-patterns' ),
					'version'           => isset( $package_data->version ) ? $package_data->version : $available_packages->version,
					'description'       => isset( $package_data->description ) ? $package_data->description : '',
					'homepage'          => $package_data->homepage,
					'preview_url'       => set_url_scheme( $package_data->preview ),
					'theme_name'     	=> $package_data->theme_name,
					'screenshot_url'    => $screenshot_url,
					'plugins'           => $plugins_list,
					'requiredTheme'     => isset( $package_data->category ) && ! in_array( $current_template, $package_data->category, true ),
					'requiredPlugins'   => wp_list_filter( json_decode( wp_json_encode( $plugins_list ), true ), array( 'is_active' => false ) ) ? true : false,
					'requiredVersion'   => $required_version_installed,
					'updateThemeNotice' => $required_message,
				);

				unset( $required_version );
			}
		}

		/**
		 * Filters the demos prepared for JavaScript.
		 *
		 * Could be useful for changing the order, which is by name by default.
		 *
		 * @param array $prepared_demos Array of demos.
		 */
		$prepared_demos = apply_filters( 'getbowtied_demo_importer_prepare_demos_for_js', $prepared_demos );
		$prepared_demos = array_values( $prepared_demos );

		if ( $return ) {
			return $prepared_demos;
		}

		wp_send_json_success(
			array(
				'info'  => array(
					'page'    => 1,
					'pages'   => 1,
					'results' => count( $prepared_demos ),
				),
				'demos' => array_filter( $prepared_demos ),
			)
		);
	}

	/**
	 * Ajax handler for importing a demo.
	 *
	 * @see GETBOWTIED_Demo_Upgrader
	 *
	 * @global WP_Filesystem_Base $wp_filesystem Subclass
	 */
	public function ajax_import_demo() {
		check_ajax_referer( 'updates' );

		if ( empty( $_POST['slug'] ) ) {
			wp_send_json_error(
				array(
					'slug'         => '',
					'errorCode'    => 'no_demo_specified',
					'errorMessage' => __( 'No demo specified.', 'getbowtied-kits-templates-and-patterns' ),
				)
			);
		}

		$slug   = sanitize_key( wp_unslash( $_POST['slug'] ) );
		$status = array(
			'import' => 'demo',
			'slug'   => $slug,
		);

		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			define( 'WP_LOAD_IMPORTERS', true );
		}

		if ( ! current_user_can( 'import' ) ) {
			$status['errorMessage'] = __( 'Sorry, you are not allowed to import content.', 'getbowtied-kits-templates-and-patterns' );
			wp_send_json_error( $status );
		}

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once dirname( __FILE__ ) . '/admin/class-demo-pack-upgrader.php';

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new GETBOWTIED_Demo_Pack_Upgrader( $skin );
		$template = strtolower( str_replace( '-pro', '', get_option( 'template' ) ) );
		$packages = isset( $this->demo_packages->demos ) ? json_decode( wp_json_encode( $this->demo_packages->demos ), true ) : array();
		//$result   = $upgrader->install( plugin_dir_url( __DIR__ ) . "_imports/packages/{$template}/{$slug}.zip" );
		$result   = $upgrader->install( "https://getbowtied.github.io/imports/kits-templates-and-patterns/packages/{$template}/{$slug}.zip" );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$status['debug'] = $skin->get_upgrade_messages();
		}

		if ( is_wp_error( $result ) ) {
			$status['errorCode']    = $result->get_error_code();
			$status['errorMessage'] = $result->get_error_message();
			wp_send_json_error( $status );
		} elseif ( is_wp_error( $skin->result ) ) {
			$status['errorCode']    = $skin->result->get_error_code();
			$status['errorMessage'] = $skin->result->get_error_message();
			wp_send_json_error( $status );
		} elseif ( $skin->get_errors()->get_error_code() ) {
			$status['errorMessage'] = $skin->get_error_messages();
			wp_send_json_error( $status );
		} elseif ( is_null( $result ) ) {
			global $wp_filesystem;

			$status['errorCode']    = 'unable_to_connect_to_filesystem';
			$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'getbowtied-kits-templates-and-patterns' );

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
			}

			wp_send_json_error( $status );
		}

		$demo_data            = $packages[ $slug ];
		$status['demoName']   = $demo_data['title'];
		$status['previewUrl'] = get_site_url( null, '/' );
		$status['regenerateThumbnailsUrl'] = admin_url('tools.php?page=regenerate-thumbnails');

		do_action( 'getbowtied_ajax_before_demo_import' );

		if ( ! empty( $demo_data ) ) {
			$this->import_dummy_xml( $slug, $demo_data, $status );
			$this->import_core_options( $slug, $demo_data );
			$this->import_elementor_schemes( $slug, $demo_data );
			$this->import_customizer_data( $slug, $demo_data, $status );
			$this->import_widget_settings( $slug, $demo_data, $status );

			// Update imported demo ID.
			update_option( 'getbowtied_demo_importer_activated_id', $slug );
			do_action( 'getbowtied_ajax_demo_imported', $slug, $demo_data );
		}

		wp_send_json_success( $status );
	}

	/**
	 * Triggered when clicking the rating footer.
	 */
	public function ajax_footer_text_rated() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1 );
		}

		update_option( 'getbowtied_demo_importer_admin_footer_text_rated', 1 );
		wp_die();
	}

	/**
	 * Import dummy content from a XML file.
	 *
	 * @param  string $demo_id
	 * @param  array  $demo_data
	 * @param  array  $status
	 * @return bool
	 */
	public function import_dummy_xml( $demo_id, $demo_data, $status ) {
		$import_file = $this->get_import_file_path( 'content.xml' );

		// Load Importer API.
		require_once ABSPATH . 'wp-admin/includes/import.php';

		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';

			if ( file_exists( $class_wp_importer ) ) {
				require $class_wp_importer;
			}
		}

		// Include WXR Importer.
		require dirname( __FILE__ ) . '/importers/wordpress-importer/class-wxr-importer.php';

		do_action( 'getbowtied_ajax_before_dummy_xml_import', $demo_data, $demo_id );

		// Import XML file demo content.
		if ( is_file( $import_file ) ) {
			$wp_import                    = new WP_Import();
			$wp_import->fetch_attachments = true;

			ob_start();
			$wp_import->import( $import_file );
			ob_end_clean();

			do_action( 'getbowtied_ajax_dummy_xml_imported', $demo_data, $demo_id );

			flush_rewrite_rules();
		} else {
			$status['errorMessage'] = __( 'The XML file dummy content is missing.', 'getbowtied-kits-templates-and-patterns' );
			wp_send_json_error( $status );
		}

		return true;
	}

	/**
	 * Import site core options from its ID.
	 *
	 * @param  string $demo_id
	 * @param  array  $demo_data
	 * @return bool
	 */
	public function import_core_options( $demo_id, $demo_data ) {
		if ( ! empty( $demo_data['core_options'] ) ) {
			foreach ( $demo_data['core_options'] as $option_key => $option_value ) {
				if ( ! in_array( $option_key, array( 'blogname', 'blogdescription', 'show_on_front', 'page_on_front', 'page_for_posts' ) ) ) {
					continue;
				}

				// Format the value based on option key.
				switch ( $option_key ) {
					case 'show_on_front':
						if ( in_array( $option_value, array( 'posts', 'page' ) ) ) {
							update_option( 'show_on_front', $option_value );
						}
						break;
					case 'page_on_front':
					case 'page_for_posts':
						$page = $this->get_page_by_title( $option_value );

						if ( is_object( $page ) && $page->ID ) {
							update_option( $option_key, $page->ID );
							update_option( 'show_on_front', 'page' );
						}
						break;
					default:
						update_option( $option_key, sanitize_text_field( $option_value ) );
						break;
				}
			}
		}

		return true;
	}

	/**
	 * Import elementor schemes from its ID.
	 *
	 * @param string $demo_id Demo ID.
	 * @param array  $demo_data Demo Data.
	 * @return bool
	 */
	public function import_elementor_schemes( $demo_id, $demo_data ) {
		$elementor_version = defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : false;

		if ( version_compare( $elementor_version, '3.0.0', '<=' ) ) {

			if ( ! empty( $demo_data['elementor_schemes'] ) ) {
				foreach ( $demo_data['elementor_schemes'] as $scheme_key => $scheme_value ) {
					if ( ! in_array( $scheme_key, array( 'color', 'typography', 'color-picker' ) ) ) {
						continue;
					}

					// Change scheme index to start from 1 instead.
					$scheme_value = array_combine( range( 1, count( $scheme_value ) ), $scheme_value );

					if ( ! empty( $scheme_value ) ) {
						update_option( 'elementor_scheme_' . $scheme_key, $scheme_value );
					}
				}
			}
		}

		return true;
	}

	/**
	 * Import customizer data from a DAT file.
	 *
	 * @param  string $demo_id
	 * @param  array  $demo_data
	 * @param  array  $status
	 * @return bool
	 */
	public function import_customizer_data( $demo_id, $demo_data, $status ) {
		$import_file = $this->get_import_file_path( 'customizer.dat' );

		if ( is_file( $import_file ) ) {
			$results = GETBOWTIED_Customizer_Importer::import( $import_file, $demo_id, $demo_data );

			if ( is_wp_error( $results ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Import widgets settings from WIE or JSON file.
	 *
	 * @param  string $demo_id
	 * @param  array  $demo_data
	 * @param  array  $status
	 * @return bool
	 */
	public function import_widget_settings( $demo_id, $demo_data, $status ) {
		$import_file = $this->get_import_file_path( 'widgets.wie' );

		if ( is_file( $import_file ) ) {
			$results = GETBOWTIED_Widget_Importer::import( $import_file, $demo_id, $demo_data );

			if ( is_wp_error( $results ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Update custom nav menu items URL.
	 */
	public function update_nav_menu_items() {
		$menu_locations = get_nav_menu_locations();

		foreach ( $menu_locations as $location => $menu_id ) {

			if ( is_nav_menu( $menu_id ) ) {
				$menu_items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );

				if ( ! empty( $menu_items ) ) {
					foreach ( $menu_items as $menu_item ) {
						if ( isset( $menu_item->url ) && isset( $menu_item->db_id ) && 'custom' == $menu_item->type ) {
							$site_parts = parse_url( home_url( '/' ) );
							$menu_parts = parse_url( $menu_item->url );

							// Update existing custom nav menu item URL.
							if ( isset( $menu_parts['path'] ) && isset( $menu_parts['host'] ) && apply_filters( 'getbowtied_demo_importer_nav_menu_item_url_hosts', in_array( $menu_parts['host'], array( 'demo.getbowtied.com' ) ) ) ) {
								$menu_item->url = str_replace( array( $menu_parts['scheme'], $menu_parts['host'], $menu_parts['path'] ), array( $site_parts['scheme'], $site_parts['host'], trailingslashit( $site_parts['path'] ) ), $menu_item->url );
								update_post_meta( $menu_item->db_id, '_menu_item_url', esc_url_raw( $menu_item->url ) );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Updates widgets settings data.
	 *
	 * @param  array  $widget
	 * @param  string $widget_type
	 * @param  int    $instance_id
	 * @param  array  $demo_data
	 * @return array
	 */
	public function update_widget_data( $widget, $widget_type, $instance_id, $demo_data ) {
		if ( 'nav_menu' === $widget_type ) {
			$menu     = isset( $widget['title'] ) ? $widget['title'] : $widget['nav_menu'];
			$nav_menu = wp_get_nav_menu_object( $menu );

			if ( is_object( $nav_menu ) && $nav_menu->term_id ) {
				$widget['nav_menu'] = $nav_menu->term_id;
			}
		} elseif ( ! empty( $demo_data['widgets_data_update'] ) ) {
			foreach ( $demo_data['widgets_data_update'] as $dropdown_type => $dropdown_data ) {
				if ( ! in_array( $dropdown_type, array( 'dropdown_pages', 'dropdown_categories' ), true ) ) {
					continue;
				}

				// Format the value based on dropdown type.
				switch ( $dropdown_type ) {
					case 'dropdown_pages':
						foreach ( $dropdown_data as $widget_id => $widget_data ) {
							if ( ! empty( $widget_data[ $instance_id ] ) && $widget_id === $widget_type ) {
								foreach ( $widget_data[ $instance_id ] as $widget_key => $widget_value ) {
									$page = $this->get_page_by_title( $widget_value );

									if ( is_object( $page ) && $page->ID ) {
										$widget[ $widget_key ] = $page->ID;
									}
								}
							}
						}
						break;
					default:
					case 'dropdown_categories':
						foreach ( $dropdown_data as $taxonomy => $taxonomy_data ) {
							if ( ! taxonomy_exists( $taxonomy ) ) {
								continue;
							}

							foreach ( $taxonomy_data as $widget_id => $widget_data ) {
								if ( ! empty( $widget_data[ $instance_id ] ) && $widget_id === $widget_type ) {
									foreach ( $widget_data[ $instance_id ] as $widget_key => $widget_value ) {
										$term = get_term_by( 'name', $widget_value, $taxonomy );

										if ( is_object( $term ) && $term->term_id ) {
											$widget[ $widget_key ] = $term->term_id;
										}
									}
								}
							}
						}
						break;
				}
			}
		}

		return $widget;
	}

	/**
	 * Update customizer settings data.
	 *
	 * @param  array $data
	 * @param  array $demo_data
	 * @return array
	 */
	public function update_customizer_data( $data, $demo_data ) {
		if ( ! empty( $demo_data['customizer_data_update'] ) ) {
			foreach ( $demo_data['customizer_data_update'] as $data_type => $data_value ) {
				if ( ! in_array( $data_type, array( 'pages', 'categories', 'nav_menu_locations' ) ) ) {
					continue;
				}

				// Format the value based on data type.
				switch ( $data_type ) {
					case 'pages':
						foreach ( $data_value as $option_key => $option_value ) {
							if ( ! empty( $data['mods'][ $option_key ] ) ) {
								$page = $this->get_page_by_title( $option_value );

								if ( is_object( $page ) && $page->ID ) {
									$data['mods'][ $option_key ] = $page->ID;
								}
							}
						}
						break;
					case 'categories':
						foreach ( $data_value as $taxonomy => $taxonomy_data ) {
							if ( ! taxonomy_exists( $taxonomy ) ) {
								continue;
							}

							foreach ( $taxonomy_data as $option_key => $option_value ) {
								if ( ! empty( $data['mods'][ $option_key ] ) ) {
									$term = get_term_by( 'name', $option_value, $taxonomy );

									if ( is_object( $term ) && $term->term_id ) {
										$data['mods'][ $option_key ] = $term->term_id;
									}
								}
							}
						}
						break;
					case 'nav_menu_locations':
						$nav_menus = wp_get_nav_menus();

						if ( ! empty( $nav_menus ) ) {
							foreach ( $nav_menus as $nav_menu ) {
								if ( is_object( $nav_menu ) ) {
									foreach ( $data_value as $location => $location_name ) {
										if ( $nav_menu->name == $location_name ) {
											$data['mods'][ $data_type ][ $location ] = $nav_menu->term_id;
										}
									}
								}
							}
						}
						break;
				}
			}
		}

		return $data;
	}

	/**
	 * Recursive function to address n level deep elementor data update.
	 *
	 * @param  array  $elementor_data
	 * @param  string $data_type
	 * @param  array  $data_value
	 * @return array
	 */
	public function elementor_recursive_update( $elementor_data, $data_type, $data_value ) {
		$elementor_data = json_decode( stripslashes( $elementor_data ), true );

		// Recursively update elementor data.
		foreach ( $elementor_data as $element_id => $element_data ) {
			if ( ! empty( $element_data['elements'] ) ) {
				foreach ( $element_data['elements'] as $el_key => $el_data ) {
					if ( ! empty( $el_data['elements'] ) ) {
						foreach ( $el_data['elements'] as $el_child_key => $child_el_data ) {
							if ( 'widget' === $child_el_data['elType'] ) {
								$settings   = isset( $child_el_data['settings'] ) ? $child_el_data['settings'] : array();
								$widgetType = isset( $child_el_data['widgetType'] ) ? $child_el_data['widgetType'] : '';

								if ( isset( $settings['display_type'] ) && 'categories' === $settings['display_type'] ) {
									$categories_selected = isset( $settings['categories_selected'] ) ? $settings['categories_selected'] : '';

									if ( ! empty( $data_value['data_update'] ) ) {
										foreach ( $data_value['data_update'] as $taxonomy => $taxonomy_data ) {
											if ( ! taxonomy_exists( $taxonomy ) ) {
												continue;
											}

											foreach ( $taxonomy_data as $widget_id => $widget_data ) {
												if ( ! empty( $widget_data ) && $widget_id == $widgetType ) {
													if ( is_array( $categories_selected ) ) {
														foreach ( $categories_selected as $cat_key => $cat_id ) {
															if ( isset( $widget_data[ $cat_id ] ) ) {
																$term = get_term_by( 'name', $widget_data[ $cat_id ], $taxonomy );

																if ( is_object( $term ) && $term->term_id ) {
																	$categories_selected[ $cat_key ] = $term->term_id;
																}
															}
														}
													} elseif ( isset( $widget_data[ $categories_selected ] ) ) {
														$term = get_term_by( 'name', $widget_data[ $categories_selected ], $taxonomy );

														if ( is_object( $term ) && $term->term_id ) {
															$categories_selected = $term->term_id;
														}
													}
												}
											}
										}
									}

									// Update the elementor data.
									$elementor_data[ $element_id ]['elements'][ $el_key ]['elements'][ $el_child_key ]['settings']['categories_selected'] = $categories_selected;
								}
							}
						}
					}
				}
			}
		}

		return wp_json_encode( $elementor_data );
	}

	/**
	 * Update elementor settings data.
	 *
	 * @param string $demo_id Demo ID.
	 * @param array  $demo_data Demo Data.
	 */
	public function update_elementor_data( $demo_id, $demo_data ) {
		if ( ! empty( $demo_data['elementor_data_update'] ) ) {
			foreach ( $demo_data['elementor_data_update'] as $data_type => $data_value ) {
				if ( ! empty( $data_value['post_title'] ) ) {
					$page = $this->get_page_by_title( $data_value['post_title'] );

					if ( is_object( $page ) && $page->ID ) {
						$elementor_data = get_post_meta( $page->ID, '_elementor_data', true );

						if ( ! empty( $elementor_data ) ) {
							$elementor_data = $this->elementor_recursive_update( $elementor_data, $data_type, $data_value );
						}

						// Update elementor data.
						update_post_meta( $page->ID, '_elementor_data', $elementor_data );
					}
				}
			}
		}
	}

	/**
	 * Refreshes the demo lists.
	 */
	public function refresh_demo_lists() {
		// Reset the transient if user has clicked on the `Refresh Demos` button.
		if ( isset( $_GET['refresh-demo-packages'] ) && isset( $_GET['_refresh_demo_packages_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_refresh_demo_packages_nonce'], 'refresh_demo_packages' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'getbowtied-kits-templates-and-patterns' ) );
			}

			$demo_packages = get_transient( 'getbowtied_demo_importer_packages' );

			if ( $demo_packages ) {
				delete_transient( 'getbowtied_demo_importer_packages' );
			}

			// Redirect to demo import page once the transient is clear, since on first click, none of the demo is shown up in lists.
			wp_safe_redirect( admin_url( 'themes.php?page=kits-templates-and-patterns&browse=all' ) );
		}
	}

	/**
	 * Retrieve a page by its title.
	 *
	 * @param string $title The title of the page to retrieve.
	 * @return WP_Post|null The retrieved page object or null if not found.
	 */
	public function get_page_by_title( $title ) {
		if ( ! $title ) {
			return null;
		}

		$query = new WP_Query(
			array(
				'post_type'              => 'page',
				'title'                  => $title,
				'post_status'            => 'all',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'ignore_sticky_posts'    => true,
				'update_post_term_cache' => false,
				'update_post_meta_cache' => false,
			)
		);

		if ( ! $query->have_posts() ) {
			return null;
		}

		return current( $query->posts );
	}
}

new GetBowtied_Import_Demo_Setup();
