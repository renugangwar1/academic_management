<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Semester Grade Report</title>
    <style>
        @page { margin: 20px; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #000;
        }
        .university-header {
            text-align: center;
            font-size: 14px;
            line-height: 1.4;
        }
        .bold { font-weight: bold; }
        .grade-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin: 12px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 0;
        }
        .info-table, .courses-table, .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .info-table td {
            padding: 4px;
        }
        .courses-table th, .courses-table td,
        .summary-table th, .summary-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        .section-header {
            background-color: #e6e6e6;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        .footer .sign {
            text-align: center;
        }
        .issued {
            margin-top: 20px;
            font-size: 11px;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="university-header">
        <div class="bold">जवाहरलाल नेहरू विश्वविद्यालय</div>
        <div class="bold">JAWAHARLAL NEHRU UNIVERSITY</div>
        <div>NEW DELHI - 110067</div>
        <div class="bold">राष्ट्रीय होटल प्रबंधन एवं कैटरिंग प्रौद्योगिकी परिषद्</div>
        <div>NATIONAL COUNCIL FOR HOTEL MANAGEMENT AND CATERING TECHNOLOGY</div>
        <div><em>(An Autonomous Body under Ministry of Tourism, Govt. of India)</em></div>
    </div>

    {{-- Title --}}
    <div class="grade-title">SEMESTER GRADE REPORT</div>

    {{-- Student Info --}}
    <table class="info-table">
        <tr>
            <td><strong>Name:</strong> {{ $student->name }}</td>
            <td><strong>JNU Enrollment No:</strong> {{ $student->jnu_enrollment ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Academic Chapter Code:</strong> {{ $student->institute->chapter_code ?? '—' }}</td>
            <td><strong>NCHMCT Roll No:</strong> {{ $student->nchm_roll_number }}</td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td><strong>Semester:</strong> SEM-{{ $student->semester }}</td>
            <td><strong>Academic Session:</strong> {{ $student->academic_year }}</td>
            <td><strong>Programme of Study:</strong> {{ $student->program->name ?? '—' }}</td>
        </tr>
    </table>

    {{-- Course Table --}}
    <table class="courses-table">
        <thead class="section-header">
            <tr>
                <th>Course Code</th>
                <th>Course Title</th>
                <th>Credits</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($results as $r)
                <tr>
                    <td>{{ $r->course->course_code }}</td>
                    <td>{{ $r->course->name }}</td>
                    <td>{{ $r->credits }}</td>
                    <td>{{ $r->grade_letter }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summary Table --}}
    <table class="summary-table">
        <thead class="section-header">
            <tr>
                <th colspan="4">CURRENT SEMESTER RECORD</th>
                <th colspan="4">CUMULATIVE RECORD</th>
            </tr>
        </thead>
        <tr>
            <td><strong>Total Credits</strong></td>
            <td>{{ $student->total_credits }}</td>
            <td><strong>Total Points</strong></td>
            <td>{{ $student->total_points }}</td>
            <td><strong>Total Credits</strong></td>
            <td>{{ $student->cumulative_credits }}</td>
            <td><strong>Total Points</strong></td>
            <td>{{ $student->cumulative_points }}</td>
        </tr>
        <tr>
            <td><strong>SGPA</strong></td>
            <td colspan="3">{{ number_format($student->sgpa, 2) }}</td>
            <td><strong>CGPA</strong></td>
            <td colspan="3">{{ number_format($student->cgpa, 2) }}</td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="issued">
        <strong>Date of Issue:</strong> {{ now()->format('d-m-Y') }}<br>
        <strong>Total Valid Credits Earned:</strong> {{ $student->total_credits }}
    </div>

    <div class="footer">
        <div class="sign">
            {{-- Optional QR Code --}}
            {{-- {!! QrCode::size(60)->generate($student->nchm_roll_number) !!} --}}
        </div>
        <div class="sign">
            <strong>Controller of Examinations</strong><br>
            <span>Director (Studies), NCHMCT</span>
        </div>
        <div class="sign">
            <strong>Controller of Examinations</strong><br>
            <span>JNU</span>
        </div>
    </div>

</body>
</html>
