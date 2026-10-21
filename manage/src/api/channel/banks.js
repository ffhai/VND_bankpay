import { BaseApi } from "@/utils/baseApi";

class Api extends BaseApi {
  constructor() {
    super("/channel/Banks/");
  }
}

const api = new Api();

export default api;
