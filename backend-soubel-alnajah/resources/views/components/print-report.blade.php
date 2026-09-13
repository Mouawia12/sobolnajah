@props([
    'title' => 'تقرير',
    'reportTitle' => null,
    'schoolName' => 'سُبل النجاح',
    'meta' => [],
    'isPdf' => false,
    'pdfUrl' => null,
    'landscape' => false,
])
@php
    $reportTitle = $reportTitle ?? $title;
    $pdfText = app(\App\Support\PdfArabicText::class, ['enabled' => $isPdf && App::isLocale('ar')]);
    $shape = fn ($value) => $pdfText->shape($value);

    $logoPath = public_path('images/logo.png');
    $logoSrc = file_exists($logoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath))
        : null;

    $generatedLabel = $shape(trans('print.generated_at'));
    $generatedValue = $shape(now()->format('Y-m-d H:i'));
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ App::isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { size: A4 {{ $landscape ? 'landscape' : 'portrait' }}; margin: 10mm; }
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Tahoma, sans-serif; margin: 0; color:#1f2a37; font-size:12px; line-height:1.4; background:#fff; }
        .pr-actions { margin:0 0 14px; display:flex; gap:8px; }
        .pr-actions a, .pr-actions button { padding:9px 16px; border:1px solid #1f6fbe; background:#1f6fbe; color:#fff; text-decoration:none; border-radius:8px; font-weight:700; cursor:pointer; font-size:13px; }
        .pr-actions .pr-pdf { background:#c0392b; border-color:#c0392b; }
        .pr-sheet { max-width: 900px; margin-inline:auto; padding: 6px; }

        .pr-header { display:flex; align-items:center; gap:16px; border-bottom:3px solid #1f6fbe; padding-bottom:12px; margin-bottom:16px; }
        .pr-header .pr-logo { width:70px; height:70px; object-fit:contain; flex:0 0 70px; }
        .pr-header .pr-titles { flex:1 1 auto; text-align:center; }
        .pr-school { font-size:20px; font-weight:800; color:#12355b; margin:0; }
        .pr-report-title { font-size:15px; font-weight:700; color:#1f6fbe; margin:4px 0 0; }
        .pr-gen { flex:0 0 auto; text-align:{{ App::isLocale('ar') ? 'left' : 'right' }}; font-size:10px; color:#64748b; }

        .pr-meta { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px; }
        .pr-meta .pr-meta-card { border:1px solid #dbe5f0; border-radius:8px; padding:6px 12px; background:#f8fbff; min-width:120px; }
        .pr-meta .pr-meta-label { display:block; font-size:9.5px; font-weight:700; color:#64748b; margin-bottom:2px; }
        .pr-meta .pr-meta-value { display:block; font-size:12.5px; font-weight:700; color:#1f2a37; }

        table.pr-table { width:100%; border-collapse:collapse; margin-top:4px; }
        table.pr-table th, table.pr-table td { border:1px solid #cdd7e2; padding:7px 8px; text-align:center; font-size:11.5px; }
        table.pr-table thead th { background:#eef4fb; color:#12355b; font-weight:800; }
        table.pr-table tbody tr:nth-child(even) td { background:#fafcff; }
        .pr-badge { display:inline-block; padding:2px 8px; border-radius:20px; font-weight:700; font-size:10.5px; }
        .pr-present { background:#e6f4ec; color:#2e9e5b; }
        .pr-late { background:#fdf3df; color:#c98a12; }
        .pr-absent { background:#fbe9e6; color:#e0533d; }

        .pr-footer { margin-top:18px; padding-top:8px; border-top:1px dashed #cdd7e2; font-size:10px; color:#64748b; display:flex; justify-content:space-between; }

        @media print {
            .pr-actions { display:none; }
            .pr-sheet { max-width:none; padding:0; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<div class="pr-sheet">
    @unless($isPdf)
        <div class="pr-actions">
            <button onclick="window.print()">🖨 {{ trans('print.print') }}</button>
            @if($pdfUrl)
                <a class="pr-pdf" href="{{ $pdfUrl }}">⬇ {{ trans('print.download_pdf') }}</a>
            @endif
            {!! $actions ?? '' !!}
        </div>
    @endunless

    <div class="pr-header">
        @if($logoSrc)
            <img class="pr-logo" src="{{ $logoSrc }}" alt="logo">
        @endif
        <div class="pr-titles">
            <p class="pr-school">{{ $shape($schoolName) }}</p>
            <p class="pr-report-title">{{ $shape($reportTitle) }}</p>
        </div>
        <div class="pr-gen">
            <div>{{ $generatedLabel }}</div>
            <div>{{ $generatedValue }}</div>
        </div>
    </div>

    @if(!empty($meta))
        <div class="pr-meta">
            @foreach($meta as $item)
                <div class="pr-meta-card">
                    <span class="pr-meta-label">{{ $shape($item['label'] ?? '') }}</span>
                    <span class="pr-meta-value">{{ $shape($item['value'] ?? '') }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{ $slot }}

    <div class="pr-footer">
        <span>{{ $shape($schoolName) }}</span>
        <span>{{ $shape(trans('print.system_name')) }}</span>
    </div>
</div>
</body>
</html>
