<template>
  <div class="dashboard-container">
    <div class="search-block">
      <el-form class="search-form" :inline="true" @submit.prevent="onSearch" :model="searchForm">
        <el-form-item class="search-field search-field--date">
          <el-date-picker
            v-model="searchForm.time"
            type="datetimerange"
            value-format="YYYY-MM-DD HH:mm:ss"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.code" placeholder="唯一标识" />
        </el-form-item>
        <el-form-item class="search-actions">
          <el-button type="primary" :loading="loading" @click="onSearch">查询</el-button>
          <el-button :disabled="loading" @click="onReset">重置</el-button>
          <el-button @click="onItemAdd">新增</el-button>
          <el-button type="danger" :loading="deleting" @click="onDelete">删除</el-button>
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
        <el-table-column type="selection" width="60" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="名称" />
        <el-table-column prop="code" label="标识" />
        <el-table-column prop="remark" label="备注" />
        <el-table-column prop="expire_time" label="有效期" />
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="状态" width="80" align="center">
          <template #default="scope">
            <el-tag v-if="scope.row.status === 1" disable-transitions>正常</el-tag>
            <el-tag v-if="scope.row.status === -1" disable-transitions type="info">禁用</el-tag>
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
      :title="dialogMode === 'add' ? '新增支付方式' : '编辑支付方式'"
      :mode="dialogMode"
      :model="dialogMode === 'add' ? addForm : editForm"
      :fields="formFields"
      :loading="submitting"
      :gutter="20"
      width="min(600px, 94vw)"
      @submit="dialogMode === 'add' ? onAdd($event) : onEdit($event)"
    />
  </div>
</template>
<script setup>
import FormDialog from "@/components/FormDialog/index.vue";
import { useTableList } from "@/composables/useTableList";
import CrudPagination from "@/components/CrudPagination/index.vue";
import { ref } from "vue";
import api from "@/api/admin/payType";

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

    code: "",
  },
  deleteApi: (data) => api.del(data),
});
const submitting = ref(false);

const dialogVisible = ref(false);
const dialogMode = ref("add");
const formFields = [
  { prop: "code", required: true, label: "标识", placeholder: "请输入标识" },
  { prop: "name", required: true, label: "名称", placeholder: "请输入名称" },
  {
    prop: "remark",
    label: "备注",
    placeholder: "请输入备注",
    type: "textarea",
    props: { rows: 2 },
  },
  {
    prop: "expire_time",
    label: "有效期",
    placeholder: "请输入订单有效期，单位秒",
    type: "number",
    props: { precision: 0, step: 1, min: 0 },
  },
  {
    prop: "status",
    required: true,
    label: "状态",
    placeholder: "请选择",
    type: "select",
    options: [
      { label: "启用", value: 1 },
      { label: "停用", value: -1 },
    ],
  },
];

const addForm = ref({
  code: "",
  name: "",
  expire_time: 600,
  remark: "",
  status: 1,
});

const editForm = ref({
  id: "",
  code: "",
  name: "",
  expire_time: 600,
  remark: "",
  status: 1,
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
