(function () {
    var container, searchField;
    container = document.getElementById('header-main');
    searchField = container.getElementsByClassName('search-field')[0];
    searchField.addEventListener('focus', function () {
        if (-1 === container.className.indexOf('nav-menu')) {
            container.className += ' search-opened';
        }
    });
    searchField.addEventListener('focusout', function () {
        var timeout = 0;
        if (searchField.value) {
            timeout = 500;
        }
        setTimeout(function () {
            container.className = container.className.replace(' search-opened', '');
        }, timeout);
    });
})();

(function () {
    var siteNavigation, menuItemHasChildren;
    siteNavigation = document.getElementById('site-navigation');
    menuItemHasChildren = siteNavigation.getElementsByClassName('menu-item-has-children');
    var lists = document.querySelectorAll('.menu-item-has-children > a');
    var doSomething = function (e) {
        [].forEach.call(lists, function (elem) {
            elem.parentNode.className = elem.parentNode.className.replace(' open', '');
        });
        var parent = this.parentNode;
        if (-1 === parent.className.indexOf('open')) {
            e.preventDefault();
            parent.className += ' open';
        } else {
            parent.className = parent.className.replace(' open', '');
        }
    };
    [].map.call(lists, function (elem) {
        elem.addEventListener('click', doSomething, false);
    });
})();