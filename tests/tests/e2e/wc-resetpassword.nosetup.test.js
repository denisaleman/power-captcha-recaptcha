const helpers = require('./helpers');

describe('WooCommerce Reset Password Form Captcha, No-Setup', () => {
	jest.setTimeout(60000);

	let lostPasswordUrl;

	beforeAll(async () => {
		let config = {
			username : 'e2e-reset-password-user',
			email    : 'e2e@example.com',
			password : 'rq3R490Nx*P*N0vATbazgEyL',
		};

		await page.setCookie({
			name: 'pwrcap-e2e-test',
			value: '1',
			domain: 'localhost',
			path: '/',
		});
		await helpers.createAdminIfNotExists('e2e-test', 'e2eadmin@example.com', 'acF2!3$532%yfaw');
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

	it('enables woocommerce resetpassword captcha', async () => {
		// Switch to Captchas tab
		await page.click('.pwrcap-nav-tab-wrapper a[href="#tab-captchas"]');
		await page.waitForSelector('#tab-captchas', { visible: true });
	
		// Enable resetpassword captcha checkbox
		await page.click('input[name="pwrcap_captchas_options[enable_woo_resetpassword]"]');
	
		// Submit Captchas tab form
		await page.click('#tab-captchas input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });
	});

	it('logs out as admin', async () => {
		await helpers.logoutAsAdmin();
	});

	it('requests new password', async () => {
		await helpers.createUserIfNotExists('e2e-wc-reset-password-user', 'e2easdfasdfasdfasdf@example.com');
		await helpers.confirmUserByUsername('e2e-wc-reset-password-user');

		// Go to lostpassword page
		await page.goto(lostPasswordUrl);

		// Check error message
		await page.$eval('#user_login', el => el.value = '');
		await page.type('#user_login', 'e2e-wc-reset-password-user');

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
	});

	it('receives email with reset link, follows and gets to reset form', async () => {
		await page.goto('http://localhost:8025');
		await page.click('.messages .msglist-message:first-child');
		await page.waitForSelector('#preview-plain');
	
		const messagePlainBody = await page.$eval('#preview-plain', el => el.textContent);
		const match = messagePlainBody.match(/href="(http:\/\/[^"]+\/my-account\/lost-password\/\?[^"]+)"/i);
		expect(match).toBeTruthy();
	
		// Decode HTML entities in URL
		const resetLinkUrl = match[1].replace(/&amp;/g, '&');
	
		// Visit and check reset form
		await page.goto(resetLinkUrl);
		await page.waitForSelector('.woocommerce');
		const content = await page.content();
		expect(content).toContain('Enter a new password below');
	});

	it('captcha is present on resetpassword page', async () => {
		// captcha is present
		const captcha = await page.$('.pwrcap-nosetup-wrapper');
		expect(captcha).not.toBeNull();
	});

	it('doesn\'t reset password without check "I\'m not a robot"', async () => {
		// Set new password
		await page.$eval('input#password_1', el => el.value = 'rq3R490Nx*P*N0vATbazgEyL');
		await page.$eval('input#password_2', el => el.value = 'rq3R490Nx*P*N0vATbazgEyL');
		
		// Click submit
		await Promise.all([
			page.click('form.woocommerce-ResetPassword button[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// // Redirected to action resetpass
		// expect(await page.url()).toContain('http://localhost/wp-login.php?action=resetpass');

		// Check error message
		const errorText = await page.$eval('.woocommerce-error', el => el.textContent);
		expect(errorText).toContain('Google reCAPTCHA verification failed');
	});

	it('resets password solving captcha', async () => {
		// Set new password
		await page.$eval('input#password_1', el => el.value = 'rq3R490Nx*P*N0vATbazgEyL');
		await page.$eval('input#password_2', el => el.value = 'rq3R490Nx*P*N0vATbazgEyL');

		await page.click('body');                     // triggers 'mousedown'
		await new Promise(r => setTimeout(r, 3000));  // Wait to trigger "waits" signal
		
		// Click submit
		await Promise.all([
			page.click('form.woocommerce-ResetPassword button[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// // Redirected to action resetpass
		// expect(await page.url()).toContain('http://localhost/wp-login.php?action=resetpass');

		// Has the correct notice
		const errorText = await page.$eval('.woocommerce-message', el => el.textContent);
		expect(errorText).toContain('Your password has been reset successfully.');

		await new Promise(r => setTimeout(r, 5000));  // Wait to trigger "waits" signal
	});

	it('can login with new password', async () => {
		// Can login with new password
		page = await helpers.loginAsUser({ username: 'e2e-wc-reset-password-user', password : 'rq3R490Nx*P*N0vATbazgEyL'});
		
		const cookies = await page.cookies();
		const loggedInCookie = cookies.find(c => c.name.startsWith('wordpress_logged_in'));
		expect(loggedInCookie).toBeDefined();
	});
});