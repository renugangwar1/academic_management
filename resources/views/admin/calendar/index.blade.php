@extends('layouts.admin')

@section('title', 'Calendar')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container py-4">
    <h4 class="mb-4">📅 Calendar</h4>
    <div id="calendar"></div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="eventForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addEventModalLabel">Add New Event</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="eventTitle" class="form-label">Add Event</label>
                <input type="text" class="form-control" id="eventTitle" name="title" required>
            </div>
            <input type="hidden" id="eventStart" name="start">
            <input type="hidden" id="eventEnd" name="end">
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Add </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const addEventModal = new bootstrap.Modal(document.getElementById('addEventModal'));
    const form = document.getElementById('eventForm');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 650,
        editable: true,
        selectable: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: '/admin/calendar/events',

        select: function(info) {
            // Show modal and set selected date range
            document.getElementById('eventStart').value = info.startStr;
            document.getElementById('eventEnd').value = info.endStr;
            form.reset();
            addEventModal.show();
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const title = document.getElementById('eventTitle').value;
        const start = document.getElementById('eventStart').value;
        const end = document.getElementById('eventEnd').value;

        fetch('/admin/calendar/events', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ title, start, end })
        })
        .then(response => response.json())
        .then(event => {
            calendar.refetchEvents(); // reload events properly after add
            addEventModal.hide();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Could not save event.');
        });
    });

    calendar.render();
});
</script>
@endpush
