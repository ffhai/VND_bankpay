<template>
  <template v-if="route.hidden === false">
    <!-- 判断是否有可见子路由 -->
    <el-sub-menu v-if="hasVisibleChildren" :index="route.path">
      <template #title>
        <el-icon v-if="route.meta?.icon">
          <component :is="menuIcons[route.meta.icon]" />
        </el-icon>
        <span>{{ route.meta?.title || route.path }}</span>
      </template>
      <!-- 递归渲染子菜单 -->
      <MenuItem v-for="child in visibleChildren" :key="child.path" :route="child" />
    </el-sub-menu>

    <!-- 没有子路由时，渲染为 el-menu-item -->
    <el-menu-item v-else-if="route.node_type !== 'group'" :index="route.path">
      <el-icon v-if="route.meta?.icon">
        <component :is="menuIcons[route.meta.icon]" />
      </el-icon>
      <template #title>
        <span>{{ route.meta?.title || route.path }}</span>
      </template>
    </el-menu-item>
  </template>
</template>

<script setup>
import { computed } from "vue";
import { menuIcons } from "@/utils/menuIcons";

const props = defineProps({
  route: {
    type: Object,
    required: true,
  },
});

const visibleChildren = computed(() => {
  if (!props.route.children) return [];
  return props.route.children.filter((child) => child.hidden === false);
});

const hasVisibleChildren = computed(() => {
  return visibleChildren.value.length > 0;
});
</script>
