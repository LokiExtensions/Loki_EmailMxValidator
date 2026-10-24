import {openTestPage} from '@loki/open-test-page';
import {test, expect} from '@loki/test';
import {
    configure,
    CONFIG_PATH,
    FIELD_ID,
    getComponentMessages,
    MESSAGES_ID,
    submitEmail,
    TEST_HANDLE,
    THEMES,
    waitForEmailComponent,
} from './helpers/configure';

for (const theme of THEMES) {
    test.describe(`Loki Email MX Validator (${theme})`, () => {
        test.describe('with MX validation enabled', () => {
            test.beforeEach(async ({page}) => {
                await configure(page, {theme, config: {[CONFIG_PATH]: 1}});
                await openTestPage(page, [TEST_HANDLE]);
                await waitForEmailComponent(page);
            });

            test('accepts an email address of which the domain has MX records', async ({page}) => {
                await submitEmail(page, 'jane@yireo.com');

                expect(await getComponentMessages(page)).toEqual([]);
                await expect(page.locator('#' + MESSAGES_ID)).toHaveCount(0);
                await expect(page.locator('#' + FIELD_ID)).toHaveValue('jane@yireo.com');
            });

            test('shows an error when the domain has no MX records', async ({page}) => {
                const expectedError = 'Domain "example.invalid" is not reachable for mail';
                await submitEmail(page, 'jane@example.invalid');

                await expect.poll(() => getComponentMessages(page)).toContainEqual({type: 'error', text: expectedError});
                await expect(page.locator('#' + MESSAGES_ID)).toContainText(expectedError);
            });

            test('keeps the error of the core email validator', async ({page}) => {
                await submitEmail(page, 'foo bar@yireo.com');

                await expect.poll(() => getComponentMessages(page)).toContainEqual({type: 'error', text: 'Invalid email address'});
                const messages = await getComponentMessages(page);
                expect(messages.map(message => message.text).join()).not.toContain('not reachable for mail');
                await expect(page.locator('#' + MESSAGES_ID)).toContainText('Invalid email address');
            });

            test('does not show an error for an empty value', async ({page}) => {
                await submitEmail(page, 'jane@yireo.com');
                await submitEmail(page, '');

                expect(await getComponentMessages(page)).toEqual([]);
                await expect(page.locator('#' + MESSAGES_ID)).toHaveCount(0);
            });
        });

        test.describe('with MX validation disabled', () => {
            test.beforeEach(async ({page}) => {
                await configure(page, {theme, config: {[CONFIG_PATH]: 0}});
                await openTestPage(page, [TEST_HANDLE]);
                await waitForEmailComponent(page);
            });

            test('accepts a domain without MX records', async ({page}) => {
                await submitEmail(page, 'jane@example.invalid');

                expect(await getComponentMessages(page)).toEqual([]);
                await expect(page.locator('#' + MESSAGES_ID)).toHaveCount(0);
            });

            test('still shows the error of the core email validator', async ({page}) => {
                await submitEmail(page, 'foo bar@yireo.com');

                await expect.poll(() => getComponentMessages(page)).toContainEqual({type: 'error', text: 'Invalid email address'});
            });
        });
    });
}
