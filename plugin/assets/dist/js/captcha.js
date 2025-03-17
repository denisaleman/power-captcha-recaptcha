/* empty css            */
function getSettings() {
  if (!window.pwrcap) {
    throw new Error(
      "Power Captcha reCAPTCHA Error: pwrcap is undefined."
    );
  }
  return window.pwrcap;
}
const settings = getSettings();
if (settings.debug) {
  console.log(settings);
}
function cf7VerifyCallback(container) {
  const formControl = container.closest(".wpcf7-form-control");
  formControl?.querySelector(".wpcf7-not-valid-tip")?.remove();
}
function render($container, fitToContainer = false) {
  grecaptcha.render($container, {
    sitekey: settings.site_key,
    callback() {
      cf7VerifyCallback($container);
    }
  });
  if (fitToContainer) {
    const scale = $container.clientWidth / $container.children[0].clientWidth;
    $container.style.transform = `scale(${scale})`;
  }
}
function initV2Checkbox() {
  document.querySelectorAll(".pwrcap-wrapper").forEach((wrapper) => {
    const fit = [
      "login_form",
      "register_form",
      "lostpassword_form",
      "resetpass_form"
    ].includes(wrapper.dataset.context);
    render(wrapper, fit);
  });
}
function initV2Invisible() {
  document.querySelectorAll("form").forEach((form) => {
    const wrapper = form.querySelector(".pwrcap-wrapper");
    if (!wrapper) {
      return;
    }
    const holderId = grecaptcha.render(wrapper, {
      sitekey: settings.site_key,
      size: "invisible",
      badge: "bottomright",
      callback() {
        HTMLFormElement.prototype.submit.call(form);
      }
    });
    form.addEventListener("submit", (event) => {
      event.preventDefault();
      grecaptcha.execute(holderId);
    });
  });
}
function initV3() {
  grecaptcha.execute(settings.site_key, {
    action: "validate_recaptchav3"
  }).then((token) => {
    document.querySelectorAll(".g-recaptcha-response").forEach((element) => {
      element.value = token;
    });
  });
}
window.pwrcapInitV2cbx = initV2Checkbox;
window.pwrcapInitV2inv = initV2Invisible;
window.pwrcapInitV3 = initV3;
//# sourceMappingURL=captcha.js.map
