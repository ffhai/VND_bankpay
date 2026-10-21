import { createApi } from "@/utils/baseApi";

export default createApi("/admin/config/", (request) => ({
  getItems: () => request({ url: "items" }),
  postItems: (data) => request({ url: "save", method: "post", data }),
  clearCache: () => request({ url: "clearCache" }),
}));
