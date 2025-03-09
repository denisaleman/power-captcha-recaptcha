=== Power Captcha reCAPTCHA ===
Contributors: denisaleman
Donate link: https://www.buymeacoffee.com/denisaleman
Tags: captcha, google recaptcha, comment form, login security, anti-spam security, form protection, woocommerce
Requires at least: 5.0
Tested up to: 6.8.0
Stable tag: 1.1.0
Requires PHP: 5.5
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Protect WordPress/WooCommerce/Contact Form 7 forms from spam, brute-force attacks, fake comments, accounts, or registrations with Google reCAPTCHA.

== Description ==

Protect your WordPress, WooCommerce, and Contact Form 7 forms from spam, brute-force attacks, and fake accounts using Google reCAPTCHA.

Power Captcha reCAPTCHA supports 3 Google reCAPTCHA types integrated into 6 common WordPress forms, including login and comment forms, 7 WooCommerce forms, and Contact Form 7.

== 3 CAPTCHA Types ==

- **Score-based (v3) CAPTCHA.** Seamless detection.
- **"I'm not a robot" CAPTCHA checkbox.** Verification requests with a challenge.
- **Invisible reCAPTCHA.** Improved, challenge-based CAPTCHA without a checkbox.

== 6 WordPress Forms ==
- **Login form**
- **Register form**
- **Comment form**
- **Lost password form**
- **Reset password form**
- **Register form**

== 7 WooCommerce Forms ==
- **Login form**
- **Register form**
- **Checkout form**
- **Review form**
- **Reset password form**
- **Lost password form**

== Contact Form 7 ==

As of version 1.0.7, Power Captcha reCAPTCHA integrates with Contact Form 7. You can easily add the Power Captcha reCAPTCHA field to your Contact Form 7 forms.

== Activity Report ==

The Activity Report feature for the plugin provides users with a detailed overview of captcha interactions. It tracks and displays the number of solved, failed, and empty captchas, offering a daily breakdown to monitor performance trends. Stay informed with clear insights into your captcha performance.

== Installation ==

1. Upload the `power-captcha-recaptcha` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Sign up at [Google reCAPTCHA console](https://www.google.com/recaptcha/admin) to get your Site Key and Secret Key (it's FREE).
4. Enter your Site Key and Secret Key in Settings -> Power Captcha menu in WordPress.
5. Enable the desired CAPTCHA types.

== Frequently Asked Questions ==

= How do I use Power Captcha reCAPTCHA? =

Power Captcha reCAPTCHA is an anti-spam solution that helps you secure forms and stop bots from submitting them.
After completing the setup, go to the "Captcha" tab on the settings page and select the forms you want to protect. The CAPTCHA will be integrated into the selected forms.

= Do I need to code to implement captchas? =

No coding required. The plugin provides one-click integration for all supported WordPress and WooCommerce forms.

= The plugin doesn't support some forms I need. How can I get support added for them? =

Please create a new topic with a feature request in [our support forum](https://wordpress.org/support/plugin/power-captcha-recaptcha/).

= Is the plugin relying on third-party services? =

Yes. The functionality of the plugin relies on the integration of a third-party service, specifically Google reCAPTCHA. For a thorough understanding of the service, including its terms of service and privacy policy, please refer to the official documentation provided at [Google reCAPTCHA](https://www.google.com/recaptcha/about/).

= Where can I get more information about Google reCAPTCHA? =

Read [the official documentation](https://www.google.com/recaptcha/about/)

== Screenshots ==

1. Login form CAPTCHA.
2. Example for a comment form.
3. Available CAPTCHAs for WordPress.
4. Available CAPTCHAs for WooCommerce.
5. General settings view.

== Changelog ==

= 1.0.0 =
* Plugin released.

= 1.0.1 (2024-06-11) =
* Tested up to 6.5.4
* WooCommerce tested up to 8.9.3

= 1.0.2 (2024-07-01) =
* Tested up to 6.5.5
* Screenshots added.

= 1.0.3 (2024-07-18) =
* Tested up to 6.6
* Typo fixed.

= 1.0.4 (2024-07-18) =
* Added Spanish translations.

= 1.0.5 (2024-07-28) =
* Tested up to 6.6.1
* Improved Spanish translations.
* Improved Readme.
* Fixed minor bugs.

= 1.0.6 (2024-10-28) =
* Tested up to 6.7

= 1.0.7 (2024-11-08) =
* Added Contact Form 7 integration.

= 1.0.8 (2024-11-11) =
* Added reCAPTHA reset on submit for Contact Form 7.

= 1.0.9 (2024-11-11) =
* Enhanced reCAPTHA field behavior for Contact Form 7.

= 1.0.10 (2024-12-13) =
* Added Russian translations.
* WooCommerce tested up to 9.4.3

= 1.1.0 (2025-03-09) =
* Tested up to 6.8.0
* WooCommerce tested up to 9.7.1
* Added captcha activity report feature.
* Fixed minor bugs.