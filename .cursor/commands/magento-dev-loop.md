# .claude\commands\magento-dev-loop.md
---
description: Magento 2 标准主题完整开发闭环
---

对于需求,按顺序执行,失败退回上一步,最多重试3次:

1. @planner 拆解需求和验收标准
2. @architect 设计模块结构、Service Contract、数据库schema变更(如涉及)
3. 实现代码

4. 如果改动涉及主题文件(layout XML/phtml模板/RequireJS/Knockout/Less):
   调用 @magento2-theme-builder 处理主题覆盖,并在完成后自行执行部署步骤
   (clear var/view_preprocessed → setup:static-content:deploy → cache:flush)

5. 跑 bin/magento setup:di:compile 和 setup:upgrade 验证
   如果报错,调用 @magento2-build-resolver 定位并修复(只修错,不重构)
   修复后回到步骤3重新验证

6. @tdd-guide 补充并运行 PHPUnit 测试

7. 并行调用:
   - @magento2-reviewer(后端逻辑审查)
   - @magento2-frontend-reviewer(如涉及前端改动)
   - @security-reviewer
   - @silent-failure-hunter

8. 任一审查标记阻断项(BLOCK),汇总问题清单,退回步骤3,最多重试3次

9. 全部通过后,@doc-updater 更新相关文档

10. 输出改动摘要 + 需要人工确认的风险点(是否需清缓存/影响生产索引器/是否需要 theme:reinstall)

需求: $ARGUMENTS