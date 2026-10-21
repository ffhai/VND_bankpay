<template>
  <el-dialog
    :model-value="modelValue"
    :title="title"
    :width="width"
    :destroy-on-close="destroyOnClose"
    @update:model-value="emit('update:modelValue', $event)"
  >
    <el-form ref="formRef" :model="form" :rules="rules" :label-width="labelWidth">
      <el-row :gutter="gutter">
        <el-col v-for="field in visibleFields" :key="field.prop" :span="field.span ?? 24">
          <el-form-item :label="field.label" :prop="field.prop">
            <slot
              :name="field.slot || field.prop"
              :field="field"
              :model="form"
              :mode="mode"
              :disabled="isDisabled(field)"
            >
              <el-select
                v-if="field.type === 'select'"
                v-bind="field.props"
                v-model="form[field.prop]"
                :disabled="isDisabled(field)"
                :placeholder="field.placeholder"
              >
                <el-option
                  v-for="option in getOptions(field)"
                  :key="option.value"
                  :label="option.label"
                  :value="option.value"
                />
              </el-select>
              <el-input-number
                v-else-if="field.type === 'number'"
                v-bind="field.props"
                v-model="form[field.prop]"
                :disabled="isDisabled(field)"
              />
              <el-switch
                v-else-if="field.type === 'switch'"
                v-bind="field.props"
                v-model="form[field.prop]"
                :disabled="isDisabled(field)"
              />
              <el-tree-select
                v-else-if="field.type === 'tree-select'"
                v-bind="field.props"
                v-model="form[field.prop]"
                :disabled="isDisabled(field)"
                :placeholder="field.placeholder"
              />
              <el-input
                v-else
                v-bind="field.props"
                v-model="form[field.prop]"
                :type="field.type === 'textarea' ? 'textarea' : 'text'"
                :disabled="isDisabled(field)"
                :placeholder="field.placeholder"
              />
            </slot>
          </el-form-item>
        </el-col>
      </el-row>
    </el-form>
    <template #footer>
      <slot name="footer" :submit="onSubmit" :close="onClose">
        <el-button @click="onClose">取 消</el-button>
        <el-button type="primary" :loading="loading" @click="onSubmit">确 定</el-button>
      </slot>
    </template>
  </el-dialog>
</template>

<script setup>
import { computed, nextTick, ref, watch } from "vue";

const props = defineProps({
  // 弹窗显隐，配合 v-model 使用
  modelValue: { type: Boolean, default: false },
  title: { type: String, default: "" },
  // add / edit，编辑态下 disabledInEdit 的字段会禁用
  mode: { type: String, default: "add" },
  // 表单初始值：弹窗打开时快照到内部副本，编辑过程不改动父组件数据
  model: { type: Object, required: true },
  // 字段配置支持 options 数组或接收当前表单的 options 函数。
  fields: { type: Array, default: () => [] },
  rules: { type: Object, default: () => ({}) },
  labelWidth: { type: String, default: "80px" },
  width: { type: String, default: "600px" },
  gutter: { type: Number, default: 0 },
  loading: { type: Boolean, default: false },
  destroyOnClose: { type: Boolean, default: false },
});

// 提交时回传编辑结果：emit('submit', data)
const emit = defineEmits(["update:modelValue", "submit"]);

const formRef = ref(null);
// 弹窗内部的编辑副本（自身可写，无需对 props 做双向绑定）
const form = ref({});

const isHidden = (field) => {
  if (typeof field.hidden === "function") {
    return field.hidden(props.mode, form.value);
  }
  return field.hidden === true;
};

const getOptions = (field) =>
  typeof field.options === "function" ? field.options(props.mode, form.value) : field.options || [];

const visibleFields = computed(() => props.fields.filter((field) => !isHidden(field)));

const isDisabled = (field) =>
  field.disabled ?? (props.mode === "edit" && Boolean(field.disabledInEdit));

// 每次打开都以最新的 model 作为初始值，并清掉上一次的校验残留
watch(
  () => props.modelValue,
  (visible) => {
    if (!visible) {
      return;
    }
    form.value = { ...props.model };
    nextTick(() => formRef.value?.clearValidate());
  },
  { immediate: true }
);

const onClose = () => emit("update:modelValue", false);

const onSubmit = async () => {
  if (formRef.value) {
    const valid = await formRef.value.validate().catch(() => false);
    if (!valid) {
      return;
    }
  }
  emit("submit", { ...form.value });
};

defineExpose({ formRef });
</script>
