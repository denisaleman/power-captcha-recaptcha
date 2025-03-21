const helpers = require('./helpers');

describe('WooCommerce Register Form Captcha, No-Setup', () => {
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
		await helpers.enableWcRegistration();
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

	it('captcha is not present on woocommerce register page', async () => {
		// Go to register page
		await page.goto('http://localhost/my-account/');

		// register form is present
		const formSelector = 'form.woocommerce-form-register';
		try {
			await page.waitForSelector(formSelector);
			const form = await page.$(formSelector);
			expect(form).not.toBeNull();
		} catch (error) {
			console.log("The element didn't appear.");
			expect(1).toBeNull();
		}
		
		// captcha is not present
		const captcha = await page.$('.pwrcap-nosetup-wrapper');
		expect(captcha).toBeNull();
	});
	
	it('activates plugin as admin', async () => {
		await helpers.loginAsAdmin();
		await helpers.activatePluginBySlug('power-captcha-recaptcha');
	});
	
	it('configures reCAPTCHA keys and selects "No-Setup"', async () => {
		await page.goto('http://localhost/wp-admin/options-general.php?page=pwrcap-settings');

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

	it('enables woocommerce register captcha', async () => {
		// Switch to Captchas tab
		await page.click('.pwrcap-nav-tab-wrapper a[href="#tab-captchas"]');
		await page.waitForSelector('#tab-captchas', { visible: true });
	
		// Enable register captcha checkbox
		await page.click('input[name="pwrcap_captchas_options[enable_woo_register]"]');
	
		// Submit Captchas tab form
		await page.click('#tab-captchas input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });
	
		// Log out from admin
		await helpers.logoutAsAdmin();
	});

	it('captcha is present on woocommerce register page', async () => {
		// Go to login page
		await page.goto('http://localhost/my-account/');

		// captcha is present
		const captcha = await page.$('.pwrcap-nosetup-wrapper');
		expect(captcha).not.toBeNull();
	});

	it('doesn\'t register without solving captcha', async () => {
		await helpers.deleteUserIfFound('e2e-test-wc-register-form@example.com');

		await page.goto('http://localhost/my-account/');

		// set username and password fields
		await page.$eval('#reg_email', el => el.value = '');
		await page.$eval('#reg_password', el => el.value = '');
		await page.$eval('#reg_email', el => el.value = 'e2e-test-wc-register-form@example.com');
		await page.$eval('#reg_password', el => el.value = 'acF2!3$532%yfaw');
		
		// Click the submit button
		await Promise.all([
			page.click('form.woocommerce-form-register button[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Still on login page
		expect(await page.url()).toContain('http://localhost/my-account/');

		// Check error message
		const errorText = await page.$eval('.woocommerce-error', el => el.textContent);
		expect(errorText).toContain('Google reCAPTCHA verification failed');

		await helpers.expectUserNotFound('e2e-test-wc-register-form@example.com', 'customer');
		await helpers.deleteUserIfFound('e2e-test-wc-register-form@example.com');
	});

	it('registers solving captcha', async () => {
		await helpers.deleteUserIfFound('e2e-test-wc-register-form@example.com');

		await page.goto('http://localhost/my-account/');

		// set username and password fields
		await page.$eval('#reg_email', el => el.value = '');
		await page.$eval('#reg_password', el => el.value = '');
		await page.$eval('#reg_email', el => el.value = 'e2e-test-wc-register-form@example.com');
		await page.$eval('#reg_password', el => el.value = 'acF2!3$532%yfaw');

		// solve captcha
		await page.click('body');                     // triggers 'mousedown'
		await new Promise(r => setTimeout(r, 3000));  // Wait to trigger "waits" signal

		// Click the submit button
		await Promise.all([
			page.click('form.woocommerce-form-register button[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Expect URL to stay the same
		expect(await page.url()).toContain('/my-account/');
	
		// Ensure error message is not present
		const hasError = await page.$('.woocommerce-error');
		expect(hasError).toBeNull();
	
		// Confirm user is logged in by checking for logout link or account content
		const accountContent = await page.content();
		expect(accountContent).toContain('wp-login.php?action=logout');

		await helpers.expectUserFound('e2e-test-wc-register-form@example.com', 'customer');
		await helpers.deleteUserIfFound('e2e-test-wc-register-form@example.com');
	});
});