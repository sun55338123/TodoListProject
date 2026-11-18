# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 專案環境

1. 框架：Laravel 12（PHP 8.2 可用的最新主版本。Laravel 13 需要 PHP ^8.3，不可升級）。
2. PHP：8.2.12，來自 XAMPP（`C:\xampp\php\php.exe`）。
3. 資料庫：MySQL 相容的 MariaDB 10.4.32（XAMPP 內建），資料庫名稱 `todo_list`（utf8mb4）。
   * MariaDB 10.4 預設沒有啟用 performance_schema，`php artisan db:show` 會失敗。查看資料庫狀態改用 `php artisan migrate:status`。
4. 前端：Vue + Vite 7 + Tailwind CSS 4（`resources/css`、`resources/js`）。
   * 架構為前後端分離 SPA：Vue SPA 透過 API 與 Laravel 溝通。
   * SPA 放在同一個 Laravel 專案的 `resources/js`，由 Vite 建置，同網域部署。
   * 驗證使用 Sanctum SPA Authentication（Cookie Session）。登入狀態由伺服器端 Session 保存，前端不保存 Token（不使用 localStorage 存放憑證）。
5. 測試框架：PHPUnit（`tests/Feature`、`tests/Unit`）。

## 產品需求

本專案為每日 TodoList 系統。不實作後台管理介面。

### 目前開發範圍

1. 第一階段只實作：帳號、個人每日清單、個人週目標。
2. 團體功能（第 4 節）及所有與團體相關的規格（團體任務、團體週目標）暫緩。資料表與程式不得預先實作團體功能。
3. 第 2 節第 4、5 項的「團體任務項目」在第一階段不實作。

### 1. 帳號

1. 開放使用者自行註冊。
2. 使用帳號密碼登入，不實作第三方登入。
3. 每位使用者的個人資料彼此隔離。

### 2. 每日清單（個人）

1. 每日清單屬於個人，不在團體內共享。
2. 使用者可以新增項目，並標記完成或未完成。
3. 可以查看與修改過去日期的清單。
4. 項目來源有兩種：
   * A. 使用者自行建立的個人項目。
   * B. 從所屬團體的任務加入。
5. 未完成項目的處理：
   * A. 個人項目：不延到隔天。
   * B. 團體任務項目：未完成時延到隔天。

6. 個人每日任務分為兩種：
   * A. 單日任務：未掛勾週目標。只出現在建立的那一天，未完成也不延到隔天。
   * B. 週目標子任務：掛勾一個週目標。未完成時每天持續顯示，直到完成為止（可跨週）。
   * 完成狀態一律由使用者自行打勾。

### 3. 週目標

1. 一週從星期一開始。
2. 週目標為項目式清單：使用者在每週建立多個目標項目。
3. 使用者可以設定個人週目標。
4. 週目標與每日任務的掛勾：
   * A. 在每日清單新增任務時，可以選擇掛勾當週的一個週目標。
   * B. 在週目標頁面，可以直接為目標新增子任務，子任務會加入每日清單。
   * C. 掛勾後，該每日任務即為週目標的子任務。
5. 週目標的完成方式：
   * A. 沒有子任務：由使用者自行打勾。
   * B. 有子任務：顯示子任務完成百分比，子任務全部完成時自動完成，不能手動打勾。之後再新增子任務會自動變回未完成。
6. 本週整體完成率：已完成目標數 / 目標總數。
7. 刪除週目標時，其子任務一併刪除。
8. 團體週目標在團體內共享（暫緩）。

### 4. 團體（暫緩）

1. 使用者可以建立團體，建立者成為 owner。
2. 成員角色分為 owner 與 member。
3. 加入方式：
   * A. 邀請：owner 可以邀請使用者加入。
   * B. 申請：使用者可以申請加入團體。
4. 團體內只共享週目標。成員的個人每日清單不共享。
5. 團體提供的任務可以被成員加入自己的每日清單。

### 待確認事項

以下規格尚未定義。實作前必須先向使用者確認，不得自行假設：

團體階段（暫緩）：

1. 團體任務與週目標的關係
   * 「團體任務」是否等同於團體週目標，或是週目標底下的子項目。
2. 團體週目標的數值進度
   * 進度以每位成員分開計算，或以整個團體合計。
3. owner 與 member 的權限
   * 誰可以建立、修改、刪除週目標與團體任務。
   * member 是否也能邀請他人。
   * 誰可以核准加入申請。
   * owner 是否可以移除成員、轉移 owner 身分，以及團體可否有多個 owner。
4. 團體任務延到隔天
   * 持續延到完成為止，或只延到該週結束。

## 架構

### 後端（`app/`）

請求流程：`routes/api.php` → FormRequest 驗證 → Controller → Service 介面 → Repository 介面 → Eloquent Model → Resource 輸出。

