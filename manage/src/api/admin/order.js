import { BaseApi } from "@/utils/baseApi";

class Api extends BaseApi {
  constructor() {
    super("/admin/order/");
  }

  confirmed(data) {
    return this.request({
      url: "confirmed",
      method: "post",
      data,
    });
  }
  notify(data) {
    return this.request({
      url: "notify",
      method: "post",
      data,
    });
  }
}

const api = new Api();

export default api;
