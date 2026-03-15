import { expect, Page } from '@playwright/test';

export class GroupsPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/settings/groups');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/settings/groups');
        await expect(this.page.locator('.card')).toBeVisible({ timeout: 10000 });
    }

    async gotoAddGroup(): Promise<void> {
        await this.page.goto('/settings/groups/add-group');
        await this.page.waitForSelector('input#name', { timeout: 10000 });
    }

    async expectAddFormLoaded(): Promise<void> {
        await expect(this.page).toHaveURL('/settings/groups/add-group');
        await expect(this.page.locator('form')).toBeVisible({ timeout: 10000 });
        await expect(this.page.locator('input#name')).toBeVisible();
    }

    async fillName(name: string): Promise<void> {
        await this.page.locator('input#name').fill(name);
    }

    async selectFirstColor(): Promise<void> {
        const component = this.page.locator('fingather-color-picker#color');
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item').first().waitFor({ timeout: 10000 });
        await component.locator('.dropdown-menu.show button.dropdown-item').first().click();
    }

    /** Returns true if there is at least one enabled asset available to assign. */
    async hasAvailableAsset(): Promise<boolean> {
        const component = this.page.locator('fingather-select-multi#assetIds');
        await component.locator('button').first().click();
        await this.page.waitForTimeout(500);
        const enabledItems = component.locator('.dropdown-menu.show button.dropdown-item:not([disabled]):not(.disabled)');
        const count = await enabledItems.count();
        // Close the dropdown
        await this.page.keyboard.press('Escape');
        return count > 0;
    }

    async selectFirstAsset(): Promise<void> {
        const component = this.page.locator('fingather-select-multi#assetIds');
        await component.locator('button').first().click();
        await component.locator('.dropdown-menu.show button.dropdown-item:not([disabled]):not(.disabled)').first().waitFor({ timeout: 10000 });
        await component.locator('.dropdown-menu.show button.dropdown-item:not([disabled]):not(.disabled)').first().click();
        await this.page.keyboard.press('Escape');
    }

    async submitForm(): Promise<void> {
        await this.page.locator('fingather-save-button button').click();
    }

    async expectRedirectedToList(): Promise<void> {
        await expect(this.page).toHaveURL('/settings/groups', { timeout: 10000 });
    }

    async expectGroupVisible(name: string): Promise<void> {
        await expect(this.page.locator('table tbody tr td', { hasText: name })).toBeVisible({ timeout: 10000 });
    }

    async getGroupRowCount(): Promise<number> {
        await this.page.waitForSelector('table', { timeout: 10000 });
        return this.page.locator('table tbody tr').count();
    }

    async clickEditGroup(name: string): Promise<void> {
        const row = this.page.locator('table tbody tr').filter({ hasText: name });
        await row.locator('a[href*="edit-group"]').click();
    }

    async deleteGroup(name: string): Promise<void> {
        const row = this.page.locator('table tbody tr').filter({ hasText: name });
        await row.locator('fingather-delete-button button').click();
        await expect(this.page.locator('.modal-footer button.btn-danger')).toBeVisible({ timeout: 5000 });
        await this.page.locator('.modal-footer button.btn-danger').click();
        await expect(this.page.locator('table tbody tr td', { hasText: name })).not.toBeVisible({ timeout: 10000 });
    }
}
