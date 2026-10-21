import { createApi } from "@/utils/baseApi";

export default createApi("/admin/rolePerms/", (request) => ({
  save: (data) => request({ url: "save", method: "post", data }),
}));
