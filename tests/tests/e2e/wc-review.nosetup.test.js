const helpers = require('./helpers');

/**
 * This works in Astra, 
 *
 * @todo test with OceanWP
 */

describe('WooCommerce Review Form Captcha, No-Setup', () => {
	jest.setTimeout(60000);

	beforeAll(async () => {
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
		await helpers.prepareReviewProduct('test-review-product');
	});

	afterAll(async () => {
		await helpers.deleteUserIfFound('e2e-reset-password-user');
		await helpers.tearDownReviewProduct('test-review-product');
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

	it('enables woocommerce review captcha', async () => {
		// Switch to Captchas tab
		await page.click('.pwrcap-nav-tab-wrapper a[href="#tab-captchas"]');
		await page.waitForSelector('#tab-captchas', { visible: true });
	
		// Enable review captcha checkbox
		await page.click('input[name="pwrcap_captchas_options[enable_comment]"]');
	
		// Submit Captchas tab form
		await page.click('#tab-captchas input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });
	});

	it('goes to a product page', async () => {
		await Promise.all([
			page.goto('http://localhost/product/' + 'test-review-product' + '/'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	});

	it('doesn\'t show captcha for logged in users', async () => {
		// there is a comment form
		const formSelector = 'form[action="http://localhost/wp-comments-post.php"]';
		try {
			await page.waitForSelector(formSelector);
			const form = await page.$(formSelector);
			expect(form).not.toBeNull();
		} catch (error) {
			console.log("The element didn't appear.");
			expect(1).toBeNull();
		}

		// captcha is not present on the page
		const captcha = await page.$('.pwrcap-nosetup-wrapper');
		expect(captcha).toBeNull();
	});

	it('logs out as admin', async () => {
		await helpers.logoutAsAdmin();
	});

	it('goes to a product page', async () => {
		await Promise.all([
			page.goto('http://localhost/product/' + 'test-review-product' + '/'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	});

	it('shows captcha for guests', async () => {
		// there is a comment form
		const formExists = await page.$eval('form[action="http://localhost/wp-comments-post.php"]', () => true).catch(() => false);
		expect(formExists).toBe(true);

		// captcha is present on the pag
		const captcha = await page.$('.pwrcap-nosetup-wrapper');
		expect(captcha).not.toBeNull();
	});

	it('cannot leave comment without solving captcha', async () => {
		await Promise.all([
			page.goto('http://localhost/product/' + 'test-review-product' + '/'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		await page.$eval('textarea[name="comment"]', el => el.value = 'Just a test review');
		await page.$eval('input[name="author"]', el => el.value = 'e2e test');
		await page.$eval('input[name="email"]', el => el.value = 'e2e@example.com');
		await page.click('.stars a[role="radio"]:nth-child(5)');

		// submit comment
		await Promise.all([
			page.click('form[action="http://localhost/wp-comments-post.php"] input[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		expect(await page.url()).toContain('http://localhost/wp-comments-post.php');

		const content = await page.content();
		expect(content).toContain('Google reCAPTCHA verification failed');
	});

	it('leaves comment solving captcha', async () => {
		await Promise.all([
			page.goto('http://localhost/product/' + 'test-review-product' + '/'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		await page.$eval('textarea[name="comment"]', el => el.value = 'Just a test review');
		await page.$eval('input[name="author"]', el => el.value = 'e2e test');
		await page.$eval('input[name="email"]', el => el.value = 'e2e@example.com');
		await page.click('.stars a[role="radio"]:nth-child(5)');

		await page.click('body');                     // triggers 'mousedown'
		await new Promise(r => setTimeout(r, 3000));  // Wait to trigger "waits" signal

		// submit comment
		await Promise.all([
			page.click('form[action="http://localhost/wp-comments-post.php"] input[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// we are on the same page after refresh
		expect(await page.url()).toContain('http://localhost/product/' + 'test-review-product' + '/');

		// we can see our comment
		const content = await page.content();
		expect(content).toContain('Just a test review');
	});
});