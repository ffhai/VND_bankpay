<template>
  <div id="app">
    <el-config-provider :locale="zhCn">
      <RouterView />
    </el-config-provider>
  </div>
</template>

<script setup>
import { onMounted, provide } from "vue";
import { RouterView } from "vue-router";
import { ElConfigProvider } from "element-plus";

import zhCn from "element-plus/es/locale/lang/zh-cn";

// 组件名称（在 Vue DevTools 中显示）
defineOptions({
  name: "App",
});

// 生命周期钩子
onMounted(() => {});

// 提供全局数据（跨组件共享）
provide("appVersion", "1.0.0");
</script>

<style scoped>
/* scoped 表示样式只在此组件生效 */
#app {
  /* min-height: 100vh; */
  /* background-color: #f5f5f5; */
}
</style>
