<template>
  <div style="padding: 10px">
    <textarea :id="editorId"></textarea>
  </div>
</template>
<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from "vue";
import user from "@/api/user";

const props = defineProps({
  modelValue: { type: String, default: "" },
  height: { type: Number, default: 400 },
});

const emit = defineEmits(["update:modelValue"]);

// TinyMCE 8 使用的开源插件（同时用于动态加载与 init 配置）
const plugins = [
  "advlist",
  "anchor",
  "autolink",
  "autoresize",
  "charmap",
  "code",
  "codesample",
  "directionality",
  "emoticons",
  "fullscreen",
  "help",
  "image",
  "insertdatetime",
  "link",
  "lists",
  "media",
  "nonbreaking",
  "pagebreak",
  "preview",
  "searchreplace",
  "table",
  "visualblocks",
  "visualchars",
  "wordcount",
];

const editorId = ref(`mce-${Math.random().toString().slice(2)}`);
let editor = null;

const loadScript = (src) =>
  new Promise((resolve, reject) => {
    const script = document.createElement("script");
    script.src = src;
    script.async = false;
    script.onload = () => resolve();
    script.onerror = () => reject(new Error(`脚本加载失败: ${src}`));
    document.head.appendChild(script);
  });

const loadTinyMCE = async () => {
  if (window.tinymce) return;

  const scripts = [
    "./tinymce/tinymce.min.js",
    "./tinymce/models/dom/model.min.js",
    "./tinymce/themes/silver/theme.min.js",
    "./tinymce/icons/default/icons.min.js",
    ...plugins.map((p) => `./tinymce/plugins/${p}/plugin.min.js`),
    "./tinymce/langs/zh-CN.js",
  ];

  for (const src of scripts) {
    await loadScript(src);
  }
};

onMounted(async () => {
  await loadTinyMCE();
  initEditor();
});
const imageUploadHandler = (blobInfo) =>
  new Promise((resolve, reject) => {
    user
      .uploadFile({ image: blobInfo.blob() })
      .then((data) => resolve(data?.url || ""))
      .catch((error) => reject(error?.message || "上传失败"));
  });

const initEditor = () => {
  window.tinymce.init({
    selector: `#${editorId.value}`,
    height: props.height,
    menubar: false,
    branding: false,
    // 自托管 GPL 开源许可（TinyMCE 8 不提供 license_key 会禁用编辑器）
    license_key: "gpl",
    language: "zh-CN",
    skin_url: "./tinymce/skins/ui/oxide",
    content_css: "./tinymce/skins/content/default/content.min.css",
    plugins: plugins.join(" "),
    toolbar:
      "undo redo | blocks | bold italic underline strikethrough subscript superscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | searchreplace | removeformat | fullscreen code preview",
    toolbar_mode: "wrap",
    images_upload_handler: imageUploadHandler,
    setup: (ed) => {
      editor = ed;
      ed.on("init", () => props.modelValue && ed.setContent(props.modelValue));
      ed.on("Change", () => emit("update:modelValue", ed.getContent()));
    },
  });
};

watch(
  () => props.modelValue,
  (val) => {
    if (editor && val !== editor.getContent()) {
      editor.setContent(val || "");
    }
  }
);

onBeforeUnmount(() => {
  if (editor) {
    editor.destroy();
    editor = null;
  }
});

defineExpose({
  getContent: () => editor?.getContent() || "",
  setContent: (html) => editor?.setContent(html),
});
</script>
