async function deletePluginData() {
	await page.goto('http://localhost/?delete_plugin_data=1');
	const content = await page.evaluate(() => document.body.innerText.trim());
	expect(content).toBe('OK');
}

async function createAdminIfNotExists(username = 'e2e-test', email = 'e2e@example.com', password = 'acF2!3$532%yfaw') {
	return createUserIfNotExists(username, email, password, 'administrator');
}

async function createUserIfNotExists(username, email, password = 'Test1234!', role = 'subscriber') {
	const params = new URLSearchParams({
		create_user: username,
		email,
		password,
		role,
	});
	await page.goto(`http://localhost/?${params.toString()}`);
	const content = await page.evaluate(() => document.body.innerText.trim());
	expect(content).toBe('OK');
}

async function confirmUserByUsername(username) {
	const params = new URLSearchParams({
		confirm_user: username,
	});
	await page.goto(`http://localhost/?${params.toString()}`);
	const content = await page.evaluate(() => document.body.innerText.trim());
	expect(content).toBe('OK');
}

async function prepareCommentPost(slug) {
	const params = new URLSearchParams({ prepare_comment_post: slug });
	await page.goto(`http://localhost/?${params.toString()}`);
	const content = await page.evaluate(() => document.body.innerText.trim());
	expect(content).toBe('OK');
}

async function tearDownCommentPost(slug) {
	const params = new URLSearchParams({ teardown_comment_post: slug });
	await page.goto(`http://localhost/?${params.toString()}`);
	const content = await page.evaluate(() => document.body.innerText.trim());
	expect(content).toBe('OK');
}

async function preparePage(slug, content = '') {
	const params = new URLSearchParams({ prepare_page: slug });
	if (content) {
		params.set('content', content);
	}
	await page.goto(`http://localhost/?${params.toString()}`);
	const responseText = await page.evaluate(() => document.body.innerText.trim());
	expect(responseText).toBe('OK');
}

async function tearDownPage(slug) {
	const params = new URLSearchParams({ teardown_page: slug });
	await page.goto(`http://localhost/?${params.toString()}`);
	const content = await page.evaluate(() => document.body.innerText.trim());
	expect(content).toBe('OK');
}

async function prepareReviewProduct(slug) {
	const params = new URLSearchParams({ prepare_review_product: slug });
	await page.goto(`http://localhost/?${params.toString()}`);
	const content = await page.evaluate(() => document.body.innerText.trim());
	expect(content).toBe('OK');
}

async function tearDownReviewProduct(slug) {
	const params = new URLSearchParams({ teardown_review_product: slug });
	await page.goto(`http://localhost/?${params.toString()}`);
	const content = await page.evaluate(() => document.body.innerText.trim());
	expect(content).toBe('OK');
}

async function getWcLostPasswordURL() {
	await page.goto('http://localhost/?get_wc_lost_password_url=1');
	const url = await page.evaluate(() => document.body.innerText.trim());

	expect(url).toMatch(/^https?:\/\/.+/); // Ensure it looks like a URL
	return url;
}

async function deactivatePlugin(pluginFile = '1') {
	await page.goto(`http://localhost/?deactivate_plugin=${pluginFile}`);
	const content1 = await page.evaluate(() => document.body.innerText.trim());
	expect(content1).toBe('OK');
}

async function enableRegistration() {
	await page.goto('http://localhost/?enable_registration=1');
	const content1 = await page.evaluate(() => document.body.innerText.trim());
	expect(content1).toBe('OK');
}

async function enableWcRegistration() {
	await page.goto('http://localhost/?enable_woocommerce_registration=1');
	const content1 = await page.evaluate(() => document.body.innerText.trim());
	expect(content1).toBe('OK');
}

async function expectUserFound(username, role = 'subscriber') {
	await page.goto(`http://localhost/?check_user=${username}&expected_role=${role}`);
	const content1 = await page.evaluate(() => document.body.innerText.trim());
	expect(content1).toBe('FOUND');
}

async function expectUserNotFound(username, role = 'subscriber') {
	await page.goto(`http://localhost/?check_user=${username}&expected_role=${role}`);
	const content1 = await page.evaluate(() => document.body.innerText.trim());
	expect(content1).toBe('NOT FOUND');
}

async function deleteUserIfFound(username) {
	await page.goto(`http://localhost/?delete_user=${username}`)
	const content1 = await page.evaluate(() => document.body.innerText.trim());
	expect(content1).toBe('OK');
}

async function loginAsAdmin({ username = 'e2e-test', password = 'acF2!3$532%yfaw', beforeSubmit } = {}) {
	await page.goto('http://localhost/wp-login.php');

	// Clear the username and password fields before typing
	await page.$eval('#user_login', el => el.value = '');
	await page.$eval('#user_pass', el => el.value = '');

	// Type in the username and password
	await page.$eval('#user_login', (el, value) => el.value = value, username);
	await page.$eval('#user_pass', (el, value) => el.value = value, password);

	// Click the submit button
	await Promise.all([
		typeof beforeSubmit === 'function' ? await beforeSubmit() : function () {},
		page.click('#wp-submit'),
		page.waitForNavigation({ waitUntil: 'networkidle0' }),
	]);

	return page;
}

