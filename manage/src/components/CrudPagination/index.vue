<template>
  <div class="page-block">
    <el-pagination
      :current-page="currentPage"
      :page-size="pageSize"
      :page-sizes="pageSizes"
      layout="total, sizes, prev, pager, next, jumper"
      :total="total"
      @size-change="emit('size-change', $event)"
      @current-change="emit('current-change', $event)"
    />
  </div>
</template>

<script setup>
defineProps({
  currentPage: { type: Number, default: 1 },
  pageSize: { type: Number, default: 20 },
  pageSizes: { type: Array, default: () => [20, 50, 100, 200, 300, 400] },
  total: { type: Number, default: 0 },
});

const emit = defineEmits(["size-change", "current-change"]);
</script>
