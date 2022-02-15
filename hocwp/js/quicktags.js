window.hocwpThemeQuickTags = window.hocwpThemeQuickTags || {};

jQuery(document).ready(function ($) {
    (function () {
        var quickTags = $(".quicktags-toolbar");

        if (quickTags.length) {
            QTags.addButton('h2', 'h2', '<h2>', '</h2>\n\n', '', hocwpThemeQuickTags.description.h2, 30);
            QTags.addButton('h3', 'h3', '<h3>', '</h3>\n\n', '', hocwpThemeQuickTags.description.h3, 30);
            QTags.addButton('h4', 'h4', '<h4>', '</h4>\n\n', '', hocwpThemeQuickTags.description.h4, 30);
            QTags.addButton('hr', 'hr', '\n<hr>\n', '', '', hocwpThemeQuickTags.description.hr, 30);
            QTags.addButton('dl', 'dl', '<dl>\n', '</dl>\n\n', '', hocwpThemeQuickTags.description.dl, 100);
            QTags.addButton('dt', 'dt', '\t<dt>', '</dt>\n', '', hocwpThemeQuickTags.description.dt, 101);
            QTags.addButton('dd', 'dd', '\t<dd>', '</dd>\n', '', hocwpThemeQuickTags.description.dd, 102);
            QTags.addButton('tab', 'tab', '\t', '', '', hocwpThemeQuickTags.description.tab, 102);
            QTags.addButton('nextpage', 'Page break', '\n<!--nextpage-->\n', '', '', hocwpThemeQuickTags.description.nextpage, 202);
        }
    })();
});