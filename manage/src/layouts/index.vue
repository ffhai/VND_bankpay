<!-- src/layouts/Layout.vue -->
<template>
  <el-container class="layout-container">
    <el-aside :width="isCollapse ? '72px' : '232px'" class="aside-container">
      <!-- Logo -->
      <div class="logo-wrapper">
        <img v-if="websiteInfo?.logo" :src="websiteInfo?.logo" alt="Logo" class="logo-img" />
        <span v-else class="logo-mark">P</span>
        <span v-show="!isCollapse" class="logo-text">{{
          websiteInfo?.site_name || "支付管理"
        }}</span>
      </div>

      <!-- 菜单 -->
      <el-menu
        :default-active="activeMenu"
        :default-openeds="defaultOpeneds"
        :collapse="isCollapse"
        :collapse-transition="false"
        background-color="#142c35"
        text-color="#a9bbc3"
        active-text-color="#70d7c0"
        router
        class="menu-container"
      >
        <MenuItem v-for="menuRoute in menuRoutes" :key="menuRoute.path" :route="menuRoute" />
      </el-menu>
    </el-aside>

    <!-- 右侧容器 -->
    <el-container class="workspace-container">
      <!-- 顶部栏 -->
      <el-header height="60px" class="header-container">
        <!-- 左侧：控制按钮 + 面包屑 -->
        <div class="header-left">
          <!-- 菜单折叠控制按钮 -->
          <el-tooltip :content="isCollapse ? '展开菜单' : '折叠菜单'" placement="bottom">
            <button
              class="collapse-button"
              type="button"
              :aria-label="isCollapse ? '展开菜单' : '折叠菜单'"
              :aria-expanded="!isCollapse"
              @click="toggleCollapse"
            >
              <el-icon :size="20">
                <Expand v-if="isCollapse" />
                <Fold v-else />
              </el-icon>
            </button>
          </el-tooltip>

          <el-breadcrumb separator="/" class="breadcrumb">
            <el-breadcrumb-item :to="{ path: '/' }">首页</el-breadcrumb-item>
            <el-breadcrumb-item v-for="(item, index) in breadcrumbs" :key="index">
              {{ item.meta?.title || item.name }}
            </el-breadcrumb-item>
          </el-breadcrumb>
        </div>

        <!-- 右侧：用户信息 -->
        <div class="header-right">
          <el-dropdown @command="handleCommand">
            <div class="user-info">
              <el-avatar
                v-if="userInfo?.avatar || websiteInfo?.logo"
                :size="32"
                style="background: transparent"
                :src="userInfo?.avatar || websiteInfo?.logo"
              />
              <el-avatar v-else :size="32" class="user-avatar">{{
                (userInfo?.nickname || "用户").slice(0, 1)
              }}</el-avatar>
              <span class="username">{{ userInfo?.nickname || "用户" }}</span>
              <el-icon>
                <ArrowDown />
              </el-icon>
            </div>
            <template #dropdown>
              <el-dropdown-menu>
                <el-dropdown-item command="password">修改密码</el-dropdown-item>
                <el-dropdown-item divided command="logout">退出登录</el-dropdown-item>
              </el-dropdown-menu>
            </template>
          </el-dropdown>
        </div>
      </el-header>

      <!-- 主体内容 -->
      <el-main class="main-container">
        <router-view />
      </el-main>
    </el-container>
  </el-container>
</template>

<script setup>
import { ref, computed, watch, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Expand, Fold, ArrowDown } from "@element-plus/icons-vue";
import MenuItem from "./MenuItem.vue";
import { useMediaQuery } from "@vueuse/core";
import transformRoutes from "@/router/routes";
import { redirectToLogin } from "@/utils/token";

import { useUserStore } from "@/store/modules/user";
import { useOvertStore } from "@/store/modules/overt";

const userStore = useUserStore();
const overtStore = useOvertStore();

const userInfo = ref({});
const websiteInfo = ref({});

const route = useRoute();
const router = useRouter();

// 菜单折叠状态
const compactViewport = useMediaQuery("(max-width: 900px)");
const isCollapse = ref(compactViewport.value);
watch(compactViewport, (compact) => {
  isCollapse.value = compact;
});

// 当前激活的菜单路径
const activeMenu = computed(() => {
  // 如果有自定义高亮路径，使用它
  if (route.meta?.activeMenu) {
    return route.meta.activeMenu;
  }
  return route.path;
});

// 默认展开的菜单（根据当前路由自动展开）
const defaultOpeneds = computed(() => {
  const paths = route.path.split("/");
  const openPaths = [];
  let currentPath = "";

  // 从根路径开始，逐级构建父路径
  for (let i = 1; i < paths.length - 1; i++) {
    currentPath += "/" + paths[i];
    openPaths.push(currentPath);
  }

  return openPaths;
});

const buildMenuTree = async () => {
  const routeItems = await userStore.getRoutesItems();
  return transformRoutes(routeItems);
};

const menuRoutes = ref([]);

// 面包屑数据
const breadcrumbs = ref([]);

