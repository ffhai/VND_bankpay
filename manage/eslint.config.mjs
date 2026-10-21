import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import prettierConfig from "eslint-config-prettier";

export default [
  {
    ignores: ["dist/**", "node_modules/**", "public/**", "coverage/**"],
  },
  js.configs.recommended,
  ...pluginVue.configs["flat/recommended"],
  prettierConfig,
  {
    languageOptions: {
      globals: {
        defineProps: "readonly",
        defineEmits: "readonly",
        defineExpose: "readonly",
        defineOptions: "readonly",
        withDefaults: "readonly",
      },
    },
    rules: {
      // 项目依赖 unplugin-auto-import 自动引入 API/组件，关闭 no-undef
      "no-undef": "off",
      "no-unused-vars": "warn",
      "no-empty": ["error", { allowEmptyCatch: true }],
      "vue/multi-word-component-names": "off",
      "vue/no-unused-vars": "warn",
      "vue/attributes-order": "off",
    },
  },
];
