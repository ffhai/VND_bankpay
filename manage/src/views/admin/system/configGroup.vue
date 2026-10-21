<template>
  <div class="dashboard-container">
    <div class="search-block">
      <el-form class="search-form" :inline="true" @submit.prevent="onSearch" :model="searchForm">
        <el-form-item class="search-field search-field--date">
          <el-date-picker
            v-model="searchForm.time"
            type="datetimerange"
            value-format="X"
            format="YYYY-MM-DD HH:mm:ss"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.keyword" placeholder="搜索" />
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
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="name" label="名称" />
        <el-table-column prop="sort" label="排序" />
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
import { computed, ref } from "vue";
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
  fetchList: (params) => configGroup.getItems(params),
  deleteApi: (data) => configGroup.del(data),
  initialSearch: { keyword: "", time: "" },
});

const dialogVisible = ref(false);
const mode = ref("add");
const form = ref({});
const dialogTitle = computed(() => (mode.value === "add" ? "添加" : "编辑"));

// 添加与编辑共用同一份字段配置；disabledInEdit 的字段在编辑时不可修改
const formFields = [
  { prop: "name", label: "名称", placeholder: "请输入名称" },
  { prop: "sort", label: "排序", placeholder: "请输入排序,数字越大越靠前" },
  {
    prop: "status",
    label: "状态",
    type: "select",
    placeholder: "请选择",
    options: [
      { label: "启用", value: 1 },
      { label: "停用", value: -1 },
    ],
  },
];

const onAdd = () => {
  mode.value = "add";
  form.value = { name: "", sort: "", status: 1 };
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
      await configGroup.add(data);
      ElMessage.success("添加成功");
    } else {
      await configGroup.edit(data);
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
</script>
