<?php
/**
 * Admin View: Page - System Status
 *
 * @package GetBowtied_Import_Demo_Content
 */

defined( 'ABSPATH' ) || exit;

global $wpdb, $wp_rewrite;;
$curl_data = function_exists( 'curl_version' ) ? curl_version() : false;
$gd_data   = function_exists( 'gd_info' ) ? gd_info() : false;
$theme     = wp_get_theme();
?>
<div class="kits-templates-and-patterns-system-status">
	<h2><?php esc_html_e( 'System Status', 'getbowtied-kits-templates-and-patterns' ); ?></h2>

	<table class="kits-templates-and-patterns-status-table widefat">
		<thead>
		<tr>
			<th><?php esc_html_e( 'System Info', 'getbowtied-kits-templates-and-patterns' ); ?></th>
			<th></th>
			<th></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td><?php esc_html_e( 'Operating System:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( PHP_OS ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Server:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'MySQL Version:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( $wpdb->get_var( 'SELECT VERSION()' ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'PHP Version:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( PHP_VERSION ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'PHP Max Execution Time:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( ini_get( 'max_execution_time' ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'PHP Max Upload Size:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( ini_get( 'upload_max_filesize' ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'PHP Post Max Size:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( ini_get( 'post_max_size' ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'PHP Max Input Vars:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( ini_get( 'max_input_vars' ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'PHP Memory Limit:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( ini_get( 'memory_limit' ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'cURL Installed:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php extension_loaded( 'curl' ) ? esc_html_e( 'Yes', 'getbowtied-kits-templates-and-patterns' ) : esc_html_e( 'No', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td></td>
		</tr>
		<?php if ( $curl_data ) : ?>
			<tr>
				<td><?php esc_html_e( 'cURL version:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
				<td><?php echo esc_html( $curl_data['version'] ); ?></td>
				<td></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td><?php esc_html_e( 'GD Installed:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php extension_loaded( 'gd' ) ? esc_html_e( 'Yes', 'getbowtied-kits-templates-and-patterns' ) : esc_html_e( 'No', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td></td>
		</tr>
		<?php if ( $gd_data ) : ?>
			<tr>
				<td><?php esc_html_e( 'GD version:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
				<td><?php echo esc_html( $gd_data['GD Version'] ); ?></td>
				<td></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td><?php esc_html_e( 'Write Permission:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo GETBOWTIED_Demo_Importer_Status::get_write_permission(); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Demo Pack Server Connection:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo GETBOWTIED_Demo_Importer_Status::get_demo_server_connection_status(); ?></td>
			<td></td>
		</tr>
		</tbody>
	</table>


	<table class="kits-templates-and-patterns-status-table widefat">
		<thead>
		<tr>
			<th><?php esc_html_e( 'WordPress Info', 'getbowtied-kits-templates-and-patterns' ); ?></th>
			<th></th>
			<th></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td><?php esc_html_e( 'Version:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Site URL:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( get_site_url() ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Home URL:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( get_home_url() ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Multisite:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php is_multisite() ? esc_html_e( 'Yes', 'getbowtied-kits-templates-and-patterns' ) : esc_html_e( 'No', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Max Upload Size:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( size_format( wp_max_upload_size() ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Memory Limit:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( WP_MEMORY_LIMIT ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Max Memory Limit:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( WP_MAX_MEMORY_LIMIT ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Permalink Structure:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo '' !== $wp_rewrite->permalink_structure ? esc_html( $wp_rewrite->permalink_structure ) : esc_html__( 'Plain', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Language:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( get_bloginfo( 'language' ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Debug Mode Enabled:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php WP_DEBUG ? esc_html_e( 'Yes', 'getbowtied-kits-templates-and-patterns' ) : esc_html_e( 'No', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Script Debug Mode Enabled:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php SCRIPT_DEBUG ? esc_html_e( 'Yes', 'getbowtied-kits-templates-and-patterns' ) : esc_html_e( 'No', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'GetBowtied Import Demo Content Version:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( GETBOWTIED_IDC_VERSION ); ?></td>
			<td></td>
		</tr>
		</tbody>
	</table>


	<table class="kits-templates-and-patterns-status-table widefat">
		<thead>
		<tr>
			<th><?php esc_html_e( 'Theme Info', 'getbowtied-kits-templates-and-patterns' ); ?></th>
			<th></th>
			<th></th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td><?php esc_html_e( 'Name:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( $theme->get( 'Name' ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Version:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( $theme->get( 'Version' ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Author:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( $theme->get( 'Author' ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Author URL:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php echo esc_html( $theme->get( 'AuthorURI' ) ); ?></td>
			<td></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Child Theme:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td><?php is_child_theme() ? esc_html_e( 'Yes', 'getbowtied-kits-templates-and-patterns' ) : esc_html_e( 'No', 'getbowtied-kits-templates-and-patterns' ); ?></td>
			<td>
				<?php
				if ( ! is_child_theme() ) :
					printf(
					/* translators: 1. Opening anchor tag for GetBowtied Child Tutorial, 2. Closing the anchor tag */
						esc_html__( 'If you want to modify the features of the theme then, we recommend you to use %1$s child theme. %2$s', 'getbowtied-kits-templates-and-patterns' ),
						'<a href="https://getbowtied.com/blog/tutorial-creating-wordpress-child-theme/" target="_blank">',
						'</a>'
					);
				endif;
				?>
			</td>
		</tr>
		<?php if ( is_child_theme() ) : ?>
			<tr>
				<td><?php esc_html_e( 'Parent Theme Name:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
				<td><?php echo esc_html( $theme->parent()->get( 'Name' ) ); ?></td>
				<td></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Parent Theme Version:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
				<td><?php echo esc_html( $theme->parent()->get( 'Version' ) ); ?></td>
				<td></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Parent Theme Author:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
				<td><?php echo esc_html( $theme->parent()->get( 'Author' ) ); ?></td>
				<td></td>
			</tr>
			<tr>
				<td><?php esc_html_e( 'Parent Theme Author URL:', 'getbowtied-kits-templates-and-patterns' ); ?></td>
				<td><?php echo esc_html( $theme->parent()->get( 'AuthorURI' ) ); ?></td>
				<td></td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>


	<table class="kits-templates-and-patterns-status-table widefat">
		<thead>
		<tr>
			<th><?php esc_html_e( 'Active Plugins', 'getbowtied-kits-templates-and-patterns' ); ?></th>
			<th></th>
			<th></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$active_plugin_lists = GETBOWTIED_Demo_Importer_Status::get_active_plugins();

		// Display the active plugin lists.
		foreach ( $active_plugin_lists as $active_plugin_list ) :
			echo '<tr>';
			echo '<td>';
			if ( $active_plugin_list['PluginURI'] ) :
				$plugin_name = '<a href="' . $active_plugin_list['PluginURI'] . '" target="_blank">' . $active_plugin_list['Name'] . '</a>';
			else :
				$plugin_name = $active_plugin_list['Name'];
			endif;

			if ( $active_plugin_list['Version'] ) :
				$plugin_name .= ' - ' . $active_plugin_list['Version'];
			endif;

			echo $plugin_name;
			echo '</td>';

			echo '<td>';
			if ( $active_plugin_list['Author'] ) :
				printf(
					/* translators: 1. Plugin author name. */
					esc_html__( 'By %s', 'getbowtied-kits-templates-and-patterns' ),
					esc_html( $active_plugin_list['Author'] )
				);
			endif;
			echo '</td>';

			echo '<td></td>';
			echo '</tr>';
		endforeach;
		?>
		</tbody>
	</table>


	<table class="kits-templates-and-patterns-status-table widefat">
		<thead>
		<tr>
			<th><?php esc_html_e( 'Inactive Plugins', 'getbowtied-kits-templates-and-patterns' ); ?></th>
			<th></th>
			<th></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$inactive_plugin_lists = GETBOWTIED_Demo_Importer_Status::get_inactive_plugins();

		// Display the inactive plugin lists.
		foreach ( $inactive_plugin_lists as $inactive_plugin_list ) :
			echo '<tr>';
			echo '<td>';
			if ( $inactive_plugin_list['PluginURI'] ) :
				$plugin_name = '<a href="' . $inactive_plugin_list['PluginURI'] . '" target="_blank">' . $inactive_plugin_list['Name'] . '</a>';
			else :
				$plugin_name = $inactive_plugin_list['Name'];
			endif;

			if ( $inactive_plugin_list['Version'] ) :
				$plugin_name .= ' - ' . $inactive_plugin_list['Version'];
			endif;

			echo $plugin_name;
			echo '</td>';

			echo '<td>';
			if ( $inactive_plugin_list['Author'] ) :
				printf(
				/* translators: 1. Plugin author name. */
					esc_html__( 'By %s', 'getbowtied-kits-templates-and-patterns' ),
					esc_html( $inactive_plugin_list['Author'] )
				);
			endif;
			echo '</td>';

			echo '<td></td>';
			echo '</tr>';
		endforeach;
		?>
		</tbody>
	</table>


	<h2><?php esc_html_e( 'Copy &amp; Paste', 'getbowtied-kits-templates-and-patterns' ); ?></h2>

	<div class="kits-templates-and-patterns-status-report">
		<p><?php esc_html_e( 'While creating support request, please provide us the details generated below within the support request. It might help us to tackle on the issue more conviniently.', 'getbowtied-kits-templates-and-patterns' ); ?></p>
		<div id="system-status-report">
			<textarea readonly="readonly"></textarea>
		</div>

		<p class="submit">
			<button id="copy-system-status-report" class="button-primary">
				<?php esc_html_e( 'Copy System Status', 'getbowtied-kits-templates-and-patterns' ); ?>
			</button>
		</p>
	</div>
</div>
