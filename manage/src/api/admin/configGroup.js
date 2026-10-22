import { createApi } from "@/utils/baseApi";

export default createApi("/admin/configGroup/", (request) => ({
  getItems: () => request({ url: "items" }),
}));
