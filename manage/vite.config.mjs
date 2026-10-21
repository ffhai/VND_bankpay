// vite.config.js
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import fs from "fs";
import path from "path";
import AutoImport from "unplugin-auto-import/vite";
import Components from "unplugin-vue-components/vite";
import { ElementPlusResolver } from "unplugin-vue-components/resolvers";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/**
 * Element Plus 的按需样式（组件用 style/index 引入 SCSS，指令用 style/css）是
 * unplugin-vue-components 在编译期注入的深层导入，预扫描只看源码、看不到它们，
 * 于是运行期每加载到一个新组件就重新预构建并整页刷新：
 *   [vite] optimized dependencies changed. reloading
 * 这里在启动时把全部组件样式一次性登记进预构建，之后不再触发重载。
 * 这些入口里的 .scss/.css 会被预构建排除（external），仍走 Vite 正常样式管线。
 */
const elementPlusStyleEntries = (() => {
  const componentsDir = path.resolve(__dirname, "node_modules/element-plus/es/components");
  if (!fs.existsSync(componentsDir)) {
    return [];
  }
  const entries = [];
  for (const name of fs.readdirSync(componentsDir)) {
    for (const variant of ["index", "css"]) {
      if (fs.existsSync(path.join(componentsDir, name, "style", `${variant}.mjs`))) {
        entries.push(`element-plus/es/components/${name}/style/${variant}`);
      }
    }
  }
  return entries;
})();

export default defineConfig({
  base: "./",
  // 插件配置
  plugins: [
    vue(),
    AutoImport({
      resolvers: [ElementPlusResolver()],
      imports: ["vue", "vue-router", "pinia"],
    }),
    Components({
      resolvers: [
        ElementPlusResolver({
          importStyle: "sass", // 使用 SCSS
        }),
      ],
    }),
  ],
  publicDir: "public",
  // 依赖预构建：见 elementPlusStyleEntries 注释
  optimizeDeps: {
    include: elementPlusStyleEntries,
  },
  // 路径别名
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./src"),
      components: path.resolve(__dirname, "./src/components"),
      views: path.resolve(__dirname, "./src/views"),
      utils: path.resolve(__dirname, "./src/utils"),
    },
  },

  // 开发服务器配置
  server: {
    port: 3000, // 端口号
    open: true, // 自动打开浏览器
    host: "0.0.0.0", // 允许局域网访问
    https: false, // 是否启用 https
  },

  // 构建配置
  build: {
    outDir: "dist", // 输出目录
    assetsDir: "assets", // 静态资源目录
    sourcemap: false, // 是否生成 sourcemap
    //minify: "terser", // 压缩方式：'terser' | 'esbuild'
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes("node_modules")) {
            // Vue 生态
            if (id.includes("vue") || id.includes("pinia") || id.includes("vue-router")) {
              return "vue-core";
            }

            // Element Plus
            if (id.includes("element-plus") || id.includes("@element-plus")) {
              return "ui-element";
            }

            // 图表库
            if (id.includes("echarts") || id.includes("zrender")) {
              return "charts";
            }

            // 工具库
            if (id.includes("lodash") || id.includes("dayjs")) {
              return "utils";
            }

            // 其他依赖
            return "vendor";
          }
        },
        // 文件命名
        chunkFileNames: "js/[name]-[hash].js",
        entryFileNames: "js/[name]-[hash].js",
        assetFileNames: (assetInfo) => {
          const info = assetInfo.name.split(".");
          const ext = info[info.length - 1];
          if (/\.(png|jpe?g|gif|svg|webp|ico)$/.test(assetInfo.name)) {
            return `images/[name]-[hash].[ext]`;
          }
          if (/\.(css)$/.test(assetInfo.name)) {
            return `css/[name]-[hash].[ext]`;
          }
          return `assets/[name]-[hash].[ext]`;
        },
      },
    },
    chunkSizeWarningLimit: 1000, // 警告阈值 1MB
    // terserOptions: {
    //   compress: {
    //     drop_console: true, // 生产环境移除 console
    //     drop_debugger: true,
    //   },
    // },
  },

  // 环境变量
  define: {
    __APP_VERSION__: JSON.stringify("1.0.0"),
  },
});
