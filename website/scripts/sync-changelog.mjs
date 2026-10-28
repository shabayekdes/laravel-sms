// Copies the repository CHANGELOG.md into the docs so it has one source.
import { readFileSync, writeFileSync } from 'node:fs';

const source = new URL('../../CHANGELOG.md', import.meta.url);
const target = new URL('../docs/changelog.md', import.meta.url);

const body = readFileSync(source, 'utf8').replace(/^# .*\n/, '');
const frontMatter = [
  '---',
  'title: Changelog',
  'custom_edit_url: https://github.com/shabayekdes/laravel-sms/edit/main/CHANGELOG.md',
  '---',
  '',
  '# Changelog',
  '',
].join('\n');

writeFileSync(target, frontMatter + body.replace(/^\n+/, ''));
