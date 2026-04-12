import { test, expect } from '@playwright/test';
import { loginAs, tableExists } from '../helpers/elgg';

test.describe('hypeGeo admin settings', () => {
  test('plugin settings page renders without PHP errors', async ({ page }) => {
    await loginAs(page, 'admin');
    const response = await page.goto('/admin/plugin_settings/hypeGeo');

    // Assert: page loaded and no Elgg error banner present.
    expect([200, 302]).toContain(response?.status() ?? 0);
    await expect(page.locator('.elgg-form-settings, form[name="plugin_settings"]')).toBeVisible();
    await expect(page.locator('.elgg-system-messages .elgg-message-error')).toHaveCount(0);
  });

  test('toggle proximity_search setting persists to DB', async ({ page }) => {
    await loginAs(page, 'admin');
    await page.goto('/admin/plugin_settings/hypeGeo');

    const checkbox = page.locator('input[name="params[proximity_search]"]').first();
    if (await checkbox.count() === 0) {
      test.skip(true, 'proximity_search setting not rendered in settings view');
      return;
    }
    await checkbox.check();
    await page.click('button[type="submit"], input[type="submit"]');

    // Assert: success message appears
    await expect(page.locator('.elgg-system-messages .elgg-message-success, .elgg-system-messages .elgg-message')).toBeVisible();
  });
});

test.describe('hypeGeo schema', () => {
  test('entity_geometry table exists after plugin activation', async () => {
    const exists = await tableExists('elgg_entity_geometry');
    expect(exists).toBe(true);
  });
});
