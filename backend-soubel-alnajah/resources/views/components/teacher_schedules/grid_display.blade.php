<div class="table-responsive">
    <table class="table table-bordered text-center align-middle">
        <thead>
            <tr>
                <th style="min-width:120px">{{ trans('teacher_schedule.slot') }}</th>
                @foreach($slots as $slot)
                    <th>
                        <div>{{ $slot->label ?: ('#' . $slot->slot_index) }}</div>
                        <small class="text-muted">{{ $slot->starts_at ?: '--:--' }} - {{ $slot->ends_at ?: '--:--' }}</small>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($days as $dayIndex => $dayLabel)
                <tr>
                    <th>{{ $dayLabel }}</th>
                    @foreach($slots as $slot)
                        @php($cell = $matrix[$dayIndex][$slot->slot_index] ?? null)
                        <td class="text-start" style="min-width:180px">
                            @if($cell)
                                <div><strong>{{ $cell['subject_name'] ?: '—' }}</strong></div>
                                <div>{{ trans('teacher_schedule.class_name') }}: {{ $cell['class_name'] ?: '—' }}</div>
                                <div>{{ trans('teacher_schedule.room') }}: {{ $cell['room_name'] ?: '—' }}</div>
                                @if(!empty($cell['note']))
                                    <div class="text-muted">{{ $cell['note'] }}</div>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
