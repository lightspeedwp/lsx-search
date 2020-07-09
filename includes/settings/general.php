<div class="uix-field-wrapper">
	<ul class="ui-tab-nav">
		<?php if ( class_exists( 'LSX_Currencies' ) ) { ?>
			<li><a href="#ui-currencies" class="active"><?php esc_html_e( 'Currencies', 'lsx-search' ); ?></a></li>
		<?php } ?>
	</ul>

	<?php if ( class_exists( 'LSX_Currencies' ) ) { ?>
		<div id="ui-currencies" class="ui-tab active">
			<table class="form-table">
				<tbody>
					<?php do_action( 'lsx_framework_dashboard_tab_content', 'currency_switcher' ); ?>
				</tbody>
			</table>
		</div>
	<?php } ?>

	<?php do_action( 'lsx_framework_dashboard_tab_bottom', 'general' ); ?>
</div>
