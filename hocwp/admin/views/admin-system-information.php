<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'hocwp_theme_load_views' ) ) {
	require_once HOCWP_THEME_CORE_PATH . '/inc/template.php';
}

echo '<div id="systemInformation">';
hocwp_theme_load_views( 'module-print-dev-info' );
echo '</div>';
?>
<button id="copySystemInfo" class="button"
        data-copied-text="<?php esc_attr_e( 'Copied!', 'hocwp-theme' ); ?>"><?php _e( 'Copy all', 'hocwp-theme' ); ?></button>
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