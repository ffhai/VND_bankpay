<template>
  <div class="dashboard-container">
    <div class="search-block">
      <el-form class="search-form" :inline="true" @submit.prevent="onSearch" :model="searchForm">
        <el-form-item class="search-field search-field--date">
          <el-date-picker
            v-model="searchForm.time"
            type="datetimerange"
            value-format="YYYY-MM-DD HH:mm:ss"
            range-separator="至"
            start-placeholder="开始日期"
            end-placeholder="结束日期"
          />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.keyword" placeholder="搜索订单号" clearable />
        </el-form-item>
        <el-form-item class="search-field">
          <el-input v-model="searchForm.ps" placeholder="搜索附言" clearable />
        </el-form-item>
        <el-form-item class="search-field">
          <el-select v-model="searchForm.pay_type" placeholder="支付方式">
            <el-option label="全部" value="" />
            <el-option
              v-for="item in paytypeList"
              :key="item.code"
              :label="item.name"
              :value="item.code"
            />
          </el-select>
        </el-form-item>
        <el-form-item class="search-field">
          <el-select v-model="searchForm.status" placeholder="筛选状态">
            <el-option label="全部状态" value="" />
            <el-option label="待支付" value="1" />
            <el-option label="已支付" value="2" />
            <el-option label="失败" value="-1" />
          </el-select>
        </el-form-item>
        <el-form-item class="search-actions">
          <el-button type="primary" :loading="loading" @click="onSearch">查询</el-button>
          <el-button :disabled="loading" @click="onReset">重置</el-button>
        </el-form-item>
      </el-form>
    </div>
    <div class="order-summary">
      <el-tag type="primary" size="large">合计金额：{{ sum.amount }}</el-tag>
    </div>
    <div class="data-list">
      <el-table
        v-loading="loading"
        :data="items"
        style="width: 100%"
        empty-text="暂无数据"
        @selection-change="handleSelectionChange"
      >
        <el-table-column type="selection" width="60" />
        <!-- <el-table-column prop="id" label="ID" width="80" /> -->
        <el-table-column label="订单编号" min-width="200">
          <template #default="scope">
            <div style="word-break: break-all">
              {{ scope.row.order_id }}
            </div>
            <div style="word-break: break-all">支付方式:{{ scope.row.pay_type }}</div>
          </template>
        </el-table-column>
        <el-table-column label="通道">
          <template #default="scope">
            <div style="word-break: break-all">卡号:{{ scope.row.bank_number }}</div>
            <div style="word-break: break-all">附言:{{ scope.row.ps }}</div>
          </template>
        </el-table-column>
        <el-table-column label="金额" width="150">
          <template #default="scope">
            <div style="word-break: break-all">
              {{ scope.row.amount.toLocaleString("zh-CN") }}
            </div>
          </template>
        </el-table-column>

        <el-table-column label="时间" width="220">
          <template #default="scope">
            <div style="word-break: break-all">创建:{{ scope.row.create_time }}</div>
            <div style="word-break: break-all">支付:{{ scope.row.pay_time }}</div>
            <div style="word-break: break-all">过期:{{ scope.row.expire_time }}</div>
          </template>
        </el-table-column>

        <el-table-column label="状态" width="80" align="center">
          <template #default="scope">
            <div>
              <el-tag v-if="scope.row.status === 1" type="danger">待支付</el-tag>
              <el-tag v-if="scope.row.status === 2" type="primary">已支付</el-tag>
              <el-tag v-if="scope.row.status === -1" disable-transitions type="info">失败</el-tag>
            </div>
            <div>
              <el-tag v-if="scope.row.notify_status === 1" type="danger">未回调</el-tag>
              <el-tag v-if="scope.row.notify_status === 2" type="primary">已回调</el-tag>
              <el-tag v-if="scope.row.notify_status === -1" type="info">失败</el-tag>
            </div>
          </template>
        </el-table-column>
        <el-table-column label="操作" fixed="right" width="180">
          <template #default="scope">
            <el-button
              type="danger"
              size="small"
              :loading="isRowActionPending('confirmed:' + scope.row.order_id)"
              @click="onConfirmed(scope.row)"
              v-if="scope.row.status == 1"
              >确认支付</el-button
            >
            <el-button
              type="warning"
              size="small"
              :disabled="scope.row.status !== 2"
              :loading="isRowActionPending('notify:' + scope.row.order_id)"
              @click="onNotify(scope.row)"
              >回调</el-button
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
import { computed, ref, onMounted } from "vue";
import order from "@/api/channel/order";
import payType from "@/api/admin/payType";

const {
  loading,
  items,
  total,
  searchForm,
  getItems,
  onSearch,
  onReset,
  onCurrentChange,
  onSizeChange,
  runRowAction,
  isRowActionPending,
  handleSelectionChange,
  extend,
} = useTableList({
  fetchList: (params) => order.getItems(params),
  initialSearch: {
    keyword: "",
    time: "",
    pay_type: "",
    order_id: "",
    ps: "",
    status: "",
  },
});
const sum = computed(() => extend.value ?? { amount: 0 });

const paytypeList = ref([]);

const onConfirmed = (item) =>
  runRowAction("confirmed:" + item.order_id, () => {
    return ElMessageBox.confirm("此操作将确认订单已支付, 是否继续?", "提示", {
      confirmButtonText: "确定",
      cancelButtonText: "取消",
      type: "warning",
    })
      .then(async () => {
        const loadingInstance = ElLoading.service({ fullscreen: true });
        try {
          const result = await order.confirmed({ order_id: item.order_id });
          ElMessage.success(
            result.result === "already_processed" ? "订单已处理，无需重复确认" : "确认成功"
          );
          getItems();
        } catch (error) {
          console.log(error);
        } finally {
          loadingInstance.close();
        }
      })
      .catch(() => {
        // catch error
      });
  });
const onNotify = (item) =>
  runRowAction("notify:" + item.order_id, () => {
    return ElMessageBox.confirm("此操作将回调商户, 是否继续?", "提示", {
      confirmButtonText: "确定",
      cancelButtonText: "取消",
      type: "warning",
    })
      .then(async () => {
        const loadingInstance = ElLoading.service({ fullscreen: true });
        try {
          await order.notify({ order_id: item.order_id });
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
const loadPayTypeList = async () => {
  try {
    const response = await payType.getOptions();
    paytypeList.value = response;
  } catch (error) {
    console.error("获取支付方式列表失败:", error);
  }
};

onMounted(() => {
  loadPayTypeList();
});
</script>
