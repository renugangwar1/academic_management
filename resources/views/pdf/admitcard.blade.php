@extends('layouts.pdf')

<style>
    body {
        font-family: 'roboto', sans-serif;
        font-size: 11px;
        margin: 20px;
        line-height: 1.5;
    }

    .admit-card {
        width: 700px;
        margin: 0 auto;
        font-size: 11px;
    }

    .full-width {
        width: 100%;
        border-collapse: collapse;
    }

    .logo {
        width: 3.5rem;
    }

    .center-text {
        text-align: center;
    }

    .main-title {
        margin: 0;
        font-size: 14px;
        white-space: nowrap;
    }

    .subtitle {
        margin: 0;
        font-weight: bold;
        font-size: 12px;
    }

    .admission-ticket {
        margin: 4px 0;
        font-weight: bold;
        font-size: 12px;
    }

    .heading {
        margin-top: 15px;
        margin-bottom: 10px;
        position: relative;
    }

    .line-height-small {
        line-height: 16px;
    }

    .bold {
        font-weight: bold;
    }

    .uppercase {
        text-transform: uppercase;
    }

    .photo-placeholder {
        position: absolute;
        right: 0;
        top: 0;
        border: 1px solid;
        padding: 50px 8px;
        font-weight: bold;
        font-size: 10px;
    }

    .signature-section {
        width: 100%;
        display: flex;
        justify-content: space-between;
        margin-top: 25px;
        padding-bottom: 5px;
        border-bottom: 1px solid #000;
    }

    .text-left {
        text-align: left;
    }

    .text-right {
        text-align: right;
    }

    .important-note {
        margin-top: 15px;
    }

    .instructions {
        width: 100%;
        margin-top: 20px;
    }

    .instruction-list {
        font-size: 11px;
        padding-left: 18px;
        margin-bottom: 8px;
    }

    .signature-controller {
        width: 100%;
        text-align: right;
        margin-top: 15px;
    }

    .signature-img {
        width: 8rem;
        padding-right: 10px;
    }

    .signature-controller p {
        padding-right: 30px;
        margin: 2px 0;
    }
</style>

@section('content')
<div class="admit-card">
    <table class="full-width">
        <tr>
            <td><img src="{{ public_path('assets/imgs/jnulogo.png') }}" class="logo" alt="JNU"></td>
            <td class="center-text">
                <h4 class="main-title"><strong>NATIONAL COUNCIL FOR HOTEL MANAGEMENT AND CATERING TECHNOLOGY</strong></h4>
                <h4 class="subtitle">A-34, SECTOR-62, NOIDA-201301</h4>
                <h4 class="admission-ticket">ADMISSION TICKET</h4>
            </td>
            <td class="text-right"><img src="{{ public_path('assets/imgs/logo.png') }}" class="logo" alt="NCHMCT"></td>
        </tr>
    </table>

    <div class="heading">
        <table class="full-width line-height-small">
            <tr><td><strong>CENTRE:</strong></td><td class="uppercase">{{ $student->institute->name }}</td></tr>
            <tr><td><strong>COURSE:</strong></td><td class="uppercase">{{ $student->program->name }}</td></tr>
            <tr><td><strong>ROLL NO:</strong></td><td>{{ $student->nchm_roll_number }}</td></tr>
            <tr><td><strong>STUDENT NAME:</strong></td><td class="uppercase">{{ $student->name }}</td></tr>
            <tr><td><strong>EXAM TYPE:</strong></td><td>Regular</td></tr>
 
<tr>
    <td><strong>ACADEMIC YEAR:</strong></td>
    <td>{{ $session->year ?? 'N/A' }}</td>
</tr>



<tr>
    <td><strong>{{ strtoupper($structure === 'semester' ? 'SEMESTER' : 'YEAR') }}:</strong></td>
    <td>{{ $level }}</td>
</tr>


            <tr><td><strong>DATE OF ISSUE:</strong></td><td>{{ now()->format('d-m-Y') }}</td></tr>
<tr>
  <td><strong>APPEARING SUBJECTS:</strong></td>
  <td>
           {{ $student->appearingCourses->pluck('course_code')->filter()->implode(', ') ?: 'N/A' }}
  </td>
</tr>      </table>

        <div class="photo-placeholder">Paste your photograph</div>
    </div>

    <div class="signature-section">
        <table class="full-width">
            <tr>
                <td class="text-left"><strong>SIGNATURE OF CANDIDATE</strong></td>
                <td class="text-right"><strong>SIGNATURE OF PRINCIPAL</strong></td>
            </tr>
        </table>
    </div>

    <div class="important-note bold">
        <p><strong style="text-decoration:underline;">IMPORTANT:</strong> The Principal may issue this ticket after verifying eligibility as per the rules and after the attestation of the photo and signature of the candidate.</p>
    </div>

    <div class="instructions bold">
        <h4 class="text-center" style="margin-bottom: 10px;">INSTRUCTIONS</h4>
        <ol class="instruction-list">
            <li>Admission into the Examination hall will be only on the production of Admission Ticket.</li>
            <li>Candidates should take their seat at least ten minutes prior to the commencement of the examination.</li>
            <li>Candidates are advised to read do's and don'ts for the examination.</li>
            <li>No candidates will be allowed to carry any paper other than the admission ticket.</li>
            <li>Personal calculators, smart watches, mobile phones, or any other electronic/communication devices are <strong style="text-decoration:underline;">STRICTLY PROHIBITED</strong> inside the examination hall.</li>
            <li>During the examination, candidates may be checked for the possession of any of the prohibited items. If found, the candidate will be debarred from the examination and/or face disciplinary action.</li>
        </ol>

        <div class="signature-controller">
            <img src="{{ public_path('assets/imgs/signature.png') }}" class="signature-img" alt="Controller Signature">
            <p><strong>Dr. SATVIR SINGH</strong></p>
            <strong>(CONTROLLER OF EXAMINATION)</strong>
        </div>
    </div>
</div>
@endsection
