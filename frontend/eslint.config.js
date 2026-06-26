// @ts-check
const eslint = require('@eslint/js');
const tseslint = require('typescript-eslint');
const angular = require('angular-eslint');
const simpleImportSort = require('eslint-plugin-simple-import-sort');
const unusedImports = require('eslint-plugin-unused-imports');

module.exports = tseslint.config(
    {
        ignores: ['projects/**/*'],
    },
    {
        files: ['**/*.ts'],
        extends: [
            eslint.configs.recommended,
            ...tseslint.configs.recommended,
            ...angular.configs.tsRecommended,
        ],
        processor: angular.processInlineTemplates,
        languageOptions: {
            parserOptions: {
                projectService: true,
                tsconfigRootDir: __dirname,
            },
        },
        plugins: {
            'simple-import-sort': simpleImportSort,
            'unused-imports': unusedImports,
        },
        rules: {
            '@angular-eslint/prefer-on-push-component-change-detection': 'error',
            'comma-dangle': ['error', 'always-multiline'],
            'no-continue': 'off',
            'no-return-assign': 'off',
            'class-methods-use-this': 'off',
            'no-restricted-syntax': ['error', 'ForInStatement', 'LabeledStatement', 'WithStatement'],
            'no-param-reassign': 'off',
            'max-len': ['error', 150],
            'simple-import-sort/imports': 'error',
            'simple-import-sort/exports': 'error',
            '@typescript-eslint/no-unused-vars': 'off',
            'unused-imports/no-unused-imports': 'error',
            'unused-imports/no-unused-vars': [
                'warn',
                { vars: 'all', varsIgnorePattern: '^_', args: 'after-used', argsIgnorePattern: '^_' },
            ],
            '@typescript-eslint/no-shadow': 'error',
            '@typescript-eslint/no-explicit-any': 'error',
            '@angular-eslint/directive-selector': [
                'error',
                { type: 'attribute', prefix: 'fingather', style: 'camelCase' },
            ],
            '@angular-eslint/component-selector': [
                'error',
                { type: 'element', prefix: 'fingather', style: 'kebab-case' },
            ],
            '@typescript-eslint/explicit-member-accessibility': 'error',
            '@typescript-eslint/explicit-function-return-type': 'error',
        },
    },
    {
        files: ['**/*.html'],
        extends: [
            ...angular.configs.templateRecommended,
            ...angular.configs.templateAccessibility,
        ],
        rules: {
            '@angular-eslint/template/click-events-have-key-events': 'off',
            '@angular-eslint/template/prefer-self-closing-tags': 'error',
            '@angular-eslint/template/prefer-ngsrc': 'error',
            '@angular-eslint/template/prefer-control-flow': 'error',
        },
    },
);
