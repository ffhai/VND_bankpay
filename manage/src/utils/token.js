const TOKEN_KEY = "token";

/**
 * 读取登录令牌
 */
export const getToken = () => localStorage.getItem(TOKEN_KEY) || "";

/**
 * 写入登录令牌
 */
export const setToken = (token) => {
  if (token) {
    localStorage.setItem(TOKEN_KEY, token);
  } else {
    clearToken();
  }
};

/**
 * 清除登录令牌
 */
export const clearToken = () => {
  localStorage.removeItem(TOKEN_KEY);
};

// 防止并发 401 触发多次跳转
let redirecting = false;

/**
 * 登录态已失效：清掉令牌、回到登录页并整页刷新
 *
 * 整页刷新是必要的：动态权限路由已经注册进 router 实例，且 hasAddedRoutes 标记为已注册，
 * 只做路由跳转的话，下一个账号会继续沿用上一个账号的路由（表现为菜单缺失或整页 404）。
 */
export const redirectToLogin = () => {
  if (redirecting) {
    return;
  }
  redirecting = true;

  clearToken();
  window.location.hash = "#/login";
  window.location.reload();
};
