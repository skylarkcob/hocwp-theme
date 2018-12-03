jQuery(document).ready(function ($) {
    var body = $("body");

    (function () {
        $.widget("custom.combobox", {
            _create: function () {
                this.wrapper = $("<span>")
                    .addClass("custom-combobox")
                    .insertAfter(this.element);

                this.element.hide();
                this._createAutocomplete();
                this._createShowAllButton();
            },
            _createAutocomplete: function () {
                var selected = this.element.children(":selected"),
                    value = (selected && selected.val()) ? selected.text() : "",
                    element = this.element,
                    input = this.input;

                input = this.input = $("<input name='" + this.element.attr("name") + "' class='form-control'>")
                    .appendTo(this.wrapper)
                    .attr("title", "")
                    .addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left")
                    .autocomplete({
                        delay: 0,
                        minLength: 0,
                        source: $.proxy(this, "_source")
                    })
                    .tooltip({
                        classes: {
                            "ui-tooltip": "ui-state-highlight"
                        }
                    })
                    .on("change", function () {
                        body.trigger("hocwpThemeComboboxInputChange", [this.value, input]);
                    });

                this._on(this.input, {
                    autocompleteselect: function (event, ui) {
                        ui.item.option.selected = true;

                        this._trigger("select", event, {
                            item: ui.item.option
                        });

                        body.trigger("hocwpThemeComboboxInputChange", [ui.item.label, input]);
                    }
                });
            },
            _createShowAllButton: function () {
                var input = this.input,
                    wasOpen = false;

                $("<a>")
                    .attr("tabIndex", -1)
                    .attr("title", this.element.data("show-items-text"))
                    .tooltip()
                    .appendTo(this.wrapper)
                    .button({
                        icons: {
                            primary: "ui-icon-triangle-1-s"
                        },
                        text: false
                    })
                    .removeClass("ui-corner-all")
                    .addClass("custom-combobox-toggle ui-corner-right")
                    .on("mousedown", function () {
                        wasOpen = input.autocomplete("widget").is(":visible");
                    })
                    .on("click", function () {
                        input.trigger("focus");

                        if (wasOpen) {
                            return;
                        }

                        input.autocomplete("search", "");
                    });
            },
            _source: function (request, response) {
                var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");

                response(this.element.children("option").map(function () {
                    var text = $(this).text();

                    if (this.value && ( !request.term || matcher.test(text) ))
                        return {
                            label: text,
                            value: text,
                            option: this
                        };
                }));
            },
            _destroy: function () {
                this.wrapper.remove();
                this.element.show();
            }
        });

        $("select[data-combobox='1']").combobox();
    })();
});