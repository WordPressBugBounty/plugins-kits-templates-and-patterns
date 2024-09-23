<?php

add_action(
    'in_admin_footer',
    function() {
        wp_enqueue_script( 'jquery' );
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
    
		if (
			! get_user_meta(
				get_current_user_id(),
				'getbowtied-ktp-welcome-pointer-dismissed',
				true
			)
		):
			
		?>
			<style>
				.getbowtied-ktp-welcome-pointer {

				}
				/*.getbowtied-ktp-welcome-pointer h3 {
					background: #ff0000;
					border-color: #ff0000;
				}
				.getbowtied-ktp-welcome-pointer h3:before {
					color: #ff0000;
				}*/
			</style>

			<script>
			jQuery(
				function() {
					jQuery('#menu-appearance').pointer( 
						{
							content:
								"<h3>Kits, Templates and Patterns<\/h3>" +
								"<p>Effortlessly import engaging templates for a vibrant website foundation.</p>" +
								'<p><a href="<?php echo esc_url(admin_url('themes.php?page=kits-templates-and-patterns')); ?>" class="button button-primary button-large">View the Templates</a></p>',


							position:
								{
									edge:  'left',
									align: 'center'
								},

							pointerClass:
								'getbowtied-ktp-welcome-pointer',

							//pointerWidth: 800,
							
							close: function() {
								/*jQuery.post(
									ajaxurl,
									{
										pointer: 'getbowtied-ktp-welcome-pointer',
										action: 'dismiss-getbowtied-ktp-welcome-pointer',
									}
								);*/
							},

						}
					).pointer('open');
				}
			);
			</script>

		<?php
		endif;
	}
);

add_action(
	'admin_init',
	function() {

		if ( isset( $_POST['action'] ) && 'dismiss-getbowtied-ktp-welcome-pointer' == $_POST['action'] ) {

			update_user_meta(
				get_current_user_id(),
				'getbowtied-ktp-welcome-pointer-dismissed',
				$_POST['pointer'],
				true
			);
		}
	}
);