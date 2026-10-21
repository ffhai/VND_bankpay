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
          <el-input v-model="searchForm.name" placeholder="搜索名称" />
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
        <el-table-column prop="type" label="角色标识" />
        <el-table-column prop="name" label="角色名称" />
        <el-table-column prop="remark" label="备注" />
        <el-table-column prop="create_time" label="创建时间" width="160" />
        <el-table-column label="状态" width="80" align="center">
          <template #default="scope">
            <el-tag v-if="scope.row.status === 1" disable-transitions>正常</el-tag>
            <el-tag v-if="scope.row.status === -1" disable-transitions type="info">禁用</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="150" fixed="right">
          <template #default="scope">
            <el-button type="primary" size="small" @click="onItemEdit(scope.row)">编辑</el-button>
            <el-button type="warning" size="small" @click="onItemPerms(scope.row)">权限</el-button>
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

    <el-dialog title="权限" v-model="permsVisible">
      <p class="permission-hint">
        目录仅用于组织；页面、操作和 API 独立授权，勾选页面不会自动授予操作/API 权限。
      </p>
      <el-input
        v-model="permsFilter"
        clearable
        placeholder="搜索名称、权限标识或路由"
        style="margin-bottom: 12px"
      />
      <el-form :model="permsForm">
        <el-tree
          ref="treeRef"
          :data="treeData"
          :props="treeProps"
          node-key="id"
          show-checkbox
          check-strictly
          highlight-current
          :filter-node-method="filterNode"
          :expand-on-click-node="false"
        >
          <template #default="{ data }">
            <el-icon v-if="data.node_type === 'group'">
              <House />
            </el-icon>
            {{ data.name }}
            <el-tag size="small" effect="plain" :type="nodeTypeTag(data.node_type)">
              {{ nodeTypeLabel(data.node_type) }}
            </el-tag>
            <el-tag v-if="data.route_path" size="small" type="success" effect="plain">
              {{ data.route_path }}
            </el-tag>
            <el-tag v-if="data.permission_key" size="small" type="primary" effect="plain">
              {{ data.permission_key }}
            </el-tag>
            <el-tag
              v-if="data.module && data.controller && data.action"
              size="small"
              type="warning"
              effect="plain"
            >
              {{ data.module }}/{{ data.controller }}/{{ data.action }}
            </el-tag>
            <el-tag v-if="data.status !== 1" type="info" size="small">禁用</el-tag>
          </template>
        </el-tree>
      </el-form>
      <template #footer>
        <el-button @click="permsVisible = false">取 消</el-button>
        <el-button type="primary" @click="onPerms" v-loading="loading">确 定</el-button>
      </template>
    </el-dialog>
  </div>
</template>
<script setup>
import { computed, ref, onMounted, nextTick, watch } from "vue";
import { House } from "@element-plus/icons-vue";
import roles from "@/api/admin/roles";
import perms from "@/api/admin/perms";
import rolePerms from "@/api/admin/rolePerms";
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
  fetchList: (params) => roles.getItems(params),
  deleteApi: (data) => roles.del(data),
  initialSearch: { name: "", time: "" },
});

const dialogVisible = ref(false);
const mode = ref("add");
const form = ref({});
const dialogTitle = computed(() => (mode.value === "add" ? "添加" : "编辑"));

// 添加与编辑共用同一份字段配置；disabledInEdit 的字段在编辑时不可修改
const formFields = [
  { prop: "type", label: "角色标识", placeholder: "请输入角色标识" },
  { prop: "name", label: "角色名称", placeholder: "请输入角色名称" },
  { prop: "remark", label: "备注", placeholder: "可空" },
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
  form.value = { type: "", name: "", remark: "", status: 1 };
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
      await roles.add(data);
      ElMessage.success("添加成功");
    } else {
      await roles.edit(data);
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
//权限设置

const permsVisible = ref(false);
const permsForm = ref({
  type: "",
  perms_ids: [],
});
const permsFilter = ref("");
const treeData = ref([]);
const treeProps = {
  children: "children",
  label: "name",
  value: "id",
  disabled: "disabled",
};
const treeRef = ref(null);
const filterNode = (value, data) => {
  if (!value) return true;
  const keyword = value.toLowerCase();
  return [
    data.name,
    data.permission_key,
    data.route_path,
    data.module,
    data.controller,
    data.action,
  ]
    .filter(Boolean)
    .some((field) => String(field).toLowerCase().includes(keyword));
};
const onItemPerms = (item) => {
  permsForm.value.type = item.type;
  permsForm.value.perms_ids = item.perms_ids || [];
  permsFilter.value = "";
  permsVisible.value = true;
  nextTick(() => {
    if (treeRef.value) {
      treeRef.value.setCheckedKeys([]);
      treeRef.value.setCheckedKeys(permsForm.value.perms_ids);
    }
  });
};
const getTreeItems = async () => {
  const data = await perms.getItems();
  const markDisabled = (nodes) =>
    nodes.map((node) => ({
      ...node,
      disabled: node.node_type === "group" || Number(node.status) !== 1,
      children: markDisabled(node.children || []),
    }));
  treeData.value = markDisabled(data);
};
const onPerms = async () => {
  const data = treeRef.value.getCheckedKeys();
  permsForm.value.perms_ids = data;

  loading.value = true;
  try {
    await rolePerms.save(permsForm.value);
    ElMessage.success("编辑成功");
    permsVisible.value = false;
    await getItems();
  } catch (error) {
    console.error("保存角色权限失败:", error);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  try {
    await getTreeItems();
  } catch (error) {
    console.error("获取权限节点失败:", error);
  }
});

watch(permsFilter, (value) => treeRef.value?.filter(value));

const nodeTypeLabels = {
  group: "目录",
  page: "页面",
  action: "操作权限",
  api: "API 权限",
};
const nodeTypeLabel = (type) => nodeTypeLabels[type] || "未知类型";
const nodeTypeTag = (type) =>
  ({ group: "info", page: "success", action: "warning", api: "primary" })[type] || "info";
</script>
