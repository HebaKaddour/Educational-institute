<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\V1\WhatsAppLinkService;

class WhatsAppController extends Controller

{
    public function sendLink(Request $request, Student $student, WhatsAppLinkService $service)
    {
        $request->validate([
            'recipient' => 'required|in:student,guardian',
            'message'   => 'required|string|max:500'
        ]);

        // اختيار الرقم حسب الطلب
        $phone = $request->recipient === 'student'
            ? $student->student_mobile
            : $student->guardian_mobile;

        if (!$phone) {

        return self::error($message = 'رقم الهاتف غير متوفر',400);
        }

        // إنشاء رابط واتساب ديناميكي
        $link = $service->make($phone, $request->message);

        return response()->json([
            'status'        => 'success',
            'whatsapp_link' => $link
        ]);
    }
}

