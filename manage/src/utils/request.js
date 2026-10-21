import axios from "axios";
import { ElMessage } from "element-plus";
import { getToken, redirectToLogin } from "@/utils/token";

// 创建 axios 实例
const request = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 10000,
  headers: {
    "Content-Type": "application/json",
  },
});

// 请求拦截器
request.interceptors.request.use(
  (config) => {
    // 添加 token
    const token = getToken();
    if (token) {
      config.headers["Api-Token"] = token;
    }

    // 添加请求时间戳防止缓存
    if (config.method === "get") {
      config.params = {
        ...config.params,
        _t: Date.now(),
      };
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// 业务成功码
const CODE_SUCCESS = 1;
// 未登录（登录态失效）
const CODE_NOT_AUTH = -10001;

/**
 * 统一处理后端 {code, msg, data} 信封
 * 后端既可能以 HTTP 200 返回业务错误，也会用 401/403/422/500 表达语义，这里统一按 code 判断
 */
const handleEnvelope = (payload) => {
  const { code, data, msg } = payload || {};

  if (code === CODE_SUCCESS) {
    return data;
  }

  if (code === CODE_NOT_AUTH) {
    // 登录态失效：清掉令牌并回到登录页
    redirectToLogin();
    return Promise.reject(new Error(msg || "未登录或登录已过期，请重新登录"));
  }

  ElMessage.error(msg || "请求失败");

  return Promise.reject(new Error(msg || "请求失败"));
};

request.interceptors.response.use(
  (response) => handleEnvelope(response.data),
  (error) => {
    const payload = error.response?.data;

    // 后端返回了错误信封（401/403/422/500 等）时按信封给出具体原因，
    // 否则（网络中断、网关错误页）才提示网络异常
    if (payload && typeof payload.code !== "undefined") {
      return handleEnvelope(payload);
    }

    console.error("HTTP Error:", error.response ? error.response.status : "Network Error");
    ElMessage.error("网络异常，请检查网络连接");

    return Promise.reject(error);
  }
);

export default request;
