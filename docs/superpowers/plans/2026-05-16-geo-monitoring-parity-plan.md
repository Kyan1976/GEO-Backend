# GEO Monitoring Parity Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement GEO monitoring capabilities 1-6, excluding optimization recommendations: citation/source monitoring, stronger SOV, ranking/position, prompt coverage reporting, project-level scheduled monitoring, and alert triggering.

**Architecture:** Keep the current project-centric GEO system. Add small focused services for citation extraction and alert evaluation, extend existing visibility metrics with citation/ranking fields, and surface aggregated metrics in existing dashboard/report/alerts pages. Maintain mainland platform scope: Doubao and DeepSeek.

**Tech Stack:** Node.js/Express, Sequelize/SQLite, Next.js 16, Ant Design, node:test.

---

### Scope

Included:
- Citation/source extraction and source counters.
- SOV based on weighted visibility score, not only raw mention count.
- Brand recommendation and rank/position metadata.
- Prompt category coverage aggregation.
- Project-level schedule settings and scheduler execution.
- Alert rule evaluation after each run.

Excluded:
- Optimization recommendations/content action plans.
- External citation crawler enrichment.
- New third-party AI platforms.

### Files

- Modify: `backend/models/VisibilityMetric.js` for citation, recommendation, score, and category fields.
- Modify: `backend/models/BrandProject.js` for project schedule settings.
- Modify: `backend/app.js` for existing DB column checks.
- Modify: `backend/services/VisibilityAnalysisService.js` for weighted SOV, recommendation, and ranking.
- Create: `backend/services/CitationAnalysisService.js` for URL/domain extraction.
- Create: `backend/services/AlertEvaluationService.js` for enabled rule evaluation.
- Modify: `backend/services/ProjectRunService.js` to store new metrics and evaluate alerts.
- Modify: `backend/services/SchedulerService.js` to run project schedules from `BrandProject`.
- Modify: `backend/services/ProjectMetricsService.js` for coverage/citation/recommendation aggregates.
- Modify: `backend/routes/geoProjects.js` for project schedule fields and dashboard/report summaries.
- Modify: `nextjs-frontend/src/app/geo/projects/page.tsx` for schedule controls.
- Modify: `nextjs-frontend/src/app/geo/project-dashboard/page.tsx` for new metric cards/tables.
- Modify: `nextjs-frontend/src/app/geo/reports/page.tsx` for report output and CSV.
- Modify: `nextjs-frontend/src/app/geo/alerts/page.tsx` for latest trigger details.
- Add tests under `backend/tests/`.

### Tasks

- [x] Add failing tests for citation extraction from URLs/domains and platform metadata.
- [x] Implement `CitationAnalysisService.extractSources`.
- [x] Add failing tests for weighted visibility: brand rank, recommendation flag, visibility score, and SOV.
- [x] Update `VisibilityAnalysisService` to compute weighted entity scores and ranking metadata.
- [x] Extend `VisibilityMetric` and `app.js` schema migration with new nullable/defaulted fields.
- [x] Wire citation/ranking fields into `ProjectRunService.createVisibilityMetric`.
- [x] Add prompt category derivation from prompt tags and store `prompt_category`.
- [x] Extend `ProjectMetricsService.summarize` and `buildTrend` with citation rate, recommendation rate, average rank, and category coverage.
- [x] Add project schedule fields and UI controls: enabled, daily time.
- [x] Extend `SchedulerService` to run enabled brand projects with project platforms and enabled prompts.
- [x] Implement `AlertEvaluationService.evaluateProject` for visibility drop, competitor ahead, negative sentiment, and task failure.
- [x] Call alert evaluation after project/manual/scheduled runs.
- [x] Update dashboard and report pages to show citation, recommendation, rank, and category coverage.
- [x] Run `npm --prefix backend test`, `npm run lint`, and `npm run build`.
- [x] Restart dev server and verify key pages load.

### Completion Criteria

- Backend tests cover citation extraction, weighted SOV/ranking, category aggregation, and alert evaluation.
- New project runs persist citation/rank/recommendation/category fields.
- Dashboard and reports expose the new metrics.
- Project creation/edit supports automatic monitoring schedule.
- Alerts update `last_triggered_at` and trigger detail fields when thresholds are met.
- Verification commands pass with no new errors.
