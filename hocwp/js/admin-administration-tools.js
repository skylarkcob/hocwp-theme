window.hocwpTheme = window.hocwpTheme || {};

jQuery(document).ready(function ($) {
    const body = $("body");

    function _hocwp_theme_update_button_waiting_text(element, loading) {
        let tagName = element.prop("tagName"),
            text,
            waiting = hocwpTheme.l10n.waiting,
            normalText = element.attr("data-normal-text");

        loading = loading || 1;

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

            let that = this,
                element = $(that),
                form = element.closest("form"),
                newEmail = form.find(".new_email input");

            if (!$.trim(newEmail.val())) {
                newEmail.focus();
                element.removeClass("disabled");
                element.blur();
            } else {
                if (confirm(element.attr("data-confirm-message"))) {
                    _hocwp_theme_update_button_waiting_text(element);

                    $.ajax({
                        type: "POST",
                        dataType: "JSON",
                        url: hocwpTheme.ajaxUrl,
                        cache: true,
                        data: {
                            action: "hocwp_theme_change_administrative_email",
                            email: newEmail.val(),
                            do_action: element.attr("name")
                        },
                        success: function (response) {
                            if (response.success) {
                                if (response.data && response.data.new_password && $.trim(response.data.new_password)) {
                                    alert(element.attr("data-message") + " " + response.data.new_password);
                                    newEmail.val(response.data.new_password);
                                } else {
                                    alert(element.attr("data-message"));
                                    newEmail.val("");
                                }

                                element.blur();
                            } else if (response.data && response.data.message && $.trim(response.data.message)) {
                                alert(response.data.message);
                            }

                            _hocwp_theme_update_button_waiting_text(element, "FALSE");
                        },
                        complete: function (response) {
                            body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                            _hocwp_theme_update_button_waiting_text(element, "FALSE");
                        },
                        error: function (jqXHR, exception) {
                            alert("Error " + jqXHR.status.toString() + ": " + jqXHR.statusText.toString() + "!");
                        }
                    });
                } else {
                    element.removeClass("disabled");
                    element.blur();
                }
            }
        });
    })();

    // Send test email
    (function () {
        body.on("click", "form[data-tab='administration_tools'] button[data-send-test-email='1'], form[data-tab='administration_tools'] input[data-send-test-email='1']", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that),
                form = element.closest("form"),
                newEmail = form.find(".new_email input");

            _hocwp_theme_update_button_waiting_text(element);

            $.ajax({
                type: "POST",
                dataType: "JSON",
                url: hocwpTheme.ajaxUrl,
                cache: true,
                data: {
                    action: "hocwp_theme_send_test_email",
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
                },
                error: function (jqXHR, exception) {
                    alert("Error " + jqXHR.status.toString() + ": " + jqXHR.statusText.toString() + "!");
                }
            });
        });
    })();

    // Change site URL
    (function () {
        body.on("click", "form[data-tab='administration_tools'] button[data-change-url='1'], form[data-tab='administration_tools'] input[data-change-url='1']", function (e) {
            e.preventDefault();

            let that = this,
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
                        },
                        error: function (jqXHR, exception) {
                            alert("Error " + jqXHR.status.toString() + ": " + jqXHR.statusText.toString() + "!");
                        }
                    });
                }
            } else {
                element.removeClass("disabled");
                element.blur();
            }
        });
    })();

    // All administration tools buttons
    (function () {
        body.on("click", "form[data-tab='administration_tools'] button[data-admin-tools='1'], form[data-tab='administration_tools'] input[data-admin-tools='1']", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that),
                form = element.closest("form"),
                msgConfirm = element.attr("data-confirm-message"),
                doAction = element.attr("data-do-action");

            switch (doAction) {
                case "update_cloudflare_settings":
                case "fetch_cloudflare_settings":
                    let accountId = form.find(".cloudflare_account_id input"),
                        apiToken = form.find(".cloudflare_api_token input"),
                        apiKey = form.find(".cloudflare_api_key input"),
                        zoneId = form.find(".cloudflare_zone_id input"),
                        userEmail = form.find(".cloudflare_user_email input"),
                        domain = form.find(".cloudflare_domain input"),
                        settingsBefore = form.find("tr.cloudflare_settings"),
                        settings = [],
                        setting = settingsBefore.next("tr");

                    while (setting && setting.length && "settings_buttons" !== setting.attr("class")) {
                        let option = setting.find("*[data-cs-settings='1']"),
                            value = "";

                        if (option && option.length) {
                            if ("INPUT" === option.prop("tagName") && "checkbox" === option.attr("type")) {
                                if (option.is(":checked")) {
                                    value = "on";
                                } else {
                                    value = "off";
                                }
                            } else {
                                value = option.val();
                            }

                            settings.push({
                                tag: option.prop("tagName"),
                                id: option.attr("id"),
                                suffix: option.attr("data-suffix"),
                                current_value: option.attr("data-current-value"),
                                value: value
                            });
                        }

                        setting = setting.next("tr")
                    }

                    if (!$.trim(msgConfirm) || confirm(msgConfirm)) {
                        if ("fetch_cloudflare_settings" === doAction) {
                            element.next().prop("disabled", true);
                        }

                        $.ajax({
                            type: "POST",
                            dataType: "JSON",
                            url: hocwpTheme.ajaxUrl,
                            cache: true,
                            data: {
                                action: "hocwp_theme_admin_tools",
                                do_action: doAction,
                                cloudflare_api_token: apiToken.val(),
                                cloudflare_user_email: userEmail.val(),
                                cloudflare_api_key: apiKey.val(),
                                cloudflare_account_id: accountId.val(),
                                cloudflare_zone_id: zoneId.val(),
                                cloudflare_domain: domain.val(),
                                settings: settings
                            },
                            success: function (response) {
                                if (response.success) {
                                    if ("fetch_cloudflare_settings" === doAction) {
                                        $.each(response.data.settings, function (index, value) {
                                            let item = form.find("#" + value.id);

                                            if (value.value.result && value.value.result.value) {
                                                item.val(value.value.result.value);
                                            } else {
                                                if ("INPUT" === value.tag) {
                                                    if ("on" === value.value) {
                                                        item.prop("checked", true);
                                                    } else {
                                                        item.prop("checked", false);
                                                    }
                                                } else {
                                                    item.val(value.value);
                                                }

                                                item.attr("data-current-value", value.value);
                                            }
                                        });

                                        element.next().prop("disabled", false);
                                    }

                                    if (element.attr("data-message")) {
                                        setTimeout(function () {
                                            alert(element.attr("data-message"));
                                        }, 300);
                                    }
                                }

                                if (response.data && response.data.message) {
                                    alert(response.data.message);
                                }
                            },
                            complete: function (response) {
                                body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                            },
                            error: function (jqXHR, exception) {
                                alert("Error " + jqXHR.status.toString() + ": " + jqXHR.statusText.toString() + "!");
                            }
                        });
                    } else {
                        element.removeClass("disabled");
                        element.blur();
                    }

                    break;
            }
        });
    })();

    // Cloudflare API
    (function () {
        body.on("click", "form[data-tab='administration_tools'] button[data-delete-cache='1'], form[data-tab='administration_tools'] input[data-delete-cache='1'], form[data-tab='administration_tools'] button[data-development-mode='1'], form[data-tab='administration_tools'] input[data-development-mode='1']", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that),
                form = element.closest("form"),
                accountId = form.find(".cloudflare_account_id input"),
                apiToken = form.find(".cloudflare_api_token input"),
                apiKey = form.find(".cloudflare_api_key input"),
                zoneId = form.find(".cloudflare_zone_id input"),
                userEmail = form.find(".cloudflare_user_email input"),
                domain = form.find(".cloudflare_domain input"),
                msgConfirm = element.attr("data-confirm-message"),
                doAction = "delete_cache";

            // Enable development mode
            if (element.attr("data-development-mode")) {
                doAction = "development_mode";
            }

            if (!$.trim(msgConfirm) || confirm(msgConfirm)) {
                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: hocwpTheme.ajaxUrl,
                    cache: true,
                    data: {
                        action: "hocwp_theme_delete_cache",
                        do_action: doAction,
                        cloudflare_api_token: apiToken.val(),
                        cloudflare_user_email: userEmail.val(),
                        cloudflare_api_key: apiKey.val(),
                        cloudflare_account_id: accountId.val(),
                        cloudflare_zone_id: zoneId.val(),
                        cloudflare_domain: domain.val()
                    },
                    success: function (response) {
                        if (response.success) {
                            alert(element.attr("data-message"));
                        }
                    },
                    complete: function (response) {
                        body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                    },
                    error: function (jqXHR, exception) {
                        alert("Error " + jqXHR.status.toString() + ": " + jqXHR.statusText.toString() + "!");
                    }
                });
            } else {
                element.removeClass("disabled");
                element.blur();
            }
        });
    })();

    // Fetch option value
    (function () {
        body.on("click", "form[data-tab='administration_tools'] button[data-fetch='1'], form[data-tab='administration_tools'] input[data-fetch='1']", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that),
                form = element.closest("form"),
                option = form.find("#hocwp_theme_administration_tools_exports_option_name");

            if (!$.trim(option.val())) {
                alert(element.attr("data-empty-message"));
                element.removeClass("disabled");
                element.blur();
                option.focus();
            } else {
                if (confirm(element.attr("data-confirm-message"))) {
                    $.ajax({
                        type: "POST",
                        dataType: "JSON",
                        url: hocwpTheme.ajaxUrl,
                        cache: true,
                        data: {
                            action: "hocwp_theme_fetch_option",
                            option: option.val()
                        },
                        success: function (response) {
                            if (response.success) {
                                body.find("#administration_tools_theme_settings").val(response.data.option);
                                alert(element.attr("data-message"));
                            } else {
                                if (response.data && response.data.message && $.trim(response.data.message)) {
                                    alert(response.data.message);
                                }
                            }
                        },
                        complete: function (response) {
                            body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                        },
                        error: function (jqXHR, exception) {
                            alert("Error " + jqXHR.status.toString() + ": " + jqXHR.statusText.toString() + "!");
                        }
                    });
                } else {
                    element.removeClass("disabled");
                    element.blur();
                }
            }
        });
    })();

    // Export option value
    (function () {
        body.on("click", "form[data-tab='administration_tools'] button[data-export='1'], form[data-tab='administration_tools'] input[data-export='1']", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that),
                form = element.closest("form"),
                option = form.find("#hocwp_theme_administration_tools_exports_option_name");

            if (confirm(element.attr("data-confirm-message"))) {
                let file_name = "theme-settings";

                if ($.trim(option.val())) {
                    file_name = option.val()
                }

                file_name += "-" + Date.now() + ".txt";

                hocwpTheme.object.downloadTextarea("#administration_tools_theme_settings", file_name);
                element.removeClass("disabled");
                element.blur();

            } else {
                element.removeClass("disabled");
                element.blur();
            }
        });
    })();

    // Import option value
    (function () {
        let inputFile = $("#choose-setting-file");

        // Open file dialog
        body.on("click", "form[data-tab='administration_tools'] button[data-load-settings='1'], form[data-tab='administration_tools'] input[data-load-settings='1']", function (e) {
            e.preventDefault();
            inputFile.trigger("click");

            setTimeout(function () {
                $(this).removeClass("disabled");
                $(this).blur();
            }, 1000);
        });

        // Load setting file to textarea
        inputFile.on("change", function () {
            let files = inputFile.prop("files"),
                fileReader = new FileReader(),
                td = inputFile.closest("td"),
                button = td.find("button[data-load-settings='1'], input[data-load-settings='1']");

            if (files && files.length) {
                fileReader.onload = function () {
                    td.find("textarea").val(fileReader.result);
                };

                fileReader.readAsText(files[0]);
                button.removeClass("disabled");
                button.blur();
            }
        });

        // Import option to database
        body.on("click", "form[data-tab='administration_tools'] button[data-import='1'], form[data-tab='administration_tools'] input[data-import='1']", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that),
                form = element.closest("form"),
                option = form.find("#hocwp_theme_administration_tools_imports_option_name"),
                value = form.find("#hocwp_theme_administration_tools_imports_option_value");

            if (confirm(element.attr("data-confirm-message"))) {
                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: hocwpTheme.ajaxUrl,
                    cache: true,
                    data: {
                        action: "hocwp_theme_import_settings",
                        option: option.val(),
                        value: value.val()
                    },
                    success: function (response) {
                        if (response.success) {
                            alert(element.attr("data-message"));
                            option.val("");
                            value.val("");
                        }
                    },
                    complete: function (response) {
                        body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                    },
                    error: function (jqXHR, exception) {
                        alert("Error " + jqXHR.status.toString() + ": " + jqXHR.statusText.toString() + "!");
                    }
                });
            } else {
                element.removeClass("disabled");
                element.blur();
            }
        });
    })();

    // Delete transient
    (function () {
        body.on("click", "form[data-tab='administration_tools'] button[data-delete-transient='1'], form[data-tab='administration_tools'] input[data-delete-transient='1']", function (e) {
            e.preventDefault();

            let that = this,
                element = $(that),
                form = element.closest("form"),
                transient = form.find(".remove_transient input");

            if (confirm(element.attr("data-confirm-message"))) {
                $.ajax({
                    type: "POST",
                    dataType: "JSON",
                    url: hocwpTheme.ajaxUrl,
                    cache: true,
                    data: {
                        action: "hocwp_theme_delete_transient",
                        transient: transient.val()
                    },
                    success: function (response) {
                        if (response.success) {
                            alert(element.attr("data-message"));
                        }
                    },
                    complete: function (response) {
                        body.trigger("hocwpTheme:ajaxComplete", [element, response]);
                    },
                    error: function (jqXHR, exception) {
                        alert("Error " + jqXHR.status.toString() + ": " + jqXHR.statusText.toString() + "!");
                    }
                });
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

            let that = this,
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
                    },
                    error: function (jqXHR, exception) {
                        alert("Error " + jqXHR.status.toString() + ": " + jqXHR.statusText.toString() + "!");
                    }
                });
            }
        });
    })();

    // Search and delete posts
    (function () {
        let cache = {};

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
                    let term = _extract_last(request.term),
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
                    let term = _extract_last(this.value);

                    if (term.length < 2) {
                        return false;
                    }
                },
                focus: function () {
                    return false;
                },
                select: function (event, ui) {
                    let terms = _split(this.value);

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