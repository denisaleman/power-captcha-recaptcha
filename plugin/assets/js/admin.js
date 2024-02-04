(function($) {
	$(document).ready(function() {
		var $captchaV2Fieldset = $("#fieldset-captcha-v2-type"),
			$captchaTypeRadio = $(".captcha-type-radio");
		$captchaTypeRadio.on("change", function() {
			if( "v2" === $(this).filter(":checked").val() ) {
				$captchaV2Fieldset.show()
			} else {
				$captchaV2Fieldset.hide()
			}
		});
	});
})(jQuery);

(function($) {
	$(document).ready((function() {
		this.$navTabLinks = $(".pwrcap-nav-tab-wrapper .nav-tab");
		this.$activeTabLink = $(".pwrcap-nav-tab-wrapper a.nav-tab.nav-tab-active");
		this.$tabContent = $(".pwrcap-tabs-stage .pwrcap-tab-content");
		this.activateTab = function($tab) {
			if (typeof $tab === 'undefined' || 0 === $tab.length) {
				return 0
			}
			this.$activeTabLink.removeClass("nav-tab-active");
			$tab.addClass("nav-tab-active");
			this.$activeTabLink = $tab;
			$(".tabs-stage .ui-sortable").hide();
			var tabContentId = $tab.attr("href");
			$(tabContentId).show();
			return tabContentId;
		}.bind(this);
		
		this.historyPushHash = function(a) {
			history.pushState ? history.pushState(null, null, a) : location.hash = a
		};
		
		this.$navTabLinks.on("click", function(e) {
			e.preventDefault();
			var tabHashId = this.activateTab($(e.target));
			if(tabHashId) {
				this.historyPushHash(tabHashId);
			}
		}.bind(this));
		
		$(window).on("hashchange", function() {
			if ( !location.hash ) {
				return;
			}
			this.$navTabLinks.each(function(idx, navTabLink) {
				var $navTabLink = $(navTabLink);
				if( $navTabLink.attr("href") == location.hash ) {
					this.activateTab($navTabLink);
				}
			}.bind(this))
		}.bind(this)).trigger("hashchange");
	}))
})(jQuery);