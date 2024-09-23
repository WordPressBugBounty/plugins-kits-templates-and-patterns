<?php
/**
 * Admin View: Page - Importer
 *
 * @package GetBowtied_Import_Demo_Content
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wrap kits-templates-and-patterns">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Kits, Templates and Patterns', 'getbowtied-kits-templates-and-patterns' ); ?></h1>

	<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'refresh-demo-packages', 'true' ), 'refresh_demo_packages', '_refresh_demo_packages_nonce' ) ); ?>"
	   class="page-title-action"
	   title="<?php esc_html_e( 'If you do not see the new demos on the list, please click this button to fetch all the available demos.', 'getbowtied-kits-templates-and-patterns' ); ?>"
	>
		<?php esc_html_e( 'Refresh Items', 'getbowtied-kits-templates-and-patterns' ); ?>
	</a>

	<?php if ( apply_filters( 'getbowtied_demo_importer_upcoming_demos', false ) ) : ?>
		<a href="<?php echo esc_url( 'https://getbowtied.com/upcoming-demos' ); ?>" class="page-title-action" target="_blank"><?php esc_html_e( 'Upcoming Demos', 'getbowtied-kits-templates-and-patterns' ); ?></a>
	<?php endif; ?>

	<hr class="wp-header-end">

	<div class="error hide-if-js">
		<p><?php esc_html_e( 'The Demo Importer screen requires JavaScript.', 'getbowtied-kits-templates-and-patterns' ); ?></p>
	</div>

	<h2 class="screen-reader-text hide-if-no-js"><?php esc_html_e( 'Filter demos list', 'getbowtied-kits-templates-and-patterns' ); ?></h2>

	<div class="wp-filter hide-if-no-js">
		<div class="filter-section">
			<div class="filter-count">
				<span class="count theme-count demo-count"></span>
			</div>

			<?php if ( ! empty( $this->demo_packages->categories ) ) : ?>
				<ul class="filter-links categories">
					<?php foreach ( $this->demo_packages->categories as $slug => $label ) : ?>
						<?php if ( $slug === 'all' || $slug === get_option('template') ) : ?>
							<li><a href="#" data-sort="<?php echo esc_attr( $slug ); ?>" class="category-tab"><?php echo esc_html( $label ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<div class="filter-section right">
			<?php if ( ! empty( $this->demo_packages->pagebuilders ) ) : ?>
				<ul class="filter-links pagebuilders">
					<?php foreach ( $this->demo_packages->pagebuilders as $slug => $label ) : ?>
						<?php if ( 'default' !== $slug ) : ?>
							<li><a href="#" data-type="<?php echo esc_attr( $slug ); ?>" class="pagebuilder-tab"><?php echo esc_html( $label ); ?></a></li>
						<?php else : ?>
							<li><a href="#" data-type="<?php echo esc_attr( $slug ); ?>" class="pagebuilder-tab tips" data-tip="<?php esc_attr_e( 'Without Page Builder', 'getbowtied-kits-templates-and-patterns' ); ?>"><?php echo esc_html( $label ); ?></a></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<form class="search-form"></form>
		</div>
	</div>
	<h2 class="screen-reader-text hide-if-no-js"><?php esc_html_e( 'Themes list', 'getbowtied-kits-templates-and-patterns' ); ?></h2>
	<div class="theme-browser content-filterable"></div>
	<div class="theme-install-overlay wp-full-overlay expanded"></div>

	<p class="no-themes"><?php esc_html_e( 'No demos found. Try a different search.', 'getbowtied-kits-templates-and-patterns' ); ?></p>
	<span class="spinner"></span>
</div>

<script id="tmpl-demo" type="text/template">
	<# if ( data.screenshot_url ) { #>
		<div class="theme-screenshot">
			<img src="{{ data.screenshot_url }}" alt="" />
		</div>
	<# } else { #>
		<div class="theme-screenshot blank"></div>
	<# } #>

	<# if ( data.isPremium ) { #>
		<span class="premium-demo-banner"><?php esc_html_e( 'Premium', 'getbowtied-kits-templates-and-patterns' ); ?></span>
	<# } #>

	<# if ( data.isPro && data.isAllThemePlan ) { #>
		<span class="premium-demo-banner"><?php esc_html_e( 'Pro Plus', 'getbowtied-kits-templates-and-patterns' ); ?></span>
	<# } #>

	<# if ( data.isPro && ! data.isAllThemePlan ) { #>
		<span class="premium-demo-banner"><?php esc_html_e( 'Pro', 'getbowtied-kits-templates-and-patterns' ); ?></span>
	<# } #>

	<div class="theme-author">
		<?php
		/* translators: %s: Demo author name */
		printf( esc_html__( 'By %s', 'getbowtied-kits-templates-and-patterns' ), '{{{ data.author }}}' );
		?>
	</div>

	<div class="theme-id-container">
		<# if ( data.active ) { #>
			<h2 class="theme-name" id="{{ data.id }}-name">
				<?php
				/* translators: %s: Demo name */
				printf( __( '<span>Imported:</span> %s', 'getbowtied-kits-templates-and-patterns' ), '{{{ data.name }}}' ); // @codingStandardsIgnoreLine
				?>
			</h2>
		<# } else { #>
			<h2 class="theme-name" id="{{ data.id }}-name">{{{ data.name }}}</h2>
		<# } #>

		<div class="theme-actions">
			<# if ( data.active ) { #>
				<a class="button button-primary live-preview" target="_blank" href="<?php echo esc_url( get_site_url( null, '/' ) ); ?>"><?php esc_html_e( 'Live Preview', 'getbowtied-kits-templates-and-patterns' ); ?></a>
			<# } else { #>
				<# if ( data.isPremium ) { #>
					<a class="button button-primary purchase-now" href="{{ data.homepage }}" target="_blank"><?php esc_html_e( 'Buy Now', 'getbowtied-kits-templates-and-patterns' ); ?></a>
				<# } else if ( data.isPro ) { #>
					<a class="button button-primary purchase-now" href="{{ data.homepage }}" target="_blank"><?php esc_html_e( 'Buy Now', 'getbowtied-kits-templates-and-patterns' ); ?></a>
				<# } else if ( data.isAllThemePlan ) { #>
					<a class="button button-primary purchase-now" href="{{ data.homepage }}" target="_blank"><?php esc_html_e( 'Upgrade Theme Plan', 'getbowtied-kits-templates-and-patterns' ); ?></a>
				<# } else if ( data.requiredVersion ) { #>
					<a class="button button-primary" href="<?php echo current_user_can( 'update_themes' ) ? esc_url( admin_url( '/update-core.php' ) ) : '#'; ?>" title="{{ data.updateThemeNotice }}" target="_blank"><?php esc_html_e( 'Update', 'getbowtied-kits-templates-and-patterns' ); ?></a>
				<# } else { #>
					<?php
					/* translators: %s: Demo name */
					$aria_label = sprintf( esc_html_x( 'Import %s', 'demo', 'getbowtied-kits-templates-and-patterns' ), '{{ data.name }}' );
					?>
					<!--<a class="button button-primary hide-if-no-js demo-import" href="#" data-name="{{ data.name }}" data-slug="{{ data.id }}" aria-label="<?php echo esc_attr( $aria_label ); ?>" data-plugins="{{ JSON.stringify( data.plugins ) }}"><?php esc_html_e( 'Import', 'getbowtied-kits-templates-and-patterns' ); ?></a>-->
				<# } #>
				<button class="button preview install-demo-preview"><?php esc_html_e( 'Preview', 'getbowtied-kits-templates-and-patterns' ); ?></button>
			<# } #>
		</div>
	</div>

	<# if ( data.imported ) { #>
		<div class="notice notice-success notice-alt"><p><?php echo esc_html_x( 'Imported', 'demo', 'getbowtied-kits-templates-and-patterns' ); ?></p></div>
	<# } #>
