<?php
/**
 * Admin View: Page - Status
 *
 * @package GetBowtied_Import_Demo_Content
 */

defined( 'ABSPATH' ) || exit;

// Tabs on status page.
$current_tab = ! empty( $_REQUEST['tab'] ) ? sanitize_title( $_REQUEST['tab'] ) : 'status';
$tabs        = array(
	'status' => esc_html__( 'System Status', 'getbowtied-kits-templates-and-patterns' ),
	'faq'    => esc_html__( 'FAQ\'s', 'getbowtied-kits-templates-and-patterns' ),
);
$tabs        = apply_filters( 'getbowtied_demo_importer_status_tabs', $tabs );
?>
<div class="wrap kits-templates-and-patterns-status">
	<nav class="nav-tab-wrapper">
		<?php
		foreach ( $tabs as $name => $label ) {
			echo '<a href="' . admin_url( 'themes.php?page=kits-templates-and-patterns-status&tab=' . $name ) . '" class="nav-tab ';
			if ( $current_tab == $name ) {
				echo 'nav-tab-active';
			}
			echo '">' . $label . '</a>';
		}
		?>
	</nav>
	<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
	<?php
	switch ( $current_tab ) {
		case 'status':
			GETBOWTIED_Demo_Importer_Status::system_status();
			break;

		case 'faq':
			GETBOWTIED_Demo_Importer_Status::demo_import_faqs();
			break;

		default:
			do_action( 'getbowtied_demo_importer_status_content_' . $current_tab );
			break;
	}
	?>
</div>
