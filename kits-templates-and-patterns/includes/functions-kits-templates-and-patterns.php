<?php
/**
 * Demo Importer Functions.
 *
 * @package GetBowtied_Import_Demo_Content\Functions
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Disable BlockArt redirection.
add_filter( 'blockart_activation_redirect', '__return_false' );

/**
 * Ajax handler for installing a required plugin.
 *
 * @since 1.5.0
 *
 * @see Plugin_Upgrader
 *
 * @global WP_Filesystem_Base $wp_filesystem Subclass
 */
function getbowtied_ajax_install_required_plugin() {
	check_ajax_referer( 'updates' );

	if ( empty( $_POST['plugin'] ) || empty( $_POST['slug'] ) ) {
		wp_send_json_error(
			array(
				'slug'         => '',
				'errorCode'    => 'no_plugin_specified',
				'errorMessage' => __( 'No plugin specified.', 'getbowtied-kits-templates-and-patterns' ),
			)
		);
	}

	$slug   = sanitize_key( wp_unslash( $_POST['slug'] ) );
	$plugin = plugin_basename( sanitize_text_field( wp_unslash( $_POST['plugin'] ) ) );
	$url = sanitize_url( $_POST['url'] );

	$status = array(
		'install' => 'plugin',
		'slug'    => sanitize_key( wp_unslash( $_POST['slug'] ) ),
	);

	if ( ! current_user_can( 'install_plugins' ) ) {
		$status['errorMessage'] = __( 'Sorry, you are not allowed to install plugins on this site.', 'getbowtied-kits-templates-and-patterns' );
		wp_send_json_error( $status );
	}

	include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

	// Looks like a plugin is installed, but not active.
	if ( file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {
		$plugin_data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
		$status['plugin']     = $plugin;
		$status['pluginName'] = $plugin_data['Name'];

		if ( current_user_can( 'activate_plugin', $plugin ) && is_plugin_inactive( $plugin ) ) {
			$result = activate_plugin( $plugin );

			if ( is_wp_error( $result ) ) {
				$status['errorCode']    = $result->get_error_code();
				$status['errorMessage'] = $result->get_error_message();
				wp_send_json_error( $status );
			}

			wp_send_json_success( $status );
		}
	}

	if ( ! isset($url) || $url == "" ) {
	
		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => sanitize_key( wp_unslash( $_POST['slug'] ) ),
				'fields' => array(
					'sections' => false,
				),
			)
		);

		if ( is_wp_error( $api ) ) {
			$status['errorMessage'] = $api->get_error_message();
			wp_send_json_error( $status );
		}

		$status['pluginName'] = $api->name;

		$download_link = $api->download_link;

	} else {
		
		$download_link = $url;

	}

	$skin     = new WP_Ajax_Upgrader_Skin();
	$upgrader = new Plugin_Upgrader( $skin );
	//$result   = $upgrader->install( $api->download_link );
	$result   = $upgrader->install( $download_link );

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

	if ( ! isset($url) || $url == "" ) {
		$install_status = install_plugin_install_status( $api );
		$install_status_file = $install_status['file'];
	} else {
		$install_status_file = $plugin;
	}

	if ( current_user_can( 'activate_plugin', $install_status_file ) && is_plugin_inactive( $install_status_file ) ) {
		$result = activate_plugin( $install_status_file );

		if ( is_wp_error( $result ) ) {
			$status['errorCode']    = $result->get_error_code();
			$status['errorMessage'] = $result->get_error_message();
			wp_send_json_error( $status );
		}
	}

	wp_send_json_success( $status );
}
add_action( 'wp_ajax_install-required-plugin', 'getbowtied_ajax_install_required_plugin', 1 );

/**
 * Get an attachment ID from the filename.
 *
 * @param  string $filename
 * @return int Attachment ID on success, 0 on failure
 */
function getbowtied_get_attachment_id( $filename ) {
	$attachment_id = 0;

	$file = basename( $filename );

	$query_args = array(
		'post_type'   => 'attachment',
		'post_status' => 'inherit',
		'fields'      => 'ids',
		'meta_query'  => array(
			array(
				'value'   => $file,
				'compare' => 'LIKE',
				'key'     => '_wp_attachment_metadata',
			),
		),
	);

	$query = new WP_Query( $query_args );

	if ( $query->have_posts() ) {

		foreach ( $query->posts as $post_id ) {

			$meta = wp_get_attachment_metadata( $post_id );

			$original_file       = basename( $meta['file'] );
			$cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );

			if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
				$attachment_id = $post_id;
				break;
			}
		}
	}

	return $attachment_id;
}

