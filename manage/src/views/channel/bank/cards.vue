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
          <el-input v-model="searchForm.keyword" placeholder="搜索卡号" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-select v-model="searchForm.bank_code" placeholder="搜索银行">
            <el-option label="全部" value="" />
            <el-option
              v-for="(val, key) in bankList"
              :key="key"
              :label="val.short_name"
              :value="val.code"
            />
          </el-select>
        </el-form-item>

        <el-form-item class="search-field">
          <el-select v-model="searchForm.bank_user_id" placeholder="搜索户名">
            <el-option label="全部" value="" />
            <el-option
              v-for="(val, key) in bankUserList"
              :key="key"
              :label="val.name"
              :value="val.id"
            />
          </el-select>
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
          <el-button @click="onItemAdd">新增</el-button>
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
        <el-table-column label="银行">
          <template #default="scope">
            <div>{{ scope.row.bankinfo?.code }}</div>
            <div>{{ scope.row.bank_number }}</div>
          </template>
        </el-table-column>
        <el-table-column label="户名" width="180">
          <template #default="scope">
            <div>{{ scope.row.bankuser?.name }}</div>
            <div>{{ scope.row.bankuser?.idcard }}</div>
          </template>
        </el-table-column>
        <el-table-column label="余额" width="120">
          <template #default="scope">
            <div>{{ scope.row.balance.toLocaleString("zh-CN") }}</div>
          </template>
        </el-table-column>
        <el-table-column label="最大限额" width="180">
          <template #default="scope">
            <div>入款:{{ scope.row.limit_in_max.toLocaleString("zh-CN") }}</div>
            <div>出款:{{ scope.row.limit_out_max.toLocaleString("zh-CN") }}</div>
          </template>
        </el-table-column>
        <el-table-column label="网银" width="180">
          <template #default="scope">
            <div>
              <el-text v-if="scope.row.online_account && scope.row.online_password">{{
                scope.row.online_account
              }}</el-text>
              <el-text v-if="!scope.row.online_account" type="danger">未设置</el-text>
            </div>
            <div>手机:{{ scope.row.phone_number || "未设置" }}</div>
          </template>
        </el-table-column>
        <el-table-column label="回调">
          <template #default="scope">
            <div v-if="scope.row.notify_type == 'online'">网银</div>
            <div v-if="scope.row.notify_type == 'app'">App</div>
            <div>{{ scope.row.notify_device }}</div>
          </template>
        </el-table-column>
        <el-table-column label="流水" width="120">
          <template #default="scope">
            <div>{{ scope.row.amount.toLocaleString("zh-CN") }}</div>
          </template>
        </el-table-column>
        <el-table-column label="时间" width="200">
          <template #default="scope">
            <div>更新:{{ scope.row.update_time }}</div>
            <div>调用:{{ scope.row.last_time }}</div>
          </template>
        </el-table-column>
        <el-table-column prop="remark" label="备注" />
        <el-table-column label="状态" width="80" align="center">
          <template #default="scope">
            <el-tag v-if="scope.row.status === 1" disable-transitions>正常</el-tag>
            <el-tag v-if="scope.row.status === -1" disable-transitions type="info">禁用</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="操作" width="130" fixed="right">
          <template #default="scope">
            <el-button type="primary" size="small" @click="onItemEdit(scope.row)">编辑</el-button>
            <el-button type="success" size="small" @click="onTestVietqr(scope.row)">Qr</el-button>
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
      :title="dialogMode === 'add' ? '新增银行卡' : '编辑银行卡'"
      :mode="dialogMode"
      :model="dialogMode === 'add' ? addForm : editForm"
      :fields="formFields"
      :loading="loading"
      :gutter="20"
      width="min(800px, 94vw)"
      @submit="dialogMode === 'add' ? onAdd($event) : onEdit($event)"
    />

    <TestVietqrDialog v-model="vietqrVisible" :card="vietqrCard" :api="api" />
  </div>
</template>
<script setup>
import FormDialog from "@/components/FormDialog/index.vue";
import CrudPagination from "@/components/CrudPagination/index.vue";
import { ref, onMounted } from "vue";
import TestVietqrDialog from "@/components/TestVietqrDialog/index.vue";
import api from "@/api/channel/bankCards";
import banks from "@/api/channel/banks";
import bankUser from "@/api/channel/bankUser";

