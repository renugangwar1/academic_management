<?php
namespace App\Traits;

use App\Models\StudentSessionHistory;

trait HasSessionHistory
{
    public function logSessionHistory(string $promotionType = 'auto'): void
    {
        StudentSessionHistory::create([
            'student_id'          => $this->id,
            'academic_session_id' => $this->academic_session_id,
            'program_id'          => $this->program_id,
            'institute_id'        => $this->institute_id,
            'semester'            => $this->semester,
            'promotion_type'      => $promotionType,
            'promoted_by'         => auth()->id(),
            'promoted_at'         => now(),
        ]);
    }
}
