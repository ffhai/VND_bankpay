import { createApi } from "@/utils/baseApi";

export default createApi("/admin/member/", (request) => ({
  // 调整账号余额
  adjust: (data) => request({ url: "adjust", method: "post", data }),
}));
