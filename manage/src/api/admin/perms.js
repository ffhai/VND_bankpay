import { createApi } from "@/utils/baseApi";

export default createApi("/admin/perms/", (request) => ({
  createCurd: (data) => request({ url: "createCurd", method: "post", data }),
}));
