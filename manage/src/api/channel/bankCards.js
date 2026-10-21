import { BaseApi } from "@/utils/baseApi";

class Api extends BaseApi {
  testVietqr(data) {
    return this.request({ url: "testVietqr", data });
  }

  constructor() {
    super("/channel/BankCards/");
  }
}

const api = new Api();

export default api;
