<template>
  <div class="dashboard-container">
    <div class="search-block">
      <el-form class="search-form" :inline="true" @submit.prevent="onSearch" :model="searchForm">
        <el-form-item class="search-field">
          <el-select v-model="searchForm.user_id" placeholder="选择用户" clearable filterable>
            <el-option
              v-for="item in userOptions"
              :key="item.id"
              :label="item.username"
              :value="item.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item class="search-field">
          <el-select v-model="searchForm.type" placeholder="交易类型" clearable>
            <el-option v-for="t in typeOptions" :key="t.value" :label="t.label" :value="t.value" />
          </el-select>
        </el-form-item>
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
        <el-form-item class="search-actions">
          <el-button type="primary" @click="onSearch">查询</el-button>
        </el-form-item>
      </el-form>
    </div>

    <div class="data-list">
      <el-table v-loading="loading" :data="items" style="width: 100%" empty-text="暂无数据">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="用户" width="120" />
        <el-table-column label="变动金额(元)" width="140" align="right">
          <template #default="scope">
            <span :class="scope.row.change_amount > 0 ? 'amount-in' : 'amount-out'">
              {{ formatFen(scope.row.change_amount) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column label="变动后余额(元)" width="140" align="right">
          <template #default="scope">
            {{ formatFen(scope.row.balance_after) }}
          </template>
        </el-table-column>
        <el-table-column prop="type_name" label="类型" width="100" />
        <el-table-column prop="remark" label="备注" />
        <el-table-column prop="operator" label="操作员" width="120" />
        <el-table-column prop="create_time" label="时间" width="160" />
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
import { ref, onMounted } from "vue";
import balance from "@/api/admin/balance";
import member from "@/api/admin/member";
import CrudPagination from "@/components/CrudPagination/index.vue";
import { useTableList } from "@/composables/useTableList";
import { useBalanceTypes } from "@/composables/useBalanceTypes";
import { formatFen } from "@/utils/format";

const { loading, items, total, searchForm, onSearch, onCurrentChange, onSizeChange } = useTableList(
  {
    fetchList: (params) => balance.getItems(params),
    initialSearch: { user_id: "", type: "", time: "" },
  }
);

const userOptions = ref([]);
// 交易类型来自后端静态配置，见 composables/useBalanceTypes.js
const { typeOptions } = useBalanceTypes();

const getUsers = async () => {
  try {
    userOptions.value = await member.getOptions();
  } catch (error) {
    console.error("获取用户失败:", error);
  }
};

onMounted(() => {
  getUsers();
});
</script>

<style scoped>
.amount-in {
  color: #67c23a;
}
.amount-out {
  color: #f56c6c;
}
</style>
