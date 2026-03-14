import { test as setup } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';

const authFile = 'e2e/.auth/user.json';

setup('authenticate', async ({ page }) => {
    const email = process.env['E2E_USER_EMAIL'] ?? 'test@fingather.test';
    const password = process.env['E2E_USER_PASSWORD'] ?? 'Test1234!';

    await page.goto('/authentication/login');
    await page.waitForSelector('#email');
    await page.fill('#email', email);
    await page.fill('#password', password);
    await page.getByRole('button', { name: 'Login' }).click();
    await page.waitForURL((url) => !url.pathname.startsWith('/authentication'), { timeout: 15000 });

    // Ensure auth dir exists
    const dir = path.dirname(authFile);
    if (!fs.existsSync(dir)) {
        fs.mkdirSync(dir, { recursive: true });
    }

    await page.context().storageState({ path: authFile });
});
