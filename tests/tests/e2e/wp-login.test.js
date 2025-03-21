const helpers = require('./helpers');

describe('WordPress Login Form Captcha, v2 "I\'m not a robot"', () => {
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

	it('configures reCAPTCHA keys and selects v2 "I\'m not a robot"', async () => {
		// Fill in site and secret keys in General tab
		await page.$eval('input[name="pwrcap_general_options[site_key]"]', el => el.value = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
		await page.$eval('input[name="pwrcap_general_options[secret_key]"]', el => el.value = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
	
		// Select "Challenge (v2)" radio
		await page.click('input[name="pwrcap_general_options[captcha_type]"][value="v2"]');
	
		// Wait for sub options
		await page.waitForSelector('input[name="pwrcap_general_options[captcha_v2_type]"][value="v2cbx"]', { visible: true });
	
		// Select "I'm not a robot" Checkbox option
		await page.click('input[name="pwrcap_general_options[captcha_v2_type]"][value="v2cbx"]');
	
		// Submit the form and wait for save
		await page.click('#tab-general input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });
	});

	it('enables login captcha', async () => {
		// Switch to Captchas tab
		await page.click('.pwrcap-nav-tab-wrapper a[href="#tab-captchas"]');
		await page.waitForSelector('#tab-captchas', { visible: true });
	
		// Enable login captcha checkbox
		await page.click('input[name="pwrcap_captchas_options[enable_login]"]');
	
		// Submit Captchas tab form
		await page.click('#tab-captchas input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });
	
		// Log out from admin
		await page.goto('http://localhost/wp-admin/');
		await page.waitForSelector('#wp-admin-bar-logout > a');
		const logoutHref = await page.$eval('#wp-admin-bar-logout > a', el => el.href);
		await page.goto(logoutHref);
	});

	it('captcha is present on login page', async () => {
		// Go to login page
		await page.goto('http://localhost/wp-login.php');
		await page.waitForSelector('.pwrcap-wrapper', { visible: true });

		const iframeSrcs = await page.$$eval('.pwrcap-wrapper iframe', iframes =>
			iframes.map(iframe => iframe.src)
		);

		const recaptchaIframeFound = iframeSrcs.some(src =>
			src.startsWith('https://www.google.com/recaptcha')
		);

		expect(recaptchaIframeFound).toBe(true);
	});

	it('there is no login error notice', async () => {
		const loginErrorNotice = await page.$('#login_error');
		expect(loginErrorNotice).toBeNull();
	});

	it('doesn\'t logs in without check "I\'m not a robot"', async () => {
		const page = await helpers.loginAsAdminExpectingFailure();

		// Still on login page
		expect(await page.url()).toContain('wp-login.php');

		// Check error message
		const errorText = await page.$eval('#login_error', el => el.textContent);
		expect(errorText).toContain('Google reCAPTCHA verification failed');
	});

	it('logs in with check "I\'m not a robot"', async () => {
		await helpers.loginAsAdmin({ beforeSubmit: helpers.checkRecaptchaCheckbox});

		await new Promise(resolve => setTimeout(resolve, 20000));
	
		// Verify we are on the WordPress admin dashboard after login
		expect(await page.url()).toContain('/wp-admin/');
	});
});