import { expect, Page, test } from '@playwright/test';

import { OnboardingPage } from './pages/onboarding.page';
import { SignUpPage } from './pages/sign-up.page';

// All tests create their own user — no stored auth state needed
test.use({ storageState: { cookies: [], origins: [] } });

const TEST_PASSWORD = 'Test1234!';

async function signUpNewUser(page: Page, emailTag: string): Promise<void> {
    const signUp = new SignUpPage(page);
    await signUp.goto();
    await signUp.signUp('Test User', `onboarding-${emailTag}@fingather.test`, TEST_PASSWORD);
}

test.describe('Sign up', () => {
    test('sign up with valid data redirects to onboarding step one', async ({ page }) => {
        await signUpNewUser(page, 'signup');
        await expect(page).toHaveURL('/onboarding/step-one', { timeout: 10000 });
    });
});

test.describe('Onboarding', () => {
    test('skip on step one navigates to dashboard', async ({ page }) => {
        await signUpNewUser(page, 'skip1');
        const onboarding = new OnboardingPage(page);
        await onboarding.expectStepOne();
        await onboarding.clickSkip();
        await onboarding.expectDashboard();
    });

    test('complete step one then skip on step two navigates to dashboard', async ({ page }) => {
        await signUpNewUser(page, 'skip2');
        const onboarding = new OnboardingPage(page);
        await onboarding.expectStepOne();
        await onboarding.fillPortfolioName('My Portfolio');
        await onboarding.clickContinue();
        await onboarding.expectStepTwo();
        await onboarding.clickSkip();
        await onboarding.expectDashboard();
    });

    test('complete step one and step two then skip on step three navigates to dashboard', async ({ page }) => {
        await signUpNewUser(page, 'skip3');
        const onboarding = new OnboardingPage(page);
        await onboarding.expectStepOne();
        await onboarding.fillPortfolioName('My Portfolio');
        await onboarding.clickContinue();
        await onboarding.expectStepTwo();
        await onboarding.clickContinue();
        await onboarding.expectStepThree();
        await onboarding.clickSkip();
        await onboarding.expectDashboard();
    });
});
