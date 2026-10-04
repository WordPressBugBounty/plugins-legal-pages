jQuery(document).ready(function ($) {

    function isLegalPagesScreen() {
        const params = new URLSearchParams(window.location.search);
        const page = params.get('page');
        // ✅ Cover both free and pro pages
        return page === 'adl-legal-pages' || page === 'adl-legal-pages-pro';
    }

    const $menu = $('#adminmenu li.toplevel_page_adl-legal-pages');

    const isProActive = $menu.find('a[href*="adl-legal-pages#/all-popups"]').length > 0;

    const freeChildSel = 'a[href*="#/pro-features/all-popups"], a[href*="#/pro-features/cookie-bar"]';
    const proChildSel  = 'a[href*="adl-legal-pages#/all-popups"], a[href*="adl-legal-pages#/cookie-bar"]';
    const childSel     = isProActive ? proChildSel : freeChildSel;

    function getProChildren() {
        return $menu.find('.wp-submenu li').has(childSel);
    }

    function getProParent() {
        return $menu.find('.wp-submenu li').has('a[href$="#/pro-features"]');
    }

    function setProExpanded(expanded) {
        const $children = getProChildren();
        const $parent   = getProParent();

        if (expanded) {
            $children.show();
            $parent.addClass('lp-pro-open').removeClass('lp-pro-collapsed');
        } else {
            $children.hide();
            $parent.addClass('lp-pro-collapsed').removeClass('lp-pro-open');
        }
    }

    $(document).on('click', '#adminmenu #toplevel_page_adl-legal-pages ul.wp-submenu li a[href$="#/pro-features"]', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const isExpanded = getProParent().hasClass('lp-pro-open');
        setProExpanded(!isExpanded);
    });

    // Detail/edit screens that aren't themselves a registered submenu item,
    // but belong under one conceptually (an edit screen for a list page) —
    // mapped to that submenu's hash so the right item gets highlighted
    // instead of silently matching nothing.
    const routeAliases = [
        { test: /^\/edit-template(\/|$)/, target: '#/legal-page-templates' },
        { test: /^\/add-new-template(\/|$)/, target: '#/legal-page-templates' },
        { test: /^\/edit-legal-page(\/|$)/, target: '#/all-legal-pages' },
    ];

    function updateActiveMenu() {
        if (!isLegalPagesScreen()) return;

        $menu.find('.wp-submenu li').removeClass('current');

        const rawHash = (window.location.hash || '').replace('#', '');

        if (!rawHash) {
            // Bare page load — this is the Settings screen itself.
            $menu.find('a[href="admin.php?page=adl-legal-pages"]').parent().addClass('current');
            return;
        }

        const alias = routeAliases.find(function (a) { return a.test.test(rawHash); });
        const matchHash = alias ? alias.target : '#' + rawHash;

        const $match = $menu.find('a[href*="' + matchHash + '"]').parent();

        if ($match.length) {
            $match.addClass('current');
            if ($match.has(childSel).length) {
                setProExpanded(true);
            }
        }
        // No match (e.g. /setup-wizard, or any future/unmapped route): leave
        // every submenu item un-highlighted rather than defaulting to
        // Settings — the top-level "Legal Pages" highlight (set by WP core
        // from the page= query var) is still correct and untouched.
    }

    $(document).on('click', '#adminmenu a[href="admin.php?page=adl-legal-pages"]', function (e) {
        if (!isLegalPagesScreen()) return;
        e.preventDefault();
        window.location.hash = '';
        updateActiveMenu();
    });

    setProExpanded(false);
    updateActiveMenu();
    $(window).on('hashchange', updateActiveMenu);
});
