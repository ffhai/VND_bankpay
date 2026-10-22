<template>
  <div class="order-statistics">
    <article
      v-for="period in groups || periods"
      :key="period.key"
      class="period-card"
      :class="period.key"
    >
      <div class="period-heading">
        <div>
          <p class="period-label">{{ period.label }}</p>
          <span class="period-description">{{ period.description }}</span>
        </div>
        <span class="period-icon"
          ><el-icon><Calendar /></el-icon
        ></span>
      </div>
      <div class="amount-statistic">
        <span class="metric-label">{{ amountLabel }}</span>
        <strong>{{ formatInteger(statistics[period.key][amountKey]) }}</strong>
      </div>
      <div class="count-statistic">
        <span class="metric-label"
          ><el-icon><Tickets /></el-icon>{{ countLabel }}</span
        >
        <span
          ><strong>{{ formatInteger(statistics[period.key].count) }}</strong
          ><small>{{ countUnit }}</small></span
        >
      </div>
    </article>
  </div>
</template>

<script setup>
import { Calendar, Tickets } from "@element-plus/icons-vue";
defineProps({
  statistics: { type: Object, required: true },
  amountKey: { type: String, default: "amount" },
  amountLabel: { type: String, default: "订单金额" },
  countLabel: { type: String, default: "订单总数" },
  countUnit: { type: String, default: "笔" },
  groups: { type: Array, default: null },
});
const periods = [
  { key: "today", label: "今日", description: "今日创建的成功订单" },
  { key: "yesterday", label: "昨日", description: "昨日创建的成功订单" },
  { key: "total", label: "总计", description: "截至当前的成功订单" },
];
// 金额直接按十进制字符串分组，避免大额合计转换为浮点数后丢失精度。
const formatInteger = (value) => String(value).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
</script>

<style scoped>
.order-statistics {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 20px;
}
.period-card {
  --accent: #4666e5;
  --tint: #eef2ff;
  position: relative;
  padding: 24px;
  border: 1px solid #e7ebf3;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 5px 22px #24365b05;
  overflow: hidden;
}
.period-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 24px;
  right: 24px;
  height: 3px;
  background: var(--accent);
  border-radius: 0 0 4px 4px;
}
.yesterday {
  --accent: #66758c;
  --tint: #f0f3f8;
}
.total {
  --accent: #188a71;
  --tint: #e9f7f1;
}
.period-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
.period-label {
  color: #25334b;
  font-size: 17px;
  font-weight: 600;
  margin: 0 0 7px;
}
.period-description {
  color: #8a95a8;
  font-size: 12px;
}
.period-icon {
  display: grid;
  place-items: center;
  flex-shrink: 0;
  width: 42px;
  height: 42px;
  border-radius: 13px;
  background: var(--tint);
  color: var(--accent);
  font-size: 21px;
}
.amount-statistic {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 30px 0 25px;
}
.metric-label {
  color: #78859a;
  font-size: 13px;
}
.amount-statistic > strong {
  color: #202f48;
  font-size: clamp(23px, 2.5vw, 36px);
  font-weight: 650;
  line-height: 1.3;
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.7px;
  overflow-wrap: anywhere;
}
.count-statistic {
  border-top: 1px solid #edf0f5;
  padding-top: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}
.count-statistic .metric-label {
  display: flex;
  align-items: center;
  gap: 7px;
}
.count-statistic strong {
  color: var(--accent);
  font-size: 21px;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}
.count-statistic small {
  color: #929bad;
  font-size: 12px;
  margin-left: 7px;
}
@media (max-width: 900px) {
  .order-statistics {
    grid-template-columns: 1fr;
    gap: 16px;
  }
  .amount-statistic > strong {
    font-size: 32px;
  }
}
</style>
