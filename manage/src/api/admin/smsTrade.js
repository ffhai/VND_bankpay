import request from "@/utils/request";

export default {
  getItems: (data) => request({ url: "/admin/smsTrade/items", method: "post", data }),
  del: (data) => request({ url: "/admin/smsTrade/del", method: "post", data }),
};
