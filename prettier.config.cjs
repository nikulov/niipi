module.exports = {
    printWidth: 140,

    singleQuote: true,
    semi: true,
    trailingComma: 'all',

    plugins: ['prettier-plugin-tailwindcss', 'prettier-plugin-blade'],

    overrides: [
        {
            files: '*.blade.php',
            options: {
                parser: 'blade',
                printWidth: 180,
            },
        },
    ],
};
