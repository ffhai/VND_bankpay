import { createApi } from "@/utils/baseApi";

export default createApi("/admin/balance/", (request) => ({
  // 交易类型选项（后端静态配置 BalanceService::$typeMap）
  getTypes: () => request({ url: "types" }),
}));
