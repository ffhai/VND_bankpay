import { BaseApi } from "@/utils/baseApi";

class Api extends BaseApi {
  constructor() {
    super("/admin/PayType/");
  }

  // 如果将来有 configGroup 独有的接口，写在这里
  // getTree(data) {
  //   return this.request({ url: "tree", method: "post", data });
  // }
}

const api = new Api();

export default api;
