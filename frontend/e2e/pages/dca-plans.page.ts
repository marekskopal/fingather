import { expect, Page } from '@playwright/test';

export class DcaPlansPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/dca-plans');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/dca-plans');
        await expect(this.page.locator('.card')).toBeVisible({ timeout: 10000 });
    }

    async gotoAddDcaPlan(): Promise<void> {
        await this.page.goto('/dca-plans/add-dca-plan');
        await this.page.waitForSelector('fingather-select#targetType', { timeout: 10000 });
    }

    async expectAddFormLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/dca-plans/add-dca-plan');
        await expect(this.page.locator('form')).toBeVisible({ timeout: 10000 });
        await expect(this.page.locator('fingather-select#targetType')).toBeVisible();
        await expect(this.page.locator('input#amount')).toBeVisible();
        await expect(this.page.locator('fingather-select#currencyId')).toBeVisible();
        await expect(this.page.locator('input#intervalMonths')).toBeVisible();
        await expect(this.page.locator('fingather-date-input#startDate')).toBeVisible();
    }

    private async selectFirstInDropdown(id: string): Promise<void> {
        const component = this.page.locator(`fingather-select#${id}`);
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        await component.locator('.dropdown-menu.show button.dropdown-item').first().click();
    }

    private async selectInDropdownByLabel(id: string, labelText: string): Promise<void> {
        const component = this.page.locator(`fingather-select#${id}`);
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        const items = component.locator('.dropdown-menu.show button.dropdown-item');
        const count = await items.count();
        for (let i = 0; i < count; i++) {
            const text = await items.nth(i).textContent();
            if (text?.toLowerCase().includes(labelText.toLowerCase())) {
                await items.nth(i).click();
                return;
            }
        }
        await items.first().click();
    }

    async selectTargetType(labelText: string): Promise<void> {
        await this.selectInDropdownByLabel('targetType', labelText);
    }

    async selectFirstTargetType(): Promise<void> {
        await this.selectFirstInDropdown('targetType');
    }

    async selectFirstCurrency(): Promise<void> {
        await this.selectFirstInDropdown('currencyId');
    }

    async fillAmount(amount: string): Promise<void> {
        await this.page.locator('input#amount').fill(amount);
    }

    async fillIntervalMonths(months: string): Promise<void> {
        await this.page.locator('input#intervalMonths').fill(months);
    }

    async fillStartDate(date: string): Promise<void> {
        const input = this.page.locator('fingather-date-input#startDate input');
        await input.click();
        await input.evaluate((el: HTMLInputElement, val: string) => {
            el.type = 'date';
            el.value = val;
            el.dispatchEvent(new Event('change', { bubbles: true }));
        }, date);
    }

    async submitForm(): Promise<void> {
        await this.page.locator('fingather-save-button button').click();
    }

    async expectRedirectedToList(): Promise<void> {
        await expect(this.page).toHaveURL('/dca-plans', { timeout: 10000 });
    }

    async getPlanRowCount(): Promise<number> {
        await this.page.waitForSelector('table', { timeout: 10000 });
        return this.page.locator('table tbody tr').count();
    }

    async clickEditFirst(): Promise<void> {
        await this.page.waitForSelector('table tbody tr a[href*="edit-dca-plan"]', { timeout: 10000 });
        await this.page.locator('table tbody tr a[href*="edit-dca-plan"]').first().click();
    }

    async clickDeleteFirst(): Promise<void> {
        await this.page.waitForSelector('fingather-delete-button button', { timeout: 10000 });
        await this.page.locator('fingather-delete-button button').first().click();
    }

    async confirmDelete(): Promise<void> {
        await expect(this.page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        await this.page.locator('.modal-footer button.btn-danger').click();
    }
}
