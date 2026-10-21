import { BaseApi } from "@/utils/baseApi";

class Api extends BaseApi {
  constructor() {
    super("/channel/BankUser/");
  }
}

const api = new Api();

export default api;
