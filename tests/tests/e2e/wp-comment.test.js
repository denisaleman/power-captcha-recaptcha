const helpers = require('./helpers');

/**
 * This works in Astra, OceanWP
 */

describe('WordPress Comment Form Captcha, v2 "I\'m not a robot"', () => {
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
		await helpers.createUserIfNotExists('e2e-reset-password-user', 'e2easdfhtejrtehtj@example.com');
		await helpers.confirmUserByUsername('e2e-reset-password-user');
		await helpers.prepareCommentPost('test-comment-post');

		await helpers.loginAsAdmin();
		
		await helpers.activatePluginBySlug('power-captcha-recaptcha');
	});

	afterAll(async () => {
		await helpers.deleteUserIfFound('e2e-reset-password-user');
		await helpers.tearDownCommentPost('test-comment-post');
		await page.setCookie({
			name: 'pwrcap-e2e-test',
			value: '',
			domain: 'localhost',
			path: '/',
			expires: Date.now() / 1000 - 60,
		});
	});

	it('configures reCAPTCHA and enables reset password captcha', async () => {
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
	
		// Enable comment captcha checkbox
		await page.click('input[name="pwrcap_captchas_options[enable_comment]"]');
	
		// Submit Captchas tab form
		await page.click('#tab-captchas input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });

		// Go to permalinks and set it to have post name in post permalinks
		await page.goto('http://localhost/wp-admin/options-permalink.php');
		await page.click('input#permalink-input-post-name');
		await Promise.all([
			page.click('input#submit'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	});

	it('doesn\'t show captcha for logged in users', async () => {
		let postname = 'test-comment-post';
	
		// Go to lostpassword page
		await page.goto('http://localhost/' + postname + '/');

		// there is a comment form
		const formExists = await page.$eval('form[action="http://localhost/wp-comments-post.php"]', () => true).catch(() => false);
		expect(formExists).toBe(true);

		// attepmpt to get captcha element
		const iframeExists = await page.$('.pwrcap-wrapper iframe');

		// captcha is not present on the page
		expect(iframeExists).toBeNull();
	});

	it('logs out as admin', async () => {
		// Log out from admin
		await page.goto('http://localhost/wp-admin/');
		await page.waitForSelector('#wp-admin-bar-logout > a');
		const logoutHref = await page.$eval('#wp-admin-bar-logout > a', el => el.href);
		await page.goto(logoutHref);
	});

	it('show captcha for guests', async () => {
		let postname = 'test-comment-post';

		// Go to lostpassword page
		await page.goto('http://localhost/' + postname + '/');

		// captcha is present on the page
		const iframeSrcs = await page.$$eval('.pwrcap-wrapper iframe', iframes =>
			iframes.map(iframe => iframe.src)
		);
		const recaptchaIframeFound = iframeSrcs.some(src =>
			src.startsWith('https://www.google.com/recaptcha')
		);
		expect(recaptchaIframeFound).toBe(true);
	});

	it('cannot leave comment without check "I\'m not a robot"', async () => {
		let postname = 'test-comment-post';

		// Go to lostpassword page
		await page.goto('http://localhost/' + postname + '/');

		await page.type('textarea[name="comment"]', 'Just a test comment');
		await page.type('input[name="author"]', 'e2e test');
		await page.type('input[name="email"]', 'e2e@example.com');

		// submit comment
		await Promise.all([
			page.click('form[action="http://localhost/wp-comments-post.php"] input[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		expect(await page.url()).toContain('http://localhost/wp-comments-post.php');

		const content = await page.content();
		expect(content).toContain('Google reCAPTCHA verification failed');
	});

	it('leaves comment with check "I\'m not a robot"', async () => {
		let postname = 'test-comment-post';

		// Go to lostpassword page
		await page.goto('http://localhost/' + postname + '/');

		await page.type('textarea[name="comment"]', 'Just a test comment');
		await page.type('input[name="author"]', 'e2e test');
		await page.type('input[name="email"]', 'e2e@example.com');

		await helpers.checkRecaptchaCheckbox();

		// submit comment
		await Promise.all([
			page.click('form[action="http://localhost/wp-comments-post.php"] input[type="submit"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// we are on the same page after refresh
		expect(await page.url()).toContain('http://localhost/' + postname + '/');

		// we can see our comment
		const content = await page.content();
		expect(content).toContain('Just a test comment');
	});
});