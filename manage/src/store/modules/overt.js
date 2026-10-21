import { defineStore } from "pinia";
import overt from "@/api/overt";
import { useFetchCache } from "@/composables/useFetchCache";

export const useOvertStore = defineStore("overt", () => {
  // ========== State & 通用请求缓存 ==========
  const { cache, loading, fetchData, clearCache, getCache } = useFetchCache();

  // ========== Actions ==========

  /**
   * 获取站点信息
   * @param {boolean} useCache - 是否使用缓存，默认 true
   * @returns {Promise<any>}
   */
  const getWebsite = async (useCache = true) => {
    return fetchData("website", () => overt.init(), useCache);
  };

  return {
    cache,
    loading,
    getWebsite,
    clearCache,
    getCache,
    fetchData,
  };
});
