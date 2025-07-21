<?php

namespace App\Services\Grading;

class CourseGrade
{
    public float $credit;
    public int $internal;
    public int $external;
    public int $attendance;
    public int $total;
    public int $gradePoint;
    public string $gradeLetter;
    public string $status;

    public function __construct(float $credit, int $internal, int $external, int $attendance, int $externalPassingMarks = 30)
    {
        $this->credit     = $credit;
        $this->internal   = $internal;
        $this->external   = $external;
        $this->attendance = $attendance;
        $this->total      = $internal + $external + $attendance;

        $this->gradePoint  = $this->calculateGradePoint($this->total);
        $this->gradeLetter = $this->calculateGradeLetter($this->gradePoint);

        // 🟥 Apply failure rule if external is below passing
        if ($external < $externalPassingMarks) {
            $this->gradePoint  = 0;
            $this->gradeLetter = 'F';
            $this->status      = 'FAIL';
        } else {
            $this->status = $this->gradePoint === 0 ? 'FAIL' : 'PASS';
        }
    }

    private function calculateGradePoint(int $total): int
    {
        return match (true) {
            $total >= 95      => 9,
            $total >= 84.99   => 8,
            $total >= 74.99   => 7,
            $total >= 64.99   => 6,
            $total >= 54.99   => 5,
            $total >= 44.99   => 4,
            $total >= 34.99   => 3,
            $total >= 24.99   => 2,
            $total >= 14.99   => 1,
            default           => 0,
        };
    }

    private function calculateGradeLetter(int $gradePoint): string
    {
        return match ($gradePoint) {
            9 => 'A+',
            8 => 'A',
            7 => 'A−',
            6 => 'B+',
            5 => 'B',
            4 => 'B−',
            3 => 'C+',
            2 => 'C',
            1 => 'C−',
            default => 'F',
        };
    }
}
