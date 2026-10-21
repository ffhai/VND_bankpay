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
          <el-input v-model="searchForm.keyword" placeholder="搜索姓名" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.idcard" placeholder="搜索身份证号" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-select v-model="searchForm.status" placeholder="状态">
            <el-option label="全部" value="" />
            <el-option label="启用" value="1" />
            <el-option label="停用" value="-1" />
          </el-select>
        </el-form-item>
        <el-form-item class="search-actions">
          <el-button type="primary" @click="onSearch">查询</el-button>
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
        <el-table-column label="码商">
          <template #default="scope">
            <div>{{ scope.row.user?.username }}</div>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="姓名" />
        <el-table-column prop="idcard" label="身份证号" width="150" />
        <el-table-column prop="remark" label="备注" />
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
      title="编辑银行户名"
      mode="edit"
      :model="editForm"
      :fields="formFields"
      :loading="loading"
      :gutter="20"
      width="min(600px, 94vw)"
      @submit="onEdit"
    />
  </div>
</template>
<script setup>
import FormDialog from "@/components/FormDialog/index.vue";
import CrudPagination from "@/components/CrudPagination/index.vue";
import { ref, onMounted } from "vue";
import api from "@/api/admin/bankUser";

const loading = ref(false);
const dialogVisible = ref(false);
const formFields = [
  { prop: "name", label: "姓名", placeholder: "请输入姓名" },
  { prop: "idcard", label: "身份证号", placeholder: "请输入身份证号" },
  {
    prop: "remark",
    label: "备注",
    placeholder: "请输入备注",
    type: "textarea",
    props: { rows: 2 },
  },
  {
    prop: "status",
    label: "状态",
    placeholder: "请选择",
    type: "select",
    options: [
      { label: "启用", value: 1 },
      { label: "停用", value: -1 },
    ],
  },
];

const searchForm = ref({
  keyword: "",
  time: "",
  page: 1,
  pagesize: 20,
});
const total = ref(0);
const items = ref([]);

const editForm = ref({
  id: "",
  name: "",
  idcard: "",
  remark: "",
  status: -1,
});
const multipleSelection = ref([]);

const onSearch = () => {
  searchForm.value.page = 1;
  getItems();
};
const getItems = async () => {
  loading.value = true;
  try {
    const response = await api.getItems(searchForm.value);
    items.value = response.items;
    total.value = response.count;
  } catch (error) {
    console.error("获取列表失败:", error);
  } finally {
    loading.value = false;
  }
};

const onCurrentChange = (page) => {
  searchForm.value.page = page;
  getItems();
};
const onSizeChange = (size) => {
  searchForm.value.pagesize = size;
  getItems();
};

const onItemEdit = (item) => {
  editForm.value = { ...item };
  dialogVisible.value = true;
};
const onEdit = async (data) => {
  loading.value = true;
  try {
    await api.edit(data);
    dialogVisible.value = false;
    ElMessage.success("编辑成功");
    getItems();
  } catch (error) {
    console.error("获取列表失败:", error);
  } finally {
    loading.value = false;
  }
};
const onDelete = () => {
  if (multipleSelection.value.length === 0) {
    ElMessage.warning("请先选择要删除的项");
    return;
  }
  ElMessageBox.confirm("此操作将永久删除选中数据, 是否继续?", "提示", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning",
  })
    .then(async () => {
      const loadingInstance = ElLoading.service({ fullscreen: true });
      try {
        const ids = multipleSelection.value.map((item) => item.id);
        await api.del({ ids: ids });
        ElMessage.success("删除成功");
        getItems();
      } catch {
        // 请求错误由统一请求层提示。
      } finally {
        loadingInstance.close();
      }
    })
    .catch(() => {
      // catch error
    });
};

const handleSelectionChange = (val) => {
  multipleSelection.value = val;
};

onMounted(() => {
  getItems();
});
</script>
