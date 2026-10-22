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
          <el-input v-model="searchForm.phone_number" placeholder="搜索手机号" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.content" placeholder="搜索推送内容" />
        </el-form-item>
        <el-form-item class="search-field">
          <el-select v-model="searchForm.status" placeholder="搜索状态">
            <el-option label="全部" value="" />
            <el-option label="未处理" value="1" />
            <el-option label="已处理" value="2" />
            <el-option label="处理失败" value="-1" />
          </el-select>
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
        <el-table-column prop="user.username" label="账号" width="150" />
        <el-table-column prop="phone_number" label="手机号" width="150" />

        <el-table-column label="推送原文">
          <template #default="scope">
            <div style="word-break: break-all">
              {{ scope.row.content }}
            </div>
          </template>
        </el-table-column>
        <el-table-column prop="time" label="时间" width="160" />
        <el-table-column label="状态" width="100" align="center">
          <template #default="scope">
            <el-tag v-if="scope.row.status === 1" disable-transitions type="info">未处理</el-tag>
            <el-tag v-if="scope.row.status === 2" disable-transitions type="success">已处理</el-tag>
            <el-tag v-if="scope.row.status === -1" disable-transitions type="danger"
              >处理失败</el-tag
            >
          </template>
        </el-table-column>
        <el-table-column label="操作" width="110" fixed="right">
          <template #default="scope">
            <el-button
              type="primary"
              size="small"
              :loading="isRowActionPending('reset:' + scope.row.id)"
              @click="onItemReset(scope.row)"
              >重新处理</el-button
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
import api from "@/api/admin/deviceNotify";

const {
  loading,
  items,
  total,
  searchForm,
  getItems,
  onSearch,
  onCurrentChange,
  onSizeChange,
  runRowAction,
  isRowActionPending,
  handleSelectionChange,
  onDelete,
  deleting,
} = useTableList({
  fetchList: (params) => api.getItems(params),
  initialSearch: {
    username: "",
    time: "",
    status: "",

    phone_number: "",
    content: "",
  },
  deleteApi: (data) => api.del(data),
});

const onItemReset = (item) =>
  runRowAction("reset:" + item.id, () => {
    return ElMessageBox.confirm("此操作将状态重置为未处理，是否继续？", "提示", {
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
  });
</script>
