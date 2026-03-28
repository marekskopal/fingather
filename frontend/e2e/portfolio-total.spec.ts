import { expect, test, type Locator, type Page } from '@playwright/test';

import { DashboardPage } from './pages/dashboard.page';

const performanceCardSelector = 'fingather-portfolio-total .performance-card';
const percentageRegex = /-?\d+\.\d{2}\s*%/;

async function openDashboard(page: Page): Promise<Locator> {
    const dashboard = new DashboardPage(page);
    await dashboard.goto();
    await dashboard.expectPortfolioSectionVisible();

    const card = page.locator(performanceCardSelector);
    await expect(card).toBeVisible();

    await expect(card.locator('.performance-views-track .performance-view').first()).toBeVisible({ timeout: 10000 });

    return card;
}

test.describe('Portfolio total — performance card switcher', () => {
    test('starts in gain view; toggle switches to return rate and back', async ({ page }) => {
        const card = await openDashboard(page);
        const track = card.locator('.performance-views-track');
        const toggle = card.locator('.performance-toggle');

        await expect(track).not.toHaveClass(/show-return-rate/);

        const initialTitle = (await card.locator('h3.h6').innerText()).trim();
        expect(initialTitle.length).toBeGreaterThan(0);

        await toggle.click();
        await expect(track).toHaveClass(/show-return-rate/);
        await expect(card.locator('.return-rate')).toBeVisible();

        const switchedTitle = (await card.locator('h3.h6').innerText()).trim();
        expect(switchedTitle).not.toBe(initialTitle);

        await toggle.click();
        await expect(track).not.toHaveClass(/show-return-rate/);

        const restoredTitle = (await card.locator('h3.h6').innerText()).trim();
        expect(restoredTitle).toBe(initialTitle);
    });

    test('return rate view shows TWR and MWR rows with percentage values', async ({ page }) => {
        const card = await openDashboard(page);
        await card.locator('.performance-toggle').click();

        const rows = card.locator('.return-rate tbody tr');
        await expect(rows).toHaveCount(2);

        const twrRow = rows.nth(0);
        await expect(twrRow.locator('td').first()).toHaveText(/TWR/);
        const twrValueCells = twrRow.locator('td').nth(1).locator('div');
        await expect(twrValueCells).toHaveCount(2);
        await expect(twrValueCells.nth(0)).toHaveText(percentageRegex);
        await expect(twrValueCells.nth(1)).toHaveText(/p\.a\./);
        await expect(twrValueCells.nth(1)).toHaveText(percentageRegex);

        const mwrRow = rows.nth(1);
        await expect(mwrRow.locator('td').first()).toHaveText(/MWR/);
        const mwrValueCells = mwrRow.locator('td').nth(1).locator('div');
        await expect(mwrValueCells).toHaveCount(1);
        await expect(mwrValueCells.first()).toHaveText(/p\.a\./);
        await expect(mwrValueCells.first()).toHaveText(percentageRegex);
    });

    test('return rate sign does not contradict gain sign', async ({ page }) => {
        const card = await openDashboard(page);

        const gainView = card.locator('.performance-view').first();
        const gainPercent = gainView.locator('div').filter({ hasText: percentageRegex }).first();
        await expect(gainPercent).toBeVisible();

        const gainClass = (await gainPercent.getAttribute('class')) ?? '';
        const gainSign: 'green' | 'red' | 'neutral' = gainClass.includes('green')
            ? 'green'
            : gainClass.includes('red')
                ? 'red'
                : 'neutral';

        await card.locator('.performance-toggle').click();

        const twrCumulative = card.locator('.return-rate tbody tr').nth(0).locator('td').nth(1).locator('div').nth(0);
        const mwrPerAnnum = card.locator('.return-rate tbody tr').nth(1).locator('td').nth(1).locator('div').nth(0);

        const twrClass = (await twrCumulative.getAttribute('class')) ?? '';
        const mwrClass = (await mwrPerAnnum.getAttribute('class')) ?? '';

        // 0% (neutral) is acceptable on either side; only an opposite sign is a contradiction.
        if (gainSign === 'green') {
            expect(twrClass).not.toContain('red');
            expect(mwrClass).not.toContain('red');
        } else if (gainSign === 'red') {
            expect(twrClass).not.toContain('green');
            expect(mwrClass).not.toContain('green');
        }
    });
});
