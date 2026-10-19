jQuery(document).ready(function ($) {

    function isLegalPagesScreen() {
        const params = new URLSearchParams(window.location.search);
        const page = params.get('page');
        // ✅ Cover both free and pro pages
        return page === 'adl-legal-pages' || page === 'adl-legal-pages-pro';
    }

    const $menu = $('#adminmenu li.toplevel_page_adl-legal-pages');

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

    updateActiveMenu();
    $(window).on('hashchange', updateActiveMenu);
});