const vietqrVisible = ref(false);
const vietqrCard = ref(null);
const onTestVietqr = (card) => {
  vietqrCard.value = card;
  vietqrVisible.value = true;
};
const loading = ref(false);
const dialogVisible = ref(false);
const dialogMode = ref("add");
const formFields = [
  {
    prop: "bank_code",
    label: "银行",
    placeholder: "所属银行",
    span: 12,
    type: "select",
    options: () => bankList.value.map((item) => ({ label: item.short_name, value: item.code })),
    props: { filterable: true },
  },
  { prop: "bank_number", label: "卡号", placeholder: "请输入卡号", span: 12 },
  {
    prop: "bank_user_id",
    label: "户名",
    placeholder: "所属户名",
    span: 12,
    type: "select",
    options: () => bankUserList.value.map((item) => ({ label: item.name, value: item.id })),
    props: { filterable: true },
  },
  {
    prop: "limit_in_max",
    label: "入款限额",
    placeholder: "请输入款金额限制,0为不限制",
    span: 12,
    type: "number",
    props: { precision: 0, step: 1, min: 0 },
  },
  {
    prop: "limit_out_max",
    label: "出款限额",
    placeholder: "请输出款金额限制,0为不限制",
    span: 12,
    type: "number",
    props: { precision: 0, step: 1, min: 0 },
  },
  { prop: "online_account", label: "网银账号", placeholder: "请输入网银账号", span: 12 },
  { prop: "online_password", label: "网银密码", placeholder: "请输入网银密码", span: 12 },
  { prop: "phone_number", label: "手机号码", placeholder: "请输入手机号码", span: 12 },
  {
    prop: "notify_type",
    label: "回调方式",
    placeholder: "请选择",
    span: 12,
    type: "select",
    options: [
      { label: "网银", value: "online" },
      { label: "App", value: "app" },
    ],
  },
  { prop: "notify_device", label: "回调设备", placeholder: "", span: 12 },
  {
    prop: "balance",
    label: "账户余额",
    placeholder: "请输入账户余额",
    span: 12,
    type: "number",
    props: { precision: 0, step: 1, min: 0 },
  },
  {
    prop: "status",
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
    prop: "remark",
    label: "备注",
    placeholder: "请输入备注",
    span: 24,
    type: "textarea",
    props: { rows: 2 },
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
const bankList = ref([]);
const bankUserList = ref([]);

const addForm = ref({
  bank_number: "",
  bank_code: "",
  bank_user_id: "",
  balance: 0,
  limit_in_max: 0,
  limit_out_max: 0,
  online_account: "",
  online_password: "",
  phone_number: "",
  notify_type: "",
  notify_device: "",
  remark: "",
  status: -1,
});

const editForm = ref({
  id: "",
  bank_number: "",
  bank_code: "",
  bank_user_id: "",
  balance: 0,
  limit_in_max: 0,
  limit_out_max: 0,
  online_account: "",
  online_password: "",
  phone_number: "",
  notify_type: "",
  notify_device: "",
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

const onItemAdd = () => {
  dialogMode.value = "add";
  dialogVisible.value = true;
};
const onAdd = async (data) => {
  loading.value = true;
  try {
    await api.add(data);
    dialogVisible.value = false;
    ElMessage.success("添加成功");
    getItems();
  } catch (error) {
    console.error("获取列表失败:", error);
  } finally {
    loading.value = false;
  }
};
const onItemEdit = (item) => {
  editForm.value = { ...item };
  dialogMode.value = "edit";
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
const loadBankUserList = async () => {
  try {
    const response = await bankUser.getOptions();
    bankUserList.value = response;
  } catch (error) {
    console.error("获取银行用户列表失败:", error);
  }
};
const loadBankList = async () => {
  try {
    const response = await banks.getOptions();
    bankList.value = response;
  } catch (error) {
    console.error("获取银行列表失败:", error);
  }
};
const handleSelectionChange = (val) => {
  multipleSelection.value = val;
};

onMounted(() => {
  getItems();
  loadBankUserList();
  loadBankList();
});
</script>
