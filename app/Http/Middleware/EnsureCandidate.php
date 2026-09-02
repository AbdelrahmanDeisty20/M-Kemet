<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCandidate
{
    /**
     * يمنع أي مستخدم من نوع "company" من الوصول إلى مسارات الباحث عن عمل.
     * يُسمح فقط للمستخدمين من نوع "candidate".
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.unauthenticated'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->user_type !== 'candidate') {
            return response()->json([
                'status'  => false,
                'message' => __('messages.candidateOnly'),
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
