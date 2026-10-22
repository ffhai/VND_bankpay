<template>
  <div class="dashboard-container">
    <div class="search-block">
      <el-form class="search-form" :inline="true" :model="searchForm" @submit.prevent="onSearch">
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
          <el-input v-model="searchForm.notify_id" placeholder="搜索设备推送ID" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.phone_number" placeholder="搜索手机号" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.verify_code" placeholder="搜索验证码" />
        </el-form-item>
        <el-form-item class="search-actions">
          <el-button type="primary" :loading="loading" @click="onSearch">查询</el-button>
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
        <el-table-column prop="notify_id" label="设备推送ID" width="140" />
        <el-table-column prop="phone_number" label="手机号" min-width="180" />
        <el-table-column prop="verify_code" label="验证码" min-width="160" />
        <el-table-column prop="time" label="时间" min-width="180" />
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
import api from "@/api/admin/smsCode";

const {
  loading,
  deleting,
  items,
  total,
  searchForm,
  onSearch,
  onCurrentChange,
  onSizeChange,
  handleSelectionChange,
  onDelete,
} = useTableList({
  fetchList: (params) => api.getItems(params),
  deleteApi: (data) => api.del(data),
  initialSearch: { notify_id: "", phone_number: "", verify_code: "", time: "" },
});
</script>
