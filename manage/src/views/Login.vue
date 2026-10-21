<template>
  <div class="login-container">
    <el-form
      ref="formRef"
      :model="form"
      :rules="formRules"
      label-width="0"
      class="login-form"
      @keyup.enter="submitForm(formRef)"
    >
      <div class="title-container">
        <div class="login-brand">
          <img v-if="website.logo" :src="website.logo" alt="站点标志" /><span v-else>P</span>
        </div>
        <h3 class="title">{{ website.site_name }}</h3>
        <p class="login-subtitle">欢迎回来，登录以管理您的业务</p>
      </div>
      <!-- 账号 -->
      <el-form-item prop="username">
        <el-input
          v-model="form.username"
          aria-label="账号"
          autocomplete="username"
          placeholder="请输入账号"
          size="large"
          :prefix-icon="User"
          clearable
        />
      </el-form-item>

      <!-- 密码 -->
      <el-form-item prop="password">
        <el-input
          v-model="form.password"
          type="password"
          aria-label="密码"
          autocomplete="current-password"
          placeholder="请输入密码"
          size="large"
          :prefix-icon="Lock"
          show-password
          clearable
        />
      </el-form-item>

      <!-- 图片验证码 -->
      <el-form-item prop="captcha" v-if="website.captcha_status === 1">
        <div class="captcha-wrapper">
          <el-input
            v-model="form.captcha"
            placeholder="请输入验证码"
            size="large"
            :prefix-icon="Stamp"
            clearable
            class="captcha-input"
          />
          <button type="button" aria-label="更换验证码" class="captcha-image" @click="onCaptcha">
            <img :src="captcha_image" alt="验证码" :title="'点击更换验证码'" />
          </button>
        </div>
      </el-form-item>

      <!-- 登录按钮 -->
      <el-form-item>
        <el-button
          type="primary"
          size="large"
          @click="submitForm(formRef)"
          class="login-btn"
          :loading="loading"
        >
          登录控制台
        </el-button>
      </el-form-item>
      <p class="login-footer">
        <el-icon><Lock /></el-icon> 安全登录 · 高效管理
      </p>
    </el-form>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import { User, Lock, Stamp } from "@element-plus/icons-vue";

import overt from "@/api/overt";
import { useUserStore } from "@/store/modules/user";

const router = useRouter();
const userStore = useUserStore();

const formRef = ref();
const form = ref({
  username: "",
  password: "",
  captcha: "",
  captcha_key: "",
});
const formRules = reactive({
  username: [{ required: true, message: "请输入账号", trigger: "blur" }],
  password: [{ required: true, message: "请输入密码", trigger: "blur" }],
});
const captcha_image = ref("");
const loading = ref(false);

const website = ref({
  site_name: "管理面板",
  captcha_status: 2,
});
const onCaptcha = () => {
  overt.getCaptcha().then((data) => {
    form.value.captcha_key = data.key;
    captcha_image.value = data.data;
  });
};

const submitForm = async (formEl) => {
  if (!formEl) return;
  await formEl.validate((valid, fields) => {
    if (valid) {
      login();
    } else {
      console.log("error submit!", fields);
    }
  });
};
const login = async () => {
  loading.value = true;
  try {
    const data = await overt.postLogin(form.value);
    userStore.signIn(data.token);
    ElMessage.success("登录成功");
    router.push({ path: "/" });
  } catch (err) {
    console.error("登录失败", err);
    // 验证码通常一次性有效，登录失败后需要换一张
    if (website.value.captcha_status === 1) {
      onCaptcha();
    }
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  try {
    website.value = await overt.init();
  } catch (error) {
    console.error("获取站点信息失败", error);
  }
  // 未开启验证码时不请求验证码图片
  if (website.value.captcha_status === 1) {
    onCaptcha();
  }
});
</script>
<style scoped>
.login-container {
  display: grid;
  place-items: center;
  min-height: 100dvh;
  padding: 32px 20px;
  background:
    radial-gradient(ellipse at 15% 10%, #deeee8 0, transparent 48%),
    radial-gradient(ellipse at 90% 90%, #e2eaee 0, transparent 45%), #f4f7f8;
}
.login-form {
  width: 100%;
  max-width: 420px;
  padding: 40px;
  background: #fff;
  border: 1px solid #e4ece9;
  border-radius: 20px;
  box-shadow: 0 24px 80px #203e3510;
}
.title-container {
  margin-bottom: 32px;
}
.login-brand {
  width: 44px;
  height: 44px;
  display: grid;
  place-items: center;
  background: #138577;
  border-radius: 12px;
  color: #fff;
  font-size: 27px;
  font-weight: 700;
  margin-bottom: 26px;
}
.login-brand img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: inherit;
}

.title {
  font-size: 26px;
  color: #243544;
  font-weight: 650;
  line-height: 1.4;
}
.login-subtitle {
  color: #788594;
  margin-top: 10px;
  font-size: 13px;
  line-height: 1.8;
}
.login-form :deep(.el-input__wrapper) {
  min-height: 46px;
  padding-inline: 14px;
  border-radius: 9px;
  background: #fafcfc;
}
.login-form :deep(.el-input__prefix) {
  margin-right: 6px;
  color: #8c9b9f;
}
.login-form :deep(.el-form-item) {
  margin-bottom: 24px;
}
.captcha-wrapper {
  display: flex;
  gap: 10px;
  width: 100%;
  align-items: center;
}
.captcha-input {
  flex: 1;
  min-width: 0;
}
.captcha-image {
  flex-shrink: 0;
  width: 116px;
  height: 46px;
  cursor: pointer;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #dce4e8;
  background: #f5f8f9;
  padding: 0;
}
.captcha-image img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: contain;
}
.login-btn {
  width: 100%;
  border-radius: 9px;
  font-size: 14px;
  font-weight: 600;
  height: 46px;
}
.login-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-size: 11px;
  color: #89979f;
  margin-top: 4px;
}
@media (max-width: 480px) {
  .login-form {
    padding: 30px 24px;
  }
}
</style>
