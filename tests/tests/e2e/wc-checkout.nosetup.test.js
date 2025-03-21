const helpers = require('./helpers');

/**
 * This works in Astra, OceanWP
 */

describe('WooCommerce Checkout Form Captcha, No-Setup', () => {
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
		await helpers.createClassicCheckoutPage();
	});

	afterAll(async () => {
		await page.setCookie({
			name: 'pwrcap-e2e-test',
			value: '',
			domain: 'localhost',
			path: '/',
			expires: Date.now() / 1000 - 60,
		});
		await helpers.deleteClassicCheckoutPage();
	});

	it('adds product to cart so that checkout becomes available', async () => {
		// Go to shop page
		await page.goto('http://localhost/shop/');
		await page.waitForSelector('.products .product');
	
		// Detect theme via body class
		const themeSelector = await page.evaluate(() => {
			const body = document.body;
			if (body.classList.contains('theme-oceanwp')) {
				return '.theme-oceanwp .products .product h2 a';
			}
			if (body.classList.contains('theme-astra')) {
				return '.products .product a';
			}
			throw new Error('Unsupported theme detected');
		});
	
		// Get first product link using the correct selector
		const productLink = await page.$eval(themeSelector, el => el.href);

		await Promise.all([
			page.goto(productLink),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	
		// Add to cart
		await Promise.all([
			page.evaluate(() => {
				const submit = document.querySelector('.product form.cart button[type="submit"]');
				if (submit) submit.click();
			}),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	
		// Verify product was added to cart
		const cartContent = await page.content();
		expect(cartContent).toMatch(/has been added to your cart|View cart/i);
	});

	it('captcha is not present on woocommerce checkout page', async () => {
		// Go to checkout page
		await page.goto('http://localhost/classic-wc-checkout/');

		// form is present
		const formSelector = 'form.woocommerce-checkout';
		try {
			await page.waitForSelector(formSelector);
			const form = await page.$(formSelector);
			expect(form).not.toBeNull();
		} catch (error) {
			console.log("The element didn't appear.");
			expect(1).toBeNull();
		}

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

	it('enables woocommerce checkout captcha', async () => {
		// Switch to Captchas tab
		await page.click('.pwrcap-nav-tab-wrapper a[href="#tab-captchas"]');
		await page.waitForSelector('#tab-captchas', { visible: true });
	
		// Enable register captcha checkbox
		await page.click('input[name="pwrcap_captchas_options[enable_woo_checkout]"]');
	
		// Submit Captchas tab form
		await page.click('#tab-captchas input.pwrcap-sumbit-button');
		await page.waitForSelector('.notice-success, .updated', { visible: true });
	
		// Log out from admin
		await helpers.logoutAsAdmin();
	});

	it('adds product to cart so that checkout becomes available', async () => {
		// Go to shop page
		await page.goto('http://localhost/shop/');
		await page.waitForSelector('.products .product');
	
		// Detect theme via body class
		const themeSelector = await page.evaluate(() => {
			const body = document.body;
			if (body.classList.contains('theme-oceanwp')) {
				return '.theme-oceanwp .products .product h2 a';
			}
			if (body.classList.contains('theme-astra')) {
				return '.products .product a';
			}
			throw new Error('Unsupported theme detected');
		});
	
		// Get first product link using the correct selector
		const productLink = await page.$eval(themeSelector, el => el.href);

		await Promise.all([
			page.goto(productLink),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	
		// Add to cart
		await Promise.all([
			page.evaluate(() => {
				const submit = document.querySelector('.product form.cart button[type="submit"]');
				if (submit) submit.click();
			}),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	
		// Verify product was added to cart
		const cartContent = await page.content();
		expect(cartContent).toMatch(/has been added to your cart|View cart/i);
	});

	it('captcha is not present on woocommerce checkout page', async () => {
		// Go to checkout page
		await page.goto('http://localhost/classic-wc-checkout/');

		// form is present
		const formSelector = 'form.woocommerce-checkout';
		try {
			await page.waitForSelector(formSelector);
			const form = await page.$(formSelector);
			expect(form).not.toBeNull();
		} catch (error) {
			console.log("The element didn't appear.");
			expect(1).toBeNull();
		}

		await page.waitForSelector('.pwrcap-nosetup-wrapper');

		const captcha = await page.$('.pwrcap-nosetup-wrapper');
		expect(captcha).not.toBeNull();
	});

	/**
	 * Tested with Astra
	 */
	it('Astra: fills out and submits the checkout form without solving captcha', async () => {
		await page.goto('http://localhost/classic-wc-checkout/');
		await page.waitForSelector('form.woocommerce-checkout');

		await page.evaluate(() => {
			document.querySelector('#billing_first_name').value = 'John';
			document.querySelector('#billing_last_name').value = 'Doe';
			document.querySelector('#billing_address_1').value = 'Test Address';
			document.querySelector('#billing_city').value = 'Test City';
			document.querySelector('#billing_postcode').value = '90210';
			document.querySelector('#billing_email').value = 'e2e-test-checkout@example.com';
			document.querySelector('#billing_country').value = 'ES';
		});

		// wait until button is clickable
		await page.waitForFunction(() => {
			const btn = document.querySelector('button#place_order');
			return btn && !btn.disabled && btn.offsetParent !== null;
		});
	
		await Promise.all([
			page.click('button#place_order'),
			page.waitForResponse(response => 
				response.url().includes('wc-ajax=checkout') && response.status() === 200
			),
		]);

		// Check error message
		const errorText = await page.$eval('.woocommerce-error', el => el.textContent);
		expect(errorText).toContain('Google reCAPTCHA verification failed');
	});

	it('Astra: fills out and submits the checkout form with solving captcha', async () => {
		await page.goto('http://localhost/classic-wc-checkout/');
		await page.waitForSelector('form.woocommerce-checkout');

		await page.evaluate(() => {
			document.querySelector('#billing_first_name').value = 'John';
			document.querySelector('#billing_last_name').value = 'Doe';
			document.querySelector('#billing_address_1').value = 'Test Address';
			document.querySelector('#billing_city').value = 'Test City';
			document.querySelector('#billing_postcode').value = '90210';
			document.querySelector('#billing_email').value = 'e2e-test-checkout@example.com';
			document.querySelector('#billing_country').value = 'ES';
		});

		await page.click('body');                     // triggers 'mousedown'
		await new Promise(r => setTimeout(r, 3000));  // Wait to trigger "waits" signal

		// wait until button is clickable
		await page.waitForFunction(() => {
			const btn = document.querySelector('button#place_order');
			return btn && !btn.disabled && btn.offsetParent !== null;
		});
	
		await page.click('button#place_order');

		await Promise.race([
			page.waitForResponse(response =>
				response.url().includes('wc-ajax=checkout') && response.status() === 200
			),
			page.waitForNavigation({ waitUntil: 'networkidle0' })
		]);

		await page.waitForNavigation({ waitUntil: 'networkidle0' });

		expect(await page.url()).toContain('http://localhost/checkout/order-received/');
	});
});