import { expect, test } from '@playwright/test';
import * as path from 'path';

const languages = [
    {
        code: 'en',
        settings: 'Settings',
        assets: 'Assets',
        transactions: 'Transactions',
        dividends: 'Dividends',
    },
    {
        code: 'cs',
        settings: 'Nastavení',
        assets: 'Aktiva',
        transactions: 'Transakce',
        dividends: 'Dividendy',
    },
    {
        code: 'de',
        settings: 'Einstellungen',
        assets: 'Vermögenswerte',
        transactions: 'Transaktionen',
        dividends: 'Dividenden',
    },
    {
        code: 'es',
        settings: 'Configuración',
        assets: 'Activos',
        transactions: 'Transacciones',
        dividends: 'Dividendos',
    },
    {
        code: 'fr',
        settings: 'Paramètres',
        assets: 'Actifs',
        transactions: 'Transactions',
        dividends: 'Dividendes',
    },
];

test.describe('Localization', () => {
    test.afterAll(async ({ browser }) => {
        const context = await browser.newContext({
            storageState: path.join(__dirname, '.auth/user.json'),
            ignoreHTTPSErrors: true,
        });
        const page = await context.newPage();
        try {
            await page.goto('/');
            await page.waitForSelector('fingather-language-selector', { timeout: 10000 });
            await page.locator('fingather-language-selector #languageSelector').click();
            await page.waitForSelector('fingather-language-selector [ngbDropdownMenu] button', { timeout: 5000 });
            await page.locator('fingather-language-selector [ngbDropdownMenu] button').filter({ hasText: 'en' }).click();
            await page.waitForTimeout(1000);
        } finally {
            await context.close();
        }
    });

    for (const lang of languages) {
        test(`navbar is translated to ${lang.code}`, async ({ page }) => {
            await page.goto('/');
            await page.waitForSelector('fingather-language-selector', { timeout: 10000 });

            // Open the language selector dropdown
            await page.locator('fingather-language-selector #languageSelector').click();
            await page.waitForSelector('fingather-language-selector [ngbDropdownMenu] button', { timeout: 5000 });

            // Click the target language
            await page.locator(`fingather-language-selector [ngbDropdownMenu] button`).filter({ hasText: lang.code }).click();

            // Verify navbar links are translated
            await expect(page.locator('.navbar a.nav-link', { hasText: lang.settings }).first()).toBeVisible({ timeout: 10000 });
            await expect(page.locator('.navbar a.nav-link', { hasText: lang.assets }).first()).toBeVisible();
            await expect(page.locator('.navbar a.nav-link', { hasText: lang.transactions }).first()).toBeVisible();
            await expect(page.locator('.navbar a.nav-link', { hasText: lang.dividends }).first()).toBeVisible();
        });
    }
});
