module.exports = {
    "env": {
        "browser": true,
        "es2021": true
    },
    "extends": "eslint:recommended",
    "overrides": [
    ],
    "parserOptions": {
        "ecmaVersion": "latest"
    },
    "rules": {
        "no-unused-vars": "off",
        "no-undef": "off",
        "no-redeclare": "off",
    },
    "globals": {
        "$": "readonly",
        "jQuery": "readonly",
        "App": "readonly"
    }
}
