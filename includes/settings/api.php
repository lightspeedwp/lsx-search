<div class="uix-field-wrapper">
	<ul class="ui-tab-nav">
		<li><a href="#ui-settings" class="active"><?php esc_html_e( 'Settings', 'lsx-search' ); ?></a></li>
		<li><a href="#ui-keys"><?php esc_html_e( 'License Keys', 'lsx-search' ); ?></a></li>
	</ul>

	<div id="ui-settings" class="ui-tab active">
		<table class="form-table" style="margin-top:-13px !important;">
			<tbody>
				<?php do_action( 'lsx_framework_api_tab_content', 'settings' ); ?>
			</tbody>
		</table>
	</div>

	<div id="ui-keys" class="ui-tab">
		<table class="form-table" style="margin-top:-13px !important;">
			<tbody>
			<?php
				$api_keys_content = false;
				ob_start();
				do_action( 'lsx_framework_api_tab_content', 'api' );
				$api_keys_content = ob_end_clean();
				if ( false !== $api_keys_content ) {
					?>
						<p class="info"><?php esc_html_e( 'Enter the license keys for your add-ons in the boxes below.', 'lsx-search' ); ?></p>
					<?php
					do_action( 'lsx_framework_api_tab_content', 'api' );
				} else {
					?>
					<p class="info"><?php esc_html_e( 'You have not installed any add-ons yet. View our list of add-ons', 'lsx-search' ); ?> <a href="<?php echo esc_url( admin_url( 'themes.php' ) ); ?>?page=lsx-welcome"><?php esc_html_e( 'here', 'lsx-search' ); ?></a>.</p>
				<?php }	?>
			</tbody>
		</table>
	</div>

	<?php do_action( 'lsx_framework_api_tab_bottom', 'api' ); ?>
</div>
