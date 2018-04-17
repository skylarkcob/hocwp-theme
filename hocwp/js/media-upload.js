window.wp = window.wp || {};
window.hocwpThemeMediaUpload = window.hocwpThemeMediaUpload || {};

jQuery(document).ready(function ($) {
    var body = $("body");

    function hocwpThemeUCWords(str) {
        return str.replace(/\w\S*/g, function (txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    }

    body.on("click", ".hocwp-theme .select-media", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var selectMedia = $(this),
            container = selectMedia.closest("div"),
            multiple = Boolean(parseInt(selectMedia.attr("data-multiple")) || parseInt(hocwpThemeMediaUpload.multiple)),
            frame = null;
        if (frame) {
            frame.open();
        } else {
            var title = $.trim(selectMedia.attr("data-title")) || hocwpThemeMediaUpload.l10n.title,
                buttonText = $.trim(selectMedia.attr("data-button-text")) || hocwpThemeMediaUpload.l10n.buttonText,
                media_type = $.trim(selectMedia.attr("data-media-type")) || "image";
            title = title.replace("%s", hocwpThemeUCWords(media_type));
            buttonText = buttonText.replace("%s", media_type);
            frame = wp.media({
                title: title,
                button: {
                    text: buttonText
                },
                multiple: multiple,
                library: {
                    type: media_type
                }
            });

            frame.open();
        }
        frame.on("select", function () {
            var items = frame.state().get("selection");

            if (multiple) {

            } else {
                var item = items.first().toJSON();
                if (item) {
                    var image = document.createElement("img"),
                        widget = container.closest(".widget");

                    image.setAttribute("src", item.url);
                    image.setAttribute("alt", item.alt);
                    image.setAttribute("width", item.width);
                    image.setAttribute("height", item.height);
                    container.find("input").val(item.id).trigger("change");
                    selectMedia.html(image);

                    if (!selectMedia.hasClass("has-media")) {
                        var description = hocwpThemeMediaUpload.updateImageDescription,
                            removeButton = hocwpThemeMediaUpload.removeImageButton;
                        description = description.replace("%s", media_type);
                        removeButton = removeButton.replace("%s", media_type);
                        $(description).insertAfter(container.find(".hide-if-no-js"));
                        $(removeButton).insertAfter(container.find(".hide-if-no-js").last());
                        selectMedia.addClass("has-media");
                    }

                    if (widget.length) {
                        widget.find("input[type='submit']").val(wpWidgets.l10n.save).prop("disabled", false);
                    }
                }
            }

            container.find("a, img").trigger("blur");
        });
    });

    body.on("click", ".hocwp-theme .remove-media", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var element = $(this),
            container = element.closest("div"),
            selectMedia = container.find(".select-media");
        selectMedia.html(selectMedia.attr("data-text"));
        selectMedia.removeClass("has-media");
        element.parent().remove();
        container.find(".howto").remove();
        container.find("input").val("").trigger("change");
    });
});