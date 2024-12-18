<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_theme_load_views' ) ) {
	require_once( HOCWP_THEME_CORE_PATH . '/inc/template.php' );
}

echo '<div id="systemInformation">';
hocwp_theme_load_views( 'module-print-dev-info' );
echo '</div>';
?>
    <button id="copySystemInfo" class="button"
            data-copied-text="<?php esc_attr_e( 'Copied!', 'hocwp-theme' ); ?>"
            aria-label="<?php esc_attr_e( 'Copy all', 'hocwp-theme' ); ?>"><?php _e( 'Copy all', 'hocwp-theme' ); ?></button>
    <script>
        function copyFunction() {
            const el = this;
            const cl = " disabled";
            this.className += cl;

            setTimeout(function () {
                const copyText = document.getElementById("systemInformation").textContent;
                const textArea = document.createElement("textarea");
                textArea.textContent = copyText;
                document.body.append(textArea);
                textArea.select();
                document.execCommand("copy");
                textArea.remove();
                alert(el.getAttribute("data-copied-text"));
            }, 1000);

            setTimeout(function () {
                el.className = el.className.replace(cl, "");
            }, 2000);
        }

        document.getElementById("copySystemInfo").addEventListener("click", copyFunction);
    </script>
    <h2 class="title"><?php _e( 'Website Errors', 'hocwp-theme' ); ?></h2>
    <p><?php _e( 'Read recent website errors on your server.', 'hocwp-theme' ); ?></p>
<?php
$files = array(
	'error_log',
	'wp-admin/error_log',
	'wp-includes/error_log',
	'wp-content/error_log'
);

$has_error = false;

$count = 1;

foreach ( $files as $file ) {
	$file = trailingslashit( ABSPATH ) . $file;

	if ( file_exists( $file ) ) {
		$data = ht_util()->filesystem()->get_contents( $file );

		if ( ! empty( $data ) ) {
			$has_error = true;
			?>
            <p>
                <label><em><?php printf( '<strong>%s.</strong> %s', $count, $file ); ?></em></label>
            </p>
            <textarea class="widefat large-text code" rows="10"><?php echo $data; ?></textarea>
			<?php
			$count ++;
		}
	}
}

if ( ! $has_error ) {
	?>
    <p><em><?php _e( 'Great! No recent error found on your server.', 'hocwp-theme' ); ?></em></p>
	<?php
}