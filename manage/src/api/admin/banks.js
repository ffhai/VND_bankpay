import { BaseApi } from "@/utils/baseApi";

class Api extends BaseApi {
  constructor() {
    super("/admin/Banks/");
  }
}

const api = new Api();

export default api;
