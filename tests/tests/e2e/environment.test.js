const helpers = require('./helpers');

describe('Environment', () => {
	beforeAll(async () => {
		await page.goto('http://localhost');
	});

	it('includes a meta tag marking test environment', async () => {
		const content = await page.evaluate(() => {
			const metaTag = document.querySelector('meta[name="environment"]');
			return metaTag ? metaTag.getAttribute('content') : null;
		});

		expect(content).toBe('test');
	});
});