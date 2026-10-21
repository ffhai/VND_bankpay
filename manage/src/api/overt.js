import { createApi } from "@/utils/baseApi";

export default createApi("/common/overt/", (request) => ({
  init: () => request({ url: "init" }),
  getCaptcha: () => request({ url: "captcha" }),
  postLogin: (data) => request({ url: "login", method: "post", data }),
}));
