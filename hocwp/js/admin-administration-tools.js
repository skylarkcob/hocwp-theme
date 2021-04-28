window.hocwpTheme = window.hocwpTheme || {};

jQuery(document).ready(function ($) {
    const body = $("body");

    function _hocwp_theme_update_button_waiting_text(element, loading) {
        var tagName = element.prop("tagName"),
            text = "",
            waiting = hocwpTheme.l10n.waiting,
            normalText = element.attr("data-normal-text");

        loading = loading || 1;
        console.log(loading);
        if ($.trim(normalText)) {
            text = normalText;
        } else {
            if ("INPUT" === tagName) {
                text = element.val();
            } else {
                text = element.text();
            }
        }

        if ($.trim(text) && $.trim(waiting)) {
            if (1 === loading) {
                element.attr("data-normal-text", text);

                if ("INPUT" === tagName) {
                    element.val(waiting);
                } else {
                    element.text(waiting);
                }
            } else {
                if ("INPUT" === tagName) {
                    element.val(text);
                } else {
                    element.text(text);
                }
            }
        }
    }

    // Change admin email
    (function () {
        body.on("click", "form[data-tab='administration_tools'] button[data-change-email='1'], form[data-tab='administration_tools'] input[data-change-email='1']", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                form = element.closest("form"),
                newEmail = form.find(".new_email input");

            if (!$.trim(newEmail.val())) {
                newEmail.focus();
                element.removeClass("disabled");
                element.blur();
            } else {
                _hocwp_theme_update_button_waiting_text(element);

                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: hocwpTheme.ajaxUrl,
                    cache: true,
                    data: {
                        action: "hocwp_theme_change_administrative_email",
                        email: newEmail.val()
                    },
                    success: function (response) {
                        if (response.success) {
                            alert(element.attr("data-message"));
                            newEmail.val("");
                            element.blur();
                        } else if (response.data && response.data.message && $.trim(response.data.message)) {
                            alert(response.data.message);
                        }

                        _hocwp_theme_update_button_waiting_text(element, "FALSE");
                    },
                    complete: function (response) {
                        body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                        _hocwp_theme_update_button_waiting_text(element, "FALSE");
                    }
                });
            }
        });
    })();

    // Change site URL
    (function () {
        body.on("click", "form[data-tab='administration_tools'] button[data-change-url='1'], form[data-tab='administration_tools'] input[data-change-url='1']", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                form = element.closest("form"),
                oldUrl = form.find(".old_url input"),
                newUrl = form.find(".new_url input");

            if (confirm(element.attr("data-confirm-message"))) {
                if (!$.trim(oldUrl.val())) {
                    oldUrl.focus();
                    element.removeClass("disabled");
                    element.blur();
                } else if (!$.trim(newUrl.val())) {
                    newUrl.focus();
                    element.removeClass("disabled");
                    element.blur();
                } else {
                    $.ajax({
                        type: "POST",
                        dataType: "JSON",
                        url: hocwpTheme.ajaxUrl,
                        cache: true,
                        data: {
                            action: "hocwp_theme_change_site_url",
                            old_url: oldUrl.val(),
                            new_url: newUrl.val()
                        },
                        success: function (response) {
                            if (response.success) {
                                alert(element.attr("data-message"));
                            }
                        },
                        complete: function (response) {
                            body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                        }
                    });
                }
            } else {
                element.removeClass("disabled");
                element.blur();
            }
        });
    })();

    // Import administrative boundaries
    (function () {
        body.on("click", "form[data-tab='administration_tools'] button[data-import-ab='1'], form[data-tab='administration_tools'] input[data-import-ab='1']", function (e) {
            e.preventDefault();

            var that = this,
                element = $(that),
                form = element.closest("form"),
                district = form.find(".district input"),
                commune = form.find(".commune input"),
                taxonomy = form.find(".ab_taxonomy select");

            if (!$.trim(taxonomy.val())) {
                taxonomy.focus();
                element.removeClass("disabled");
                element.blur();
            } else {
                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: hocwpTheme.ajaxUrl,
                    cache: true,
                    data: {
                        action: "hocwp_theme_import_administrative_boundaries",
                        district: district.is(":checked") ? 1 : 0,
                        commune: commune.is(":checked") ? 1 : 0,
                        taxonomy: taxonomy.val()
                    },
                    success: function (response) {
                        if (response.success) {
                            alert(element.attr("data-message"));
                        }
                    },
                    complete: function (response) {
                        body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                    }
                });
            }
        });
    })();

    // Search and delete posts
    (function () {
        var cache = {};

        function _split(val) {
            return val.split(/,\s*/);
        }

        function _extract_last(term) {
            return _split(term).pop();
        }

        function _search_post_ajax(element) {
            element.on("keydown", function (event) {
                if (event.keyCode === $.ui.keyCode.TAB &&
                    $(this).autocomplete("instance").menu.active) {
                    event.preventDefault();
                }
            }).autocomplete({
                source: function (request, response) {
                    var term = _extract_last(request.term),
                        postType = $("#delete_post_type").val(),
                        cacheKey = "";

                    if (!$.trim(postType)) {
                        postType = "any";
                    }

                    cacheKey = postType + "_" + term;

                    if (cacheKey in cache) {
                        response(cache[cacheKey]);
                        return;
                    }

                    const ajaxData = {
                        action: "hocwp_theme_search_post",
                        term: term,
                        post_type: postType,
                        post_ids: $("#post_ids").val(),
                        search_post: $("#search_post").val(),
                        nonce: hocwpTheme.nonce
                    };

                    $.getJSON(hocwpTheme.ajaxUrl, ajaxData, function (data) {
                        cache[cacheKey] = data;
                        response(data);
                    });
                },
                search: function () {
                    var term = _extract_last(this.value);

                    if (term.length < 2) {
                        return false;
                    }
                },
                focus: function () {
                    return false;
                },
                select: function (event, ui) {
                    var terms = _split(this.value);

                    terms.pop();
                    terms.push(ui.item.value);
                    terms.push("");
                    this.value = terms.join(", ");

                    return false;
                },
                minLength: 2,
                open: function () {
                    $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
                },
                close: function () {
                    $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
                }
            });
        }

        _search_post_ajax($(".delete-posts-form input[name='search_post']"));
    })();
});