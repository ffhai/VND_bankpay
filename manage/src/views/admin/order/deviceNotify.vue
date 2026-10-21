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
          <el-input v-model="searchForm.username" placeholder="搜索账号" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.order_id" placeholder="搜索订单号" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.card_number" placeholder="搜索卡号" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.remark" placeholder="搜索附言" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-select v-model="searchForm.status" placeholder="搜索状态">
            <el-option label="全部" value="" />
            <el-option label="未处理" value="0" />
            <el-option label="匹配成功" value="1" />
            <el-option label="匹配失败" value="2" />
            <el-option label="其他" value="-1" />
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
        <el-table-column prop="user.username" label="账号" width="150" />
        <el-table-column prop="type" label="Type" width="120px" />

        <el-table-column label="推送原文">
          <template #default="scope">
            <div style="word-break: break-all">
              {{ scope.row.content }}
            </div>
          </template>
        </el-table-column>
        <el-table-column label="匹配" width="260">
          <template #default="scope">
            <div v-if="scope.row.type == 'BANK_TRADE'">
              <div style="word-break: break-all">卡号：{{ scope.row.card_number }}</div>
              <div style="word-break: break-all">
                余额：{{ scope.row.balance.toLocaleString("zh-CN") }}
              </div>
              <div style="word-break: break-all">附言：{{ scope.row.remark }}</div>
            </div>
            <div v-if="scope.row.type == 'SMS_CODE'">
              <div style="word-break: break-all">手机号码：{{ scope.row.phone_number }}</div>
              <div style="word-break: break-all">验证码：{{ scope.row.sms_code }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="time" label="时间" width="160" />
        <el-table-column label="状态" width="80" align="center">
          <template #default="scope">
            <el-tag v-if="scope.row.status === -1" disable-transitions type="warning">其他</el-tag>
            <el-tag v-if="scope.row.status === 0" disable-transitions type="info">未匹配</el-tag>
            <el-tag v-if="scope.row.status === 1" disable-transitions>匹配成功</el-tag>
            <el-tag v-if="scope.row.status === 2" disable-transitions type="info">匹配失败</el-tag>
          </template>
        </el-table-column>
        <el-table-column label="操作" width="80" fixed="right">
          <template #default="scope">
            <el-button type="primary" size="small" @click="onItemReset(scope.row)"
              >重新匹配</el-button
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
  </div>
</template>
<script setup>
import CrudPagination from "@/components/CrudPagination/index.vue";
import { ref, onMounted } from "vue";
import api from "@/api/admin/deviceNotify";

const loading = ref(false);

const searchForm = ref({
  order_id: "",
  username: "",
  time: "",
  status: "",
  page: 1,
  pagesize: 20,
});
const total = ref(0);
const items = ref([]);

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
const onItemReset = (item) => {
  ElMessageBox.confirm("此操作将重新匹配原文, 是否继续?", "提示", {
    confirmButtonText: "确定",
    cancelButtonText: "取消",
    type: "warning",
  })
    .then(async () => {
      const loadingInstance = ElLoading.service({ fullscreen: true });
      try {
        await api.reset({ id: item.id });
        ElMessage.success("操作成功");
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
