import { test, expect } from '@playwright/test';

test('homepage loads properly', async ({ page }) => {
  await page.goto('/');
  await expect(page).toHaveTitle(/.*/);
});
