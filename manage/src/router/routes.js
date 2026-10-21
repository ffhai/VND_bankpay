const routesMap = {
  "/admin/system": {
    meta: { title: "系统", icon: "Setting" },
    hidden: false,
  },
  "/admin/system/config": {
    name: "设置",
    component: () => import("@/views/admin/system/config.vue"),
    meta: { title: "设置" },
    hidden: false,
  },
  "/admin/system/configGroup": {
    name: "设置分组",
    component: () => import("@/views/admin/system/configGroup.vue"),
    meta: { title: "设置分组" },
    hidden: false,
  },
  "/admin/system/configList": {
    name: "设置列表",
    component: () => import("@/views/admin/system/configList.vue"),
    meta: { title: "设置列表" },
    hidden: false,
  },
  "/admin/system/upload": {
    name: "文件管理",
    component: () => import("@/views/admin/system/upload.vue"),
    meta: { title: "文件管理" },
    hidden: false,
  },
  "/admin/system/editor": {
    name: "富文本",
    component: () => import("@/views/admin/system/editor.vue"),
    meta: { title: "富文本" },
    hidden: false,
  },

  "/admin/account": {
    meta: { title: "账号管理", icon: "User" },
    hidden: false,
  },
  "/admin/account/member": {
    name: "账号",
    component: () => import("@/views/admin/account/member.vue"),
    meta: { title: "账号" },
    hidden: false,
  },
  "/admin/account/appList": {
    name: "API管理",
    component: () => import("@/views/admin/account/appList.vue"),
    meta: { title: "API管理" },
    hidden: false,
  },
  "/admin/account/roles": {
    name: "角色管理",
    component: () => import("@/views/admin/account/roles.vue"),
    meta: { title: "角色管理" },
    hidden: false,
  },
  "/admin/account/perms": {
    name: "权限节点",
    component: () => import("@/views/admin/account/perms.vue"),
    meta: { title: "权限节点" },
    hidden: false,
  },

  "/admin/finance": {
    meta: { title: "财务管理", icon: "Wallet" },
    hidden: false,
  },
  "/admin/finance/balance": {
    name: "余额记录",
    component: () => import("@/views/admin/account/balance.vue"),
    meta: { title: "余额记录" },
    hidden: false,
  },

  "/user": {
    meta: { title: "个人中心", icon: "User" },
    hidden: false,
  },
  "/user/balance": {
    name: "我的余额",
    component: () => import("@/views/user/balance.vue"),
    meta: { title: "我的余额" },
    hidden: false,
  },
};

export const routeOptions = Object.entries(routesMap).map(([path, route]) => ({
  value: path,
  label: `${route.meta?.title || route.name || path} (${path})`,
  node_type: route.component ? "page" : "group",
}));

/**
 * 把后端返回的权限路由转换成 vue-router 可注册的路由配置
 *
 * 两点注意：
 * 1. 不能直接改动 routesMap 中的对象，否则「注册路由」与「渲染菜单」两次调用会互相污染
 *    （例如上一次的 children 残留到下一次）；
 * 2. 未在 routesMap 中登记的路径直接跳过，不再返回 false，
 *    避免调用方访问 route.component 时抛错。
 */
const transformRoutes = (paths = []) => {
  return paths.reduce((routes, path) => {
    const routeConfig = routesMap[path.name];
    if (!routeConfig) {
      console.warn(`[router] routesMap 未登记该路径，已跳过: ${path.name}`);
      return routes;
    }

    const route = { ...routeConfig, path: path.name, node_type: path.node_type };
    if (Array.isArray(path.children) && path.children.length > 0) {
      route.children = transformRoutes(path.children);
    }
    routes.push(route);

    return routes;
  }, []);
};

export default transformRoutes;
