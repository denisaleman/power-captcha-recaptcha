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

	it('enables lostpassword captcha', async () => {
		// Switch to Captchas tab
		await page.click('.pwrcap-nav-tab-wrapper a[href="#tab-captchas"]');
		await page.waitForSelector('#tab-captchas', { visible: true });
	
		// Enable lostpassword captcha checkbox
		await page.click('input[name="pwrcap_captchas_options[enable_lostpassword]"]');
	
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
		// Go to lostpassword page
		await page.goto('http://localhost/wp-login.php?action=lostpassword');
		await page.waitForSelector('.pwrcap-nosetup-wrapper');

		const captcha = await page.$('.pwrcap-nosetup-wrapper');
		expect(captcha).not.toBeNull();
	});

	it('doesn\'t retrieve password reset link without solving captcha', async () => {
		await helpers.createUserIfNotExists('e2e-retrieve-password-user', 'e2e@example.com');
		await helpers.confirmUserByUsername('e2e-retrieve-password-user');

		// Go to lostpassword page
		await page.goto('http://localhost/wp-login.php?action=lostpassword');

		// Check error message
		await page.$eval('#user_login', el => el.value = '');
		await page.$eval('#user_login', el => el.value = 'e2e-retrieve-password-user');

		await Promise.all([
			page.click('#wp-submit'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Still on login page
		expect(await page.url()).toContain('http://localhost/wp-login.php?action=lostpassword');

		// Check error message
		const errorText = await page.$eval('#login_error', el => el.textContent);
		expect(errorText).toContain('Google reCAPTCHA verification failed');

		await helpers.deleteUserIfFound('e2e-retrieve-password-user');
	});

	it('retrieves password reset link solving captcha', async () => {
		await helpers.createUserIfNotExists('e2e-retrieve-password-user', 'e2e@example.com');
		await helpers.confirmUserByUsername('e2e-retrieve-password-user');

		// Go to lostpassword page
		await page.goto('http://localhost/wp-login.php?action=lostpassword');

		// Check error message
		await page.$eval('#user_login', el => el.value = '');
		await page.$eval('#user_login', el => el.value = 'e2e-retrieve-password-user');

		await page.click('body');                     // triggers 'mousedown'
		await new Promise(r => setTimeout(r, 3000));  // Wait to trigger "waits" signal

		await Promise.all([
			page.click('#wp-submit'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Still on login page
		expect(await page.url()).toContain('http://localhost/wp-login.php?checkemail=confirm');

		// Check error message
		const confirmText = await page.$eval('#login-message', el => el.textContent);
		expect(
			confirmText.includes('Check your email for the confirmation link, then visit the') ||
			confirmText.includes('The email could not be sent')
		).toBe(true);

		await helpers.deleteUserIfFound('e2e-retrieve-password-user');
	});
});