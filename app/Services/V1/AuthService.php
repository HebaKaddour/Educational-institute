<?php
namespace App\Services\V1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


class AuthService{

public function login(array $credentials): ?array
    {
        if (!Auth::attempt($credentials)) {
         return null;
        }
        $user = Auth::user();
        $token = $user->createToken('token')->plainTextToken;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles, // موجود
            'permissions' => $user->getAllPermissions()->pluck('name'), // جميع الصلاحيات
            'token' => $token
        ];
    }
    public function changePassword(string $currentPassword, string $newPassword): void
    {
        $user = Auth::user();

        if (!$user) {
            throw ValidationException::withMessages([
                'user' => ['المستخدم غير موجود']
            ]);
        }

        // التحقق من كلمة المرور الحالية
        if (!Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['كلمة المرور الحالية غير صحيحة']
            ]);
        }

        // تحديث كلمة المرور
        $user->password = Hash::make($newPassword);
        $user->save();
    }


public function logout(): bool
{
    $user = Auth::user();
    if ($user) {
        // حذف التوكن الحالي فقط
        $user->currentAccessToken()->delete();
        return true;
    }
    return false;
}


}

