import {expect, Page} from '@playwright/test';

export const THEMES = ['Magento/luma', 'Hyva/default-csp'];

export const TEST_HANDLE = 'loki_email_mx_validator_test';

export const CONFIG_PATH = 'loki_components/validators/enable_mx_validation_for_email';

export const COMPONENT_ID = 'LokiEmailMxValidatorTestEmail';

export const ELEMENT_ID = 'loki-email-mx-validator-test-email';

export const FIELD_ID = ELEMENT_ID + '-field';

export const MESSAGES_ID = ELEMENT_ID + '-messages';

declare const Alpine: any;

export async function configure(page: Page, config: object) {
    const token = process.env.TEST_TOKEN;
    if (!token) {
        throw new Error('TEST_TOKEN is not set');
    }

    const response = await page.request.post(
        '/loki_functional_tests/index/configure?token=' + encodeURIComponent(token),
        {form: {config: JSON.stringify(config)}}
    );

    const body = await response.text();
    expect(response.status(), 'Configure response: ' + body).toBe(200);

    const data = JSON.parse(body);
    expect(data.error, 'Configure error').toBeUndefined();
    expect(data.result?.theme?.errors, 'Theme errors').toBeUndefined();
}

export async function waitForEmailComponent(page: Page) {
    await page.waitForFunction((componentId) => {
        const Alpine = (window as any).Alpine;
        return Alpine
            && Alpine.store('LokiComponents')
            && Alpine.store('LokiComponents').getComponentIds().includes(componentId);
    }, COMPONENT_ID);
}

/**
 * Fill in the email field like a user would and wait for the AJAX roundtrip to finish
 */
export async function submitEmail(page: Page, email: string) {
    const responsePromise = page.waitForResponse(response =>
        response.url().includes('/loki_components/index/html') && response.request().method() === 'POST'
    );

    const field = page.locator('#' + FIELD_ID);
    await field.fill(email);
    await field.press('Tab');

    const response = await responsePromise;
    expect(response.status()).toBe(200);

    await waitForEmailComponent(page);
}

export async function getComponentMessages(page: Page): Promise<Array<{type: string, text: string}>> {
    return page.evaluate((componentId) => {
        return Alpine.store('LokiComponents').get(componentId).messages
            .map((message: any) => ({type: message.type, text: message.text}));
    }, COMPONENT_ID);
}
