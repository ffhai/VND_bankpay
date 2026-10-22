<template>
  <div class="dashboard-container">
    <div class="search-block">
      <el-form class="search-form" :inline="true" :model="searchForm" @submit.prevent="onSearch">
        <el-form-item class="search-field"
          ><el-input v-model="searchForm.notify_id" placeholder="搜索设备推送ID"
        /></el-form-item>
        <el-form-item class="search-field"
          ><el-input v-model="searchForm.bank_number" placeholder="搜索银行卡号"
        /></el-form-item>
        <el-form-item class="search-field"
          ><el-input v-model="searchForm.order_id" placeholder="搜索关联订单"
        /></el-form-item>
        <el-form-item class="search-field"
          ><el-input v-model="searchForm.remark" placeholder="搜索附言"
        /></el-form-item>
        <el-form-item class="search-field">
          <el-select v-model="searchForm.status" placeholder="匹配状态" clearable>
            <el-option label="匹配成功" :value="1" />
            <el-option label="匹配失败" :value="2" />
          </el-select>
        </el-form-item>
        <el-form-item class="search-actions">
          <el-button type="primary" :loading="loading" @click="onSearch">查询</el-button>
          <el-button :disabled="loading" @click="onReset">重置</el-button>
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
        <el-table-column type="selection" width="55" />
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="notify_id" label="设备推送ID" width="120" />
        <el-table-column prop="bank_number" label="银行卡号" min-width="170" />
        <el-table-column prop="balance" label="余额" width="140" align="right"
          ><template #default="scope">{{
            formatAmount(scope.row.balance)
          }}</template></el-table-column
        >
        <el-table-column prop="change" label="变动" width="140" align="right"
          ><template #default="scope"
            ><span
              :class="scope.row.change > 0 ? 'amount-in' : scope.row.change < 0 ? 'amount-out' : ''"
              >{{ formatAmount(scope.row.change) }}</span
            ></template
          ></el-table-column
        >
        <el-table-column prop="remark" label="附言" min-width="160" show-overflow-tooltip />
        <el-table-column prop="order_id" label="关联订单" min-width="200" />
        <el-table-column prop="status" label="状态" width="100" align="center">
          <template #default="scope">
            <el-tag v-if="scope.row.status === 1" type="success" disable-transitions
              >匹配成功</el-tag
            >
            <el-tag v-if="scope.row.status === 2" type="danger" disable-transitions
              >匹配失败</el-tag
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
import { useTableList } from "@/composables/useTableList";
import CrudPagination from "@/components/CrudPagination/index.vue";
import api from "@/api/admin/smsTrade";
import { formatAmount } from "@/utils/format";

const {
  loading,
  deleting,
  items,
  total,
  searchForm,
  onSearch,
  onReset,
  onCurrentChange,
  onSizeChange,
  handleSelectionChange,
  onDelete,
} = useTableList({
  fetchList: (params) => api.getItems(params),
  deleteApi: (data) => api.del(data),
  initialSearch: { notify_id: "", bank_number: "", order_id: "", remark: "", status: "" },
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
