/* eslint-disable @typescript-eslint/no-require-imports */
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

function collectSourceFiles(dir) {
  const entries = fs.readdirSync(dir, { withFileTypes: true });
  return entries.flatMap((entry) => {
    const fullPath = path.join(dir, entry.name);
    if (entry.isDirectory()) return collectSourceFiles(fullPath);
    return /\.(tsx|jsx)$/.test(entry.name) ? [fullPath] : [];
  });
}

test('Ant Design Space uses orientation instead of deprecated direction', () => {
  const root = path.resolve(__dirname, '..');
  const offenders = collectSourceFiles(root)
    .filter((file) => fs.readFileSync(file, 'utf8').includes('<Space'))
    .filter((file) => /<Space\b[\s\S]*?\bdirection=/.test(fs.readFileSync(file, 'utf8')))
    .map((file) => path.relative(root, file));

  assert.deepEqual(offenders, []);
});

test('Ant Design Alert uses title instead of deprecated message prop', () => {
  const root = path.resolve(__dirname, '..');
  const offenders = collectSourceFiles(root)
    .filter((file) => fs.readFileSync(file, 'utf8').includes('<Alert'))
    .filter((file) => /<Alert\b[\s\S]*?\bmessage=/.test(fs.readFileSync(file, 'utf8')))
    .map((file) => path.relative(root, file));

  assert.deepEqual(offenders, []);
});

test('Ant Design InputNumber uses Space.Compact instead of deprecated addonAfter', () => {
  const root = path.resolve(__dirname, '..');
  const offenders = collectSourceFiles(root)
    .filter((file) => fs.readFileSync(file, 'utf8').includes('<InputNumber'))
    .filter((file) => /<InputNumber\b[^>]*\baddonAfter=/.test(fs.readFileSync(file, 'utf8')))
    .map((file) => path.relative(root, file));

  assert.deepEqual(offenders, []);
});

test('Ant Design Table pagination uses placement instead of deprecated position', () => {
  const root = path.resolve(__dirname, '..');
  const offenders = collectSourceFiles(root)
    .filter((file) => fs.readFileSync(file, 'utf8').includes('pagination'))
    .filter((file) => {
      const source = fs.readFileSync(file, 'utf8');
      return [...source.matchAll(/pagination=\{\{([\s\S]*?)\}\}/g)]
        .some((match) => /\bposition\s*:/.test(match[1]));
    })
    .map((file) => path.relative(root, file));

  assert.deepEqual(offenders, []);
});

test('Geo prompt history conditional Descriptions rows fill the configured columns', () => {
  const root = path.resolve(__dirname, '..');
  const source = fs.readFileSync(path.join(root, 'app/geo/prompts/page.tsx'), 'utf8');

  assert.equal(/Descriptions\.Item label="情绪依据" span=\{1\}/.test(source), false);
  assert.equal(/Descriptions\.Item label="情绪风险词" span=\{1\}/.test(source), false);
});
