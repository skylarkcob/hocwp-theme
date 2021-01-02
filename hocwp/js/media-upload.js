window.wp = window.wp || {};
window.hocwpTheme = window.hocwpTheme || {};
window.hocwpThemeMediaUpload = window.hocwpThemeMediaUpload || {};

var HOCWP_Theme_Media_Upload = function () {
    this.openFrameEdit = function (frame, mediaId) {
        frame.on("open", function () {
            if (mediaId && $.isNumeric(mediaId)) {
                frame.state().get("selection").add(wp.media.attachment(mediaId));
            }
        }).open();
    };

    this.ucWords = function (str) {
        return str.replace(/\w\S*/g, function (txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    };
};

jQuery(document).ready(function ($) {
    var body = $("body");

    function __open_frame_edit(f, i) {
        f.on("open", function () {
            if (i && $.isNumeric(i)) {
                f.state().get("selection").add(wp.media.attachment(i));
            }
        }).open();
    }

    function hocwpThemeUCWords(str) {
        return str.replace(/\w\S*/g, function (txt) {
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
        });
    }

    body.on("click", ".hocwp-theme .remove-media-data", function (e) {
        e.preventDefault();
        e.stopPropagation();

        var element = $(this),
            container = element.closest("div"),
            inputUrl = container.find("input.media-url"),
            inputId = container.find("input.media-id");

        inputUrl.val("");
        inputId.val("");

        element.hide();
    });

    // Add multiple images to list images.
    body.on("click", ".hocwp-theme .insert-images", function (e) {
        e.preventDefault();
        e.stopPropagation();

        var that = this,
            element = $(that),
            container = element.closest("div"),
            listImages = container.find(".list-images"),
            multiple = true,
            frame = null;

        if (frame) {
            frame.open();
        } else {
            var title = $.trim(element.attr("data-title")) || hocwpThemeMediaUpload.l10n.title,
                buttonText = $.trim(element.attr("data-button-text")) || hocwpThemeMediaUpload.l10n.buttonText,
                media_type = "image";

            title = title.replace("%s", hocwpThemeUCWords(media_type));
            buttonText = buttonText.replace("%s", media_type);

            var settings = {
                title: title,
                button: {
                    text: buttonText
                },
                multiple: multiple
            };

            frame = wp.media(settings);

            frame.open();
        }

        frame.on("select", function () {
            var items = frame.state().get("selection"),
                input = container.find("input").not(":input[type='submit']"),
                oldValues = input.val(),
                value = "";

            // Check if field has images
            if (oldValues) {
                value = $.parseJSON(oldValues);
            }

            if (!value) {
                value = [];
            }

            items.map(function (attachment) {
                attachment = attachment.toJSON();

                if (attachment) {
                    var image = document.createElement("img"),
                        li = document.createElement("li"),
                        span = document.createElement("span");

                    image.setAttribute("src", attachment.url);
                    image.setAttribute("alt", attachment.alt);
                    image.setAttribute("width", attachment.width);
                    image.setAttribute("height", attachment.height);

                    li.setAttribute("class", "ui-state-default");
                    li.setAttribute("data-id", attachment.id);
                    li.appendChild(image);

                    span.setAttribute("class", "dashicons dashicons-no-alt");
                    li.appendChild(span);

                    listImages.append(li);

                    value.push(attachment.id);
                }
            });

            value = JSON.stringify(value);
            input.val(value);

            if (value) {
                container.find("button.remove-images").show();
            }

            element.trigger("blur");
        });
    });

    // Click image to change image in list images.
    body.on("click", ".hocwp-theme .list-images img", function (e) {
        e.preventDefault();
        e.stopPropagation();

        var that = this,
            element = $(that),
            listItem = element.closest("li"),
            container = listItem.closest("div"),
            multiple = false,
            frame = null,
            index = listItem.index();

        if (frame) {
            frame.open();
        } else {
            var title = $.trim(element.attr("data-title")) || hocwpThemeMediaUpload.l10n.changeTitle,
                buttonText = $.trim(element.attr("data-button-text")) || hocwpThemeMediaUpload.l10n.buttonText,
                media_type = "image";

            title = title.replace("%s", hocwpThemeUCWords(media_type));
            buttonText = buttonText.replace("%s", media_type);

            var settings = {
                title: title,
                button: {
                    text: buttonText
                },
                multiple: multiple
            };

            frame = wp.media(settings);

            frame.open();
        }

        frame.on("select", function () {
            var items = frame.state().get("selection"),
                input = container.find("input").not(":input[type='submit']"),
                value = $.parseJSON(input.val()),
                item = items.first().toJSON();

            if (!value) {
                value = [];
            }

            var image = document.createElement("img"),
                li = document.createElement("li"),
                span = document.createElement("span");

            image.setAttribute("src", item.url);
            image.setAttribute("alt", item.alt);
            image.setAttribute("width", item.width);
            image.setAttribute("height", item.height);

            li.setAttribute("class", "ui-state-default");
            li.setAttribute("data-id", item.id);
            li.appendChild(image);

            span.setAttribute("class", "dashicons dashicons-no-alt");
            li.appendChild(span);

            listItem.replaceWith(li);

            value[index] = (item.id);

            value = JSON.stringify(value);
            input.val(value);

            element.trigger("blur");

            if (value) {
                container.find("button.remove-images").show();
            }
        });
    });

    // Select media button click.
    body.on("click", ".hocwp-theme .select-media", function (e) {
        e.preventDefault();
        e.stopPropagation();

        var selectMedia = $(this),
            container = selectMedia.closest("div"),
            multiple = Boolean(parseInt(selectMedia.attr("data-multiple")) || parseInt(hocwpThemeMediaUpload.multiple)),
            frame = null,
            removeMedia = container.find("button.remove-media-data");

        if (frame) {
            //frame.open();
        } else {
            var title = $.trim(selectMedia.attr("data-title")) || hocwpThemeMediaUpload.l10n.title,
                buttonText = $.trim(selectMedia.attr("data-button-text")) || hocwpThemeMediaUpload.l10n.buttonText,
                media_type = $.trim(selectMedia.attr("data-media-type")) || "image";

            title = title.replace("%s", hocwpThemeUCWords(media_type));
            buttonText = buttonText.replace("%s", media_type);

            var settings = {
                title: title,
                button: {
                    text: buttonText
                },
                multiple: multiple
            };

            if ("file" != media_type) {
                settings.library = {
                    type: media_type
                };
            }

            frame = wp.media(settings);

            //frame.open();
        }

        let mediaId = selectMedia.closest(".media-box").find("input[type='hidden']").val();

        __open_frame_edit(frame, mediaId);

        frame.on("select", function () {
            var items = frame.state().get("selection");

            if (multiple) {

            } else {
                var item = items.first().toJSON();

                if (item) {
                    if ("file" === media_type) {
                        var inputUrl = container.find("input.media-url"),
                            inputId = container.find("input.media-id");

                        inputUrl.val(item.url);
                        inputId.val(item.id);

                        removeMedia.show();
                    } else {
                        var image = document.createElement("img"),
                            widget = container.closest(".widget");

                        image.setAttribute("src", item.url);
                        image.setAttribute("alt", item.alt);
                        image.setAttribute("width", item.width);
                        image.setAttribute("height", item.height);
                        container.find("input").not(":input[type='submit']").val(item.id).trigger("change");
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
                            widget.find("input[type='submit']").val(hocwpTheme.l10n.save).prop("disabled", false);
                        }
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

    body.on("click", ".list-images .ui-state-default .dashicons-no-alt", function (e) {
        e.preventDefault();

        var that = this,
            element = $(that),
            li = element.closest("li"),
            container = li.closest("div"),
            input = container.find("input");

        if (confirm(hocwpTheme.l10n.confirmDeleteMessage)) {
            var value = $.parseJSON(input.val());

            if (value) {
                value.splice(li.index(), 1);
                value = JSON.stringify(value);
                input.val(value);
                li.remove();
            }
        }
    });

    // Remove all images in list images.
    body.on("click", ".images-box button.remove-images", function (e) {
        e.preventDefault();

        var that = this,
            element = $(that),
            container = element.closest("div"),
            input = container.find("input"),
            list = container.find(".list-images");

        if (confirm(hocwpTheme.l10n.confirmDeleteMessage)) {
            input.val("");
            list.html("");

            element.trigger("blur");
            element.hide();
        }
    });
});