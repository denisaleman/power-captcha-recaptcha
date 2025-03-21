const helpers = require('./helpers');

describe('Contact Form 7 Integration, v2 "I\'m not a robot"', () => {
	jest.setTimeout(60000);

	let cf7FormShortcode;
	let cf7PostEditUrl;
	let testPageSlug = 'e2e-test-page-cf7-form';

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
		await helpers.deactivatePlugin('contact-form-7/wp-contact-form-7.php');
		await helpers.preparePage(testPageSlug);
	});

	afterAll(async () => {
		// await helpers.tearDownPage(testPageSlug);

		// await page.goto(cf7PostEditUrl);
		// await page.on('dialog', async dialog => {
		// 	if (dialog.type() === 'confirm') {
		// 		await dialog.accept();
		// 	} else {
		// 		await dialog.dismiss();
		// 	}
		// });
		// await page.click('#delete-action input.delete');

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
	
	it('configures reCAPTCHA keys and selects "I\'m not a robot"', async () => {
		await page.goto('http://localhost/wp-admin/options-general.php?page=pwrcap-settings');

		// Select "No-Setup" Checkbox option
		await page.click('input[name="pwrcap_general_options[captcha_type]"][value="v2"]');

		// Wait for options
		await page.waitForSelector('input[name="pwrcap_general_options[captcha_v2_type]"][value="v2cbx"]', { visible: true });

		// Select "I'm not a robot" Checkbox option
		await page.click('input[name="pwrcap_general_options[captcha_v2_type]"][value="v2cbx"]');

		// Wait for options
		await page.waitForSelector('input[name="pwrcap_general_options[site_key]"]');

		// Fill in site and secret keys in General tab
		await page.$eval('input[name="pwrcap_general_options[site_key]"]', el => el.value = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI');
		await page.$eval('input[name="pwrcap_general_options[secret_key]"]', el => el.value = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe');
	
		// Submit the form and wait for save
		await page.click('#tab-general input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });
	});

	test('should show warning if Contact Form 7 is deactivated or not installed', async () => {
		await page.goto('http://localhost/wp-admin/options-general.php?page=pwrcap-settings');
		
		const isMissing = await page.evaluate(() => {
			return Array.from(document.querySelectorAll('p')).some(el =>
				el.textContent.includes('The Contact Form 7 plugin is not installed or is deactivated.')
			);
		});
		
		expect(isMissing).toBe(true); // or false depending on what you're testing
	});

	it('activates Contact Form 7', async () => {
		await helpers.activatePluginBySlug('contact-form-7');
	});

	test('should not show warning that Contact Form 7 is deactivated or not installed', async () => {
		await page.goto('http://localhost/wp-admin/options-general.php?page=pwrcap-settings');
	
		const isMissing = await page.evaluate(() => {
			return Array.from(document.querySelectorAll('p')).some(el =>
				el.textContent.includes('The Contact Form 7 plugin is not installed or is deactivated.')
			);
		});
	
		expect(isMissing).toBe(false);
	});

	it('adds test form in Contact Form 7', async () => {
		await page.goto('http://localhost/wp-admin/admin.php?page=wpcf7-new');
	
		// Fill in the title field
		await page.type('input#title', 'e2e-test-power-captcha-recaptcha-form');
	
		// Wait for tag generator list to be visible
		await page.waitForSelector('#tag-generator-list');
	
		// Click the "power captcha reCaptcha" button
		await page.evaluate(() => {
			const buttons = Array.from(document.querySelectorAll('#tag-generator-list button'));
			const targetButton = buttons.find(btn =>
				btn.dataset.target === 'tag-generator-panel-power_captcha_recaptcha' &&
				btn.textContent.trim().toLowerCase() === 'power captcha recaptcha'
			);
			if (targetButton) {
				targetButton.click();
			}
		});

		// Wait for dialog to appear
		await page.waitForSelector('dialog#tag-generator-panel-power_captcha_recaptcha', { visible: true });

		await new Promise(r => setTimeout(r, 500));

		await Promise.all([
			// Fill in the `class:` and `id:` input fields and trigger change events
			page.evaluate(() => {
				const dialog = document.querySelector('dialog#tag-generator-panel-power_captcha_recaptcha');
				if (!dialog) return;
		
				const classInput = dialog.querySelector('input[data-tag-option="class:"]');
				const idInput = dialog.querySelector('input[data-tag-option="id:"]');
		
				if (classInput) {
					classInput.value = 'test-class';
					classInput.dispatchEvent(new Event('input', { bubbles: true }));
					classInput.dispatchEvent(new Event('change', { bubbles: true }));
				}
		
				if (idInput) {
					idInput.value = 'test-id';
					idInput.dispatchEvent(new Event('input', { bubbles: true }));
					idInput.dispatchEvent(new Event('change', { bubbles: true }));
				}
			}),

			await new Promise(r => setTimeout(r, 500)),
		
			// Click the "insert tag" button (invisible workaround)
			page.evaluate(() => {
				const button = document.querySelector('dialog#tag-generator-panel-power_captcha_recaptcha .insert-box button');
				if (button) button.click();
			}),

			await new Promise(r => setTimeout(r, 500)),
		]);
		
		await Promise.all([
			page.click('#major-publishing-actions [name="wpcf7-save"]'),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);

		// Extract post ID from the current URL
		cf7PostEditUrl = page.url();

		// Wait for the shortcode label to appear
		await page.waitForSelector('label[for="wpcf7-shortcode"]');

		// Get the shortcode value
		cf7FormShortcode = await page.$eval('#wpcf7-shortcode', el => el.value);

		// Optional: assert it contains expected format
		expect(cf7FormShortcode).toMatch(/\[contact-form-7.*\]/);

		await new Promise(r => setTimeout(r, 1000));
	});

	it('check prepared page exists and has no form', async () => {
		await page.goto(`http://localhost/${testPageSlug}/`);
	
		// Wait for page to load
		await page.waitForSelector('body', { visible: true });
	
		// Check page title or content to ensure it's not a 404
		const is404 = await page.evaluate(() => {
			return document.title.includes('Page not found') ||
				   document.body.textContent.includes('404') ||
				   document.body.textContent.includes('Nothing Found');
		});
		expect(is404).toBe(false);
	
		// Check that the form does NOT exist
		const formExists = await page.$('form.wpcf7-form.init') !== null;
		expect(formExists).toBe(false);
	});

	it('adds form shortcode to the prepared page', async () => {
		await helpers.preparePage(testPageSlug, cf7FormShortcode);
	});

	it('check prepared page to have the form', async () => {
		await page.goto(`http://localhost/${testPageSlug}/`);
	
		// Wait for page to load
		await page.waitForSelector('body', { visible: true });
	
		// Check page title or content to ensure it's not a 404
		const is404 = await page.evaluate(() => {
			return document.title.includes('Page not found') ||
				   document.body.textContent.includes('404') ||
				   document.body.textContent.includes('Nothing Found');
		});
		expect(is404).toBe(false);
	
		// Check that the form exists
		const formExists = await page.$('form.wpcf7-form.init') !== null;
		expect(formExists).toBe(true);
	});

	it('captcha is present on the form', async () => {
		await page.goto(`http://localhost/${testPageSlug}/`);
	
		// Wait for page to load
		await page.waitForSelector('body', { visible: true });

		const iframeSrcs = await page.$$eval('.pwrcap-wrapper iframe', iframes =>
			iframes.map(iframe => iframe.src)
		);

		const recaptchaIframeFound = iframeSrcs.some(src =>
			src.startsWith('https://www.google.com/recaptcha')
		);

		expect(recaptchaIframeFound).toBe(true);
	});

	it('captcha wrapper has corresponding class and id', async () => {
		await page.waitForSelector('.pwrcap-wrapper', { visible: true });
	
		// Get the parent of .pwrcap-wrapper and check its attributes
		const { parentTag, parentId, parentClass } = await page.$eval('.pwrcap-wrapper', el => {
			const parent = el.closest('.wpcf7-form-control');
			return {
				parentTag: parent ? parent.tagName : null,
				parentId: parent ? parent.id : null,
				parentClass: parent ? parent.className : null
			};
		});
	
		// Sanity check that the parent exists
		expect(parentTag).not.toBeNull();
	
		// Assert ID and class
		expect(parentId).toBe('test-id');
		expect(parentClass.split(/\s+/)).toEqual(expect.arrayContaining(['wpcf7-form-control', 'test-class']));
	});

	it('empty submission shows correct errors', async () => {
		// Submit the form
		await page.click('input[type="submit"].wpcf7-form-control');
	
		// Wait for response output to be visible
		await page.waitForSelector('.wpcf7-response-output', { visible: true });
	
		// Get the response text
		const responseText = await page.evaluate(() => {
			const response = document.querySelector('.wpcf7-response-output');
			return response ? response.innerText.trim() : '';
		});
	
		// Assert the expected error message is shown
		expect(responseText).toMatch(/one or more fields have an error/i);

		// Check for the required field error span after CAPTCHA
		const captchaErrorExists = await page.evaluate(() => {
			const captcha = document.querySelector('.pwrcap-wrapper');
			if (!captcha) return false;
			const errorSpan = captcha.nextElementSibling;
			return (
				errorSpan &&
				errorSpan.classList.contains('wpcf7-not-valid-tip') &&
				errorSpan.innerText.trim().toLowerCase().includes('please fill out this field')
			);
		});

		expect(captchaErrorExists).toBe(true);

		await new Promise(r => setTimeout(r, 1000));
	});

	it('fills in everything except for captcha', async () => {
		// Fill out the input fields
		await page.$eval('input[name="your-name"]', el => el.value = '');
		await page.type('input[name="your-name"]', 'Test User');
		await page.$eval('input[name="your-email"]', el => el.value = '');
		await page.type('input[name="your-email"]', 'test@example.com');
		await page.$eval('input[name="your-subject"]', el => el.value = '');
		await page.type('input[name="your-subject"]', 'Test Subject');
		await page.$eval('textarea[name="your-message"]', el => el.value = '');
		await page.type('textarea[name="your-message"]', 'This is a test message.');

		// Submit the form
		await page.click('input[type="submit"].wpcf7-form-control');

		// Wait for response output to be visible
		await page.waitForSelector('.wpcf7-response-output', { visible: true });

		// Get the response text
		const responseText = await page.evaluate(() => {
			const response = document.querySelector('.wpcf7-response-output');
			return response ? response.innerText.trim() : '';
		});

		// Assert the expected error message is shown
		expect(responseText).toMatch(/one or more fields have an error/i);

		// Check that the CAPTCHA field has an error (i.e., it’s marked as invalid)
		const captchaError = await page.evaluate(() => {
			const captcha = document.querySelector('.pwrcap-wrapper');
			const captchaErrorSpan = captcha ? captcha.nextElementSibling : null;
			return captchaErrorSpan && captchaErrorSpan.classList.contains('wpcf7-not-valid-tip');
		});

		expect(captchaError).toBe(true);

		await new Promise(r => setTimeout(r, 1000));
	});

	it('fills in captcha but missed something', async () => {
		// Intentionally miss the email field
		await page.$eval('input[name="your-email"]', el => el.value = '');

		await helpers.checkRecaptchaCheckbox();

		// Submit the form
		await page.click('input[type="submit"].wpcf7-form-control');
	
		// Wait for response output to be visible
		await page.waitForSelector('.wpcf7-response-output', { visible: true });
	
		// Get the response text
		const responseText = await page.evaluate(() => {
			const response = document.querySelector('.wpcf7-response-output');
			return response ? response.innerText.trim() : '';
		});
	
		// Assert the expected error message is shown
		expect(responseText).toMatch(/one or more fields have an error/i);

		// Check that the CAPTCHA field has an error (i.e., it’s marked as invalid)
		const captchaError = await page.evaluate(() => {
			const captcha = document.querySelector('.pwrcap-wrapper');
			const captchaErrorSpan = captcha ? captcha.nextElementSibling : null;
			return captchaErrorSpan && captchaErrorSpan.classList.contains('wpcf7-not-valid-tip');
		});

		expect(captchaError).toBe(null);

		await new Promise(r => setTimeout(r, 1000));
	});

	it('fills in everything and sucessfully submit the form', async () => {
		// Fill out the input fields
		await page.$eval('input[name="your-name"]', el => el.value = '');
		await page.type('input[name="your-name"]', 'Test User');
		await page.$eval('input[name="your-email"]', el => el.value = '');
		await page.type('input[name="your-email"]', 'test@example.com');
		await page.$eval('input[name="your-subject"]', el => el.value = '');
		await page.type('input[name="your-subject"]', 'Test Subject');
		await page.$eval('textarea[name="your-message"]', el => el.value = '');
		await page.type('textarea[name="your-message"]', 'This is a test message.');

		await helpers.checkRecaptchaCheckbox();

		// Submit the form
		await page.click('input[type="submit"].wpcf7-form-control');

		// Wait for response output to be visible
		await page.waitForSelector('.wpcf7-response-output', { visible: true });

		// Get the response text
		const responseText = await page.evaluate(() => {
			const response = document.querySelector('.wpcf7-response-output');
			return response ? response.innerText.trim() : '';
		});

		// Check that the CAPTCHA field has NO error
		const captchaError = await page.evaluate(() => {
			const captcha = document.querySelector('.pwrcap-wrapper');
			const captchaErrorSpan = captcha ? captcha.nextElementSibling : null;
			return captchaErrorSpan && captchaErrorSpan.classList.contains('wpcf7-not-valid-tip');
		});
		expect(captchaError).toBe(null);

		// Assert the expected error message is shown
		expect(responseText).toMatch(/thank you for your message/i);

		await new Promise(r => setTimeout(r, 1000));
	});
});