/**
 * Clear data before demo import AJAX action.
 *
 * @see getbowtied_reset_widgets()
 * @see getbowtied_delete_nav_menus()
 * @see getbowtied_remove_theme_mods()
 */
if ( apply_filters( 'getbowtied_clear_data_before_demo_import', true ) ) {
	add_action( 'getbowtied_ajax_before_demo_import', 'getbowtied_reset_widgets', 10 );
	add_action( 'getbowtied_ajax_before_demo_import', 'getbowtied_delete_nav_menus', 20 );
	add_action( 'getbowtied_ajax_before_demo_import', 'getbowtied_remove_theme_mods', 30 );
}

/**
 * Reset existing active widgets.
 */
function getbowtied_reset_widgets() {
	$sidebars_widgets = wp_get_sidebars_widgets();

	// Reset active widgets.
	foreach ( $sidebars_widgets as $key => $widgets ) {
		$sidebars_widgets[ $key ] = array();
	}

	wp_set_sidebars_widgets( $sidebars_widgets );
}

/**
 * Delete existing navigation menus.
 */
function getbowtied_delete_nav_menus() {
	$nav_menus = wp_get_nav_menus();

	// Delete navigation menus.
	if ( ! empty( $nav_menus ) ) {
		foreach ( $nav_menus as $nav_menu ) {
			wp_delete_nav_menu( $nav_menu->slug );
		}
	}
}

/**
 * Remove theme modifications option.
 */
function getbowtied_remove_theme_mods() {
	remove_theme_mods();
}

/**
 * After demo imported AJAX action.
 *
 * @see getbowtied_set_elementor_load_fa4_shim()
 */
add_action( 'getbowtied_ajax_demo_imported', 'getbowtied_set_elementor_load_fa4_shim' );

/**
 * Set Elementor Load FontAwesome 4 support.
 */
function getbowtied_set_elementor_load_fa4_shim() {
	$elementor_load_fa4_shim = get_option( 'elementor_load_fa4_shim' );

	if ( ! $elementor_load_fa4_shim ) {
		update_option( 'elementor_load_fa4_shim', 'yes' );
	}
}

/**
 * After demo imported AJAX action.
 *
 * @see getbowtied_set_elementor_active_kit()
 */
add_action( 'getbowtied_ajax_demo_imported', 'getbowtied_set_elementor_active_kit' );

/**
 * Set Elementor kit properly.
 */
function getbowtied_set_elementor_active_kit() {
	$elementor_version = defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : false;

	if ( version_compare( $elementor_version, '3.0.0', '>=' ) ) {

		global $wpdb;
		$page_ids = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE (post_name = %s OR post_title = %s) AND post_type = 'elementor_library' AND post_status = 'publish'", 'default-kit', 'Default Kit' ) );

		if ( ! is_null( $page_ids ) ) {

			$page_id    = 0;
			$delete_ids = array();

			// Retrieve page with greater id and delete others.
			if ( sizeof( $page_ids ) > 1 ) {

				foreach ( $page_ids as $page ) {
					if ( $page->ID > $page_id ) {
						if ( $page_id ) {
							$delete_ids[] = $page_id;
						}

						$page_id = $page->ID;
					} else {
						$delete_ids[] = $page->ID;
					}
				}
			} else {
				$page_id = $page_ids[0]->ID;
			}

			// Update `elementor_active_kit` page.
			if ( $page_id > 0 ) {
				wp_update_post(
					array(
						'ID'        => $page_id,
						'post_name' => sanitize_title( 'Default Kit' ),
					)
				);
				update_option( 'elementor_active_kit', $page_id );
			}
		}
	}
}

/**
 * After demo imported AJAX action.
 *
 * @see getbowtied_set_wc_pages()
 */
add_action( 'getbowtied_ajax_demo_imported', 'getbowtied_set_wc_pages' );

/**
 * Set WC pages properly and disable setup wizard redirect.
 *
 * After importing demo data filter out duplicate WC pages and set them properly.
 * Happens when the user run default woocommerce setup wizard during installation.
 *
 * Note: WC pages ID are stored in an option and slug are modified to remove any numbers.
 *
 * @param string $demo_id
 */
