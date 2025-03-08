import globals from 'globals';
import js from '@eslint/js';
import stylistic from '@stylistic/eslint-plugin';

/** @type {import('eslint').Linter.Config[]} */
export default [
  {
    plugins: {
      '@stylistic': stylistic,
    },
    languageOptions: {
      globals: { ...globals.browser }
    },
  },
  stylistic.configs.customize({
    indent: 2,
    quotes: 'single',
    semi: true,
    braceStyle: '1tbs',
  }),
  js.configs.recommended,
  {
    rules: {
      '@stylistic/curly-newline': ['error', 'always'],
      '@stylistic/arrow-parens': ['error', 'always'],
      'arrow-body-style': ['error', 'as-needed'],
      'curly': ['error'],
      'eqeqeq': ['error'],
    },
  },
];
