# .claude\commands\magento-full-loop.md
---
description: Magento 2 完整开发闭环 — 探索→规划→架构→编码→构建验证/Debug→测试→并行审查→Git/PR→文档
---

对于需求 "$ARGUMENTS",严格按以下阶段顺序执行。每个阶段完成后简短汇报状态再进入下一阶段。任何阶段失败，按该阶段标注的重试规则处理，超过重试上限一律停止并汇总完整历史，等待人工确认，不要自行放宽标准硬闯过去。

---

## 阶段 0: Git 分支准备

1. 跑 `git status`，确认工作区干净（如果有未提交改动，先提醒我，不要自动 stash 或丢弃）
2. `git checkout main && git pull`
3. 基于需求生成一个简短的 kebab-case 分支名，跑 `git checkout -b feature/<分支名>`

---

## 阶段 1: 探索与规划

4. 用 @code-explorer 探索这个需求涉及的现有模块、类、layout 文件，输出一份简短的"改动影响范围"清单
5. 用 @planner 基于上面的探索结果，拆解成具体任务和验收标准

---

## 阶段 2: 架构设计

6. 用 @architect 设计：
   - 是新建模块还是扩展现有模块
   - 如涉及数据库变更，设计 db_schema.xml 结构
   - Service Contract 接口定义（Api/ 和 Model/ 的划分）
   - 如需要拦截其他类，判断用 Plugin 还是 Observer，并检查 sortOrder 冲突风险

---

## 阶段 3: 编码实现

7. 按架构方案实现代码，遵循项目 `.claude/rules/ecc/` 下的编码规范
8. 如果本次改动涉及 layout XML / phtml 模板 / RequireJS / Knockout / Less 等主题文件：
   调用 @magento2-theme-builder 处理主题覆盖，完成后自行执行部署步骤
   （清理 var/view_preprocessed → setup:static-content:deploy → cache:flush）

---

## 阶段 4: 构建验证 + Debug 子循环

9. 依次跑：
   ```
   bin/magento setup:di:compile
   bin/magento setup:upgrade --dry-run
   ```
10. 根据结果分流：
    - **编译/部署类报错**（DI compile 失败、schema 冲突、Composer 依赖问题等）
      → 调用 @magento2-build-resolver 修复（只修错，不重构，不擅自优化无关代码）
      → 修复后回到第 9 步重新验证
    - **命令能跑通，但功能行为不符合预期**（结果不对、逻辑错误）
      → 调用 @magento2-bug-investigator 排查根因（会先在 var/log/exception.log 等日志中定位证据，
        禁止不看证据直接猜测修复；修复前会先加一个能复现该bug的回归测试）
      → 根据排查结果回到阶段 3 重新实现相关部分，新增的回归测试保留，纳入阶段5的测试集
    - 重试规则：
      - 第 1 次失败 → 对应 agent 修复，回到验证
      - 第 2 次失败 → 同一 agent 换一个诊断角度再试一次
      - 第 3 次仍失败 → **停止自动重试**，把完整的报错历史（含每次尝试的修复内容和结果）汇总给我，等待人工判断，不要继续第 4 次尝试

---

## 阶段 5: 测试

11. 用 @tdd-guide 补充/运行 PHPUnit 测试：
    ```
    vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist --filter <相关模块>
    ```
12. 测试失败时先判断：
    - 是测试本身写得不对 → 退回 @tdd-guide 重写测试
    - 是代码逻辑真的有 bug → 退回 @magento2-bug-investigator 排查
    - 最多重试 3 次，超过则停止汇总，等待人工确认

---

## 阶段 6: 并行审查

13. 并行调用以下 agent（不要串行等待，同时发起）：
    - @magento2-reviewer — 后端逻辑审查
    - @magento2-frontend-reviewer — 前端改动审查（仅当阶段3涉及主题文件时调用）
    - @security-reviewer — 安全审查
    - @silent-failure-hunter — 检查是否有被静默吞掉的异常/失败
14. 汇总所有审查意见，按 阻断(BLOCK) / 建议(SUGGEST) 分类：
    - 有任何 BLOCK 项 → 退回阶段 3 修改，最多重试 3 次
    - 只有 SUGGEST 项 → 记录下来放进最终摘要，可以继续，不强制处理

---

## 阶段 7: 提交、GitHub PR、文档

15. 用 @doc-updater 更新相关文档 / CHANGELOG（如项目有维护的话）
15.5. 用 @pr-test-analyzer 审查本次改动的测试覆盖质量（不是重跑测试，是评估测试写得够不够全面）：
     - 检查每个改动的功能点是否都有对应测试
     - 检查是否覆盖了边界情况和错误路径
     - 按 critical / important / nice-to-have 分级列出覆盖缺口
     - 如果有 critical 级别缺口，退回 @tdd-guide 补充测试后再继续；
       important/nice-to-have 级别的缺口记录下来放进最终摘要，不阻塞流程
16. 提交代码：
    ```
    git add -A
    git commit -m "feat: <需求简述>

    - <改动点1>
    - <改动点2>

    Generated with ECC magento-full-loop"
    git push -u origin feature/<分支名>
    ```
17. 用 `gh pr create` 创建 PR，PR 正文必须包含：
    - 改动说明摘要
    - 测试结果清单（PHPUnit 通过 / di:compile 通过 / 审查通过情况）
    - 风险提示：是否需要清缓存、是否影响生产索引器、是否需要 theme:reinstall
18. **执行 `git push` 和 `gh pr create` 之前，先把完整的 commit message 和 PR 内容展示给我确认，不要未经确认直接推送到远程仓库**

---

## 最终输出

全流程结束后，给我一份摘要，包含：
- 本次改动涉及的文件清单
- PR 链接
- 阶段 6 里记录的 SUGGEST 级别建议（未强制处理的部分）
- 所有需要人工确认的风险点
