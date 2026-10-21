import { createApi } from "@/utils/baseApi";

export default createApi("/user/balance/", (request) => ({
  // 当前用户余额
  getInfo: () => request({ url: "info", method: "get" }),
  // 交易类型选项（后端静态配置 BalanceService::$typeMap）
  getTypes: () => request({ url: "types" }),
}));
