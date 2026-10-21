import { BaseApi } from "@/utils/baseApi";

class Api extends BaseApi {
  constructor() {
    super("/merchant/order/");
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
