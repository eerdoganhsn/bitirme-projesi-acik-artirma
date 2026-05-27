<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSeller
{
    /**
     * Gelen isteği kontrol et.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Kullanıcı giriş yapmış mı VE 2. Kullanıcı bir satıcı mı?
        if (auth()->check() && auth()->user()->is_seller) {
            // Her şey yolunda, içeri geçebilirsin.
            return $next($request);
        }

        // Eğer satıcı değilse (veya giriş yapmamışsa) anasayfaya şutla ve hata mesajı ver
        return redirect('/')->with('error', 'Bu alana erişim yetkiniz bulunmamaktadır.');
    }
}