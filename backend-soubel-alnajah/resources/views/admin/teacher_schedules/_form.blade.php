@php
    $editing = isset($schedule);
    $slotsSource = old('slots', $editing ? $schedule->slots->map(fn($slot) => [
        'slot_index' => $slot->slot_index,
        'label' => $slot->label,
        'starts_at' => $slot->starts_at,
        'ends_at' => $slot->ends_at,
    ])->values()->all() : $defaultSlots);

    $slotsSource = collect($slotsSource)->sortBy('slot_index')->values()->all();

    $matrixSource = old('entries', $editing ? $matrix : []);
@endphp

<div class="row">
    <div class="col-md-3 mb-3">
        <label class="form-label">{{ trans('teacher_schedule.teacher') }}</label>
        <select name="teacher_id" class="form-select" required>
            <option value="">--</option>
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}" @selected((int) old('teacher_id', $editing ? $schedule->teacher_id : 0) === (int) $teacher->id)>
                    {{ $teacher->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label">{{ trans('teacher_schedule.academic_year') }}</label>
        <input type="text" class="form-control" name="academic_year" value="{{ old('academic_year', $editing ? $schedule->academic_year : '') }}" placeholder="2025/2026" required>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">{{ trans('teacher_schedule.branch_name') }}</label>
        <input type="text" class="form-control" name="branch_name" value="{{ old('branch_name', $editing ? $schedule->branch_name : '') }}">
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label">{{ trans('teacher_schedule.status') }}</label>
        <select name="status" class="form-select" required>
            <option value="draft" @selected(old('status', $editing ? $schedule->status : 'draft') === 'draft')>{{ trans('teacher_schedule.draft') }}</option>
            <option value="published" @selected(old('status', $editing ? $schedule->status : 'draft') === 'published')>{{ trans('teacher_schedule.published') }}</option>
        </select>
    </div>
    <div class="col-md-2 mb-3">
        <label class="form-label">{{ trans('teacher_schedule.visibility') }}</label>
        <select name="visibility" class="form-select" required>
            <option value="authenticated" @selected(old('visibility', $editing ? $schedule->visibility : 'authenticated') === 'authenticated')>{{ trans('teacher_schedule.authenticated') }}</option>
            <option value="public" @selected(old('visibility', $editing ? $schedule->visibility : 'authenticated') === 'public')>{{ trans('teacher_schedule.public') }}</option>
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label class="form-label">{{ trans('teacher_schedule.approved_at') }}</label>
        <input type="date" class="form-control" name="approved_at" value="{{ old('approved_at', $editing && $schedule->approved_at ? $schedule->approved_at->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">{{ trans('teacher_schedule.signature_text') }}</label>
        <input type="text" class="form-control" name="signature_text" value="{{ old('signature_text', $editing ? $schedule->signature_text : '') }}">
    </div>
    <div class="col-md-5 mb-3">
        <label class="form-label">{{ trans('teacher_schedule.show') }}</label>
        <input type="text" class="form-control" name="title" value="{{ old('title', $editing ? $schedule->title : '') }}" placeholder="{{ trans('teacher_schedule.title') }}">
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-3 mb-2">
    <h5 class="mb-0">{{ trans('teacher_schedule.time') }}</h5>
    <button type="button" class="btn btn-outline-secondary btn-sm" id="add-slot">{{ trans('teacher_schedule.add_slot') }}</button>
</div>
<div class="table-responsive mb-3">
    <table class="table table-bordered" id="slots-table">
        <thead>
        <tr>
            <th style="width:90px">#</th>
            <th>{{ trans('teacher_schedule.slot') }}</th>
            <th>{{ trans('timetable.from') }}</th>
            <th>{{ trans('timetable.to') }}</th>
            <th style="width:90px">{{ trans('teacher_schedule.remove_slot') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($slotsSource as $i => $slot)
            <tr>
                <td><input type="number" min="1" max="12" class="form-control slot-index" name="slots[{{ $i }}][slot_index]" value="{{ $slot['slot_index'] }}" required></td>
                <td><input type="text" class="form-control slot-label" name="slots[{{ $i }}][label]" value="{{ $slot['label'] ?? '' }}"></td>
                <td><input type="time" class="form-control" name="slots[{{ $i }}][starts_at]" value="{{ $slot['starts_at'] ?? '' }}"></td>
                <td><input type="time" class="form-control" name="slots[{{ $i }}][ends_at]" value="{{ $slot['ends_at'] ?? '' }}"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-slot">x</button></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<h5 class="mb-2">{{ trans('teacher_schedule.weekly_schedule') }}</h5>
<div class="table-responsive">
    <table class="table table-bordered align-middle" id="matrix-table">
        <thead>
        <tr>
            <th style="min-width:120px">{{ trans('timetable.day') }}</th>
            @foreach($slotsSource as $slot)
                <th data-slot="{{ $slot['slot_index'] }}">
                    <div>{{ $slot['label'] ?: ('#' . $slot['slot_index']) }}</div>
                    <small class="text-muted">{{ $slot['starts_at'] ?? '--:--' }} - {{ $slot['ends_at'] ?? '--:--' }}</small>
                </th>
            @endforeach
        </tr>
        </thead>
        <tbody>
        @foreach($days as $dayIndex => $dayLabel)
            <tr>
                <th>{{ $dayLabel }}</th>
                @foreach($slotsSource as $slot)
                    @php($slotIndex = (int) $slot['slot_index'])
                    @php($cell = $matrixSource[$dayIndex][$slotIndex] ?? [])
                    <td data-slot="{{ $slotIndex }}" style="min-width:220px">
                        <input type="text" class="form-control mb-1" name="entries[{{ $dayIndex }}][{{ $slotIndex }}][subject_name]" placeholder="{{ trans('teacher_schedule.subject') }}" value="{{ $cell['subject_name'] ?? '' }}">
                        <input type="text" class="form-control mb-1" name="entries[{{ $dayIndex }}][{{ $slotIndex }}][class_name]" placeholder="{{ trans('teacher_schedule.class_name') }}" value="{{ $cell['class_name'] ?? '' }}">
                        <input type="text" class="form-control mb-1" name="entries[{{ $dayIndex }}][{{ $slotIndex }}][room_name]" placeholder="{{ trans('teacher_schedule.room') }}" value="{{ $cell['room_name'] ?? '' }}">
                        <input type="text" class="form-control" name="entries[{{ $dayIndex }}][{{ $slotIndex }}][note]" placeholder="{{ trans('teacher_schedule.note') }}" value="{{ $cell['note'] ?? '' }}">
                    </td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@section('jsa')
<script>
(function () {
    const slotsTableBody = document.querySelector('#slots-table tbody');
    const matrixTable = document.getElementById('matrix-table');
    const addSlotBtn = document.getElementById('add-slot');
    const days = @json(array_keys($days));

    function rebuildMatrixHeaders() {
        const headerRow = matrixTable.querySelector('thead tr');
        while (headerRow.children.length > 1) {
            headerRow.removeChild(headerRow.lastElementChild);
        }

        const slots = getSlots();
        slots.forEach((slot) => {
            const th = document.createElement('th');
            th.setAttribute('data-slot', slot.slotIndex);
            th.innerHTML = `<div>${slot.label || ('#' + slot.slotIndex)}</div><small class="text-muted">${slot.startsAt || '--:--'} - ${slot.endsAt || '--:--'}</small>`;
            headerRow.appendChild(th);
        });

        const bodyRows = matrixTable.querySelectorAll('tbody tr');
        bodyRows.forEach((row) => {
            while (row.children.length > 1) {
                row.removeChild(row.lastElementChild);
            }
            const day = row.getAttribute('data-day') || row.querySelector('th').dataset.day;
            slots.forEach((slot) => {
                const td = document.createElement('td');
                td.setAttribute('data-slot', slot.slotIndex);
                td.style.minWidth = '220px';
                td.innerHTML = [
                    `<input type="text" class="form-control mb-1" name="entries[${day}][${slot.slotIndex}][subject_name]" placeholder="{{ trans('teacher_schedule.subject') }}">`,
                    `<input type="text" class="form-control mb-1" name="entries[${day}][${slot.slotIndex}][class_name]" placeholder="{{ trans('teacher_schedule.class_name') }}">`,
                    `<input type="text" class="form-control mb-1" name="entries[${day}][${slot.slotIndex}][room_name]" placeholder="{{ trans('teacher_schedule.room') }}">`,
                    `<input type="text" class="form-control" name="entries[${day}][${slot.slotIndex}][note]" placeholder="{{ trans('teacher_schedule.note') }}">`
                ].join('');
                row.appendChild(td);
            });
        });
    }

    function getSlots() {
        const rows = slotsTableBody.querySelectorAll('tr');
        return Array.from(rows).map((row) => ({
            slotIndex: parseInt(row.querySelector('.slot-index').value, 10),
            label: row.querySelector('.slot-label').value,
            startsAt: row.querySelector('input[name*="[starts_at]"]').value,
            endsAt: row.querySelector('input[name*="[ends_at]"]').value,
        })).filter(slot => Number.isInteger(slot.slotIndex));
    }

    function reindexSlotInputs() {
        const rows = slotsTableBody.querySelectorAll('tr');
        rows.forEach((row, i) => {
            row.querySelectorAll('input').forEach((input) => {
                input.name = input.name.replace(/slots\[\d+\]/, `slots[${i}]`);
            });
        });
    }

    addSlotBtn?.addEventListener('click', () => {
        const rowCount = slotsTableBody.querySelectorAll('tr').length;
        const nextIndex = rowCount + 1;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="number" min="1" max="12" class="form-control slot-index" name="slots[${rowCount}][slot_index]" value="${nextIndex}" required></td>
            <td><input type="text" class="form-control slot-label" name="slots[${rowCount}][label]" value=""></td>
            <td><input type="time" class="form-control" name="slots[${rowCount}][starts_at]"></td>
            <td><input type="time" class="form-control" name="slots[${rowCount}][ends_at]"></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-slot">x</button></td>
        `;
        slotsTableBody.appendChild(row);
        rebuildMatrixHeaders();
    });

    slotsTableBody?.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-slot')) {
            e.target.closest('tr').remove();
            reindexSlotInputs();
            rebuildMatrixHeaders();
        }
    });

    slotsTableBody?.addEventListener('change', (e) => {
        if (e.target.classList.contains('slot-index') || e.target.classList.contains('slot-label') || e.target.type === 'time') {
            rebuildMatrixHeaders();
        }
    });

    matrixTable.querySelectorAll('tbody tr').forEach((row, i) => {
        row.querySelector('th').dataset.day = days[i];
    });
})();
</script>
@endsection
