<template>
  <div class="dashboard">
    <el-tabs
      v-model="activeName"
      type="card"
      style="background: #fff; padding: 20px; border-radius: 4px"
    >
      <el-tab-pane v-for="item in items" :key="item.key" :label="item.name" :name="item.key">
        <SimpleEditor v-model="item.content" />
      </el-tab-pane>
    </el-tabs>
    <el-button type="primary" style="margin: 20px 0" @click="save">保存</el-button>
  </div>
</template>
<script setup>
import { defineAsyncComponent, ref, onMounted } from "vue";
import editor from "@/api/admin/editor";

// TinyMCE 体积较大（静态资源约 4.5MB），改为进入页面时才加载
const SimpleEditor = defineAsyncComponent(() => import("@/components/Tinymce/Simple.vue"));

const items = ref([]);
const activeName = ref("");

onMounted(() => {
  editor.getItems().then((response) => {
    items.value = response;
    if (items.value.length > 0) {
      activeName.value = items.value[0].key;
    }
  });
});
const save = async () => {
  try {
    await editor.postItems(items.value);
    ElMessage.success("保存成功");
  } catch (error) {
    console.error("保存失败:", error);
    ElMessage.error("保存失败，请稍后重试");
  }
};
</script>
