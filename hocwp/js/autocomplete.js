jQuery(document).ready(function ($) {
    (function () {
        $(".hocwp-theme .autocomplete").each(function () {
            var element = $(this);
            element.autocomplete({
                source: function (request, response) {
                    $.ajax({
                        type: "GET",
                        url: hocwpTheme.ajaxUrl,
                        dataType: "JSON",
                        cache: true,
                        data: {
                            action: element.attr("data-action"),
                            term: request.term
                        },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    //log("Selected: " + ui.item.value + " aka " + ui.item.id);
                }
            });
        });
    })();
});