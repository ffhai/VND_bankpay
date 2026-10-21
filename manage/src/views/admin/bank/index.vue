<template>
  <div class="dashboard-container">
    <div class="search-block">
      <el-form class="search-form" :inline="true" @submit.prevent="onSearch" :model="searchForm">
        <el-form-item class="search-field search-field--date">
          <el-date-picker
            v-model="searchForm.time"
            type="datetimerange"
            value-format="YYYY-MM-DD HH:mm:ss"
            format="YYYY-MM-DD HH:mm:ss"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.keyword" placeholder="搜索" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-select v-model="searchForm.status" placeholder="状态">
            <el-option label="全部" value="" />
            <el-option label="启用" value="1" />
            <el-option label="停用" value="-1" />
          </el-select>
        </el-form-item>
        <el-form-item class="search-actions">
          <el-button type="primary" :loading="loading" @click="onSearch">查询</el-button>
          <el-button :disabled="loading" @click="onReset">重置</el-button>
          <el-button @click="onItemAdd">新增</el-button>
          <el-button type="danger" :loading="deleting" @click="onDelete">删除/停用</el-button>
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
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="名称" />
        <el-table-column prop="code" label="短代码" width="100" />
        <el-table-column prop="bin" label="Bin" width="100" />
        <!-- <el-table-column prop="logo" label="logo" /> -->
        <el-table-column prop="short_name" label="简称" width="150" />
        <el-table-column prop="swift_code" label="长代码" width="120" />
        <el-table-column label="状态" width="80" align="center">
          <template #default="scope">
            <el-tag v-if="scope.row.status === 1" disable-transitions>正常</el-tag>
            <el-tag v-if="scope.row.status === -1" disable-transitions type="info">禁用</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="入款" width="80" align="center">
          <template #default="scope">
            <el-tag v-if="scope.row.status_in === 1" disable-transitions>开</el-tag>
            <el-tag v-if="scope.row.status_in === -1" disable-transitions type="info">关</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="出款" width="80" align="center">
          <template #default="scope">
            <el-tag v-if="scope.row.status_out === 1" disable-transitions>开</el-tag>
            <el-tag v-if="scope.row.status_out === -1" disable-transitions type="info">关</el-tag>
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
      :title="dialogMode === 'add' ? '新增银行' : '编辑银行'"
      :mode="dialogMode"
      :model="dialogMode === 'add' ? addForm : editForm"
      :fields="formFields"
      :loading="submitting"
      :gutter="20"
      width="min(800px, 94vw)"
      @submit="dialogMode === 'add' ? onAdd($event) : onEdit($event)"
    />
  </div>
</template>
<script setup>
import FormDialog from "@/components/FormDialog/index.vue";
import { useTableList } from "@/composables/useTableList";
import CrudPagination from "@/components/CrudPagination/index.vue";
import { ref } from "vue";
import api from "@/api/admin/banks";

const {
  loading,
  items,
  total,
  searchForm,
  getItems,
  onSearch,
  onReset,
  onCurrentChange,
  onSizeChange,
  handleSelectionChange,
  onDelete,
  deleting,
} = useTableList({
  fetchList: (params) => api.getItems(params),
  initialSearch: {
    keyword: "",
    time: "",

    status: "",
  },
  deleteApi: (data) => api.del(data),
  confirmMessage: "已引用资料将停用，其余资料删除，是否继续？",
  deleteSuccessMessage: "处理完成，已引用资料保留并停用",
});
const submitting = ref(false);

const dialogVisible = ref(false);
const dialogMode = ref("add");
const formFields = [
  { prop: "name", required: true, label: "名称", placeholder: "请输入名称", span: 12 },
  { prop: "code", required: true, label: "短代码", placeholder: "请输入短代码", span: 12 },
  { prop: "bin", label: "Bin", placeholder: "请输入Bin", span: 12 },
  { prop: "logo", label: "Logo", placeholder: "请输入Logo", span: 12 },
  { prop: "short_name", label: "简称", placeholder: "请输入简称", span: 12 },
  { prop: "swift_code", label: "长代码", placeholder: "请输入长代码", span: 12 },
  {
    prop: "status",
    required: true,
    label: "状态",
    placeholder: "请选择",
    span: 12,
    type: "select",
    options: [
      { label: "启用", value: 1 },
      { label: "停用", value: -1 },
    ],
  },
  {
    prop: "status_in",
    label: "入款",
    placeholder: "请选择",
    span: 12,
    type: "select",
    options: [
      { label: "启用", value: 1 },
      { label: "停用", value: -1 },
    ],
  },
  {
    prop: "status_out",
    label: "出款",
    placeholder: "请选择",
    span: 12,
    type: "select",
    options: [
      { label: "启用", value: 1 },
      { label: "停用", value: -1 },
    ],
  },
];

const addForm = ref({
  name: "",
  code: "",
  bin: "",
  logo: "",
  short_name: "",
  swift_code: "",
  status: -1,
  status_in: -1,
  status_out: -1,
});

const editForm = ref({
  id: "",
  name: "",
  code: "",
  bin: "",
  logo: "",
  short_name: "",
  swift_code: "",
  status: -1,
  status_in: -1,
  status_out: -1,
});

const onItemAdd = () => {
  dialogMode.value = "add";
  dialogVisible.value = true;
};
const onAdd = async (data) => {
  if (submitting.value) return;
  submitting.value = true;
  try {
    await api.add(data);
    dialogVisible.value = false;
    ElMessage.success("添加成功");
    getItems();
  } catch (error) {
    console.error("获取列表失败:", error);
  } finally {
    submitting.value = false;
  }
};
const onItemEdit = (item) => {
  editForm.value = { ...item };
  dialogMode.value = "edit";
  dialogVisible.value = true;
};
const onEdit = async (data) => {
  if (submitting.value) return;
  submitting.value = true;
  try {
    await api.edit(data);
    dialogVisible.value = false;
    ElMessage.success("编辑成功");
    getItems();
  } catch (error) {
    console.error("获取列表失败:", error);
  } finally {
    submitting.value = false;
  }
};
</script>
