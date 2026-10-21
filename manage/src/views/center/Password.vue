<template>
  <div class="app-container">
    <el-form
      ref="formRef"
      :model="form"
      label-width="120px"
      :rules="formRules"
      @keyup.enter="submitForm(formRef)"
    >
      <el-form-item label="原密码" prop="password_old">
        <el-input
          v-model="form.password_old"
          type="password"
          show-password
          clearable
          placeholder="请输入原密码"
        />
      </el-form-item>
      <el-form-item label="新密码" prop="password_new">
        <el-input
          v-model="form.password_new"
          type="password"
          show-password
          clearable
          placeholder="请输入不少于6位的新密码"
        />
      </el-form-item>
      <el-form-item>
        <el-button type="primary" :loading="loading" @click="submitForm(formRef)">提交</el-button>
      </el-form-item>
    </el-form>
  </div>
</template>
<script setup>
import { ref, reactive } from "vue";

import user from "@/api/user";
import { redirectToLogin } from "@/utils/token";

const formRef = ref();
const form = ref({
  password_old: "",
  password_new: "",
});
const formRules = reactive({
  password_old: [{ required: true, message: "请输入原密码", trigger: "blur" }],
  password_new: [
    { required: true, message: "请输入新密码", trigger: "blur" },
    { min: 6, message: "新密码不能少于6位", trigger: "blur" },
  ],
});
const loading = ref(false);

const submitForm = async (formEl) => {
  if (!formEl) return;
  const valid = await formEl.validate().catch(() => false);
  if (valid) {
    submit();
  }
};
const submit = async () => {
  loading.value = true;
  try {
    await user.postPassword(form.value);
    // 密码已变更，后端会作废此前签发的 token，这里主动退出避免后续请求 401
    ElMessage.success("密码修改成功，请重新登录");
    redirectToLogin();
  } catch (err) {
    console.error("修改密码失败", err);
  } finally {
    loading.value = false;
  }
};
</script>
