<template>
  <div class="dashboard-container">
    <div class="search-block">
      <el-form class="search-form" :inline="true" @submit.prevent="onSearch" :model="searchForm">
        <el-form-item class="search-field">
          <el-input v-model="searchForm.keyword" placeholder="搜索名称" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.key" placeholder="唯一标识" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-select v-model="searchForm.group_id" placeholder="筛选分组">
            <el-option
              v-for="val in groupOptions"
              :key="val.id"
              :label="val.name"
              :value="val.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item class="search-actions">
          <el-button type="primary" @click="onSearch">查询</el-button>
          <el-button @click="onAdd">新增</el-button>
          <el-button type="danger" @click="onDelete">删除</el-button>
        </el-form-item>
      </el-form>
    </div>

    <div class="data-list">
      <el-table
        v-loading="loading"
        :data="items"
        style="width: 100%"
        empty-text="暂无数据"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="55" />
        <el-table-column prop="group_name" label="分组" width="100" />
        <el-table-column prop="name" label="名称" width="120" />
        <el-table-column prop="key" label="唯一标识" width="180" />
        <el-table-column prop="type" label="类型" width="100" />
        <el-table-column prop="placeholder" label="描述" />
        <el-table-column prop="content" label="内容" />
        <el-table-column prop="params" label="附加参数" />
        <el-table-column prop="sort" label="排序" width="80" />
        <el-table-column label="是否显示" width="80" align="center">
          <template #default="scope">
            <el-tag v-if="scope.row.show === 1" disable-transitions>是</el-tag>
            <el-tag v-if="scope.row.show === -1" disable-transitions type="info">否</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="80" fixed="right">
          <template #default="scope">
            <el-button type="primary" size="small" @click="onItemEdit(scope.row)">编辑</el-button>
          </template>
        </el-table-column>
      </el-table>
    </div>
    <CrudPagination
      :current-page="searchForm.page"
      :page-size="searchForm.pagesize"
      :total="total"
      @size-change="onSizeChange"
      @current-change="onCurrentChange"
    />
    <FormDialog
      v-model="dialogVisible"
      :title="dialogTitle"
      :mode="mode"
      :model="form"
      :fields="formFields"
      :loading="loading"
      @submit="onSubmit"
    />
  </div>
</template>
<script setup>
import { computed, ref, onMounted } from "vue";
import configList from "@/api/admin/configList";
import configGroup from "@/api/admin/configGroup";
import FormDialog from "@/components/FormDialog/index.vue";
import CrudPagination from "@/components/CrudPagination/index.vue";
import { useTableList } from "@/composables/useTableList";

const {
  loading,
  items,
  total,
  searchForm,
  getItems,
  onSearch,
  onCurrentChange,
  onSizeChange,
  handleSelectionChange,
  onDelete,
} = useTableList({
  fetchList: (params) => configList.getItems(params),
  deleteApi: (data) => configList.del(data),
  initialSearch: { keyword: "", time: "" },
});

const groupOptions = ref([]);

const dialogVisible = ref(false);
const mode = ref("add");
const form = ref({});
const dialogTitle = computed(() => (mode.value === "add" ? "添加" : "编辑"));

// 添加与编辑共用同一份字段配置；disabledInEdit 的字段在编辑时不可修改
const formFields = computed(() => [
  {
    prop: "group_id",
    label: "分组",
    type: "select",
    placeholder: "请选择",
    options: groupOptions.value.map((item) => ({ label: item.name, value: item.id })),
  },
  { prop: "name", label: "名称", placeholder: "请输入名称" },
  { prop: "key", label: "唯一标识", placeholder: "请输入唯一标识" },
  {
    prop: "type",
    label: "类型",
    type: "select",
    placeholder: "请选择",
    options: [
      { label: "文本输入", value: "text" },
      { label: "图片上传", value: "image" },
      { label: "下拉选择", value: "select" },
    ],
  },
  { prop: "placeholder", label: "描述内容", placeholder: "可空，提示消息" },
  { prop: "params", label: "附加参数", placeholder: "可空，长度小于255" },
  { prop: "content", label: "配置内容", placeholder: "请输入配置内容，长度小于255" },
  { prop: "sort", label: "排序", placeholder: "请输入排序,数字越大越靠前" },
  {
    prop: "show",
    label: "是否显示",
    type: "select",
    placeholder: "请选择",
    options: [
      { label: "是", value: 1 },
      { label: "否", value: -1 },
    ],
  },
]);

const onAdd = () => {
  mode.value = "add";
  form.value = {
    group_id: "",
    name: "",
    key: "",
    type: "",
    content: "",
    placeholder: "",
    params: "",
    sort: "",
    show: 1,
  };
  dialogVisible.value = true;
};
const onItemEdit = (item) => {
  mode.value = "edit";
  form.value = { ...item };
  dialogVisible.value = true;
};
const onSubmit = async (data) => {
  loading.value = true;
  try {
    if (mode.value === "add") {
      await configList.add(data);
      ElMessage.success("添加成功");
    } else {
      await configList.edit(data);
      ElMessage.success("编辑成功");
    }
    dialogVisible.value = false;
    getItems();
  } catch (error) {
    console.error("保存失败:", error);
  } finally {
    loading.value = false;
  }
};
const getGroupOptione = async () => {
  const response = await configGroup.getOptions();
  groupOptions.value = response;
};

onMounted(() => {
  getGroupOptione();
});
</script>
