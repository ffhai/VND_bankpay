<template>
  <el-upload
    ref="uploadRef"
    :action="uploadInfo.action"
    :headers="uploadInfo.headers"
    :multiple="false"
    name="file"
    :accept="effectiveAccept"
    :show-file-list="false"
    :on-success="handleSuccess"
    :on-error="handleError"
    :before-upload="beforeUpload"
    :disabled="!uploadInfo.action"
  >
    <slot>
      <el-button type="primary">点击上传</el-button>
    </slot>
  </el-upload>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { ElMessage } from "element-plus";
import user from "@/api/user";

// accept 支持 .pdf,.xlsx、image/* 等浏览器格式；maxSize 单位 MB。
const props = defineProps({
  modelValue: { type: String, default: "" },
  accept: { type: String, default: "" },
  maxSize: { type: Number, default: 20 },
});
const serverExtensions = ref([]);
const serverMaxSize = ref(20 * 1024 * 1024);
const effectiveAccept = computed(
  () => props.accept || serverExtensions.value.map((ext) => `.${ext}`).join(",")
);

// 定义组件事件
const emit = defineEmits(["update:modelValue", "input"]);

// 上传配置信息
const uploadInfo = ref({
  action: "",
  headers: {},
});

// 上传组件引用
const uploadRef = ref(null);

// 获取上传凭证
const fetchUploadInfo = async () => {
  try {
    const data = await user.getUploadInfo();
    serverExtensions.value = data.extensions || [];
    serverMaxSize.value = data.maxSize || 20 * 1024 * 1024;
    uploadInfo.value = {
      action: data.action || "",
      headers: data.headers || {},
    };
  } catch (error) {
    ElMessage.error("获取上传配置失败，请稍后重试");
    console.error("获取上传信息失败:", error);
  }
};

// 上传成功回调
const handleSuccess = (response) => {
  if (response?.code !== 1 || !response.data?.url) {
    ElMessage.error(response?.msg || "上传失败，未返回有效的文件信息");
    return;
  }
  emit("update:modelValue", response.data.url);
  emit("input", response.data);
};

// 上传失败回调
const handleError = (error) => {
  let message = "文件上传失败，请稍后重试";
  try {
    message = JSON.parse(error.message)?.msg || message;
  } catch {
    // 非 JSON 网络错误使用通用提示。
  }
  ElMessage.error(message);
  console.error("上传失败:", error);
};

// 前端用于及时反馈，最终以服务端白名单校验为准。
const beforeUpload = (file) => {
  const extension = file.name.split(".").pop().toLowerCase();
  if (!serverExtensions.value.includes(extension)) {
    ElMessage.error("服务器不允许上传此文件格式");
    return false;
  }
  const patterns = effectiveAccept.value
    .toLowerCase()
    .split(",")
    .map((value) => value.trim())
    .filter(Boolean);
  const mime = file.type.toLowerCase();
  if (
    patterns.length &&
    !patterns.some((pattern) => {
      if (pattern.startsWith(".")) return file.name.toLowerCase().endsWith(pattern);
      if (pattern.endsWith("/*")) return mime.startsWith(pattern.slice(0, -1));
      return mime === pattern;
    })
  ) {
    ElMessage.error("请选择符合要求的文件格式");
    return false;
  }
  const limit = Math.min(props.maxSize * 1024 * 1024, serverMaxSize.value);
  if (file.size <= 0 || file.size > limit) {
    ElMessage.error(`文件不能为空且不能超过 ${limit / 1024 / 1024}MB`);
    return false;
  }
  return true;
};

// 组件挂载时获取上传信息
onMounted(() => {
  fetchUploadInfo();
});

// 暴露刷新上传配置的方法（可选）
defineExpose({
  refresh: fetchUploadInfo,
});
</script>

<style scoped>
/* 可自定义样式，无强制要求 */
</style>
