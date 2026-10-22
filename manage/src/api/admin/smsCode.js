import request from "@/utils/request";

export default {
  getItems: (data) => request({ url: "/admin/smsCode/items", method: "post", data }),
  del: (data) => request({ url: "/admin/smsCode/del", method: "post", data }),
};
