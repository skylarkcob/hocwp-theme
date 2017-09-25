jQuery(document).ready(function ($) {
    (function () {
        var body = $("#body");

        if (!body.hasClass("wp-admin")) {
            setInterval(function () {
                var endTime = new Date().getTime();
                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: hocwpTheme.ajaxUrl,
                    cache: true,
                    data: {
                        action: "hocwp_theme_dev_taking_breaks"
                    }
                });
            }, 5000);
        }
    })();
});