<?php
namespace App\Imports;

use App\Models\Course;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class CoursesImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    private $programId;

    public function __construct($programId)
    {
        $this->programId = $programId;
    }

    public function model(array $row)
    {
        $courseCode = trim($row['course_code'] ?? '');
        $type = ucfirst(strtolower($row['type'] ?? ''));

        if (!$courseCode || empty($row['course_title']) || empty($type)) {
            return null; // skip invalid rows
        }

        return new Course([
            'course_code'     => $courseCode,
            'course_title'    => $row['course_title'],
            'type'            => $type,
            'credit_hours'    => (int) $row['credit_hours'],
            'credit_value'    => (float) $row['credit_value'],
            'has_attendance'  => isset($row['has_attendance']) ? (bool) $row['has_attendance'] : false,
            'has_internal'    => isset($row['has_internal']) ? (bool) $row['has_internal'] : false,
            'has_external'    => isset($row['has_external']) ? (bool) $row['has_external'] : false,
        ]);
    }

    public function chunkSize(): int
    {
        return 500; // Adjust as needed
    }

    public function batchSize(): int
    {
        return 500;
    }
}
