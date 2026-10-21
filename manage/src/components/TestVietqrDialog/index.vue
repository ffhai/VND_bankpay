<template>
  <el-dialog
    v-model="visible"
    title="测试 VietQR"
    width="480px"
    :close-on-click-modal="false"
    :close-on-press-escape="!loading"
    :show-close="!loading"
  >
    <el-form label-width="70px" @submit.prevent="generate">
      <el-form-item label="银行卡">
        <span>{{ card?.bank_number }}</span>
      </el-form-item>
      <el-form-item label="附言">
        <el-input
          v-model="remark"
          maxlength="25"
          placeholder="可留空，最多25位英文字母、数字或空格"
          :disabled="loading"
        />
      </el-form-item>
      <el-form-item label="金额">
        <el-input-number
          v-model="amount"
          :precision="0"
          :step="1"
          step-strictly
          :min="1"
          :max="9999999999999"
          :disabled="loading"
        />
      </el-form-item>
    </el-form>
    <div v-if="qrcode" style="text-align: center">
      <el-image :src="qrcode" fit="contain" style="width: 50%; max-height: 210px" />
    </div>
    <template #footer>
      <el-button :disabled="loading" @click="visible = false">关闭</el-button>
      <el-button type="primary" :loading="loading" @click="generate">生成二维码</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref, watch } from "vue";
import { ElMessage } from "element-plus";

const visible = defineModel({ type: Boolean, default: false });
const props = defineProps({
  card: { type: Object, default: null },
  api: { type: Object, required: true },
});
const remark = ref("");
const amount = ref(1000);
const loading = ref(false);
const qrcode = ref("");

watch(visible, (value) => {
  if (value) {
    remark.value = "";
    amount.value = 1000;
    qrcode.value = "";
  }
});

const generate = async () => {
  if (loading.value) return;
  if (!/^[A-Za-z0-9 ]{0,25}$/.test(remark.value.trim())) {
    ElMessage.warning("附言最多25位，仅支持英文字母、数字或空格");
    return;
  }
  if (!Number.isSafeInteger(amount.value) || amount.value < 1) {
    ElMessage.warning("金额必须为正整数");
    return;
  }
  loading.value = true;
  qrcode.value = "";
  try {
    const result = await props.api.testVietqr({
      id: props.card.id,
      remark: remark.value.trim(),
      amount: amount.value,
    });
    qrcode.value = result.qrcode;
  } catch {
    // API 请求层统一显示错误提示。
  } finally {
    loading.value = false;
  }
};
</script>
