<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        abort_unless($request->user() && $request->user()->role === $role && $request->user()->status === 'hoat_dong',403,'Bạn không có quyền truy cập khu vực này.');
        return $next($request);
    }
}
