import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import vm from "node:vm";
import { computed, markRaw, ref } from "vue";

const source = readFileSync(new URL("../src/views/Dashboard.vue", import.meta.url), "utf8")
  .match(/<script setup>([\s\S]*?)<\/script>/)[1]
  .replace(/^import .*;$/gm, "");
const data = {
  today: { count: 0, amount: "0" },
  yesterday: { count: 0, amount: "0" },
  total: { count: 0, amount: "0" },
};
let access = {};
const calls = [];
const api = Object.fromEntries(
  ["admin", "merchant", "channel"].map((module) => [
    module,
    {
      getOrders: async () => {
        calls.push(module);
        if (access[module] === "allowed") return data;
        throw Object.assign(new Error("denied or failed"), {
          code: access[module] === "failed" ? -1 : -10002,
        });
      },
    },
  ])
);
api.admin.getAccounts = async () => {
  calls.push("admin.accounts");
  if (access.accounts === "allowed")
    return {
      channel: { count: 0, balance: "0" },
      merchant: { count: 0, balance: "0" },
      total: { count: 0, balance: "0" },
    };
  throw Object.assign(new Error("denied"), { code: -10002 });
};
const context = vm.createContext({
  computed,
  markRaw,
  ref,
  onMounted() {},
  dashboardApi: api,
  StatisticsCards: {},
});
vm.runInContext(
  source + "\nglobalThis.subject = { panels, visiblePanels, loadStatistics, loading };",
  context
);
const state = context.subject;
access = { merchant: "allowed" };
await state.loadStatistics();
assert.equal(state.visiblePanels.value.map((panel) => panel.key).join(","), "merchant");
assert.equal(calls.length, 4);
access = { admin: "allowed", merchant: "allowed", channel: "allowed" };
await state.loadStatistics();
assert.equal(state.visiblePanels.value.length, 1);
assert.equal(state.visiblePanels.value[0].key, "admin");
assert.equal(
  calls.slice(4).join(","),
  "admin,admin.accounts",
  "admin does not request merchant or channel statistics"
);
access = { accounts: "allowed" };
await state.loadStatistics();
assert.equal(state.visiblePanels.value[0].key, "admin");
assert.equal(state.visiblePanels.value[0].widgets[0].data, null);
assert.ok(
  state.visiblePanels.value[0].widgets[1].data,
  "account permission works independently of order permission"
);
access = {};
await state.loadStatistics();
assert.equal(state.visiblePanels.value.length, 0, "revoked access clears previously visible data");
access = { channel: "failed" };
await state.loadStatistics();
assert.equal(state.visiblePanels.value[0].key, "channel");
assert.ok(state.visiblePanels.value[0].widgets[0].error);
assert.equal(state.loading.value, false);
console.log(
  "PASS independent API-driven panels, multiple grants, zero totals, revocation and failure state"
);

const messages = [];
let successHandler;
let errorHandler;
const requestContext = vm.createContext({
  axios: {
    create: () => ({
      interceptors: {
        request: { use() {} },
        response: {
          use(success, failure) {
            successHandler = success;
            errorHandler = failure;
          },
        },
      },
    }),
  },
  ElMessage: {
    error(message) {
      messages.push(message);
    },
  },
  getToken() {},
  redirectToLogin() {},
  console,
});
const requestSource = readFileSync(new URL("../src/utils/request.js", import.meta.url), "utf8")
  .replace(/^import .*;$/gm, "")
  .replace("import.meta.env.VITE_API_BASE_URL", '"/"')
  .replace("export default request;", "");
vm.runInContext(requestSource, requestContext);
await assert.rejects(
  errorHandler({
    response: { data: { code: -10002, msg: "denied" } },
    config: { silentPermissionError: true },
  }),
  (err) => err.code === -10002
);
assert.equal(messages.length, 0);
await assert.rejects(successHandler({ data: { code: -10002, msg: "denied" }, config: {} }));
assert.equal(messages.length, 1, "normal API errors retain notifications");
await assert.rejects(
  errorHandler({
    response: { data: { code: -1, msg: "failure" } },
    config: { silentPermissionError: true },
  })
);
assert.equal(messages.length, 2, "server failures are not silenced");
console.log("PASS permission probing suppresses only opted-in permission notifications");
