import { expect, Page } from '@playwright/test';

export class SettingsPage {
    constructor(private readonly page: Page) {}

    async goto(): Promise<void> {
        await this.page.goto('/settings');
    }

    async expectLoaded(): Promise<void> {
        await expect(this.page).toHaveURL(/\/settings/);
    }

    async gotoGroups(): Promise<void> {
        await this.page.goto('/settings/groups');
    }

    async expectGroupsLoaded(): Promise<void> {
        await expect(this.page).toHaveURL(/\/settings\/groups/);
    }
}
