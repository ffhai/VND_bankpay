import assert from "node:assert/strict";
import fs from "node:fs/promises";
import vm from "node:vm";

const context = vm.createContext({
  console,
  ElMessage: { success() {}, warning() {} },
  ElLoading: { service: () => ({ close() {} }) },
  ElMessageBox: { confirm: () => Promise.resolve() },
});
const vue = new vm.SyntheticModule(
  ["ref", "onMounted"],
  function () {
    this.setExport("ref", (value) => ({ value }));
    this.setExport("onMounted", () => {});
  },
  { context }
);
const source = await fs.readFile(
  new URL("../src/composables/useTableList.js", import.meta.url),
  "utf8"
);
const module = new vm.SourceTextModule(source, { context });
await module.link(() => vue);
await module.evaluate();
const pending = [];
const list = module.namespace.useTableList({
  immediate: false,
  initialSearch: { keyword: "" },
  fetchList: (params) => new Promise((resolve) => pending.push({ params, resolve })),
});
const first = list.getItems();
list.searchForm.value.keyword = "new";
const second = list.getItems();
pending[1].resolve({ items: ["new"], count: 1, sum: { amount: 100 } });
await second;
pending[0].resolve({ items: ["old"], count: 1, sum: { amount: 1 } });
await first;
assert.equal(list.items.value[0], "new");
assert.equal(list.extend.value.amount, 100);
assert.equal(list.loading.value, false);
const reset = list.onReset();
assert.equal(list.searchForm.value.keyword, "");
assert.equal(list.searchForm.value.page, 1);
pending[2].resolve({ items: [], count: 0 });
await reset;
list.searchForm.value.page = 3;
const resized = list.onSizeChange(50);
assert.equal(list.searchForm.value.page, 1);
pending[3].resolve({ items: [], count: 0 });
await resized;
let release;
let calls = 0;
const action = list.runRowAction("confirm:1", () => {
  calls++;
  return new Promise((resolve) => {
    release = resolve;
  });
});
await list.runRowAction("confirm:1", () => {
  calls++;
});
assert.equal(calls, 1);
assert.equal(list.isRowActionPending("confirm:1"), true);
release();
await action;
assert.equal(list.isRowActionPending("confirm:1"), false);
console.log(
  "PASS latest-response protection, totals, reset, pagination and duplicate action guard"
);
