# GEO Visibility Phase 1-2 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Upgrade the current AI answer detection tool into a China-mainland GEO visibility workflow for Doubao and DeepSeek, covering brand projects, competitors, prompt tracking, visibility metrics, project dashboards, basic reports, and alert rules.

**Architecture:** Add project-level business entities on the backend, attach detection records to optional project and prompt IDs, and persist normalized visibility metrics after each AI answer completes. The frontend gets new GEO user pages for project management, prompt library, project dashboard, report snapshots, and alert setup, reusing Ant Design and existing axios auth.

**Tech Stack:** Express 5, Sequelize, SQLite, Node `node:test`, Next.js App Router, React, Ant Design, @ant-design/plots.

---

## Scope

- Included platforms: `doubao`, `deepseek`.
- Excluded from this implementation: language/region fields, GA/GSC/Ads integration, PDF export, team permissions, CRM/lead attribution, large prompt database.
- Existing partial progress to preserve:
  - `backend/services/VisibilityAnalysisService.js`
  - `backend/services/ProjectMetricsService.js`
  - `backend/routes/geoProjects.js`
  - new project/competitor/prompt/metric/report/alert models
  - backend tests under `backend/tests/`

## Task 1: Finish Detection-To-Metric Integration

**Files:**
- Modify: `backend/routes/detection.js`
- Modify: `backend/services/SchedulerService.js`
- Use: `backend/services/VisibilityAnalysisService.js`
- Use: `backend/models/index.js`

- [ ] Accept optional `project_id` and `prompt_id`/`tracked_prompt_id` in `POST /api/detection/create` and `GET /api/detection/stream`.
- [ ] When `project_id` is present, verify the project belongs to the authenticated user or the user is admin.
- [ ] If `prompt_id` is present, verify it belongs to the same project.
- [ ] Store `project_id` and `tracked_prompt_id` on `QuestionRecord`.
- [ ] After an AI response is saved, load `BrandProject` and its `BrandCompetitor` rows, call `VisibilityAnalysisService.analyzeResponse`, and create one `VisibilityMetric`.
- [ ] Keep existing non-project detection behavior unchanged.
- [ ] Add/adjust backend tests where feasible for the pure metric path.
- [ ] Run `npm --prefix backend test`.

## Task 2: Harden Project APIs

**Files:**
- Modify: `backend/routes/geoProjects.js`
- Modify: `backend/models/index.js`

- [ ] Ensure all nested project resources enforce project ownership.
- [ ] Add update endpoint for competitors.
- [ ] Add delete endpoint for alert rules.
- [ ] Ensure prompt platforms are restricted to `doubao` and `deepseek`.
- [ ] Ensure dashboard and report endpoints return stable empty-state summaries when no metrics exist.
- [ ] Run `npm --prefix backend test`.

## Task 3: Add GEO Project Frontend

**Files:**
- Modify: `nextjs-frontend/src/app/geo/layout.tsx`
- Create: `nextjs-frontend/src/app/geo/projects/page.tsx`

- [ ] Add sidebar entry `品牌项目`.
- [ ] Build project list with create/edit/archive actions.
- [ ] Show competitors and tracked prompt counts in the table.
- [ ] Provide modal forms for brand name, aliases, website, industry, primary keywords, and competitors.
- [ ] Use only functional, work-focused Ant Design layout; no marketing hero.

## Task 4: Add Prompt Library Frontend

**Files:**
- Create: `nextjs-frontend/src/app/geo/prompts/page.tsx`

- [ ] Let users select a brand project.
- [ ] List prompts for the selected project.
- [ ] Create/edit/delete prompts with question, tags, enabled flag, and platforms limited to 豆包/DeepSeek.
- [ ] Provide quick templates for purchase decision, comparison, substitute, pricing, and product-fit questions.

## Task 5: Add Project Dashboard, Report, And Alert Frontend

**Files:**
- Create: `nextjs-frontend/src/app/geo/project-dashboard/page.tsx`
- Create: `nextjs-frontend/src/app/geo/reports/page.tsx`
- Create: `nextjs-frontend/src/app/geo/alerts/page.tsx`
- Modify if useful: `nextjs-frontend/src/app/geo/dashboard/page.tsx`

- [ ] Project dashboard shows total checks, brand mention rate, average Share of Voice, platform comparison, competitor mentions, trend, and recent metrics.
- [ ] Report page generates latest 30-day report snapshot and displays summary data in a printable web view.
- [ ] Alert page lists and creates alert rules for visibility drop, competitor ahead, negative sentiment, and task failure.
- [ ] Keep visuals dense and operational, consistent with existing Ant Design app.

## Task 6: Verification And Cleanup

**Files:**
- Review all changed backend and frontend files.

- [ ] Run `npm --prefix backend test`.
- [ ] Run `npm run lint`.
- [ ] Run `npm run build`.
- [ ] Fix blocking errors.
- [ ] Report remaining warnings or intentionally deferred items.
