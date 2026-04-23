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

    function updateActiveMenu() {
        if (!isLegalPagesScreen()) return;

        $menu.find('.wp-submenu li').removeClass('current');

        const hash = window.location.hash || '';

        if (hash) {
            const $match = $menu.find('a[href*="' + hash + '"]').parent();
            if ($match.length) {
                $match.addClass('current');
                if ($match.has(childSel).length) {
                    setProExpanded(true);
                }
            } else {
                $menu.find('a[href="admin.php?page=adl-legal-pages"]').parent().addClass('current');
            }
        } else {
            $menu.find('a[href="admin.php?page=adl-legal-pages"]').parent().addClass('current');
        }
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