1. `Contracts/Services/`、`Contracts/Repositories/`：介面。Controller 只依賴 Service 介面，Service 只依賴 Repository 介面。所有綁定集中在 `AppServiceProvider::$bindings`。
2. `Services/`：商業邏輯（`DailyTaskService`、`WeeklyGoalService`、`UserRegistrationService`），以及登入狀態管理 `SessionAuthenticator`（web guard）。
3. `Repositories/`：資料存取，只有這一層直接操作 Eloquent 查詢（`Eloquent*Repository`）。
4. `app/` 內不使用 Facade（`Auth::`、`Gate::`、`DB::`）。需要時注入 Contract，例如 `Illuminate\Contracts\Auth\Access\Gate`、`AuthenticatorInterface`。
5. `Policies/`：資源擁有者檢查（依命名慣例自動註冊），Controller 以注入的 `Gate` 呼叫 `authorize()`。
6. `Http/Resources/`：輸出格式。週目標清單使用 `WeeklyGoalCollection`，在 `meta` 附上週範圍與完成進度。
7. Service 的單元測試以 Mock Repository 撰寫，不需要資料庫（參考 `tests/Unit/WeeklyGoalServiceTest.php`）。
8. `Support/Week`：以星期一為起點的週範圍。所有週計算都必須經過它，不可直接呼叫 `startOfWeek()`（預設起始日受 Locale 影響）。
9. `Support/CompletionRate`：完成率計算（已完成數 / 總數，四捨五入為整數百分比，總數 0 時為 0）。前端 `utils/completion.js` 使用相同規則，修改時兩邊必須一致。
10. 日期一律由前端傳入使用者當地日期（`Y-m-d`），後端不使用伺服器的「今天」，避免時區偏移（`config/app.php` 時區為 UTC）。
11. `weekly_goals.week_start` 一律儲存該週星期一，由 `WeeklyGoalService` 正規化。
12. `daily_tasks.weekly_goal_id` 為 null 時是單日任務，有值時是週目標子任務。`daily_tasks.completed_on` 記錄實際完成的日期，子任務延續顯示的判斷依賴此欄位。
13. `WeeklyGoalRepositoryInterface` 回傳的目標一律已載入 `subtasks`，Resource 與 `isAchieved()` 依賴此關聯。

### API（`/api`，Sanctum Cookie Session）

1. 驗證：`POST /register`、`POST /login`（每分鐘 6 次限制）、`POST /logout`、`GET /user`。
2. 每日清單：`GET /daily-tasks?date=`、`POST /daily-tasks`、`PATCH /daily-tasks/{id}`、`DELETE /daily-tasks/{id}`。
   * `GET`：回傳該日的單日任務，加上延到該日的週目標子任務（任務日期不晚於該日，且未完成或在該日完成）。
   * `POST`、`PATCH` 可帶 `weekly_goal_id`（可為 null）。週目標必須屬於使用者，且為任務日期所在的那一週，由 `DailyTaskService` 檢查。
   * `PATCH` 打勾時帶 `completed_on`（使用者正在檢視的日期），未帶時使用任務日期。取消打勾時清除 `completed_on`。
3. 週目標：`GET /weekly-goals?date=`（date 為該週任一天，`meta` 含完成數、總數、百分比）、`POST`、`PATCH`、`DELETE`。
   * 每個目標含 `subtasks`、`has_subtasks`、`subtask_completed`、`subtask_total`、`subtask_percentage`。
   * 有子任務時，`is_completed` 為自動計算的結果（`WeeklyGoal::isAchieved()`），`PATCH` 修改 `is_completed` 或 `week_start` 會回傳 422。
   * 刪除目標時，子任務由外鍵 cascade 一併刪除。
4. 前端登入與註冊前必須先呼叫 `GET /sanctum/csrf-cookie`。
5. 功能測試中，需要 Session 的請求（登入、註冊）必須帶 `Referer: http://localhost` 標頭，Sanctum 才會視為 SPA 請求。

### 前端（`resources/js/`）

