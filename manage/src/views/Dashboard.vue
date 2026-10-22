<template>
  <div v-loading="loading" class="dashboard-container statistics-dashboard">
    <div class="statistics-header">
      <div>
        <span class="dashboard-eyebrow">数据概览</span>
        <h1>业务统计</h1>
        <p>查看订单与账号数据，掌握业务概况</p>
      </div>
      <el-button :icon="Refresh" :loading="loading" round @click="loadStatistics"
        >刷新数据</el-button
      >
    </div>
    <section v-for="panel in visiblePanels" :key="panel.key" class="statistics-panel">
      <template v-for="widget in panel.widgets" :key="widget.key">
        <div v-if="widget.data || widget.error" class="statistics-widget">
          <el-alert
            v-if="widget.error"
            :title="widget.error"
            type="error"
            :closable="false"
            show-icon
          />
          <component
            :is="widget.component"
            v-else
            :statistics="widget.data"
            v-bind="widget.props"
          />
        </div>
      </template>
    </section>
    <el-empty v-if="!loading && !visiblePanels.length" description="暂无可查看的统计数据" />
  </div>
</template>

<script setup>
import { computed, markRaw, onMounted, ref } from "vue";
import { Refresh } from "@element-plus/icons-vue";
import dashboardApi from "@/api/dashboard";
import StatisticsCards from "@/components/dashboard/StatisticsCards.vue";

// 各统计项独立请求、鉴权和展示；新增统计时注册接口和组件即可。
const orderWidget = {
  key: "orders",
  title: "订单统计",
  component: markRaw(StatisticsCards),
  fetch: (api) => api.getOrders(),
};
const panels = ref(
  [
    {
      key: "admin",
      title: "管理端统计",
      widgets: [
        orderWidget,
        {
          key: "accounts",
          title: "账号统计",
          component: markRaw(StatisticsCards),
          fetch: (api) => api.getAccounts(),
          props: {
            amountKey: "balance",
            amountLabel: "总余额",
            countLabel: "账号总数",
            countUnit: "个",
            groups: [
              { key: "channel", label: "码商", description: "全部码商账号及余额" },
              { key: "merchant", label: "商户", description: "全部商户账号及余额" },
              { key: "total", label: "账号合计", description: "码商与商户账号合计" },
            ],
          },
        },
      ],
    },
    { key: "merchant", title: "商户统计", widgets: [orderWidget] },
    { key: "channel", title: "码商统计", widgets: [orderWidget] },
  ].map((panel) => ({
    ...panel,
    widgets: panel.widgets.map((widget) => ({ ...widget, data: null, error: "" })),
  }))
);
const loading = ref(false);
const visiblePanels = computed(() => {
  const available = panels.value.filter((panel) =>
    panel.widgets.some((widget) => widget.data || widget.error)
  );
  const admin = available.find((panel) => panel.key === "admin");
  return admin ? [admin] : available;
});

const loadStatistics = async () => {
  loading.value = true;
  panels.value.forEach((panel) =>
    panel.widgets.forEach((widget) => {
      widget.data = null;
      widget.error = "";
    })
  );
  const loadPanel = (panel) =>
    Promise.all(
      panel.widgets.map(async (widget) => {
        try {
          widget.data = await widget.fetch(dashboardApi[panel.key]);
        } catch (err) {
          // 无权限只隐藏该统计项；网络或服务故障保留错误提示和刷新入口。
          if (err.code !== -10002) {
            widget.error = `${panel.title}的${widget.title}获取失败，请刷新重试`;
          }
        }
      })
    );
  // 先获取管理端权限结果；管理端无需请求或展示商户、码商面板。
  await loadPanel(panels.value[0]);
  if (!panels.value[0].widgets.some((widget) => widget.data || widget.error)) {
    await Promise.all(panels.value.slice(1).map(loadPanel));
  }
  loading.value = false;
};

onMounted(loadStatistics);
</script>

<style scoped>
.statistics-dashboard {
  padding: 30px;
  background: #f6f8fc;
  min-height: calc(100vh - 110px);
}
.statistics-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 32px;
}
.dashboard-eyebrow {
  color: #6e82a6;
  font-size: 12px;
  letter-spacing: 2px;
}
.statistics-header h1 {
  margin: 9px 0;
  font-size: 28px;
  font-weight: 650;
  color: #22324b;
  letter-spacing: -0.5px;
}
.statistics-header p {
  margin: 0;
  color: #8b97aa;
  font-size: 13px;
  line-height: 1.6;
}
.statistics-panel {
  margin-bottom: 28px;
}
.statistics-widget + .statistics-widget {
  margin-top: 20px;
}
@media (max-width: 600px) {
  .statistics-dashboard {
    padding: 18px;
  }
  .statistics-header {
    align-items: flex-start;
    gap: 12px;
  }
  .statistics-header h1 {
    font-size: 24px;
  }
}
</style>
