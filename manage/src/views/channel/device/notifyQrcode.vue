<template>
  <div class="dashboard-container">
    <div style="text-align: center; padding-top: 50px">
      <div v-if="item">
        <img :src="item.qrcode" alt="" style="width: 150px; height: 150px" />
        <div style="margin-top: 10px">
          请使用App扫码自动填入参数，或手动填写以下参数：

          <div style="text-align: left; margin: 20px auto; width: 300px">
            <div>Url：{{ item.base_url }} （注意包含/结尾）</div>
            <div>AppID：{{ item.appid }}</div>
            <div>Secret：{{ item.secret }}</div>
          </div>
          <p>[填写之后需要重启App]</p>
        </div>
      </div>
      <div v-if="errorMsg != ''">
        <div style="margin-top: 10px">{{ errorMsg }}</div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from "vue";
import api from "@/api/channel/device";

const item = ref(false);
const errorMsg = ref("");
const loadNotifyQrcode = async () => {
  try {
    const response = await api.getNotifyQrcode();
    item.value = response;
  } catch (error) {
    console.error("获取失败:", error);
    errorMsg.value = error;
  }
};

onMounted(() => {
  loadNotifyQrcode();
});
</script>
