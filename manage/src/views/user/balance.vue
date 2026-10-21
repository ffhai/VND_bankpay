<template>
  <div class="dashboard-container">
    <div class="balance-card">
      <span class="balance-label">我的余额</span>
      <span class="balance-value">￥{{ formatFen(balance) }}</span>
    </div>

    <div class="search-block">
      <el-form class="search-form" :inline="true" @submit.prevent="onSearch" :model="searchForm">
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
      :page-sizes="pageSizes"
      :total="total"
      @size-change="onSizeChange"
      @current-change="onCurrentChange"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import balanceApi from "@/api/user/balance";
import CrudPagination from "@/components/CrudPagination/index.vue";
import { useTableList } from "@/composables/useTableList";
import { useBalanceTypes } from "@/composables/useBalanceTypes";
import { formatFen } from "@/utils/format";

// 该页分页选项比后台列表略少
const pageSizes = [20, 50, 100, 200];

const { loading, items, total, searchForm, onSearch, onCurrentChange, onSizeChange } = useTableList(
  {
    fetchList: (params) => balanceApi.getItems(params),
    initialSearch: { type: "", time: "" },
  }
);

const balance = ref(0);
// 交易类型来自后端静态配置，见 composables/useBalanceTypes.js
const { typeOptions } = useBalanceTypes("user");

const getBalance = async () => {
  try {
    const response = await balanceApi.getInfo();
    balance.value = response.balance;
  } catch (error) {
    console.error("获取余额失败:", error);
  }
};

onMounted(() => {
  getBalance();
});
</script>

<style scoped>
.balance-card {
  display: flex;
  align-items: baseline;
  gap: 12px;
  padding: 16px 20px;
  margin-bottom: 16px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 8px;
  color: #fff;
}

.balance-label {
  font-size: 14px;
  opacity: 0.85;
}

.balance-value {
  font-size: 26px;
  font-weight: 600;
}

.amount-in {
  color: #67c23a;
}

.amount-out {
  color: #f56c6c;
}
</style>
