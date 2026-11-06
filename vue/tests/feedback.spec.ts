/*!
 * Matomo - free/libre analytics platform
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
import { rememberVote, storedVote } from '../src/feedback';

function memoryStorage(): Storage {
  const items: Record<string, string> = {};
  return {
    getItem: (key: string) => (key in items ? items[key] : null),
    setItem: (key: string, value: string) => { items[key] = value; },
  } as unknown as Storage;
}

describe('feedback vote in this browser', () => {
  it('remembers the vote per question and plugin version', () => {
    const storage = memoryStorage();
    rememberVote('free_form_table', '6.0.0-beta.1', 'down', storage);
    expect(storedVote('free_form_table', '6.0.0-beta.1', storage)).toBe('down');
  });

  it('asks again after an update of the plugin', () => {
    const storage = memoryStorage();
    rememberVote('free_form_table', '5.0.0', 'up', storage);
    expect(storedVote('free_form_table', '5.0.1', storage)).toBe('');
  });

  it('ignores unreadable or foreign values and a blocked storage', () => {
    const storage = memoryStorage();
    storage.setItem('FeinwerkDataExploration.feedback.free_form_table', '{"version":"5.0.0","vote":"maybe"}');
    expect(storedVote('free_form_table', '5.0.0', storage)).toBe('');
    storage.setItem('FeinwerkDataExploration.feedback.free_form_table', 'not json');
    expect(storedVote('free_form_table', '5.0.0', storage)).toBe('');
    const blocked = {
      getItem: () => { throw new Error('blocked'); },
      setItem: () => { throw new Error('blocked'); },
    } as unknown as Storage;
    expect(storedVote('free_form_table', '5.0.0', blocked)).toBe('');
    expect(() => rememberVote('free_form_table', '5.0.0', 'up', blocked)).not.toThrow();
  });
});
