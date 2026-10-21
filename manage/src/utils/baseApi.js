// @/utils/baseApi.js
import request from "@/utils/request";

export class BaseApi {
  /**
   * @param {string} urlPre 接口前缀，如 /admin/roles/
   */
  constructor(urlPre) {
    this.urlPre = urlPre;
  }

  /**
   * 统一请求入口，方便以后加拦截、loading、签名等
   * @param {object} options { url, method, data, params, ... }
   */
  request(options) {
    const { url, method = "post", ...rest } = options;
    return request({
      method,
      url: this.urlPre + url,
      ...rest,
    });
  }

  // ---------- 公共 CRUD ----------

  getItems(data) {
    return this.request({ url: "items", method: "post", data });
  }

  add(data) {
    return this.request({ url: "add", method: "post", data });
  }

  edit(data) {
    return this.request({ url: "edit", method: "post", data });
  }

  del(data) {
    return this.request({ url: "del", method: "post", data });
  }

  getOptions() {
    return this.request({ url: "getOptions", method: "get" });
  }
}

/**
 * 创建接口实例
 *
 * 只有公共 CRUD 的模块：createApi("/admin/roles/")
 * 需要额外接口时通过第二个参数声明，无需再写 class：
 *
 *   createApi("/admin/config/", (request) => ({
 *     getItems: () => request({ url: "items" }),
 *     postItems: (data) => request({ url: "save", method: "post", data }),
 *   }))
 *
 * @param {string} urlPre 接口前缀
 * @param {(request: Function) => object} [extend] 额外方法，参数为已绑定前缀的 request
 * @returns {BaseApi}
 */
export function createApi(urlPre, extend) {
  const api = new BaseApi(urlPre);

  if (typeof extend === "function") {
    Object.assign(api, extend(api.request.bind(api)));
  }

  return api;
}
