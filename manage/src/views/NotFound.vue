<template>
  <div class="error-container">
    <div class="error-content">
      <!-- 404 图标 -->
      <div class="error-icon">
        <el-icon :size="120" color="#409EFF">
          <WarningFilled />
        </el-icon>
      </div>

      <!-- 错误信息 -->
      <h1 class="error-code">404</h1>
      <h2 class="error-title">页面找不到了</h2>
      <p class="error-desc">很抱歉，您访问的页面不存在或已被移除</p>

      <!-- 操作按钮 -->
      <div class="error-actions">
        <el-button type="primary" size="large" @click="goHome" class="action-btn">
          <el-icon>
            <HomeFilled />
          </el-icon>
          返回首页
        </el-button>
        <el-button size="large" @click="goBack" class="action-btn">
          <el-icon>
            <Back />
          </el-icon>
          返回上一页
        </el-button>
      </div>

      <!-- 温馨提示 -->
      <div class="error-tips">
        <el-text type="info" size="small"> 您可能输入了错误的网址，或页面已被删除 </el-text>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from "vue-router";
import { WarningFilled, HomeFilled, Back } from "@element-plus/icons-vue";

const router = useRouter();

// 跳转首页
const goHome = () => {
  router.push("/");
};

// 返回上一页
const goBack = () => {
  router.back();
};
</script>

<style scoped>
.error-container {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  padding: 20px;
}

.error-content {
  text-align: center;
  background: rgba(255, 255, 255, 0.95);
  padding: 60px 50px 50px;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
  max-width: 520px;
  width: 100%;
  backdrop-filter: blur(10px);
  transition: transform 0.3s ease;
}

.error-content:hover {
  transform: translateY(-5px);
}

.error-icon {
  margin-bottom: 10px;
  animation: float 3s ease-in-out infinite;
}

.error-code {
  font-size: 80px;
  font-weight: 700;
  color: #409eff;
  margin: 0;
  line-height: 1;
  background: linear-gradient(135deg, #409eff 0%, #66b1ff 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.error-title {
  font-size: 24px;
  color: #303133;
  margin: 15px 0 10px;
  font-weight: 600;
}

.error-desc {
  color: #909399;
  font-size: 14px;
  margin: 0 0 35px;
  line-height: 1.6;
}

.error-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: 30px;
}

.action-btn {
  min-width: 140px;
  font-size: 16px;
  padding: 12px 24px;
  border-radius: 50px;
  transition: all 0.3s ease;
}

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(64, 158, 255, 0.3);
}

.action-btn:not(.el-button--primary):hover {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
}

.error-tips {
  padding-top: 20px;
  border-top: 1px solid #e4e7ed;
}

/* 浮动动画 */
@keyframes float {
  0%,
  100% {
    transform: translateY(0px);
  }

  50% {
    transform: translateY(-15px);
  }
}

/* 响应式适配 */
@media (max-width: 768px) {
  .error-content {
    padding: 40px 25px 35px;
  }

  .error-code {
    font-size: 60px;
  }

  .error-title {
    font-size: 20px;
  }

  .action-btn {
    min-width: 120px;
    font-size: 14px;
    padding: 10px 20px;
    flex: 1;
  }

  .error-actions {
    flex-direction: column;
    gap: 10px;
  }
}

@media (max-width: 480px) {
  .error-content {
    padding: 30px 20px 25px;
  }

  .error-code {
    font-size: 48px;
  }

  .error-title {
    font-size: 18px;
  }

  .error-desc {
    font-size: 13px;
  }
}
</style>
