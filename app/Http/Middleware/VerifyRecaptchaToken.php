<?php

namespace App\Http\Middleware;

use App\Exceptions\RecaptchaRequestFailed;
use App\Exceptions\RecaptchaVerificationFailed;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class VerifyRecaptchaToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $threshold = null): Response
    {
        $response = Http::asForm()
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('recaptcha.secret'),
                'response' => $request->recaptcha_token,
                'reomteip' => $request->ip()
            ])->object();

        if ($response->success === false) {
            throw new RecaptchaRequestFailed();
        }

        if ($response->score <= $threshold ?? config('recaptcha.threshold')) {
            throw new RecaptchaVerificationFailed();
        }

        return $next($request);
    }
}