</script>

<script id="tmpl-demo-preview" type="text/template">
	<div class="wp-full-overlay-sidebar">
		<div class="wp-full-overlay-header">
			<button class="close-full-overlay"><span class="screen-reader-text"><?php esc_html_e( 'Close', 'getbowtied-kits-templates-and-patterns' ); ?></span></button>
			<button class="previous-theme"><span class="screen-reader-text"><?php echo esc_html_x( 'Previous', 'Button label for a demo', 'getbowtied-kits-templates-and-patterns' ); ?></span></button>
			<button class="next-theme"><span class="screen-reader-text"><?php echo esc_html_x( 'Next', 'Button label for a demo', 'getbowtied-kits-templates-and-patterns' ); ?></span></button>
			<# if ( data.isPremium ) { #>
				<a class="button button-primary purchase-now" href="{{ data.homepage }}" target="_blank"><?php esc_html_e( 'Buy Now', 'getbowtied-kits-templates-and-patterns' ); ?></a>
			<# } else if ( data.isPro ) { #>
				<a class="button button-primary purchase-now" href="{{ data.homepage }}" target="_blank"><?php esc_html_e( 'Buy Now', 'getbowtied-kits-templates-and-patterns' ); ?></a>
			<# } else if ( data.isAllThemePlan ) { #>
				<a class="button button-primary purchase-now" href="{{ data.homepage }}" target="_blank"><?php esc_html_e( 'Upgrade Theme Plan', 'getbowtied-kits-templates-and-patterns' ); ?></a>
			<# } else if ( data.requiredTheme ) { #>
				<button class="button button-primary hide-if-no-js disabled"><?php esc_html_e( 'Import', 'getbowtied-kits-templates-and-patterns' ); ?></button>
			<# } else if ( data.requiredVersion ) { #>
				<a class="button button-primary" href="<?php echo current_user_can( 'update_themes' ) ? esc_url( admin_url( '/update-core.php' ) ) : '#'; ?>" title="{{ data.updateThemeNotice }}" target="_blank"><?php esc_html_e( 'Update', 'getbowtied-kits-templates-and-patterns' ); ?></a>
			<# } else { #>
				<# if ( data.active ) { #>
					<a class="button button-primary live-preview" target="_blank" href="<?php echo esc_url( get_site_url( null, '/' ) ); ?>"><?php esc_html_e( 'Live Preview', 'getbowtied-kits-templates-and-patterns' ); ?></a>
				<# } else { #>
					<a class="button button-primary hide-if-no-js demo-import" href="#" data-name="{{ data.name }}" data-slug="{{ data.id }}"><?php esc_html_e( 'Import', 'getbowtied-kits-templates-and-patterns' ); ?></a>
				<# } #>
			<# } #>
		</div>
		<div class="wp-full-overlay-sidebar-content">
			<div class="install-theme-info">
				<h3 class="theme-name">
					{{ data.name }}
					<# if ( data.isPremium ) { #>
						<span class="premium-demo-tag"><?php esc_html_e( 'Premium', 'getbowtied-kits-templates-and-patterns' ); ?></span>
					<# } #>

					<# if ( data.isPro && data.isAllThemePlan ) { #>
						<span class="premium-demo-tag"><?php esc_html_e( 'Pro Plus', 'getbowtied-kits-templates-and-patterns' ); ?></span>
					<# } #>

					<# if ( data.isPro && ! data.isAllThemePlan ) { #>
						<span class="premium-demo-tag"><?php esc_html_e( 'Pro', 'getbowtied-kits-templates-and-patterns' ); ?></span>
					<# } #>
				</h3>

				<span class="theme-by">
					<?php
					/* translators: %s: Demo author name */
					printf( esc_html__( 'By %s', 'getbowtied-kits-templates-and-patterns' ), '{{ data.author }}' );
					?>
				</span>

				<img class="theme-screenshot" src="{{ data.screenshot_url }}" alt="" />

				<div class="theme-details">
					<# if ( data.requiredTheme ) { #>
						<div class="demo-message notice notice-error notice-alt"><p>
							
							<?php
							/* translators: %s: Theme Name */
							printf( esc_html__( 'This package requires the %s theme to be installed and activated.', 'getbowtied-kits-templates-and-patterns' ), '<strong>{{{ data.theme_name }}}</strong>' );
							?>

							<br /><br />

							<?php
							/* translators: %s: Theme Name */
							printf( esc_html__( 'The %s theme is not currently active!', 'getbowtied-kits-templates-and-patterns' ), '<strong>{{{ data.theme_name }}}</strong>' );
							?>

							<br /><br /><a aria-label="Get the Theme" class="button button-primary" href="{{{ data.homepage }}}" target="_blank">Get the Theme</a>
						</p></div>
					<# } #>
					<div class="theme-description">{{{ data.description }}}</div>
				</div>

				<div class="plugins-details">
					<h4 class="plugins-info"><?php esc_html_e( 'Plugins Information', 'getbowtied-kits-templates-and-patterns' ); ?></h4>

					<table class="plugins-list-table widefat striped">
						<thead>
							<tr>
								<th scope="col" class="manage-column required-plugins" colspan="2"><?php esc_html_e( 'Required Plugins', 'getbowtied-kits-templates-and-patterns' ); ?></th>
							</tr>
						</thead>
						<tbody id="the-list">
							<# if ( ! _.isEmpty( data.plugins ) ) { #>
								<# _.each( data.plugins, function( plugin, slug ) { #>
									<tr class="plugin<# if ( ! plugin.is_active ) { #> inactive<# } #>" data-slug="{{ slug }}" data-plugin="{{ plugin.slug }}" data-name="{{ plugin.name }}" data-url="{{ plugin.url }}" data-home="{{ plugin.home }}">
										<td class="plugin-name">
											<# if ( _.isEmpty( plugin.home ) ) { #>
												<a href="<?php printf( esc_url( 'https://wordpress.org/plugins/%s' ), '{{ slug }}' ); ?>" target="_blank">{{ plugin.name }}</a>
											<# } else { #>
												<a href="<?php esc_url(printf( '%s', '{{ plugin.home }}' )); ?>" target="_blank">{{ plugin.name }}</a>
											<# } #>
										</td>
										<td class="plugin-status">
											<# if ( plugin.is_active && plugin.is_install ) { #>
												<span class="active"></span>
											<# } else if ( plugin.is_install ) { #>
												<span class="activate-now<# if ( ! data.requiredPlugins ) { #> active<# } #>"></span>
											<# } else { #>
												<span class="install-now<# if ( ! data.requiredPlugins ) { #> active<# } #>"></span>
											<# } #>
										</td>
									</tr>
								<# }); #>
							<# } else { #>
								<tr class="no-items">
									<td class="colspanchange" colspan="4"><?php esc_html_e( 'No plugins are required for this demo.', 'getbowtied-kits-templates-and-patterns' ); ?></td>
								</tr>
							<# } #>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="wp-full-overlay-footer">
			<div class="demo-import-actions">
				<# if ( data.isPremium ) { #>
					<a class="button button-hero button-primary purchase-now" href="{{ data.homepage }}" target="_blank"><?php esc_html_e( 'Buy Now', 'getbowtied-kits-templates-and-patterns' ); ?></a>
				<# } else if ( data.isPro ) { #>
					<a class="button button-hero button-primary purchase-now" href="{{ data.homepage }}" target="_blank"><?php esc_html_e( 'Buy Now', 'getbowtied-kits-templates-and-patterns' ); ?></a>
				<# } else if ( data.isAllThemePlan ) { #>
					<a class="button button-hero button-primary purchase-now" href="{{ data.homepage }}" target="_blank"><?php esc_html_e( 'Upgrade Theme Plan', 'getbowtied-kits-templates-and-patterns' ); ?></a>
				<# } else if ( data.requiredTheme ) { #>
					<button class="button button-hero button-primary hide-if-no-js disabled"><?php esc_html_e( 'Import', 'getbowtied-kits-templates-and-patterns' ); ?></button>
				<# } else if ( data.requiredVersion ) { #>
					<a class="button button-hero button-primary" href="<?php echo current_user_can( 'update_themes' ) ? esc_url( admin_url( '/update-core.php' ) ) : '#'; ?>" title="{{ data.updateThemeNotice }}" target="_blank"><?php esc_html_e( 'Update', 'getbowtied-kits-templates-and-patterns' ); ?></a>
				<# } else { #>
					<# if ( data.active ) { #>
						<a class="button button-primary live-preview button-hero hide-if-no-js" target="_blank" href="<?php echo esc_url( get_site_url( null, '/' ) ); ?>"><?php esc_html_e( 'Live Preview', 'getbowtied-kits-templates-and-patterns' ); ?></a>
					<# } else { #>
						<a class="button button-hero button-primary hide-if-no-js demo-import" href="#" data-name="{{ data.name }}" data-slug="{{ data.id }}"><?php esc_html_e( 'Import', 'getbowtied-kits-templates-and-patterns' ); ?></a>
					<# } #>
				<# } #>
			</div>
			<button type="button" class="collapse-sidebar button" aria-expanded="true" aria-label="<?php esc_attr_e( 'Collapse Sidebar', 'getbowtied-kits-templates-and-patterns' ); ?>">
				<span class="collapse-sidebar-arrow"></span>
				<span class="collapse-sidebar-label"><?php esc_html_e( 'Collapse', 'getbowtied-kits-templates-and-patterns' ); ?></span>
			</button>
			<div class="devices-wrapper">
				<div class="devices">
					<button type="button" class="preview-desktop active" aria-pressed="true" data-device="desktop">
						<span class="screen-reader-text"><?php esc_html_e( 'Enter desktop preview mode', 'getbowtied-kits-templates-and-patterns' ); ?></span>
					</button>
					<button type="button" class="preview-tablet" aria-pressed="false" data-device="tablet">
						<span class="screen-reader-text"><?php esc_html_e( 'Enter tablet preview mode', 'getbowtied-kits-templates-and-patterns' ); ?></span>
					</button>
					<button type="button" class="preview-mobile" aria-pressed="false" data-device="mobile">
						<span class="screen-reader-text"><?php esc_html_e( 'Enter mobile preview mode', 'getbowtied-kits-templates-and-patterns' ); ?></span>
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="wp-full-overlay-main">
		<iframe src="{{ data.preview_url }}" title="<?php esc_attr_e( 'Preview', 'getbowtied-kits-templates-and-patterns' ); ?>"></iframe>
	</div>
</script>

<?php
wp_print_request_filesystem_credentials_modal();
wp_print_admin_notice_templates();
getbowtied_print_admin_notice_templates();
