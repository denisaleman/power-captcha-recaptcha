(($) => {
    class Tabs {
        constructor() {
            this.$navTabLinks = $('.pwrcap-nav-tab-wrapper .nav-tab');
            this.$activeTabLink = $('.pwrcap-nav-tab-wrapper .nav-tab-active');

            this.bindEvents();
            this.handleHashChange();

            $(window).on('hashchange', () => this.handleHashChange());
        }

        bindEvents() {
            this.$navTabLinks.on('click', (event) => {
                event.preventDefault();

                const tabId = this.activateTab($(event.currentTarget));

                if (tabId) {
                    this.pushHash(tabId);
                }
            });
        }

        activateTab($tab) {
            if (!$tab.length) {
                return null;
            }

            this.$activeTabLink.removeClass('nav-tab-active');
            $tab.addClass('nav-tab-active');
            this.$activeTabLink = $tab;

            $('.pwrcap-tab-content').hide();

            const tabId = $tab.attr('href');
            $(tabId).show();

            return tabId;
        }

        handleHashChange() {
            if (!location.hash) {
                return;
            }

            const $tab = this.$navTabLinks.filter(
                `[href="${location.hash}"]`
            );

            this.activateTab($tab);
        }

        pushHash(hash) {
            if (history.pushState) {
                history.pushState(null, '', hash);
            } else {
                location.hash = hash;
            }
        }
    }

    $(() => {
        new Tabs();
    });
})(jQuery);