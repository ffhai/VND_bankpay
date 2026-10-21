# Element Plus + Vite

# 定义路由

## /src/router/routes.js 添加routesMap元素，键名为视图路径及后端配置权限路由

### 不填component即为根目录，Layout

# 运行与打包

# npm run dev

# npm run build

# .env.development 配置开发信息

# .env.production 配置打包信息

# 新增/编辑表单

## 列表页的「新增」「编辑」共用组件 src/components/FormDialog/index.vue，只需维护一份字段配置

弹窗内部持有一份可编辑副本：打开时把 `model` 快照进来，提交时通过 `@submit` 抛回数据。
父组件不需要（也不会被修改）逐字段双向绑定，因此弹窗里不用写 `v-model:字段` 这类语法。

```vue
<FormDialog
  v-model="dialogVisible"
  :title="dialogTitle"
  :mode="mode"
  :model="form"
  :fields="formFields"
  :loading="loading"
  @submit="onSubmit"
/>
```

```js
const dialogVisible = ref(false);
const mode = ref("add"); // add / edit
const form = ref({}); // 仅作为打开弹窗时的初始值
const dialogTitle = computed(() => (mode.value === "add" ? "添加" : "编辑"));

// data 是弹窗内的最新值，新增与编辑共用一份提交逻辑
const onSubmit = async (data) => {
  await (mode.value === "add" ? api.add(data) : api.edit(data));
  dialogVisible.value = false;
  refresh();
};

const formFields = [
  { prop: "name", label: "名称", placeholder: "请输入名称" },
  { prop: "appid", label: "APPID", disabledInEdit: true }, // 编辑时不可修改
  { prop: "status", label: "状态", type: "select", options: [{ label: "启用", value: 1 }] },
];
```

### 字段配置项

- prop / label：字段名与标签
- type：控件类型，默认 input，可选 textarea、select、number、switch、tree-select
- options：select 的选项，格式 [{ label, value }]
- props：透传给控件的属性，如 { min: 0, max: 9999 }
- span：栅格宽度，默认 24；gutter 默认 0
- hidden：true 或 (mode, model) => boolean，控制条件显示
- disabled / disabledInEdit：永久禁用 / 仅编辑态禁用
- slot：使用插槽自定义控件，插槽参数为 { field, model, mode, disabled }
  （`model` 即弹窗内部副本，可直接 `model.xxx = value` 回写，APPID「随机生成」就是这么实现的）
