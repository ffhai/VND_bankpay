import { createApi } from "@/utils/baseApi";

export default createApi("/common/account/", (request) => ({
  getUserInfo: () => request({ url: "info" }),
  logout: () => request({ url: "logout" }),
  postPassword: (data) => request({ url: "password", method: "post", data }),
  getNotice: () => request({ url: "notice" }),
  getUploadInfo: () => request({ url: "uploadinfo" }),
  uploadFile: (data) =>
    request({
      url: "upload",
      method: "post",
      data,
      headers: { "Content-Type": "multipart/form-data" },
    }),
  getRoutes: () => request({ url: "getRoutes" }),
}));
