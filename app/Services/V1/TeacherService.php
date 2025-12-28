<?php
namespace App\Services\V1;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TeacherService
{
    public function getAllTeachers()
    {
        return User::role('teacher')->get();
    }

    public function getTeacher(User $teacher): User
    {
        $this->ensureTeacher($teacher);
        return $teacher;
    }

    public function createTeacher(array $data): User
    {
        $teacher = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $teacher->assignRole('teacher');

        return $teacher;
    }

public function updateTeacher(User $teacher, array $data): User
    {
        $this->ensureTeacher($teacher);

        // تحديث جميع الحقول ما عدا كلمة المرور
        $teacher->fill(
            collect($data)->except('password')->toArray()
        );

        // تحديث كلمة المرور فقط عند إرسالها
        if (!empty($data['password'])) {
            $teacher->password = Hash::make($data['password']);
        }

        $teacher->save();

        return $teacher;
    }
    public function deleteTeacher(User $teacher): void
    {
        $this->ensureTeacher($teacher);
        $teacher->delete();
    }

    private function ensureTeacher(User $user): void
    {
        if (!$user->hasRole('teacher')) {
            throw new ModelNotFoundException('المعلم غير موجود');
        }
    }
}
