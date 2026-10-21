import { BaseApi } from "@/utils/baseApi";

class Api extends BaseApi {
  constructor() {
    super("/channel/Device/");
  }
  getNotifyQrcode() {
    return this.request({
      url: "getNotifyQrcode",
    });
  }
}

const api = new Api();

export default api;
