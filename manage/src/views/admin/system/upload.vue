<template>
  <div class="dashboard-container">
    <div class="search-block">
      <el-form :model="searchForm" :inline="true" class="search-form" @submit.prevent="onSearch">
        <el-form-item class="search-field"
          ><el-input v-model="searchForm.keyword" placeholder="搜索文件名" clearable
        /></el-form-item>
        <el-form-item class="search-field">
          <el-select v-model="searchForm.type" placeholder="全部类型" clearable>
            <el-option label="图片" value="image" /><el-option label="文件" value="file" />
          </el-select>
        </el-form-item>
        <el-form-item class="search-field search-field--date">
          <el-date-picker
            v-model="searchForm.time"
            type="datetimerange"
            value-format="X"
            start-placeholder="开始时间"
            end-placeholder="结束时间"
          />
        </el-form-item>
        <el-form-item class="search-actions">
          <el-button type="primary" @click="onSearch">查询</el-button>
          <InputUpload @input="onUploaded" />
          <el-button type="danger" @click="onDelete">删除选中</el-button>
        </el-form-item>
      </el-form>
    </div>
    <div class="data-list">
      <el-table
        v-loading="loading"
        :data="items"
        empty-text="暂无上传文件"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="48" />
        <el-table-column prop="id" label="ID" width="75" />
        <el-table-column label="预览" width="110">
          <template #default="{ row }">
            <el-image
              v-if="row.type === 'image'"
              :src="row.url"
              :preview-src-list="[row.url]"
              preview-teleported
              fit="contain"
              class="file-preview"
              lazy
            >
              <template #error><span>图片不可用</span></template>
            </el-image>
            <el-tag v-else type="info">文件</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="name" label="文件名" min-width="180" show-overflow-tooltip />
        <el-table-column label="大小" width="110"
          ><template #default="{ row }">{{ formatSize(row.size) }}</template></el-table-column
        >
        <el-table-column prop="url" label="网址" min-width="220" show-overflow-tooltip />
        <el-table-column prop="time" label="上传时间" width="165" />
        <el-table-column label="操作" width="235" fixed="right">
          <template #default="{ row }">
            <el-button size="small" @click="copyUrl(row.url)">复制网址</el-button>
            <el-button
              size="small"
              tag="a"
              :href="row.url"
              target="_blank"
              rel="noopener noreferrer"
              >打开</el-button
            >
            <el-button size="small" type="danger" @click="deleteItem(row)">删除</el-button>
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
    <el-dialog v-model="copyVisible" title="复制文件网址" width="min(560px, 90vw)">
      <el-input :model-value="copyValue" readonly @focus="($event) => $event.target.select()" />
      <p>请选中网址后复制。</p>
    </el-dialog>
  </div>
</template>
<script setup>
import { ref } from "vue";
import { ElMessage, ElMessageBox } from "element-plus";
import upload from "@/api/admin/upload";
import InputUpload from "@/components/Upload/InputUpload.vue";
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
  fetchList: (params) => upload.getItems(params),
  deleteApi: (data) => upload.del(data),
  initialSearch: { keyword: "", type: "", time: "" },
  confirmMessage: "将永久删除选中的文件和附件记录，引用这些文件的页面可能无法显示，是否继续？",
});
const copyVisible = ref(false);
const copyValue = ref("");
const copyUrl = async (url) => {
  try {
    await navigator.clipboard.writeText(url);
    ElMessage.success("网址已复制");
  } catch {
    copyValue.value = url;
    copyVisible.value = true;
  }
};
const formatSize = (size) => {
  if (size < 1024) return `${size} B`;
  if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
  return `${(size / 1024 / 1024).toFixed(2)} MB`;
};
const onUploaded = () => {
  ElMessage.success("上传成功");
  onSearch();
};
const deleteItem = async (row) => {
  try {
    await ElMessageBox.confirm(
      `确定永久删除“${row.name}”？引用此文件的页面可能无法显示。`,
      "删除文件",
      { type: "warning", confirmButtonText: "删除", cancelButtonText: "取消" }
    );
    await upload.del({ ids: [row.id] });
    ElMessage.success("删除成功");
    await getItems();
  } catch (error) {
    if (error !== "cancel" && error !== "close") console.error("删除失败", error);
  }
};
</script>
<style scoped>
.file-preview {
  width: 72px;
  height: 56px;
}
.search-actions :deep(.el-upload) {
  margin: 0 12px;
}
</style>
