import { expect, test } from '@playwright/test';

import { PortfoliosPage } from './pages/portfolios.page';

test.describe('Portfolios list', () => {
    test('portfolios page loads', async ({ page }) => {
        const portfolios = new PortfoliosPage(page);
        await portfolios.goto();
        await portfolios.expectLoaded();
    });

    test('at least one portfolio is visible', async ({ page }) => {
        const portfolios = new PortfoliosPage(page);
        await portfolios.goto();
        await portfolios.expectLoaded();
        const count = await portfolios.getPortfolioCount();
        expect(count).toBeGreaterThanOrEqual(1);
    });

    test('add portfolio link is present', async ({ page }) => {
        await page.goto('/portfolios');
        const addLink = page.locator('a[href$="/portfolios/add-portfolio"]');
        await expect(addLink).toBeVisible({ timeout: 10000 });
    });
});

test.describe('Add portfolio', () => {
    test('add portfolio form loads', async ({ page }) => {
        const portfolios = new PortfoliosPage(page);
        await portfolios.gotoAddPortfolio();
        await portfolios.expectAddFormLoaded();
    });

    test('add portfolio form has name and currency fields', async ({ page }) => {
        await page.goto('/portfolios/add-portfolio');
        await expect(page.locator('input#name')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('fingather-select#currencyId')).toBeVisible();
        await expect(page.locator('input#isDefault')).toBeVisible();
    });

    test('create portfolio and return to list', async ({ page }) => {
        const portfolios = new PortfoliosPage(page);
        await portfolios.gotoAddPortfolio();

        const portfolioName = `E2E Portfolio ${Date.now()}`;
        await portfolios.fillName(portfolioName);
        await portfolios.selectFirstCurrency();
        await portfolios.submitForm();

        await portfolios.expectRedirectedToList();
        await portfolios.expectPortfolioVisible(portfolioName);

        // Cleanup
        await portfolios.deletePortfolio(portfolioName);
    });
});

test.describe('Edit portfolio', () => {
    test('edit portfolio form loads with current values', async ({ page }) => {
        await page.goto('/portfolios');
        await page.waitForSelector('.portfolio-list', { timeout: 10000 });

        const editLink = page.locator('.portfolio a[href*="edit-portfolio"]').first();
        await expect(editLink).toBeVisible({ timeout: 10000 });
        await editLink.click();

        await expect(page).toHaveURL(/\/portfolios\/edit-portfolio\/\d+/, { timeout: 10000 });
        await expect(page.locator('input#name')).toBeVisible({ timeout: 10000 });
        const nameValue = await page.locator('input#name').inputValue();
        expect(nameValue.length).toBeGreaterThan(0);
    });

    test('edit portfolio saves and returns to list', async ({ page }) => {
        const portfolios = new PortfoliosPage(page);

        // Create a portfolio to edit
        await portfolios.gotoAddPortfolio();
        const portfolioName = `E2E Edit ${Date.now()}`;
        await portfolios.fillName(portfolioName);
        await portfolios.selectFirstCurrency();
        await portfolios.submitForm();
        await portfolios.expectRedirectedToList();

        // Click edit on newly created portfolio
        await portfolios.clickEditPortfolio(portfolioName);
        await expect(page).toHaveURL(/\/portfolios\/edit-portfolio\/\d+/, { timeout: 10000 });
        await page.waitForSelector('input#name', { timeout: 10000 });

        const updatedName = portfolioName + ' edited';
        await portfolios.fillName(updatedName);
        await portfolios.submitForm();

        await portfolios.expectRedirectedToList();
        await portfolios.expectPortfolioVisible(updatedName);

        // Cleanup
        await portfolios.deletePortfolio(updatedName);
    });
});

test.describe('Delete portfolio', () => {
    test('default portfolio has no delete button', async ({ page }) => {
        await page.goto('/portfolios');
        await page.waitForSelector('.portfolio-list', { timeout: 10000 });

        // Find portfolio cards without a delete button — the default portfolio lacks one
        const portfolioCards = page.locator('.portfolio-list .portfolio');
        const count = await portfolioCards.count();
        expect(count).toBeGreaterThanOrEqual(1);

        // At least one portfolio should NOT have a delete button (the default one)
        let foundNonDeletable = false;
        for (let i = 0; i < count; i++) {
            const hasDelete = await portfolioCards.nth(i).locator('fingather-delete-button').isVisible().catch(() => false);
            if (!hasDelete) {
                foundNonDeletable = true;
                break;
            }
        }
        expect(foundNonDeletable).toBe(true);
    });

    test('create and delete non-default portfolio', async ({ page }) => {
        const portfolios = new PortfoliosPage(page);

        // Create portfolio
        await portfolios.gotoAddPortfolio();
        const portfolioName = `E2E Delete ${Date.now()}`;
        await portfolios.fillName(portfolioName);
        await portfolios.selectFirstCurrency();
        await portfolios.submitForm();
        await portfolios.expectRedirectedToList();

        const countBefore = await portfolios.getPortfolioCount();

        // Delete it
        await portfolios.deletePortfolio(portfolioName);

        await expect(page.locator('.portfolio-list .portfolio')).toHaveCount(countBefore - 1, { timeout: 10000 });
    });

    test('delete shows confirm dialog with portfolio name', async ({ page }) => {
        const portfolios = new PortfoliosPage(page);
        await portfolios.gotoAddPortfolio();
        const portfolioName = `E2E Confirm ${Date.now()}`;
        await portfolios.fillName(portfolioName);
        await portfolios.selectFirstCurrency();
        await portfolios.submitForm();
        await portfolios.expectRedirectedToList();

        // Click delete and verify modal appears with portfolio name
        const portfolioCard = page.locator('.portfolio').filter({ hasText: portfolioName });
        await portfolioCard.locator('fingather-delete-button button').click();
        await expect(page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        await expect(page.locator('.modal-body')).toContainText(portfolioName);

        // Confirm to clean up
        await page.locator('.modal-footer button.btn-danger').click();
        await expect(page.locator('.portfolio-list .portfolio h2.h4', { hasText: portfolioName })).not.toBeVisible({ timeout: 10000 });
    });
});
