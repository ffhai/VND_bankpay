import { createApi } from "@/utils/baseApi";

export default createApi("/admin/configGroup/", (request) => ({
  getItems: () => request({ url: "items" }),
  postItems: (data) => request({ url: "save", method: "post", data }),
}));
