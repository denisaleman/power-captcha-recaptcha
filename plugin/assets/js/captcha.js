"use strict";

(function() {
  var settings = pwrcapGetLocalizedSettings();
  if( settings.debug ) {
    console.log( settings );
  }
})();

function pwrcapGetLocalizedSettings() {
  if( typeof pwrcap === 'undefined' ) {
    throw new Error('Power Captcha reCAPTCHA Error: pwrcap is undefined, check plugin.');
  }
  return pwrcap;
}

function pwrcapRenderV2cbx($container, fitToContainer = false) {
  var settings = pwrcapGetLocalizedSettings();
  var holderId = grecaptcha.render($container, {
    'sitekey'  : settings.site_key,
    'callback' : function() { 
      /**
       * @todo make an event instead of direct call
       */
      pwrcapCf7VerifyCallback($container, holderId);
    },
  });
  if(fitToContainer) {
    var scaleFactor = $container.clientWidth / $container.children[0].clientWidth;
    $container.style.transform = 'scale('+scaleFactor+')';
  }
}

function pwrcapCf7VerifyCallback($container, holderId) {
  const formControl = $container.closest('.wpcf7-form-control');
  
  if (formControl) {
      const validationTip = formControl.querySelector('.wpcf7-not-valid-tip');
      if (validationTip) {
          validationTip.remove();
      }
  }
}

function pwrcapInitV2cbx() {
  var $captchaWrapper, context;
  $captchaWrapper = document.getElementsByClassName('pwrcap-wrapper');
  for (var i = 0; i < $captchaWrapper.length; i++) {
    context = $captchaWrapper.item(i).getAttribute('data-context');
    switch (context)
    {
      case "login_form":
      case "register_form":
      case "lostpassword_form":
      case "resetpass_form":
        pwrcapRenderV2cbx($captchaWrapper.item(i), true);
        break;
      default: 
        pwrcapRenderV2cbx($captchaWrapper.item(i));
    }
  }
}

function pwrcapInitV2inv() {
  var settings = pwrcapGetLocalizedSettings();
  console.log(document.forms);
  for (var i = 0; i < document.forms.length; ++i) {
    (function(form, settings) {
      var $captchaWrapper = form.getElementsByClassName('pwrcap-wrapper');
      if( !$captchaWrapper.item(0) ) {
        return;
      }
      var holderId = grecaptcha.render($captchaWrapper[0], {
        'sitekey'  : settings.site_key,
        'size'     : 'invisible',
        'badge'    : 'bottomright', // possible values: bottomright, bottomleft, inline
        'callback' : function () {
          HTMLFormElement.prototype.submit.call(form);
        }
      });

      form.onsubmit = function (event) {
        event.preventDefault();
        grecaptcha.execute(holderId);
      };

    })(document.forms[i], settings);
  }
}

function pwrcapInitV3() {
  var settings = pwrcapGetLocalizedSettings();
  grecaptcha.execute(settings.site_key, {
    action: 'validate_recaptchav3'
  }).then(function (token) {
    document.querySelectorAll('.g-recaptcha-response').forEach(function (elem) {
      elem.value = token;
    });
  });
}