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
          <el-input v-model="searchForm.username" placeholder="搜索账号" />
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
        <el-table-column prop="role_name" label="角色" width="80" />
        <el-table-column prop="username" label="账号" />
        <el-table-column label="余额(元)" width="120" align="right">
          <template #default="scope">
            {{ formatFen(scope.row.balance) }}
          </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="状态" width="80" align="center">
          <template #default="scope">
            <el-tag v-if="scope.row.status === 1" disable-transitions>正常</el-tag>
            <el-tag v-if="scope.row.status === -1" disable-transitions type="info">禁用</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="180" fixed="right">
          <template #default="scope">
            <el-button type="primary" size="small" @click="onItemEdit(scope.row)">编辑</el-button>
            <el-button type="warning" size="small" @click="onItemAdjust(scope.row)"
              >调整余额</el-button
            >
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

    <el-dialog title="调整余额" v-model="adjustVisible">
      <el-form ref="adjustFormRef" :model="adjustForm" :rules="adjustRules">
        <el-form-item label="账号" label-width="80px">
          <el-input :model-value="adjustForm.username" disabled />
        </el-form-item>
        <el-form-item label="变动金额" label-width="80px" prop="amount">
          <el-input-number
            v-model="adjustForm.amount"
            :precision="2"
            :min="-1000000"
            :max="1000000"
            :step="1"
            style="width: 100%"
            placeholder="正数增加，负数扣减（单位：元）"
          />
        </el-form-item>
        <el-form-item label="交易类型" label-width="80px">
          <el-select v-model="adjustForm.type" placeholder="选择类型" style="width: 100%">
            <el-option v-for="t in typeOptions" :key="t.value" :label="t.label" :value="t.value" />
          </el-select>
        </el-form-item>
        <el-form-item label="备注" label-width="80px">
          <el-input v-model="adjustForm.remark" type="textarea" placeholder="备注" />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="adjustVisible = false">取 消</el-button>
        <el-button type="primary" @click="onAdjust" v-loading="loading">确 定</el-button>
      </template>
    </el-dialog>
  </div>
</template>
<script setup>
import { computed, ref, onMounted } from "vue";
import member from "@/api/admin/member";
import rolesApi from "@/api/admin/roles";
import FormDialog from "@/components/FormDialog/index.vue";
import CrudPagination from "@/components/CrudPagination/index.vue";
import { useTableList } from "@/composables/useTableList";
import { useBalanceTypes } from "@/composables/useBalanceTypes";
import { formatFen } from "@/utils/format";

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
  fetchList: (params) => member.getItems(params),
  deleteApi: (data) => member.del(data),
  initialSearch: { keyword: "", time: "" },
});

const dialogVisible = ref(false);
const mode = ref("add");
const form = ref({});
const dialogTitle = computed(() => (mode.value === "add" ? "添加" : "编辑"));

const roles = ref([]);
// 交易类型来自后端静态配置，见 composables/useBalanceTypes.js
const { typeOptions } = useBalanceTypes();
const roleOptions = computed(() =>
  roles.value.map((item) => ({ label: item.name, value: item.type }))
);

// 添加与编辑共用同一份字段配置；disabledInEdit 的字段在编辑时不可修改
const formFields = computed(() => [
  {
    prop: "type",
    label: "角色",
    type: "select",
    placeholder: "选择角色",
    options: roleOptions.value,
  },
  { prop: "username", label: "账号名称", placeholder: "请输入账号名称" },
  { prop: "password", label: "账号密码", placeholder: "密码长度不可小于6个字符" },
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
]);

const adjustVisible = ref(false);
const adjustFormRef = ref(null);
const adjustForm = ref({
  user_id: null,
  username: "",
  amount: null,
  type: "adjust",
  remark: "",
});
const adjustRules = {
  amount: [{ required: true, message: "请输入变动金额", trigger: "blur" }],
};

const getRoles = async () => {
  try {
    const response = await rolesApi.getOptions();
    roles.value = response;
  } catch (error) {
    console.error("获取列表失败:", error);
  }
};

const onAdd = () => {
  mode.value = "add";
  form.value = { type: "", username: "", password: "", status: 1 };
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
      await member.add(data);
      ElMessage.success("添加成功");
    } else {
      await member.edit(data);
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

const onItemAdjust = (item) => {
  adjustForm.value = {
    user_id: item.id,
    username: item.username,
    amount: null,
    type: "adjust",
    remark: "",
  };
  adjustVisible.value = true;
};

const onAdjust = async () => {
  const valid = await adjustFormRef.value.validate().catch(() => false);
  if (!valid) return;

  // 元 -> 分（整数）
  const changeAmount = Math.round(Number(adjustForm.value.amount) * 100);
  if (changeAmount === 0) {
    ElMessage.warning("变动金额不能为0");
    return;
  }

  loading.value = true;
  try {
    await member.adjust({
      user_id: adjustForm.value.user_id,
      change_amount: changeAmount,
      type: adjustForm.value.type,
      remark: adjustForm.value.remark,
    });
    ElMessage.success("调整成功");
    adjustVisible.value = false;
    getItems();
  } catch (error) {
    console.error("调整失败:", error);
  } finally {
    loading.value = false;
  }
};
onMounted(() => {
  getRoles();
});
</script>