function getbowtied_set_wc_pages( $demo_id ) {
	if ( class_exists( 'WooCommerce' ) ) {

		global $wpdb;
		$wc_pages = apply_filters(
			'getbowtied_wc_' . $demo_id . '_pages',
			array(
				'shop'      => array(
					'name'  => 'shop',
					'title' => 'Shop',
				),
				'cart'      => array(
					'name'  => 'cart',
					'title' => 'Cart',
				),
				'checkout'  => array(
					'name'  => 'checkout',
					'title' => 'Checkout',
				),
				'myaccount' => array(
					'name'  => 'my-account',
					'title' => 'My Account',
				),
			)
		);

		// Set WC pages properly.
		foreach ( $wc_pages as $key => $wc_page ) {

			// Get the ID of every page with matching name or title.
			$page_ids = $wpdb->get_results( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE (post_name = %s OR post_title = %s) AND post_type = 'page' AND post_status = 'publish'", $wc_page['name'], $wc_page['title'] ) );

			if ( ! is_null( $page_ids ) ) {

				$page_id    = 0;
				$delete_ids = array();

				// Retrieve page with greater id and delete others.
				if ( sizeof( $page_ids ) > 1 ) {

					foreach ( $page_ids as $page ) {
						if ( $page->ID > $page_id ) {
							if ( $page_id ) {
								$delete_ids[] = $page_id;
							}

							$page_id = $page->ID;
						} else {
							$delete_ids[] = $page->ID;
						}
					}
				} else {
					$page_id = $page_ids[0]->ID;
				}

				// Delete posts.
				foreach ( $delete_ids as $delete_id ) {
					wp_delete_post( $delete_id, true );
				}

				// Update WC page.
				if ( $page_id > 0 ) {
					wp_update_post(
						array(
							'ID'        => $page_id,
							'post_name' => sanitize_title( $wc_page['name'] ),
						)
					);
					update_option( 'woocommerce_' . $key . '_page_id', $page_id );
				}
			}
		}

		// We no longer need WC setup wizard redirect.
		delete_transient( '_wc_activation_redirect' );
	}
}

/**
 * Prints the JavaScript templates for install admin notices.
 *
 * Template takes one argument with four values:
 *
 *     param {object} data {
 *         Arguments for admin notice.
 *
 *         @type string id        ID of the notice.
 *         @type string className Class names for the notice.
 *         @type string message   The notice's message.
 *         @type string type      The type of update the notice is for. Either 'plugin' or 'theme'.
 *     }
 *
 * @since 1.4.0
 */
function getbowtied_print_admin_notice_templates() {
	?>
	<script id="tmpl-wp-installs-admin-notice" type="text/html">
		<div <# if ( data.id ) { #>id="{{ data.id }}"<# } #> class="notice {{ data.className }}"><p>{{{ data.message }}}</p></div>
	</script>
	<script id="tmpl-wp-bulk-installs-admin-notice" type="text/html">
		<div id="{{ data.id }}" class="{{ data.className }} notice <# if ( data.errors ) { #>notice-error<# } else { #>notice-success<# } #>">
			<p>
				<# if ( data.successes ) { #>
					<# if ( 1 === data.successes ) { #>
						<# if ( 'plugin' === data.type ) { #>
							<?php
							/* translators: %s: Number of plugins */
							printf( __( '%s plugin successfully installed.', 'getbowtied-kits-templates-and-patterns' ), '{{ data.successes }}' );
							?>
						<# } #>
					<# } else { #>
						<# if ( 'plugin' === data.type ) { #>
							<?php
							/* translators: %s: Number of plugins */
							printf( __( '%s plugins successfully installed.', 'getbowtied-kits-templates-and-patterns' ), '{{ data.successes }}' );
							?>
						<# } #>
					<# } #>
				<# } #>
				<# if ( data.errors ) { #>
					<button class="button-link bulk-action-errors-collapsed" aria-expanded="false">
						<# if ( 1 === data.errors ) { #>
							<?php
							/* translators: %s: Number of failed installs */
							printf( __( '%s install failed.', 'getbowtied-kits-templates-and-patterns' ), '{{ data.errors }}' );
							?>
						<# } else { #>
							<?php
							/* translators: %s: Number of failed installs */
							printf( __( '%s installs failed.', 'getbowtied-kits-templates-and-patterns' ), '{{ data.errors }}' );
							?>
						<# } #>
						<span class="screen-reader-text"><?php esc_html_e( 'Show more details', 'getbowtied-kits-templates-and-patterns' ); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
				<# } #>
			</p>
			<# if ( data.errors ) { #>
				<ul class="bulk-action-errors hidden">
					<# _.each( data.errorMessages, function( errorMessage ) { #>
						<li>{{ errorMessage }}</li>
					<# } ); #>
				</ul>
			<# } #>
		</div>
	</script>
	<?php
}
