<?php

namespace App\Services\Grading;

class SemesterGpa
{
    /** @param CourseGrade[] $courses */
    public static function sgpa(array $courses): float
    {
        $totalCredits = 0;
        $totalPoints  = 0;

        foreach ($courses as $course) {
            $totalCredits += $course->credit;
            $totalPoints  += $course->credit * $course->gradePoint;
        }

        if ($totalCredits === 0) return 0;

        return round($totalPoints / $totalCredits, 2);
    }

    public static function letter(float $gp, array $courses = []): string
    {
        // Rule: If any course failed (gradePoint 0), grade letter = Nil
        if (!empty($courses) && collect($courses)->contains(fn($c) => $c->gradePoint === 0)) {
            return 'Nil';
        }

        return match (true) {
            $gp >= 9.0 => 'A+',
            $gp >= 8.0 => 'A',
            $gp >= 7.0 => 'A-',
            $gp >= 6.0 => 'B+',
            $gp >= 5.0 => 'B',
            $gp >= 4.0 => 'B-',
            $gp >= 3.0 => 'C+',
            $gp >= 2.0 => 'C',
            $gp >= 1.0 => 'C-',
            default    => 'F',
        };
    }

    /**
     * Correct CGPA calculation
     * @param float $prevPoints Total (previous SGPA × previous credits)
     * @param float $prevCredits Previous total credits
     * @param float $thisPoints Current semester total points (SGPA × current credits)
     * @param float $thisCredits Current semester credits
     */
   public static function cgpa(array $allSgpas): float
{
    if (empty($allSgpas)) return 0;

    return round(array_sum($allSgpas) / count($allSgpas), 2);
}

}
