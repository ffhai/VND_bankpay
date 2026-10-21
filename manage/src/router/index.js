// src/router/index.js
import { createRouter, createWebHashHistory } from "vue-router";
import Layout from "@/layouts/index.vue";
import { useUserStore } from "@/store/modules/user";
import transformRoutes from "@/router/routes";

export const commonRoutes = [
  {
    path: "/",
    component: Layout,
    redirect: "/dashboard",
    children: [
      {
        path: "dashboard",
        name: "控制台",
        component: () => import("@/views/Dashboard.vue"),
        meta: { title: "控制台", icon: "Odometer" },
      },
    ],
  },
  {
    path: "/login",
    name: "Login",
    component: () => import("@/views/Login.vue"),
    hidden: true,
  },
  {
    path: "/center",
    name: "个人中心",
    component: Layout,
    children: [
      {
        path: "password",
        name: "修改密码",
        component: () => import("@/views/center/Password.vue"),
        meta: { title: "修改密码", icon: "Odometer" },
        hidden: true,
      },
    ],
  },
  {
    // 兜底路由：注册在静态路由中，避免首次进入动态路由时出现 No match 警告
    path: "/:pathMatch(.*)*",
    name: "NotFound",
    component: () => import("@/views/NotFound.vue"),
    hidden: true,
  },
];

const router = createRouter({
  // 如需 HTML5 模式（无 # 号），可改用 createWebHistory()
  history: createWebHashHistory(), // Hash 模式（有 # 号）
  routes: [...commonRoutes],

  // 可选：滚动行为
  // scrollBehavior(to, from, savedPosition) {
  //   if (savedPosition) {
  //     return savedPosition; // 浏览器前进后退时保持滚动位置
  //   } else {
  //     return { top: 0 }; // 每次路由切换滚动到顶部
  //   }
  // },
});

const addRoutes = async () => {
  const userStore = useUserStore();
  const items = transformRoutes(await userStore.getRoutesItems());
  items.forEach((item) => {
    // 目录级路由没有 component，统一挂到布局下
    if (!item.component) {
      item.component = Layout;
    }
    router.addRoute(item);
  });
};

// 白名单（不需要登录验证的路由）
const whiteList = ["/login"];
// 动态路由是否已注册（防止重复注册）
let hasAddedRoutes = false;
router.beforeEach(async (to, from, next) => {
  const hasToken = localStorage.getItem("token");
  if (hasToken) {
    if (to.path === "/login") {
      next("/");
    } else if (!hasAddedRoutes) {
      await addRoutes();
      hasAddedRoutes = true;
      // 动态路由注册后，按 path 重新导航以命中真实路由
      next({ path: to.path, query: to.query, hash: to.hash, replace: true });
    } else {
      next();
    }
  } else {
    if (whiteList.includes(to.path)) {
      next();
    } else {
      next("/login");
    }
  }
});

export default router;
