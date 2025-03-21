const helpers = require('./helpers');

describe('WordPress Reset Password Form Captcha, No-Setup', () => {
	jest.setTimeout(60000);

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
		await helpers.deleteUserIfFound('e2e-reset-password-user');
		await helpers.createUserIfNotExists('e2e-reset-password-user', 'e2e@example.com');
		await helpers.confirmUserByUsername('e2e-reset-password-user');
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

	it('enables resetpassword captcha', async () => {
		// Switch to Captchas tab
		await page.click('.pwrcap-nav-tab-wrapper a[href="#tab-captchas"]');
		await page.waitForSelector('#tab-captchas', { visible: true });
	
		// Enable register captcha checkbox
		await page.click('input[name="pwrcap_captchas_options[enable_resetpassword]"]');
	
		// Submit Captchas tab form
		await page.click('#tab-captchas input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });
	
		// Log out from admin
		await page.goto('http://localhost/wp-admin/');
		await page.waitForSelector('#wp-admin-bar-logout > a');
		const logoutHref = await page.$eval('#wp-admin-bar-logout > a', el => el.href);
		await page.goto(logoutHref);
	});

	it('requests new password', async () => {
		// Go to lostpassword page
		await page.goto('http://localhost/wp-login.php?action=lostpassword');

		// Check error message
		await page.$eval('#user_login', el => el.value = '');
		await page.type('#user_login', 'e2e-reset-password-user');

		await Promise.all([
			page.click('#wp-submit'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Still on login page
		expect(await page.url()).toContain('http://localhost/wp-login.php?checkemail=confirm');

		// Check confirmation message
		const confirmText = await page.$eval('#login-message', el => el.textContent);
		expect(
			confirmText.includes('Check your email for the confirmation link, then visit the') ||
			confirmText.includes('The email could not be sent')
		).toBe(true);
	});

	it('receives email with reset link, follows and gets to reset form', async () => {
		// Go to mailhog
		await page.goto('http://localhost:8025');

		// Open last sent email
		await page.click('.messages .msglist-message:first-child');

		// Check error message
		const messagePlainBody = await page.$eval('#preview-plain', el => el.textContent);
		expect(messagePlainBody).toContain('Someone has requested a password reset for the following account');

		// extract the link URL to reset password
		const resetLinkUrl = await page.$eval('#preview-plain a', el => el.href);

		// go to reset password page
		await page.goto(resetLinkUrl);
		const noticeText = await page.$eval('.notice.reset-pass', el => el.textContent);
		expect(noticeText).toContain('Enter your new password below or generate one.');
	});

	it('captcha is present on resetpassword page', async () => {
		await page.waitForSelector('.pwrcap-nosetup-wrapper');

		const captcha = await page.$('.pwrcap-nosetup-wrapper');
		expect(captcha).not.toBeNull();
	});

	it('doesn\'t reset password without solving captcha', async () => {
		// Set new password
		await page.waitForSelector('input#pass1');
		await page.$eval('input#pass1', el => el.value = 'rq3R490Nx*P*N0vATbazgEyL');
		
		// Click submit
		await Promise.all([
			page.click('#wp-submit'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Redirected to action resetpass
		expect(await page.url()).toContain('http://localhost/wp-login.php?action=resetpass');

		// Check error message
		const errorText = await page.$eval('#login_error', el => el.textContent);
		expect(errorText).toContain('Google reCAPTCHA verification failed');
	});

	it('resets password with solving captcha', async () => {
		await page.click('body');                     // triggers 'mousedown'
		await new Promise(r => setTimeout(r, 3000));  // Wait to trigger "waits" signal

		// set new password
		await page.$eval('input#pass1', el => el.value = 'rq3R490Nx*P*N0vATbazgEyL');
		
		// Click submit
		await Promise.all([
			page.click('#wp-submit'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Redirected to action resetpass
		expect(await page.url()).toContain('http://localhost/wp-login.php?action=resetpass');

		// Has the correct notice
		const errorText = await page.$eval('.notice.reset-pass', el => el.textContent);
		expect(errorText).toContain('Your password has been reset.');
	});

	it('can login with new password', async () => {
		// Can login with new password
		page = await helpers.loginAsUser({ username: 'e2e-reset-password-user', password : 'rq3R490Nx*P*N0vATbazgEyL'});
		
		const cookies = await page.cookies();
		const loggedInCookie = cookies.find(c => c.name.startsWith('wordpress_logged_in'));
		expect(loggedInCookie).toBeDefined();
	});
});