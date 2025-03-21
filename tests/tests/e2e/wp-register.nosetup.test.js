const helpers = require('./helpers');

describe('WordPress Register Form Captcha, No-Setup', () => {
	jest.setTimeout(60000);

	beforeAll(async () => {
		await page.setCookie({
			name: 'pwrcap-e2e-test',
			value: '1',
			domain: 'localhost',
			path: '/',
		});
		await helpers.createAdminIfNotExists();
		await helpers.deletePluginData();
		await helpers.deactivatePlugin();
		await helpers.enableRegistration();
		await helpers.loginAsAdmin();
		await helpers.activatePluginBySlug('power-captcha-recaptcha');
	});

	afterAll(async () => {
		await page.setCookie({
			name: 'pwrcap-e2e-test',
			value: '',
			domain: 'localhost',
			path: '/',
			expires: Date.now() / 1000 - 60,
		});
	});

	it('opens plugin settings', async () => {
		await page.goto('http://localhost/wp-admin/options-general.php?page=pwrcap-settings');
	});

	it('configures reCAPTCHA keys and selects "No-Setup"', async () => {
		// Fill in site and secret keys in General tab
		await page.$eval('input[name="pwrcap_general_options[site_key]"]', el => el.value = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
		await page.$eval('input[name="pwrcap_general_options[secret_key]"]', el => el.value = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
	
		// Wait for options
		await page.waitForSelector('input[name="pwrcap_general_options[captcha_type]"][value="no-setup"]', { visible: true });
	
		// Select "No-Setup" Checkbox option
		await page.click('input[name="pwrcap_general_options[captcha_type]"][value="no-setup"]');
	
		// Submit the form and wait for save
		await page.click('#tab-general input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });
	});

	it('enables register captcha', async () => {
		// Switch to Captchas tab
		await page.click('.pwrcap-nav-tab-wrapper a[href="#tab-captchas"]');
		await page.waitForSelector('#tab-captchas', { visible: true });
	
		// Enable register captcha checkbox
		await page.click('input[name="pwrcap_captchas_options[enable_register]"]');
	
		// Submit Captchas tab form
		await page.click('#tab-captchas input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });
	
		// Log out from admin
		await page.goto('http://localhost/wp-admin/');
		await page.waitForSelector('#wp-admin-bar-logout > a');
		const logoutHref = await page.$eval('#wp-admin-bar-logout > a', el => el.href);
		await page.goto(logoutHref);
	});

	it('captcha is present on register page', async () => {
		// Go to register page
		await page.goto('http://localhost/wp-login.php?action=register');
		await page.waitForSelector('.pwrcap-nosetup-wrapper');

		const captcha = await page.$('.pwrcap-nosetup-wrapper');
		expect(captcha).not.toBeNull();
	});

	it('doesn\'t register without solving captcha', async () => {
		await helpers.deleteUserIfFound('e2e-test-register-form');

		const page = await helpers.registerAsTestUserThroughtWpRegisterForm({
			username: 'e2e-test-register-form',
			email: 'e2e-test-register-form@e2e.com'
		});

		// Still on login page
		expect(await page.url()).toContain('http://localhost/wp-login.php?action=register');

		// Check error message
		const errorText = await page.$eval('#login_error', el => el.textContent);
		expect(errorText).toContain('Google reCAPTCHA verification failed');

		await helpers.expectUserNotFound('e2e-test-register-form', 'subscriber');

		await helpers.deleteUserIfFound('e2e-test-register-form');
	});

	it('registers solving captcha', async () => {
		await helpers.deleteUserIfFound('e2e-test-register-form');

		await helpers.registerAsTestUserThroughtWpRegisterForm( {
			username: 'e2e-test-register-form',
			email:    'e2e-test-register-form@e2e.com',
			beforeSubmit: async function () {
				await page.click('body');                     // triggers 'mousedown'
				await new Promise(r => setTimeout(r, 3000));  // Wait to trigger "waits" signal
			}
		} );

		// Still on login page
		expect(await page.url()).toContain('http://localhost/wp-login.php?checkemail=registered');

		// Check success message
		const errorText = await page.$eval('#login-message', el => el.textContent);
		expect(errorText).toContain('Registration complete. Please check your email, then visit the login page.');

		await helpers.expectUserFound('e2e-test-register-form', 'subscriber');

		await helpers.deleteUserIfFound('e2e-test-register-form');
	});
});