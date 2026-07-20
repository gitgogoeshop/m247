# magento-dev-implement.md
---
description: Magento 2 实现闭环 — 按已确认技术方案编码、构建验证与测试（不含重复规划/架构/内审）
---

外层 DevTeam 流程已完成 **PRD、技术方案与 PM 确认**。本命令只负责 **落地实现**，输入为已批准的技术方案全文。

## 禁止事项（必须遵守）

- **不要** 调用 @planner 重新拆需求或写验收标准
- **不要** 调用 @architect 重新做模块/Service Contract/数据库设计
- **不要** 执行 magento-dev-loop 中的四路并行审查（@magento2-reviewer、@magento2-frontend-reviewer、@security-reviewer、@silent-failure-hunter）及 BLOCK 打回循环 — 外层 CrewAI Reviewer 会审核
- **不要** 自行 git commit / push / 开 PR — 由 devteam Flow 在 ECC 结束后处理

## 执行步骤（按顺序，失败退回上一步，compile 修复最多重试 2 次）

### 1. 实现代码

- 严格按下方「技术方案」实现，引用具体 `app/code/Vendor/Module` 路径
- **硬规则**：新建自定义模块的路径 / PHP namespace / `module.xml` name 一律使用 `Megazend`（`app/code/Megazend/<Module>`、`Megazend_<Module>`）。仅当修改既有 `Gogo_*` 模块时才继续使用 Gogo
- 遵循项目 `.cursor/rules` 与现有自定义模块风格
- 若方案与代码库现状冲突，在输出摘要中说明，**不要**擅自改架构

### 2. 主题与前端（仅当改动涉及 layout XML / phtml / RequireJS / Knockout / Less 时）

- 调用 @magento2-theme-builder 处理主题覆盖
- 完成后执行：`rm -rf var/view_preprocessed/*` → `bin/magento setup:static-content:deploy -f` → `bin/magento cache:flush`

### 3. 构建验证

- 依次执行：
  ```
  bin/magento setup:di:compile
  bin/magento setup:upgrade
  ```
- 若报错：调用 @magento2-build-resolver **只修编译/配置错误**，不要重构
- 修复后回到步骤 1 相关文件修正，再重新执行本步骤
- **本步骤最多重试 2 次**，仍失败则停止并输出完整错误日志

### 4. 单元测试

- 调用 @tdd-guide 为本次变更模块补充并运行 PHPUnit（`app/code/Megazend/<Module>/Test/Unit`；若改的是既有 Gogo 模块则用对应路径）
- 测试失败则修复代码后重跑，直到通过或明确在摘要中标注无法运行的原因

### 5. 输出摘要（必须）

以 Markdown 输出，供外层 Reviewer 与 GitHub PR 使用：

1. **改动文件清单**（路径列表）
2. **实现说明**（做了什么、关键类/布局）
3. **构建与测试结果**（compile / upgrade / PHPUnit 是否通过）
4. **风险与运维提示**（是否需清缓存、reindex、static deploy、生产注意事项）
5. **与方案的差异**（若有）

---

技术方案与需求:

$ARGUMENTS
