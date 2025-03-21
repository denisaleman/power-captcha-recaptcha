const helpers = require('./helpers');

describe('WooCommerce Login Form Captcha, v2 "I\'m not a robot"', () => {
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

	it('captcha is not present on woocommerce login page', async () => {
		// Go to login page
		await page.goto('http://localhost/my-account/');

		// login form is present
		const formSelector = 'form.woocommerce-form-login';
		try {
			await page.waitForSelector(formSelector);
			const form = await page.$(formSelector);
			expect(form).not.toBeNull();
		} catch (error) {
			console.log("The element didn't appear.");
			expect(1).toBeNull();
		}
		
		// captcha is not present
		const captcha = await page.$('.pwrcap-wrapper[data-context="woocommerce_login_form"]');
		expect(captcha).toBeNull();
	});

	it('activates plugin as admin', async () => {
		await helpers.loginAsAdmin();
		await helpers.activatePluginBySlug('power-captcha-recaptcha');
	});

	it('configures reCAPTCHA and enables login captcha', async () => {
		await page.goto('http://localhost/wp-admin/options-general.php?page=pwrcap-settings');
	
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
	
		// Switch to Captchas tab
		await page.click('.pwrcap-nav-tab-wrapper a[href="#tab-captchas"]');
		await page.waitForSelector('#tab-captchas', { visible: true });
	
		// Enable woocommerce login captcha checkbox
		await page.click('input[name="pwrcap_captchas_options[enable_woo_login]"]');
	
		// Submit Captchas tab form
		await page.click('#tab-captchas input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });
	
		// Log out from admin
		await helpers.logoutAsAdmin();
	});

	it('captcha is present on woocommerce login page', async () => {
		// Go to login page
		await page.goto('http://localhost/my-account/');
		await page.waitForSelector('.pwrcap-wrapper[data-context="woocommerce_login_form"]', { visible: true });

		const iframeSrcs = await page.$$eval('.pwrcap-wrapper[data-context="woocommerce_login_form"] iframe', iframes =>
			iframes.map(iframe => iframe.src)
		);

		const recaptchaIframeFound = iframeSrcs.some(src =>
			src.startsWith('https://www.google.com/recaptcha')
		);

		expect(recaptchaIframeFound).toBe(true);
	});

	it('shows "username is required" on empty submission', async () => {
		await page.goto('http://localhost/my-account/');
		await page.waitForSelector('.pwrcap-wrapper[data-context="woocommerce_login_form"]', { visible: true });
	
		// Ensure username and password fields are empty
		await page.$eval('#username', el => el.value = '');
		await page.$eval('#password', el => el.value = '');
	
		// Skip typing anything (simulate empty form submission)
	
		// Submit the form
		await Promise.all([
			page.click('button[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	
		// Still on login page
		expect(await page.url()).toContain('http://localhost/my-account/');

		// Is not logged in
		const accountContent = await page.content();
		expect(accountContent).not.toContain('wp-login.php?action=logout');
	
		// Check for required field error
		const errorText = await page.$eval('.woocommerce-error', el => el.textContent);
		expect(errorText).toMatch(/username is required/i);
	});

	it('shows "username is required" on empty submission with captcha check', async () => {
		await page.goto('http://localhost/my-account/');
		await page.waitForSelector('.pwrcap-wrapper[data-context="woocommerce_login_form"]', { visible: true });
	
		// Ensure username and password fields are empty
		await page.$eval('#username', el => el.value = '');
		await page.$eval('#password', el => el.value = '');
	
		// Skip typing anything (simulate empty form submission)
	
		// check the reCAPTCHA
		await helpers.checkRecaptchaCheckbox('.pwrcap-wrapper[data-context="woocommerce_login_form"]');
	
		// Submit the form
		await Promise.all([
			page.click('button[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	
		// Still on login page
		expect(await page.url()).toContain('http://localhost/my-account/');

		// Is not logged in
		const accountContent = await page.content();
		expect(accountContent).not.toContain('wp-login.php?action=logout');
	
		// Check for required field error
		const errorText = await page.$eval('.woocommerce-error', el => el.textContent);
		expect(errorText).toMatch(/username is required/i);
	});

	it('doesn\'t log in without check "I\'m not a robot"', async () => {
		await page.goto('http://localhost/my-account/');
		await page.waitForSelector('.pwrcap-wrapper', { visible: true });

		// Clear the username and password fields before typing
		await page.$eval('#username', el => el.value = '');
		await page.$eval('#password', el => el.value = '');

		// Type in the username and password
		await page.type('#username', 'e2e-test');
		await page.type('#password', 'acF2!3$532%yfaw');

		// Click the submit button
		await Promise.all([
			page.click('form.woocommerce-form-login button[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Still on login page
		expect(await page.url()).toContain('http://localhost/my-account/');

		// Is not logged in
		const accountContent = await page.content();
		expect(accountContent).not.toContain('wp-login.php?action=logout');

		// Check error message
		const errorText = await page.$eval('.woocommerce-error', el => el.textContent);
		expect(errorText).toContain('Google reCAPTCHA verification failed');
	});

	it('logs in with valid credentials and passes reCAPTCHA', async () => {
		await page.goto('http://localhost/my-account/');
		await page.waitForSelector('.pwrcap-wrapper', { visible: true });
	
		// Clear the username and password fields
		await page.$eval('#username', el => el.value = '');
		await page.$eval('#password', el => el.value = '');
	
		// Type in the username and password
		await page.type('#username', 'e2e-test');
		await page.type('#password', 'acF2!3$532%yfaw');
	
		// Check reCAPTCHA checkbox
		await helpers.checkRecaptchaCheckbox('.pwrcap-wrapper[data-context="woocommerce_login_form"]');
	
		// Submit the login form
		await Promise.all([
			page.click('button[type="submit"]'),
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
	});
});