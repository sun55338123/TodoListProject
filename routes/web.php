<?php

use Illuminate\Support\Facades\Route;

// Vue SPA 入口：除了 API 與 Sanctum 路徑外，所有頁面都交由 Vue Router 處理。
Route::view('/{any?}', 'app')->where('any', '^(?!api|sanctum).*$');
