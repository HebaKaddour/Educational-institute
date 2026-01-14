<?php
namespace App\Services\V1;

use App\Models\workingDay;
use App\Enums\SchedulerDay;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class SettingsSchedulerService {


    //create working day
    public function creatWorkingDay(array $data) : Collection{
return DB::transaction(function () use ($data) {

        $result = [];

        foreach ($data['days'] as $index => $day) {

            // تحقق من أن اليوم موجود ضمن Enum
            $dayEnum = collect(SchedulerDay::cases())
                ->first(fn($case) => $case->value === $day['day_name']);

            if (!$dayEnum) {
                throw ValidationException::withMessages([
                    "days.$index.day_name" => "اسم اليوم غير صالح: {$day['day_name']}"
                ]);
            }

            // تحقق إذا يوجد نفس اليوم لأي جنس
            $existingDay = WorkingDay::where('day_name', $dayEnum->value)->first();

            if ($existingDay) {
                if ($existingDay->gender === $day['gender']) {
                    // نفس اليوم والجنس → خطأ تكرار
                    throw ValidationException::withMessages([
                        "days.$index" => "اليوم '{$day['day_name']}' مضاف مسبقًا لنفس الجنس"
                    ]);
                } else {
                    // نفس اليوم ولكن جنس مختلف → حدث الجنس
                    $existingDay->gender = $day['gender'];
                    $existingDay->updated_at = now();
                    $existingDay->save();
                    $result[] = $existingDay;
                    continue;
                }
            }

            // إذا اليوم غير موجود → إنشاء جديد
            $workingDay = WorkingDay::create([
                'day_name' => $dayEnum->value,
                'gender'   => $day['gender'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $result[] = $workingDay;
        }

        return WorkingDay::whereIn('id', collect($result)->pluck('id'))->get();
    });
    }

    //get all working days with sessions
    public function getAllWorkingDays(){
        return workingDay::with('sessions')->get();
    }

    //update working day
    public function updateWorkingDay(workingDay $workingDay, array $data): workingDay{
        $workingDay->update($data);
        return $workingDay;
    }

    //get schedule by gender
    public function getScheduleByGender(string $gender){
        return workingDay::with('sessions')->where('gender', $gender)->get();

}
}
