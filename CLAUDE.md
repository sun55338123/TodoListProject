# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 專案環境

1. 框架：Laravel 12（PHP 8.2 可用的最新主版本。Laravel 13 需要 PHP ^8.3，不可升級）。
2. PHP：8.2.12，來自 XAMPP（`C:\xampp\php\php.exe`）。
3. 資料庫：預設 SQLite（`database/database.sqlite`）。
4. 前端：Vite 7 + Tailwind CSS 4（`resources/css`、`resources/js`）。
5. 測試框架：PHPUnit（`tests/Feature`、`tests/Unit`）。

## 常用指令

1. 初始化：`composer setup`（安裝相依套件、產生 Key、Migrate、建置前端）。
2. 開發：`composer dev`（同時啟動 `artisan serve`、Queue、Pail Log、Vite）。
3. 測試：`composer test` 或 `php artisan test`。
   * 單一檔案：`php artisan test tests/Feature/ExampleTest.php`
   * 單一方法：`php artisan test --filter=test_the_application_returns_a_successful_response`
4. 程式碼格式：`vendor/bin/pint`（只檢查不修改：`vendor/bin/pint --test`）。
5. 前端建置：`npm run build`。

## 回答規則

所有回覆必須遵守 [rule.md](rule.md) 的 Global Rules（產品先生 Mode）。以下匯入完整內容：

@rule.md

## Code 規則

以下規則由 rule.md 中適用於程式開發的條款延伸而來。

1. 錯誤回報（Fail fast）
   * 建置、測試、執行失敗時，一律以 Status + Root Cause + Suggested Fix 格式回報。
   * 不得在未說明的情況下略過失敗步驟。
2. 不可逆操作前確認
   * 刪除檔案、覆寫檔案、`git push --force`、`git reset --hard`、資料庫 Migration 等動作，執行前必須列出假設並取得確認。
3. 拒絕腦補
   * 不得使用未驗證存在的 API、套件、版本或指令。使用前需先查證程式碼或文件。
   * 需求資訊不足時，明確指出缺口，不自行補完規格。
4. 範圍控制
   * 只修改被要求的部分。不主動新增未被要求的功能、重構或延伸修改。
   * 未被要求時，不在回覆中附加額外建議。
5. 命名與文字
   * 技術名詞遵循官方拼寫與大小寫（GitHub、TypeScript、Next.js），禁止 Ts、h5、RJS 等非官方縮寫。
   * 程式碼、註解、Commit Message 中禁止使用 Emoji。
   * 中文註解與文件遵循 rule.md 第五節繁體中文排版規範。
