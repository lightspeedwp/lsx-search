<div class="uix-field-wrapper">
	<ul class="ui-tab-nav">
		<?php if ( class_exists( 'LSX_Banners' ) ) { ?>
			<li><a href="#ui-placeholders" class="active"><?php esc_html_e( 'Placeholders', 'lsx-search' ); ?></a></li>
		<?php } ?>

		<?php if ( class_exists( 'LSX_Currencies' ) ) { ?>
			<?php $class_active = class_exists( 'LSX_Banners' ) ? '' : 'active' ?>
			<li><a href="#ui-currencies" class="<?php echo esc_attr( $class_active ) ?>"><?php esc_html_e( 'Currencies', 'lsx-search' ); ?></a></li>
		<?php } ?>

		<?php if ( class_exists( 'LSX_Team' ) ) { ?>
			<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) ) ? '' : 'active' ?>
			<li><a href="#ui-team" class="<?php echo esc_attr( $class_active ) ?>"><?php esc_html_e( 'Team', 'lsx-search' ); ?></a></li>
		<?php } ?>

		<?php if ( class_exists( 'LSX_Testimonials' ) ) { ?>
			<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) || class_exists( 'LSX_Team' ) ) ? '' : 'active' ?>
			<li><a href="#ui-testimonials" class="<?php echo esc_attr( $class_active ) ?>"><?php esc_html_e( 'Testimonials', 'lsx-search' ); ?></a></li>
		<?php } ?>

		<?php if ( class_exists( 'LSX_Projects' ) ) { ?>
			<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) || class_exists( 'LSX_Team' ) || class_exists( 'LSX_Testimonials' ) ) ? '' : 'active' ?>
			<li><a href="#ui-projects" class="<?php echo esc_attr( $class_active ) ?>"><?php esc_html_e( 'Projects', 'lsx-search' ); ?></a></li>
		<?php } ?>

		<?php if ( class_exists( 'LSX_Services' ) ) { ?>
			<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) || class_exists( 'LSX_Team' ) || class_exists( 'LSX_Testimonials' ) || class_exists( 'LSX_Projects' ) ) ? '' : 'active' ?>
			<li><a href="#ui-services" class="<?php echo esc_attr( $class ) ?>"><?php esc_html_e( 'Services', 'lsx-search' ); ?></a></li>
		<?php $class = ''; } ?>

		<?php if ( class_exists( 'LSX_Blog_Customizer' ) ) { ?>
			<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) || class_exists( 'LSX_Team' ) || class_exists( 'LSX_Testimonials' ) || class_exists( 'LSX_Projects' ) || class_exists( 'LSX_Services' ) ) ? '' : 'active' ?>
			<li><a href="#ui-blog-customizer" class="<?php echo esc_attr( $class_active ) ?>"><?php esc_html_e( 'Blog Customizer (posts widget)', 'lsx-search' ); ?></a></li>
		<?php } ?>

		<?php if ( class_exists( 'LSX_Sharing' ) ) { ?>
			<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) || class_exists( 'LSX_Team' ) || class_exists( 'LSX_Testimonials' ) || class_exists( 'LSX_Projects' ) || class_exists( 'LSX_Services' ) || class_exists( 'LSX_Blog_Customizer' ) ) ? '' : 'active' ?>
			<li><a href="#ui-sharing" class="<?php echo esc_attr( $class_active ) ?>"><?php esc_html_e( 'Sharing', 'lsx-search' ); ?></a></li>
		<?php } ?>

		<?php if ( class_exists( 'LSX_Videos' ) ) { ?>
			<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) || class_exists( 'LSX_Team' ) || class_exists( 'LSX_Testimonials' ) || class_exists( 'LSX_Projects' ) || class_exists( 'LSX_Services' ) || class_exists( 'LSX_Blog_Customizer' ) || class_exists( 'LSX_Sharing' ) ) ? '' : 'active' ?>
			<li><a href="#ui-videos" class="<?php echo esc_attr( $class_active ) ?>"><?php esc_html_e( 'Videos', 'lsx-search' ); ?></a></li>
		<?php } ?>

		<?php do_action( 'lsx_framework_display_tab_headings_bottom', 'display' ); ?>
	</ul>

	<?php if ( class_exists( 'LSX_Banners' ) ) { ?>
		<div id="ui-placeholders" class="ui-tab active">
			<table class="form-table">
				<tbody>
					<?php do_action( 'lsx_framework_display_tab_content', 'placeholders' ); ?>
				</tbody>
			</table>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'LSX_Currencies' ) ) { ?>
		<?php $class_active = class_exists( 'LSX_Banners' ) ? '' : 'active' ?>
		<div id="ui-currencies" class="ui-tab <?php echo esc_attr( $class_active ) ?>">
			<table class="form-table">
				<tbody>
					<?php do_action( 'lsx_framework_display_tab_content', 'currency_switcher' ); ?>
				</tbody>
			</table>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'LSX_Team' ) ) { ?>
		<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) ) ? '' : 'active' ?>
		<div id="ui-team" class="ui-tab <?php echo esc_attr( $class_active ) ?>">
			<table class="form-table">
				<tbody>
					<?php do_action( 'lsx_framework_display_tab_content', 'team' ); ?>
				</tbody>
			</table>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'LSX_Testimonials' ) ) { ?>
		<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) || class_exists( 'LSX_Team' ) ) ? '' : 'active' ?>
		<div id="ui-testimonials" class="ui-tab <?php echo esc_attr( $class_active ) ?>">
			<table class="form-table">
				<tbody>
					<?php do_action( 'lsx_framework_display_tab_content', 'testimonials' ); ?>
				</tbody>
			</table>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'LSX_Projects' ) ) { ?>
		<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) || class_exists( 'LSX_Team' ) || class_exists( 'LSX_Testimonials' ) ) ? '' : 'active' ?>
		<div id="ui-projects" class="ui-tab <?php echo esc_attr( $class_active ) ?>">
			<table class="form-table">
				<tbody>
					<?php do_action( 'lsx_framework_display_tab_content', 'projects' ); ?>
				</tbody>
			</table>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'LSX_Services' ) ) { ?>
		<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) || class_exists( 'LSX_Team' ) || class_exists( 'LSX_Testimonials' ) || class_exists( 'LSX_Projects' ) ) ? '' : 'active' ?>
		<div id="ui-services" class="ui-tab <?php echo esc_attr( $class_active ) ?>">
			<table class="form-table">
				<tbody>
					<?php do_action( 'lsx_framework_display_tab_content', 'services' ); ?>
				</tbody>
			</table>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'LSX_Blog_Customizer' ) ) { ?>
		<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) || class_exists( 'LSX_Team' ) || class_exists( 'LSX_Testimonials' ) || class_exists( 'LSX_Projects' ) || class_exists( 'LSX_Services' ) ) ? '' : 'active' ?>
		<div id="ui-blog-customizer" class="ui-tab <?php echo esc_attr( $class_active ) ?>">
			<table class="form-table">
				<tbody>
					<?php do_action( 'lsx_framework_display_tab_content', 'blog-customizer' ); ?>
				</tbody>
			</table>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'LSX_Sharing' ) ) { ?>
		<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) || class_exists( 'LSX_Team' ) || class_exists( 'LSX_Testimonials' ) || class_exists( 'LSX_Projects' ) || class_exists( 'LSX_Services' ) || class_exists( 'LSX_Blog_Customizer' ) ) ? '' : 'active' ?>
		<div id="ui-sharing" class="ui-tab <?php echo esc_attr( $class_active ) ?>">
			<table class="form-table">
				<tbody>
					<?php do_action( 'lsx_framework_display_tab_content', 'sharing' ); ?>
				</tbody>
			</table>
		</div>
	<?php } ?>

	<?php if ( class_exists( 'LSX_Videos' ) ) { ?>
		<?php $class_active = ( class_exists( 'LSX_Banners' ) || class_exists( 'LSX_Currencies' ) || class_exists( 'LSX_Team' ) || class_exists( 'LSX_Testimonials' ) || class_exists( 'LSX_Projects' ) || class_exists( 'LSX_Services' ) || class_exists( 'LSX_Blog_Customizer' ) || class_exists( 'LSX_Sharing' ) ) ? '' : 'active' ?>
		<div id="ui-videos" class="ui-tab <?php echo esc_attr( $class_active ) ?>">
			<table class="form-table">
				<tbody>
					<?php do_action( 'lsx_framework_display_tab_content', 'videos' ); ?>
				</tbody>
			</table>
		</div>
	<?php } ?>

	<?php do_action( 'lsx_framework_display_tab_bottom', 'display' ); ?>
</div>
