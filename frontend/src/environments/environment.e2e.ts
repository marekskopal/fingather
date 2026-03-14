export const environment = {
    production: false,
    apiUrl: 'http://localhost:7297/api',
    i18nPath: '/i18n/',
    i18nVersion: '8',
    stripe: {
        plans: {
            investor: {
                name: 'Investor',
                monthly: { priceId: 'price_1T13ZxF0AEwdewVQFBBCYprx', price: 8.0 },
                annual: { priceId: 'price_1QCpTXF0AEwdewVQVoDL5EDs', price: 80.0 },
                currencyId: 1,
            },
            pro: {
                name: 'Pro',
                monthly: { priceId: 'price_1T13aSF0AEwdewVQsM8ri9tt', price: 16.0 },
                annual: { priceId: 'price_1QD9FOF0AEwdewVQ2q0W0OZ2', price: 160.0 },
                currencyId: 1,
            },
        },
    },
};
