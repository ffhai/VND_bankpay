import { defineStore } from "pinia";
import user from "@/api/user";
import { useFetchCache } from "@/composables/useFetchCache";
import { clearToken, setToken } from "@/utils/token";

export const useUserStore = defineStore("user", () => {
  // ========== State & 通用请求缓存 ==========
  const { cache, loading, fetchData, clearCache, getCache } = useFetchCache();

  // ========== Actions ==========

  /**
   * 登录成功：保存令牌
   */
  const signIn = (token) => {
    setToken(token);
  };

  /**
   * 退出登录：通知服务端作废令牌，并清空本地登录态与缓存
   *
   * @param {boolean} notifyServer 是否调用退出接口
   */
  const signOut = async (notifyServer = true) => {
    try {
      if (notifyServer) {
        await user.logout();
      }
    } catch (error) {
      console.error("退出登录失败:", error);
    } finally {
      clearToken();
      clearCache();
    }
  };

  /**
   * @param {boolean} useCache - 是否使用缓存，默认 true
   * @returns {Promise<any>}
   */
  const getUserInfo = async (useCache = true) => {
    return fetchData("userInfo", () => user.getUserInfo(), useCache);
  };
  const getRoutesItems = async (useCache = true) => {
    return fetchData("routerItems", () => user.getRoutes(), useCache);
  };

  return {
    cache,
    loading,
    signIn,
    signOut,
    getUserInfo,
    getRoutesItems,
    clearCache,
    getCache,
    fetchData,
  };
});
