module.exports = {
    env: {
        node: true,
    },
    extends: [
        'eslint:recommended',
        'plugin:vue/vue3-recommended',
    ],
    rules: {
        'vue/object-curly-spacing': ['error', 'always'],
        'object-curly-spacing': ['error', 'always'],
        "vue/script-indent": ["error", 4, {
            "baseIndent": 1,
            "switchCase": 1,
            "ignores": []
        }],
    }
}
