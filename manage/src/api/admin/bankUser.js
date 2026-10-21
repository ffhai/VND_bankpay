import { BaseApi } from "@/utils/baseApi";

class Api extends BaseApi {
  constructor() {
    super("/admin/BankUser/");
  }
}

const api = new Api();

export default api;