async function loginAsUser({ username = 'e2e-test', password = 'acF2!3$532%yfaw', beforeSubmit } = {}) {
	await page.goto('http://localhost/wp-login.php');

	// Clear the username and password fields before typing
	await page.$eval('#user_login', el => el.value = '');
	await page.$eval('#user_pass', el => el.value = '');

	// Type in the username and password
	await page.$eval('#user_login', (el, value) => el.value = value, username);
	await page.$eval('#user_pass', (el, value) => el.value = value, password);

	// Click the submit button
	await Promise.all([
		typeof beforeSubmit === 'function' ? await beforeSubmit() : function () {},
		page.click('#wp-submit'),
		page.waitForNavigation({ waitUntil: 'networkidle0' }),
	]);

	return page;
}

async function logoutAsAdmin() {
	await page.goto('http://localhost/wp-admin/');
	await page.waitForSelector('#wp-admin-bar-logout > a');
	const logoutHref = await page.$eval('#wp-admin-bar-logout > a', el => el.href);
	await page.goto(logoutHref);
}

async function registerAsTestUserThroughtWpRegisterForm({ username, email, beforeSubmit } = {}) {
	await page.goto('http://localhost/wp-login.php?action=register');

	// Clear input fields before typing
	await page.$eval('#user_login', el => el.value = '');
	await page.$eval('#user_email', el => el.value = '');

	// Type in the username and email
	await page.$eval('#user_login', (el, value) => el.value = value, username);
	await page.$eval('#user_email', (el, value) => el.value = value, email);

	// Click the submit button
	await Promise.all([
		typeof beforeSubmit === 'function' ? await beforeSubmit() : function() {},
		page.click('#wp-submit'),
		page.waitForNavigation({ waitUntil: 'networkidle0' }),
	]);

	// Return the page object for further interaction
	return page;
}

async function checkRecaptchaCheckbox(root = 'body') {
    // Wait for the iframe to be present (reCAPTCHA iframe)
    await page.waitForSelector(root + ' iframe[src^="https://www.google.com/recaptcha/api2/anchor"]', { timeout: 20000 });

    // Get the iframe element
    const iframeHandle = await page.$(root + ' iframe[src^="https://www.google.com/recaptcha/api2/anchor"]');

    // Get the iframe content
    const iframeContent = await iframeHandle.contentFrame();

    // Wait for the checkbox to be present inside the iframe
    const checkboxSelector = '.recaptcha-checkbox';
    await iframeContent.waitForSelector(checkboxSelector);

    // Ensure the checkbox is not already checked
    const isChecked = await iframeContent.$eval(checkboxSelector, el => el.checked);
    if (!isChecked) {
        // Click the checkbox if it is not already checked
        await iframeContent.click(checkboxSelector);
    }
}

async function loginAsAdminExpectingFailure() {
	await page.goto('http://localhost/wp-login.php');

	// Clear and type into username field
	await page.$eval('#user_login', el => el.value = '');
	await page.type('#user_login', 'e2e-test');

	// Clear and type into password field
	await page.$eval('#user_pass', el => el.value = '');
	await page.type('#user_pass', 'acF2!3$532%yfaw');

	await page.click('#wp-submit');

	// Wait for error message instead of navigation
	await page.waitForSelector('#login_error', { visible: true });

	return page;
}

async function activatePluginBySlug(pluginSlug) {
	const pluginsUrl = 'http://localhost/wp-admin/plugins.php';
	
	// Only navigate if not already on the plugins page
	if (page.url() !== pluginsUrl) {
		await page.goto(pluginsUrl);
	}

	const activateSelector = `a[href*="plugins.php?action=activate&plugin=${pluginSlug}"]`;
	const linkExists = await page.$(activateSelector);

	if (linkExists) {
		await Promise.all([
			page.click(activateSelector),
			page.waitForNavigation({ waitUntil: 'networkidle0' }),
		]);
	} else {
		console.warn(`Plugin "${pluginSlug}" is already active or not found.`);
	}
	return page;
}

async function createClassicCheckoutPage() {
	await page.goto('http://localhost/?e2e_create_wc_checkout_page=1');
	const content = await page.evaluate(() => document.body.innerText.trim());
	expect(content).toBe('OK');
}

async function deleteClassicCheckoutPage() {
	await page.goto('http://localhost/?e2e_delete_wc_checkout_page=1');
	const content = await page.evaluate(() => document.body.innerText.trim());
	expect(['OK', 'NOT FOUND']).toContain(content);
}


module.exports = {
	deletePluginData,
	createAdminIfNotExists,
	createUserIfNotExists,
	confirmUserByUsername,
	prepareCommentPost,
	tearDownCommentPost,
	preparePage,
	tearDownPage,
	prepareReviewProduct,
	tearDownReviewProduct,
	getWcLostPasswordURL,
	deactivatePlugin,
	enableRegistration,
	enableWcRegistration,
	expectUserFound,
	expectUserNotFound,
	deleteUserIfFound,
	loginAsAdmin,
	loginAsUser,
	logoutAsAdmin,
	registerAsTestUserThroughtWpRegisterForm,
	checkRecaptchaCheckbox,
	loginAsAdminExpectingFailure,
	activatePluginBySlug,
	createClassicCheckoutPage,
	deleteClassicCheckoutPage,
};