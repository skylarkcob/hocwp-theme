window.hocwp_theme = window.hocwp_theme || {};
jQuery(document).ready(function ($) {
    var $body = $('body');
    /**
     * Ajax button
     */
    (function () {
        $body.on('click', '[data-ajax-button="1"]', function () {
            $(this).addClass('disabled');
            $body.append(hocwp_theme.ajax_overlay);
        });
        $body.on('hocwp_theme:ajax_complete', function (e, $button, response) {
            $button.removeClass('disabled');
            $body.find('.hocwp-theme.ajax-overlay').fadeOut().remove();
        });
    })();
});