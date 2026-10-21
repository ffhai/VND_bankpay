import { createApi } from "@/utils/baseApi";

export default createApi("/admin/editor/", (request) => ({
  getItems: () => request({ url: "items" }),
  postItems: (data) => request({ url: "save", method: "post", data }),
}));
