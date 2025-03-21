

const helpers = require('./helpers');

describe('First Activation', () => {
	jest.setTimeout(60000);

	beforeAll(async () => {
     await helpers.createAdminIfNotExists();
		await helpers.deletePluginData();
		await helpers.deactivatePlugin();
		await helpers.loginAsAdmin();
	});

	it('activates the plugin and checks for greeting', async () => {
		const content = await helpers.activatePluginBySlug('power-captcha-recaptcha');
	
		expect(content).toContain('Plugin activated');
		expect(content).toContain('pwrcap-notice-greeting');
	});

	it('changes page and the greeting disappears', async () => {
		await page.goto('http://localhost/wp-admin/plugins.php');
		const content = await page.content();
		expect(content).not.toContain('pwrcap-notice-greeting');
	});

	it('has warning about required setup', async () => {
		await page.goto('http://localhost/wp-admin/plugins.php');
		const content = await page.content();
		expect(content).toContain('pwrcap-notice-not-configured');
	});

	it('contains submenu link to PowerCaptcha settings', async () => {
		await page.goto('http://localhost/wp-admin/');
	
		const linkSelector = 'a[href="options-general.php?page=pwrcap-settings"]';
		const link = await page.$(linkSelector);
	
		expect(link).not.toBeNull();
	
		const linkText = await page.evaluate(el => el.textContent, link);
		expect(linkText).toContain('Power Captcha');
	});

	it('displays 4 tabs, with tab-general visible and others hidden', async () => {
		await page.goto('http://localhost/wp-admin/options-general.php?page=pwrcap-settings');
	
		const tabLinks = await page.$$('.pwrcap-nav-tab-wrapper a');
		expect(tabLinks.length).toEqual(4);
	
		const visibleTabId = 'tab-general';
		const hiddenTabIds = ['tab-captchas', 'tab-activity', 'tab-misc'];
	
		const isVisible = await page.$eval(`#${visibleTabId}`, el => el.offsetParent !== null);
		expect(isVisible).toBe(true);
	
		for (const tabId of hiddenTabIds) {
			const exists = await page.$(`#${tabId}`);
			expect(exists).not.toBeNull();
	
			const isHidden = await page.$eval(`#${tabId}`, el => el.offsetParent === null);
			expect(isHidden).toBe(true);
		}
	});

	it('shows the clicked tab and hides the others (via display style)', async () => {
		await page.goto('http://localhost/wp-admin/options-general.php?page=pwrcap-settings');
	
		const tabIds = ['tab-general', 'tab-captchas', 'tab-activity', 'tab-misc'];
	
		for (const activeTabId of tabIds) {
			await page.click(`.pwrcap-nav-tab-wrapper a[href="#${activeTabId}"]`);
			await new Promise(resolve => setTimeout(resolve, 200));
	
			for (const tabId of tabIds) {
				const displayValue = await page.$eval(`#${tabId}`, el => {
					return window.getComputedStyle(el).display;
				});
	
				if (tabId === activeTabId) {
					expect(displayValue).not.toBe('none'); // visible tab
				} else {
					expect(displayValue).toBe('none'); // hidden tabs
				}
			}
		}
	});

	it('shows "To start using reCAPTCHA" before keys are set and disappears after providing keys', async () => {
		await helpers.deletePluginData();

		// Step 1: Go to settings page
		await page.goto('http://localhost/wp-admin/options-general.php?page=pwrcap-settings');
	
		// Define tab ids
		const tabIds = ['tab-general', 'tab-captchas', 'tab-activity', 'tab-misc'];
	
		// Step 2: Check that each tab contains the caption before keys are provided
		for (const tabId of tabIds) {
			const captionText = await page.$eval(`#${tabId}`, el => el.textContent);
			expect(captionText.toLowerCase()).toContain('to start using recaptcha');
		}
	
		// Step 3: Provide keys in the form
		const siteKey = 'test-site-key';
		const secretKey = 'test-secret-key';
		await page.type('#site_key', siteKey);
		await page.type('#secret_key', secretKey);
	
		// Submit the form
		await Promise.all([
			page.click('input.pwrcap-sumbit-button'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	
		// Step 4: Check that each tab NO longer contains the caption after keys are provided
		for (const tabId of tabIds) {
			const captionText = await page.$eval(`#${tabId}`, el => el.textContent);
			expect(captionText.toLowerCase()).not.toContain('to start using recaptcha');
		}

		await helpers.deletePluginData();
	});

	it('saves settings and keeps values after refresh', async () => {
		await helpers.deletePluginData();

		await page.goto('http://localhost/wp-admin/options-general.php?page=pwrcap-settings');
	
		const siteKey = 'test-site-key';
		const secretKey = 'test-secret-key';
	
		// Fill in the fields
		await page.type('#site_key', siteKey);
		await page.type('#secret_key', secretKey);
	
		// Submit the form
		await Promise.all([
			page.click('input.pwrcap-sumbit-button'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	
		// Check for 'Settings saved.' notice
		const noticeText = await page.evaluate(() => {
			const notice = document.querySelector('.notice-success, .updated');
			return notice?.textContent || '';
		});
		expect(noticeText).toMatch(/Settings saved/i);
	
		// Refresh and confirm values are retained
		await page.reload({ waitUntil: 'networkidle0' });
	
		const savedSiteKey = await page.$eval('#site_key', el => el.value);
		const savedSecretKey = await page.$eval('#secret_key', el => el.value);
	
		expect(savedSiteKey).toBe(siteKey);
		expect(savedSecretKey).toBe(secretKey);

		await helpers.deletePluginData();
	});

	it('removes the setup warning after configuration', async () => {
		await helpers.deletePluginData();

		// Step 1: Go to settings page and fill in keys
		await page.goto('http://localhost/wp-admin/options-general.php?page=pwrcap-settings');
		await page.type('#site_key', 'test-site-key');
		await page.type('#secret_key', 'test-secret-key');
	
		// Submit the form and wait for save
		await Promise.all([
			page.click('input.pwrcap-sumbit-button'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	
		// Step 2: Go to plugins page
		await page.goto('http://localhost/wp-admin/plugins.php');
	
		// Step 3: Confirm the warning is not present
		const content = await page.content();
		expect(content).not.toContain('pwrcap-notice-not-configured');

		await helpers.deletePluginData();
	});

	it('shows warning if Site Key or Secret Key is unset', async () => {
		await helpers.deletePluginData();

		// Step 1: Go to settings page and fill in keys
		await page.goto('http://localhost/wp-admin/options-general.php?page=pwrcap-settings');
		await page.type('#site_key', 'test-site-key');
		await page.type('#secret_key', 'test-secret-key');
	
		// Submit the form and wait for save
		await Promise.all([
			page.click('input.pwrcap-sumbit-button'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	
		// Step 2: Ensure warning is NOT visible after configuration
		let content = await page.content();
		expect(content).not.toContain('pwrcap-notice-not-configured');
	
		// Step 3: Unset Site Key and check for warning
		await page.$eval('#site_key', el => el.value = ''); // Remove Site Key
		await Promise.all([
			page.click('input.pwrcap-sumbit-button'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
		content = await page.content();
		expect(content).toContain('pwrcap-notice-not-configured'); // Warning should reappear
	
		// Step 4: Unset Secret Key and check for warning again
		await page.$eval('#secret_key', el => el.value = ''); // Remove Secret Key
		await Promise.all([
			page.click('input.pwrcap-sumbit-button'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
		content = await page.content();
		expect(content).toContain('pwrcap-notice-not-configured'); // Warning should still appear
	
		// Step 5: Set both keys and check for no warning
		await page.type('#site_key', 'test-site-key');
		await page.type('#secret_key', 'test-secret-key');
		await Promise.all([
			page.click('input.pwrcap-sumbit-button'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
		content = await page.content();
		expect(content).not.toContain('pwrcap-notice-not-configured'); // No warning if both keys are present

		await helpers.deletePluginData();
	});

	//6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
	//6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
});