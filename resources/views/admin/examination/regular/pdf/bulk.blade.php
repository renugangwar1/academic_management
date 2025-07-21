<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Result</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: center; }
    </style>
</head>
<body>
    <h2>Student Result Sheet</h2>
    <p><strong>Name:</strong> {{ $student->name }}</p>
    <p><strong>Roll No:</strong> {{ $student->nchm_roll_number }}</p>
    <p><strong>Programme:</strong> {{ $student->program->name ?? '—' }}</p>

    <table>
        <thead>
            <tr>
                <th>Course</th>
                <th>Internal</th>
                <th>External</th>
                <th>Attendance</th>
                <th>Total</th>
                <th>Grade</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
           @foreach($results as $studentResults)
    @php $student = $studentResults->first()->student; @endphp
                <tr>
                    <td>{{ $r->course->course_code ?? '' }}</td>
                    <td>{{ $r->internal }}</td>
                    <td>{{ $r->external }}</td>
                    <td>{{ $r->attendance }}</td>
                    <td>{{ $r->total }}</td>
                    <td>{{ $r->grade_letter }}</td>
                    <td>{{ $r->result_status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
