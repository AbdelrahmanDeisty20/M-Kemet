<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompany
{
    /**
     * يمنع أي مستخدم من غير نوع "company" من الوصول إلى مسارات الشركات.
     * يُسمح فقط للمستخدمين من نوع "company".
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

        if ($user->user_type !== 'company') {
            return response()->json([
                'status'  => false,
                'message' => __('messages.companyOnly'),
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
