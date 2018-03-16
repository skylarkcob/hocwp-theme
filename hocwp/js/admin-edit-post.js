jQuery(document).ready(function ($) {
    var editSlugBox = $("#edit-slug-box"),
        editSlugButtons = editSlugBox.find("#edit-slug-buttons");

    editSlugButtons.on("click", ".save", function () {
        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: hocwpTheme.ajaxUrl,
            cache: true,
            data: {
                action: "hocwp_theme_change_post_name",
                post_id: $("#post_ID").val(),
                post_name: editSlugBox.find("input#new-post-slug").val()
            }
        });
    });
});