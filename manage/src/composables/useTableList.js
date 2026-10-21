import { onMounted, ref } from "vue";

/**
 * 列表页通用逻辑：查询表单、分页、表格数据、批量删除
 *
 * 各列表页原本各写一份 getItems/onSearch/onCurrentChange/onSizeChange/onDelete，
 * 这里统一收口，页面只需提供列表与删除接口。
 *
 * @param {object} options
 * @param {Function} options.fetchList 列表接口，(params) => Promise<{items, count}>
 * @param {Function} [options.deleteApi] 删除接口，(data) => Promise；不传表示该页不支持删除
 * @param {object} [options.initialSearch] 查询表单初始值
 * @param {string} [options.emptySelectionMessage] 未勾选时的提示
 * @param {string} [options.confirmMessage] 删除确认文案
 * @param {Function} [options.afterDelete] 删除成功后的回调
 * @param {boolean} [options.immediate] 是否在 mounted 时自动加载，默认 true
 */
export function useTableList(options) {
  const {
    fetchList,
    deleteApi = null,
    initialSearch = {},
    emptySelectionMessage = "请先选择要删除的项",
    confirmMessage = "此操作将永久删除选中数据, 是否继续?",
    afterDelete,
    immediate = true,
  } = options;

  const loading = ref(false);
  const items = ref([]);
  const total = ref(0);
  const extend = ref(null);
  const multipleSelection = ref([]);
  const searchForm = ref({ page: 1, pagesize: 20, ...initialSearch });
  let latestRequestId = 0;

  const getItems = async () => {
    const requestId = ++latestRequestId;
    loading.value = true;
    try {
      const response = await fetchList({ ...searchForm.value });
      if (requestId !== latestRequestId) return;
      items.value = response?.items ?? [];
      total.value = response?.count ?? 0;
      extend.value = response?.extend ?? null;
    } catch (error) {
      if (requestId === latestRequestId) {
        console.error("获取列表失败:", error);
      }
    } finally {
      if (requestId === latestRequestId) loading.value = false;
    }
  };

  const onSearch = () => {
    searchForm.value.page = 1;
    getItems();
  };

  const onCurrentChange = (page) => {
    searchForm.value.page = page;
    getItems();
  };

  const onSizeChange = (size) => {
    searchForm.value.pagesize = size;
    getItems();
  };

  const handleSelectionChange = (val) => {
    multipleSelection.value = val;
  };

  const onDelete = () => {
    if (!deleteApi) {
      return;
    }
    if (multipleSelection.value.length === 0) {
      ElMessage.warning(emptySelectionMessage);
      return;
    }

    ElMessageBox.confirm(confirmMessage, "提示", {
      confirmButtonText: "确定",
      cancelButtonText: "取消",
      type: "warning",
    })
      .then(async () => {
        const loadingInstance = ElLoading.service({ fullscreen: true });
        try {
          await deleteApi({ ids: multipleSelection.value.map((item) => item.id) });
          multipleSelection.value = [];
          ElMessage.success("删除成功");
          await getItems();
          await afterDelete?.();
        } catch (error) {
          console.error("删除失败:", error);
        } finally {
          loadingInstance.close();
        }
      })
      .catch(() => {
        // 取消删除
      });
  };

  if (immediate) {
    onMounted(getItems);
  }

  return {
    loading,
    items,
    total,
    extend,
    multipleSelection,
    searchForm,
    getItems,
    onSearch,
    onCurrentChange,
    onSizeChange,
    handleSelectionChange,
    onDelete,
  };
}
