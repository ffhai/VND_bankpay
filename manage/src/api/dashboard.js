import { createApi } from "@/utils/baseApi";

// 每个模块独立鉴权；后续统计接口放在对应模块的 Statistics 下。
const dashboard = (module) =>
  createApi(`/${module}/statistics/`, (request) => ({
    getOrders: () => request({ url: "orders", method: "get", silentPermissionError: true }),
  }));

const admin = dashboard("admin");
admin.getAccounts = () =>
  admin.request({ url: "accounts", method: "get", silentPermissionError: true });

export default {
  admin,
  merchant: dashboard("merchant"),
  channel: dashboard("channel"),
};
