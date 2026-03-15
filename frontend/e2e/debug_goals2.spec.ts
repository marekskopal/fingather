import { test } from '@playwright/test';

test('debug goals create v2', async ({ page }) => {
    const apiCalls: string[] = [];

    page.on('response', async resp => {
        if (resp.url().includes('/api/goal')) {
            const status = resp.status();
            const body = await resp.text().catch(() => '');
            apiCalls.push(`${status} ${resp.url()}: ${body.substring(0, 300)}`);
        }
    });

    await page.goto('/goals/add-goal');
    await page.waitForSelector('fingather-select#portfolioId', { timeout: 10000 });

    const portfolioSelect = page.locator('fingather-select#portfolioId');
    await portfolioSelect.locator('button').first().click();
    await portfolioSelect.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
    await portfolioSelect.locator('.dropdown-menu.show button.dropdown-item').first().click();

    await page.locator('input#targetValue').fill('100000');

    const responsePromise = page.waitForResponse(resp => resp.url().includes('/api/goals'), { timeout: 5000 }).catch(() => null);
    await page.locator('fingather-save-button button').click();
    const resp = await responsePromise;
    if (resp) {
        const body = await resp.text().catch(() => '');
        console.log(`Goals API: ${resp.status()} ${resp.url()}: ${body.substring(0, 300)}`);
    } else {
        console.log('No goals API call made');
    }
    console.log('URL:', page.url());
});
