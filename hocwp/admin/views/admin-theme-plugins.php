<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $tabs, $tab;
load_template( HOCWP_THEME_CORE_PATH . '/admin/class-hocwp-theme-plugin-install-list-table.php' );
$table = new HOCWP_Theme_Plugin_Install_List_Table();
$table->prepare_items();
?>
<div class="wrap plugin-install-php">
    <h1><?php _e( 'Theme Plugins', 'hocwp-theme' ); ?></h1>
    <hr class="wp-header-end" style="clear: both;">
    <h2 class="screen-reader-text"><?php _e( 'Filter plugins list', 'hocwp-theme' ); ?></h2>

	<?php $table->views(); ?>
    <br class="clear">
	<?php
	if ( isset( $tabs[ $tab ]['description'] ) ) {
		echo wpautop( $tabs[ $tab ]['description'] );
	}
	?>
    <form id="plugin-filter" method="post" class="plugin-installer-form tab-<?php echo sanitize_html_class( $tab ); ?>">
		<?php $table->display(); ?>
    </form>
</div>