1. `routes/web.php` 將 `api`、`sanctum` 以外的路徑都導向 `resources/views/app.blade.php`，由 Vue Router 處理。
2. `api/`：依資源分檔的 API 函式，共用 `api/http.js` 的 axios 實例。
3. `stores/auth.js`：登入狀態（Vue reactive，不使用 Pinia），Router `beforeEach` 依此做頁面保護。
4. `composables/useChecklist.js`：每日清單與週目標共用的清單操作，透過注入 API 函式運作。新增同類清單時重用此 composable 與 `components/ChecklistPanel.vue`。
   * 打勾、編輯、刪除採用「先更新畫面」：立即修改本地資料，請求失敗時還原並顯示錯誤，成功後不重新載入清單。
   * 新增項目需要伺服器產生的 id，等回應後才加入清單。
   * 頁面上的完成統計由 `utils/completion.js` 依目前清單即時計算，不使用 API 的 `meta`。
   * `ChecklistPanel` 以函式 prop（`badgesFor`、`lockedReasonFor`、`editPickerFor`）、`addPicker` 與 `item-extra` slot 擴充各頁面的差異，不在共用元件中寫入特定頁面的邏輯。
   * 修改標題使用 `rename`（先更新畫面）。修改掛勾使用 `save`（等伺服器回應再套用），因為伺服器會連帶改變 `weekly_goal` 與 `task_date`。
   * 每日清單將延續過來的未完成任務改為單日任務時，會把 `task_date` 改為目前檢視的日期，否則任務會從目前的清單消失。
   * 這個專案沒有前端測試框架。上述行為只用臨時腳本驗證過，沒有留下可重複執行的測試。
5. `utils/date.js`：當地時區日期處理。不可使用 `new Date('Y-m-d')`（會被解析為 UTC）。

## 常用指令

1. 初始化：`composer setup`（安裝相依套件、產生 Key、Migrate、建置前端）。
2. 開發：`composer dev`（同時啟動 `artisan serve`、Queue、Vite）。
   * 已從指令中移除 Pail：Pail 需要 pcntl 擴充套件，Windows 版 PHP 沒有，會導致整個指令結束。
   * 這是常駐指令，啟動後會持續執行，以 `Ctrl + C` 停止。
3. 測試：`composer test` 或 `php artisan test`。
   * 單一檔案：`php artisan test tests/Feature/ExampleTest.php`
   * 單一方法：`php artisan test --filter=test_the_application_returns_a_successful_response`
4. 程式碼格式：`vendor/bin/pint`（只檢查不修改：`vendor/bin/pint --test`）。
5. 前端建置：`npm run build`。
6. 部署（server：Ubuntu 24.04、Nginx、PHP 8.2 FPM、Node.js 24，網址 `https://todo.do-work.dev`，專案路徑 `/var/www/todo_list/html/TodoListProject`，檔案擁有者 `www-data`）：
   * 前端在 server 建置：`sudo -u www-data env npm_config_cache=/var/www/todo_list/.npm-cache npm ci`，再執行同樣前綴的 `npm run build`。www-data 的家目錄 `/var/www` 無法寫入，因此必須指定 npm 快取目錄。
   * 備用方式：`.\scripts\deploy-frontend.ps1 -KeyPath "金鑰路徑"`（本機建置、打包、上傳並替換 server 的 `public/build`）。`-SkipUpload` 只建置與打包。
   * server 上所有 `composer`、`php artisan`、`npm` 指令都以 `sudo -u www-data` 執行，避免產生 root 擁有的檔案。
   * 修改 `.env` 後必須執行 `php artisan config:cache` 才會生效。
   * `.ps1` 檔案必須存成 UTF-8 with BOM。Windows PowerShell 5.1 會把沒有 BOM 的 UTF-8 檔案當成系統編碼讀取，中文會變成亂碼並造成語法錯誤。

## 回答規則

所有回覆必須遵守 [rule.md](rule.md) 的 Global Rules（產品先生 Mode）。以下匯入完整內容：

@rule.md

## Code 規則

1. SOLID 原則
   * 撰寫程式時必須遵守 SOLID 原則：單一職責、開放封閉、里氏替換、介面隔離、依賴反轉。
   * Controller 只負責接收請求與回傳回應，商業邏輯放在獨立類別中。
   * 依賴透過建構子注入，並依賴介面而不是具體實作，透過 Laravel Service Container 綁定。

以下第 2 至 6 項由 rule.md 中適用於程式開發的條款延伸而來。

2. 錯誤回報（Fail fast）
   * 建置、測試、執行失敗時，一律以 Status + Root Cause + Suggested Fix 格式回報。
   * 不得在未說明的情況下略過失敗步驟。
3. 不可逆操作前確認
   * 刪除檔案、覆寫檔案、`git push --force`、`git reset --hard`、資料庫 Migration 等動作，執行前必須列出假設並取得確認。
4. 拒絕腦補
   * 不得使用未驗證存在的 API、套件、版本或指令。使用前需先查證程式碼或文件。
   * 需求資訊不足時，明確指出缺口，不自行補完規格。
5. 範圍控制
   * 只修改被要求的部分。不主動新增未被要求的功能、重構或延伸修改。
   * 未被要求時，不在回覆中附加額外建議。
6. 命名與文字
   * 技術名詞遵循官方拼寫與大小寫（GitHub、TypeScript、Next.js），禁止 Ts、h5、RJS 等非官方縮寫。
   * 程式碼、註解、Commit Message 中禁止使用 Emoji。
   * 中文註解與文件遵循 rule.md 第五節繁體中文排版規範。
