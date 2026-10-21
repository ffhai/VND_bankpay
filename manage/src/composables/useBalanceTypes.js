import { ref } from "vue";
import adminBalance from "@/api/admin/balance";
import userBalance from "@/api/user/balance";

/**
 * 交易类型选项
 *
 * 数据来自后端静态配置（BalanceService::$typeMap），不在前端硬编码，
 * 新增类型只需改后端 map。
 *
 * @param {"admin"|"user"} scope 取哪一侧的接口，默认 admin
 */
export function useBalanceTypes(scope = "admin") {
  const typeOptions = ref([]);
  const api = scope === "user" ? userBalance : adminBalance;

  const loadTypes = async () => {
    try {
      typeOptions.value = await api.getTypes();
    } catch (error) {
      console.error("获取交易类型失败:", error);
    }
  };

  loadTypes();

  return { typeOptions, loadTypes };
}
