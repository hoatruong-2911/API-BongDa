<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
// ⬅️ THÊM DÒNG USE NÀY
use Illuminate\Http\Middleware\HandleCors;
use App\Http\Middleware\CheckRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php', // Thường được thêm tự động, nếu thiếu hãy thêm vào
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Đăng ký alias Middleware để có thể gọi bằng tên ngắn 'role'
        $middleware->alias([
            'role' => CheckRole::class, // ⬅️ THÊM DÒNG ALIAS NÀY
        ]);
        
        // ⬅️ THÊM PHẦN NÀY ĐỂ ĐÍNH KÈM MIDDLEWARE CORS VÀO NHÓM 'api'
        $middleware->appendToGroup('api', [
            // Đảm bảo request API được phép từ các nguồn khác (cho Frontend)
            HandleCors::class,
        ]);
        // Bạn có thể thêm các middleware khác vào nhóm 'api' nếu cần (tùy chọn)
        // $middleware->appendToGroup('api', [
        //     \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        // ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
