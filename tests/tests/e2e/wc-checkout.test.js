const helpers = require('./helpers');

/**
 * This works in Astra, OceanWP
 */

describe('WooCommerce Checkout Form Captcha, v2 "I\'m not a robot"', () => {
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
		// Go to login page
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
		
		// captcha is not present
		const captcha = await page.$('.pwrcap-wrapper[data-context="woocommerce_checkout_after_customer_details"]');
		expect(captcha).toBeNull();
	});

	it('activates plugin as admin', async () => {
		await helpers.loginAsAdmin();
		await helpers.activatePluginBySlug('power-captcha-recaptcha');
	});

	it('configures reCAPTCHA and enables checkout captcha', async () => {
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

	it('captcha is present on woocommerce login page', async () => {
		// Go to login page
		await page.goto('http://localhost/classic-wc-checkout/');
		await page.waitForSelector('.pwrcap-wrapper[data-context="woocommerce_checkout_after_customer_details"]', { visible: true });

		const iframeSrcs = await page.$$eval('.pwrcap-wrapper[data-context="woocommerce_checkout_after_customer_details"] iframe', iframes =>
			iframes.map(iframe => iframe.src)
		);

		const recaptchaIframeFound = iframeSrcs.some(src =>
			src.startsWith('https://www.google.com/recaptcha')
		);

		expect(recaptchaIframeFound).toBe(true);
	});

	/**
	 * Tested with Astra
	 */
	it('Astra: fills out and submits the checkout form without check "I\'m not a robot"', async () => {
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

	it('Astra: fills out and submits the checkout form with check "I\'m not a robot"', async () => {
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

		// Check reCAPTCHA checkbox
		await helpers.checkRecaptchaCheckbox('.pwrcap-wrapper[data-context="woocommerce_checkout_after_customer_details"]');

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