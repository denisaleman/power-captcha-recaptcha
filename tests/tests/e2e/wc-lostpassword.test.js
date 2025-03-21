const helpers = require('./helpers');

describe('WooCommerce Lost Password Form Captcha, v2 "I\'m not a robot"', () => {
	jest.setTimeout(60000);

	let lostPasswordUrl;

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
		lostPasswordUrl = await helpers.getWcLostPasswordURL();
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

	it('captcha is not present on woocommerce lostpassword page', async () => {
		// Go to register page
		await page.goto(lostPasswordUrl);

		// form is present
		const formSelector = 'form.woocommerce-ResetPassword';
		try {
			await page.waitForSelector(formSelector);
			const form = await page.$(formSelector);
			expect(form).not.toBeNull();
		} catch (error) {
			console.log("The element didn't appear.");
			expect(1).toBeNull();
		}
		
		// captcha is not present
		const captcha = await page.$('.pwrcap-wrapper[data-context="woocommerce_lostpassword_form"]');
		expect(captcha).toBeNull();
	});
	
	it('activates plugin as admin', async () => {
		await helpers.loginAsAdmin();
		await helpers.activatePluginBySlug('power-captcha-recaptcha');
	});

	it('configures reCAPTCHA and enables lostpassword captcha', async () => {
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
	
		// Enable lostpassword captcha checkbox
		await page.click('input[name="pwrcap_captchas_options[enable_woo_lostpassword]"]');
	
		// Submit Captchas tab form
		await page.click('#tab-captchas input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });
	
		
	});

	it('logs out as admin', async () => {
		// Log out from admin
		await page.goto('http://localhost/wp-admin/');
		await page.waitForSelector('#wp-admin-bar-logout > a');
		const logoutHref = await page.$eval('#wp-admin-bar-logout > a', el => el.href);
		await page.goto(logoutHref);
	});

	it('captcha is present on woocommerce lostpassword page', async () => {
		// Go to register page
		await page.goto(lostPasswordUrl);

		// login form is present
		const resetPasswordForm = await page.$('form.woocommerce-ResetPassword');
		expect(resetPasswordForm).not.toBeNull();
		
		// captcha is present
		const iframeSrcs = await page.$$eval('.pwrcap-wrapper iframe', iframes =>
			iframes.map(iframe => iframe.src)
		);
	
		const recaptchaIframeFound = iframeSrcs.some(src =>
			src.startsWith('https://www.google.com/recaptcha')
		);
	
		expect(recaptchaIframeFound).toBe(true);
	});

	it('doesn\'t retrieve password reset link without check "I\'m not a robot"', async () => {
		await helpers.createUserIfNotExists('e2e-wc-retrieve-password-user', 'e2easdfasdfasdfasdf@example.com');
		await helpers.confirmUserByUsername('e2e-wc-retrieve-password-user');

		// Go to lostpassword page
		await page.goto(lostPasswordUrl);

		// Check error message
		await page.$eval('#user_login', el => el.value = '');
		await page.type('#user_login', 'e2e-wc-retrieve-password-user');

		await Promise.all([
			page.click('form.woocommerce-ResetPassword button[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Still on the page
		expect(await page.url()).toContain(lostPasswordUrl);

		// Check error message
		const errorText = await page.$eval('.woocommerce-error', el => el.textContent);
		expect(errorText).toContain('Google reCAPTCHA verification failed');

		await helpers.deleteUserIfFound('e2e-wc-retrieve-password-user');
	});

	it('retrieves password reset link with check "I\'m not a robot"', async () => {
		await helpers.createUserIfNotExists('e2e-wc-retrieve-password-user', 'e2easdfasdfasdfasdf@example.com');
		await helpers.confirmUserByUsername('e2e-wc-retrieve-password-user');

		// Go to lostpassword page
		await page.goto(lostPasswordUrl);

		// Check error message
		await page.$eval('#user_login', el => el.value = '');
		await page.type('#user_login', 'e2e-wc-retrieve-password-user');

		await helpers.checkRecaptchaCheckbox();

		await Promise.all([
			page.click('form.woocommerce-ResetPassword button[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Still on the page
		expect(await page.url()).toContain(lostPasswordUrl);

		// Check for either of the expected messages
		const pageContent = await page.content();
		const normalizedText = pageContent.replace(/\s+/g, ' ').toLowerCase();
		expect(
			normalizedText.includes('password reset email has been sent') ||
			normalizedText.includes('the email could not be sent')
		).toBe(true);

		await helpers.deleteUserIfFound('e2e-wc-retrieve-password-user');
	});

	it('verifies captcha before user existance', async () => {
		// Go to lostpassword page
		await page.goto(lostPasswordUrl);

		// Check error message
		await page.$eval('#user_login', el => el.value = '');
		await page.type('#user_login', 'unexisting-user');

		await Promise.all([
			page.click('form.woocommerce-ResetPassword button[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Still on login page
		expect(await page.url()).toContain(lostPasswordUrl);

		// Check error message
		const errorText = await page.$eval('.woocommerce-error', el => el.textContent);
		expect(errorText).toContain('Google reCAPTCHA verification failed');

		await helpers.deleteUserIfFound('e2e-wc-retrieve-password-user');
	});

	it('notices that user doesn\'t exist with correct captcha check and unexisting user', async () => {
		// Go to lostpassword page
		await page.goto(lostPasswordUrl);

		await page.$eval('#user_login', el => el.value = '');
		await page.type('#user_login', 'unexisting-user');

		await helpers.checkRecaptchaCheckbox();

		await Promise.all([
			page.click('form.woocommerce-ResetPassword button[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Still on login page
		expect(await page.url()).toContain(lostPasswordUrl);

		// Check error message
		const errorText = await page.$eval('.woocommerce-error', el => el.textContent);
		expect(errorText).toContain('Invalid username or email.');
	});
});