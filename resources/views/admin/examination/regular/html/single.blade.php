<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Semester Grade Report</title>
    <style>
        @media print {
            .no-print { display: none; }
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #000;
            margin: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            text-align: center;
            vertical-align: middle;
        }

        .header-table img {
            height: 60px;
        }

        .university-name {
            font-weight: bold;
            font-size: 16px;
        }

        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin: 12px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .info-table td {
            padding: 6px 4px;
        }

        .courses-table th, .courses-table td,
        .summary-table th, .summary-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        .section-header {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .summary-table {
            margin-top: 20px;
        }

        .issued {
            margin-top: 20px;
            font-size: 11px;
        }

        .footer {
    margin-top: 50px;
    display: flex;
    justify-content: space-between;
    font-size: 12px;
}

.sign-left {
    text-align: left;
    margin-left: 8px
    width: 40%;
}

.sign-right {
    text-align: right;
    width: 40%;
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

   

    {{-- University Header --}}
    <table class="header-table">
        <tr>
            <td style="width: 20%; text-align: left;">
                <img src="{{ asset('assets/imgs/jnulogo.png') }}" alt="JNU Logo">
            </td>
            <td style="width: 60%;">
                  <div class="university-name">जवाहरलाल नेहरू विश्वविद्यालय</div>
                   <div>नई दिल्ली - 110067</div>
                <div class="university-name">JAWAHARLAL NEHRU UNIVERSITY</div>
                <div>NEW DELHI - 110067</div>
                <div class="university-name">राष्ट्रीय होटल प्रबंधन एवं कैटरिंग प्रौद्योगिकी परिषद्</div>
                <div>(पर्यटन मंत्रालय, भारत सरकार के अधीन स्वायत्तशासी निकाय)</div>
                <div class="university-name">NATIONAL COUNCIL FOR HOTEL MANAGEMENT AND CATERING TECHNOLOGY</div>
                <div><em>(An Autonomous Body under Ministry of Tourism, Govt. of India)</em></div>
            </td>
            <td style="width: 20%; text-align: right;">
                <img src="{{ asset('assets/imgs/logo.png') }}" alt="NCHMCT Logo">
            </td>
        </tr>
    </table>

    {{-- Title --}}
    <div class="title">SEMESTER GRADE REPORT</div>

    {{-- Student Info --}}
    <table class="info-table">
        <tr>
            <td><strong>Name:</strong> {{ $student->name }}</td>
            <td><strong>JNU Enrollment No:</strong> {{ $student->enrolment_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Academic Chapter Code:</strong> {{ $student->institute->code ?? '—' }}</td>
            <td><strong>NCHMCT Roll No:</strong> {{ $student->nchm_roll_number }}</td>
        </tr>
        <tr>
    <td><strong>Semester:</strong> Semester-{{ $selectedSemester }}</td>
    <td><strong>Academic Session:</strong> {{ $academicSession->year ?? $student->academic_year }}</td>
    <td><strong>Programme of Study:</strong> {{ $student->program->name ?? '—' }}</td>
</tr>

    </table>

    {{-- Course Grades --}}
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
                    <td>{{ $r->course->course_title }}</td>
                    <td>{{ $r->credit_value }}</td>
                    <td>{{ $r->grade_letter }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summary --}}
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
    <div class="sign-left">
        <strong>Controller of Examinations</strong><br>
        <span>Director (Studies), NCHMCT</span>
    </div>
    <div class="sign-right">
        <strong>Controller of Examinations</strong><br>
        <span>JNU</span>
    </div>
</div>
 {{-- Print Button --}}
    <div class="print-button no-print">
        <button onclick="window.print()">🖨️ Print Grade Report</button>
    </div>
</body>
</html>
