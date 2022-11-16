module.exports = {
    "extends": "eslint:recommended",
    "parserOptions": {
        "ecmaVersion": "latest"
    },
    "rules": {
        "no-unused-vars": "off",
        "no-undef": "off",
        "no-redeclare": "off",
    },
    "env": {
        "browser": true,
        "es2021": true
    },
    "globals": {
        "$": "readonly",
        "jQuery": "readonly",
        "App": "readonly"
    }
}