// 更新面包屑
const updateBreadcrumbs = () => {
  const matched = route.matched;
  breadcrumbs.value = matched.filter((item) => item.path !== "/" && !item.meta?.hidden);
};

// 监听路由变化更新面包屑
watch(
  () => route.path,
  () => {
    updateBreadcrumbs();
  },
  { immediate: true }
);

// 切换菜单折叠
const toggleCollapse = () => {
  isCollapse.value = !isCollapse.value;
};

// 下拉菜单命令处理
const handleCommand = (command) => {
  if (command === "logout") {
    ElMessageBox.confirm("确定要退出登录吗？", "提示", {
      confirmButtonText: "确定",
      cancelButtonText: "取消",
    })
      .then(async () => {
        // 统一在 store 里作废服务端令牌并清空本地登录态与缓存，
        // 再整页回到登录页，确保动态路由等内存状态彻底重置
        await userStore.signOut();
        redirectToLogin();
      })
      .catch(() => {
        // 取消退出
      });
  }
  if (command === "password") {
    router.push("/center/password");
  }
};

onMounted(async () => {
  userStore
    .getUserInfo()
    .then((res) => {
      userInfo.value = res;
    })
    .catch((err) => {
      console.error("获取用户信息失败", err);
    });
  overtStore
    .getWebsite()
    .then((res) => {
      websiteInfo.value = res;
    })
    .catch((err) => {
      console.error("获取网站信息失败", err);
    });

  // 动态更新菜单：直接使用后端返回的权限路由构建，
  // 保证与 router.addRoute 注册的路由完全一致
  const routeItems = await buildMenuTree();
  menuRoutes.value = routeItems.filter((item) => item.hidden === false);
});
</script>

<style scoped>
.layout-container {
  height: 100dvh;
  overflow: hidden;
}
.workspace-container {
  min-width: 0;
}
.aside-container {
  background: #142c35;
  overflow: hidden;
  transition: width 0.25s;
  flex-shrink: 0;
}
.logo-wrapper {
  height: 76px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 0 14px;
}
.logo-img,
.logo-mark {
  width: 34px;
  height: 34px;
  flex-shrink: 0;
  border-radius: 10px;
  object-fit: contain;
}
.logo-mark {
  display: grid;
  place-items: center;
  background: #70d7c0;
  color: #142c35;
  font-size: 22px;
  font-weight: 750;
}
.logo-text {
  color: #f3f8f9;
  font-size: 17px;
  font-weight: 650;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  letter-spacing: 0.5px;
}
.menu-container {
  border-right: 0;
  height: calc(100dvh - 76px);
  overflow-y: auto;
  scrollbar-width: none;
  -ms-overflow-style: none;
  padding: 8px 10px 24px;
}
.menu-container::-webkit-scrollbar {
  display: none;
}
.menu-container :deep(.el-menu-item),
.menu-container :deep(.el-sub-menu__title) {
  height: 46px;
  line-height: 46px;
  border-radius: 8px;
  margin-bottom: 5px;
}
.menu-container :deep(.el-menu-item:hover),
.menu-container :deep(.el-sub-menu__title:hover) {
  background: #ffffff09;
}
.menu-container :deep(.el-menu-item.is-active) {
  background: #70d7c018;
  color: #70d7c0;
  font-weight: 600;
}
.menu-container :deep(.el-sub-menu .el-menu) {
  background: transparent;
}
.menu-container :deep(.el-icon) {
  font-size: 18px;
}
.menu-container.el-menu--collapse {
  width: 72px;
  padding-inline: 10px;
}
.menu-container.el-menu--collapse :deep(.el-menu-item),
.menu-container.el-menu--collapse :deep(.el-sub-menu__title) {
  padding-left: 18px !important;
}
.header-container {
  background: #fff;
  border-bottom: 1px solid #e7edef;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 28px;
  gap: 16px;
}
.header-left {
  display: flex;
  align-items: center;
  gap: 20px;
  min-width: 0;
}
.collapse-button {
  display: grid;
  place-items: center;
  width: 34px;
  height: 34px;
  color: #657585;
  background: transparent;
  border: 0;
  border-radius: 8px;
  cursor: pointer;
  flex-shrink: 0;
}
.collapse-button:hover {
  background: #f0f5f4;
  color: #138577;
}
.breadcrumb {
  font-size: 13px;
  line-height: 1.7;
}
.header-right {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}
.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  padding: 5px 8px;
  border-radius: 8px;
}
.user-info:hover {
  background: #f5f8f9;
}
.user-avatar {
  background: #e8f3f1;
  color: #138577;
  font-weight: 600;
}
.username {
  font-size: 13px;
  color: #354857;
}
.user-info > .el-icon {
  color: #8a98a4;
  font-size: 12px;
}
.main-container {
  background: #f4f7f8;
  padding: 28px;
  overflow-y: auto;
  min-width: 0;
}
@media (max-width: 767px) {
  .header-container {
    padding-inline: 14px;
  }
  .header-left {
    gap: 8px;
  }
  .main-container {
    padding: 16px 12px;
  }
  .username {
    display: none;
  }
  .breadcrumb {
    font-size: 12px;
  }
}
</style>
