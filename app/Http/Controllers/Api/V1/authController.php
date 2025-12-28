<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\V1\AuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Auth\AuthRequest;
use App\Http\Requests\V1\Auth\ChangePasswordRequest;

class authController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(AuthRequest $request){
    $credentials = $request->only(['email', 'password']);
    $result = $this->authService->login($credentials);
    if (!$result) {
        return self::error('بيانات تسجيل الدخول غير صحيحة', 401);
    }
   return self::success($result, 'تم تسجيل الدخول بنجاح', 200);

    }

    public function logout()
    {
        if ($this->authService->logout()) {
            return self::success(null, 'تم تسجيل الخروج بنجاح', 200);
        }
        return self::error('حدث خطأ أثناء تسجيل الخروج', 400);
    }

    // Change Password
public function changePassword(ChangePasswordRequest $request)
    {
        $this->authService->changePassword(
            $request->current_password,
            $request->new_password
        );

        return self::success(null, 'تم تغيير كلمة المرور بنجاح');
    }
}
