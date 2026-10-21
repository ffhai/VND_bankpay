import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import router from "./router"; // 导入路由
import "./router/routes"; // 导入路由

import "@/styles/reset.css"; // ✅ 引入重置样式
import "@/styles/search.css";
import "@/styles/theme.css";

// 标签页标题：优先使用环境变量中的站点名
const appTitle = import.meta.env.VITE_APP_TITLE;
if (appTitle) {
  document.title = appTitle;
}

const app = createApp(App);

const pinia = createPinia();
app.use(pinia);
app.use(router);
app.mount("#app");
