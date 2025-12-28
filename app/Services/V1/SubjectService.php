<?php
namespace App\Services\V1;

use App\Models\Subject;

class SubjectService{
    public function getAllSubjects(){

        return Subject::with('teacher')->get();
    }

public function getSubject(Subject $subject)
    {
        return $subject->load('teacher');
    }

    public function createSubject(array $data): Subject
    {
        return Subject::create($data);
    }

    public function updateSubject(Subject $subject, array $data): Subject
    {
        $subject->update($data);
        return $subject;
    }

    public function deleteSubject(Subject $subject): bool
    {
        return $subject->delete();
    }
}
