<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>All Semester Grade Reports</title>
    <style>
        @media print {
            .no-print { display: none; }
            .page-break { page-break-after: always; }
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #000;
            margin: 20px;
        }

        .print-button {
            margin: 20px 0;
            text-align: center;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 16px;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <!-- <div class="print-button no-print">
        <button onclick="window.print()">🖨️ Print All Reports</button>
    </div> -->

  @foreach ($students as $student)
    @include('admin.examination.regular.html.single', [
        'student'           => $student,
        'results'           => $student->results,
        'selectedSemester'  => $selectedSemester,
        'academicSession'   => $academicSession
    ])
    @if (!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach


</body>
</html>
