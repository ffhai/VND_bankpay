#!/usr/bin/env node
/**
 * 安装 git 钩子（由 npm install 的 prepare 脚本触发）
 *
 * 为什么不用 husky：本仓库的 package.json 在 manage/ 子目录，而 .git 在仓库根目录，
 * husky v9 只检查当前目录下的 .git，安装会直接失败（.git can't be found）。
 * 这里直接设置 core.hooksPath 指向本目录的 .husky，效果一致且没有额外依赖。
 *
 * core.hooksPath 属于本地 git 配置（不会提交），所以每位开发者执行一次 npm install 即可。
 */
import { execFileSync } from "node:child_process";
import { chmodSync, existsSync } from "node:fs";
import { dirname, join, relative } from "node:path";
import { fileURLToPath } from "node:url";

const manageDir = dirname(dirname(fileURLToPath(import.meta.url)));
const hooksDir = join(manageDir, ".husky");

const git = (args, options = {}) =>
  execFileSync("git", args, { cwd: manageDir, encoding: "utf8", ...options }).trim();

const run = () => {
  // CI 环境不需要本地钩子
  if (process.env.CI) {
    console.log("[hooks] CI 环境，跳过钩子安装");
    return;
  }

  // 不在 git 仓库中（例如从 tarball 安装）时静默跳过
  try {
    git(["rev-parse", "--git-dir"]);
  } catch {
    console.log("[hooks] 当前目录不在 git 仓库中，跳过钩子安装");
    return;
  }

  // git 站在仓库根目录解析 core.hooksPath，因此写入相对仓库根目录的路径
  const gitRoot = git(["rev-parse", "--show-toplevel"]);
  const relativeHooksDir = relative(gitRoot, hooksDir).split("\\").join("/") || ".husky";

  git(["config", "core.hooksPath", relativeHooksDir]);
  console.log(`[hooks] core.hooksPath -> ${relativeHooksDir}`);

  // 类 Unix 系统需要可执行权限，Windows 上 chmod 无效但不报错
  const hookFiles = ["pre-commit", "commit-msg"];
  for (const file of hookFiles) {
    const target = join(hooksDir, file);
    if (!existsSync(target)) {
      continue;
    }
    try {
      chmodSync(target, 0o755);
    } catch {
      // Windows 上忽略
    }
  }
};

run();
