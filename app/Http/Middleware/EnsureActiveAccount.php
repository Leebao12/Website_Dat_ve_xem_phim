<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        if($request->user() && $request->user()->status!=='hoat_dong') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error','Tài khoản đã bị khóa.');
        }
        return $next($request);
    }
}
