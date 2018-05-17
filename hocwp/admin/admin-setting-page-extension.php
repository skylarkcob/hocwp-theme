<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function hocwp_theme_settings_page_extension_tab( $tabs ) {
	$tabs['extension'] = array(
		'text' => __( 'Extensions', 'hocwp-theme' ),
		'icon' => '<span class="dashicons dashicons-admin-plugins"></span>'
	);

	return $tabs;
}

add_filter( 'hocwp_theme_settings_page_tabs', 'hocwp_theme_settings_page_extension_tab' );

global $hocwp_theme;

if ( 'extension' != $hocwp_theme->option->tab ) {
	return;
}

add_filter( 'hocwp_theme_settings_page_extension_display_form', '__return_false' );

require HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-extensions-list-table.php';

function hocwp_theme_settings_page_extension_field() {
	$fields = array();

	return $fields;
}

add_filter( 'hocwp_theme_settings_page_general_settings_field', 'hocwp_theme_settings_page_extension_field' );

function hocwp_theme_settings_page_extension_admin_menu() {
	global $hocwp_theme;
	add_action( "load-{$hocwp_theme->option->hook_suffix}", 'hocwp_theme_settings_page_extension_screen_options' );
}

add_action( 'admin_menu', 'hocwp_theme_settings_page_extension_admin_menu' );

function hocwp_theme_settings_page_extension_screen_options() {
	global $hocwp_theme;
	if ( ! isset( $hocwp_theme->extensions_list_table ) ) {
		$hocwp_theme->extensions_list_table = new HOCWP_Extensions_List_Table();
	}
	$screen = get_current_screen();
	if ( ! is_object( $screen ) || $screen->id != $hocwp_theme->option->hook_suffix ) {
		return;
	}
	if ( 'extension' == $hocwp_theme->option->tab ) {
		$args = array(
			'label'   => __( 'Number of items per page:', 'hocwp-theme' ),
			'default' => get_option( 'posts_per_page' ),
			'option'  => $screen->id . '_per_page'
		);
		add_screen_option( 'per_page', $args );
		$args = array(
			'label'   => __( 'Columns', 'hocwp-theme' ),
			'default' => $hocwp_theme->extensions_list_table->get_columns(),
			'option'  => $screen->id . '_columns'
		);
		add_screen_option( 'columns', $args );
	}
}

function hocwp_theme_settings_page_extension_set_screen_options( $status, $option, $value ) {
	return $value;
}

add_filter( 'set-screen-option', 'hocwp_theme_settings_page_extension_set_screen_options', 10, 3 );

function hocwp_theme_settings_page_extension_form_after() {
	?>
	<div style="padding-top: 10px;">
		<?php
		global $hocwp_theme, $plugin_page;

		if ( ! ! isset( $hocwp_theme->extensions_list_table ) ) {
			$hocwp_theme->extensions_list_table = new HOCWP_Extensions_List_Table();
		}

		$hocwp_theme->extensions_list_table->process_bulk_action();
		$hocwp_theme->extensions_list_table->prepare_items();
		$hocwp_theme->extensions_list_table->admin_notices();
		?>
		<h2 class="screen-reader-text"><?php _e( 'Filter extensions list', 'hocwp-theme' ); ?></h2>
		<?php
		$hocwp_theme->extensions_list_table->views();
		$url = HOCWP_Theme_Utility::get_current_url( true );
		?>
		<form class="search-form search-extensions" method="post" action="">
			<?php $hocwp_theme->extensions_list_table->search_box( __( 'Search', 'hocwp-theme' ), 'extension' ); ?>
		</form>
		<form method="post">
			<input type="hidden" name="page" value="<?php echo $plugin_page; ?>">
			<?php
			$hocwp_theme->extensions_list_table->display();
			?>
		</form>
	</div>
	<?php
}

add_action( 'hocwp_theme_settings_page_extension_form_after', 'hocwp_theme_settings_page_extension_form_after' );