import { expect, Page } from '@playwright/test';

export class OnboardingPage {
    constructor(private readonly page: Page) {}

    async expectStepOne(): Promise<void> {
        await expect(this.page).toHaveURL('/onboarding/step-one', { timeout: 10000 });
    }

    async expectStepTwo(): Promise<void> {
        await expect(this.page).toHaveURL('/onboarding/step-two', { timeout: 10000 });
    }

    async expectStepThree(): Promise<void> {
        await expect(this.page).toHaveURL('/onboarding/step-three', { timeout: 10000 });
    }

    async expectDashboard(): Promise<void> {
        await expect(this.page).toHaveURL('/', { timeout: 10000 });
    }

    async fillPortfolioName(name: string): Promise<void> {
        await this.page.fill('#name', name);
    }

    async clickContinue(): Promise<void> {
        await this.page.locator('fingather-save-button button').click();
    }

    async clickSkip(): Promise<void> {
        await this.page.locator('.card-footer .btn-secondary').click();
    }
}
