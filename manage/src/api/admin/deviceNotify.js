import { BaseApi } from "@/utils/baseApi";

class Api extends BaseApi {
  constructor() {
    super("/admin/DeviceNotify/");
  }
  reset(data) {
    return this.request({
      url: "reset",
      method: "post",
      data,
    });
  }
}

const api = new Api();

export default api;
