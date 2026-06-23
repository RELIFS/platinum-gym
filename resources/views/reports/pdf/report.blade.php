<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; color: #18181b; font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; line-height: 1.45; }
        .page { padding: 28px; }
        .header { border-bottom: 2px solid #18181b; padding-bottom: 14px; }
        .brand { font-size: 18px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .muted { color: #52525b; }
        h1 { margin: 18px 0 4px; font-size: 22px; }
        .meta { margin-top: 12px; width: 100%; }
        .meta td { padding: 2px 0; vertical-align: top; }
        .summary { margin: 18px 0; width: 100%; border-collapse: collapse; }
        .summary td { width: 25%; border: 1px solid #e4e4e7; padding: 9px; vertical-align: top; }
        .summary .label { color: #71717a; font-size: 9px; font-weight: 700; text-transform: uppercase; }
        .summary .value { margin-top: 4px; font-size: 14px; font-weight: 700; }
        .data { width: 100%; border-collapse: collapse; }
        .data th { background: #f4f4f5; border: 1px solid #d4d4d8; padding: 7px; text-align: left; font-size: 9px; text-transform: uppercase; }
        .data td { border: 1px solid #e4e4e7; padding: 7px; vertical-align: top; }
        .footer { border-top: 1px solid #d4d4d8; color: #71717a; margin-top: 18px; padding-top: 10px; }
    </style>
</head>
<body>
    <main class="page">
        <header class="header">
            <div class="brand">{{ $report['business']['site_name'] }}</div>
            <div class="muted">{{ $report['business']['address'] }}</div>
            <div class="muted">{{ $report['business']['phone_display'] }} | {{ $report['business']['public_email'] }}</div>
        </header>

        <h1>{{ $report['title'] }}</h1>
        <p class="muted">{{ $report['subtitle'] }}</p>

        <table class="meta">
            <tr>
                <td><strong>Periode</strong></td>
                <td>{{ $report['period'] }}</td>
                <td><strong>Tanggal export</strong></td>
                <td>{{ $report['generatedAt'] }}</td>
            </tr>
            <tr>
                <td><strong>Dibuat oleh</strong></td>
                <td>{{ $report['generatedBy'] }}</td>
                <td><strong>Jumlah data</strong></td>
                <td>{{ count($report['rows']) }}</td>
            </tr>
        </table>

        @if (! empty($report['summary']))
            <table class="summary">
                <tr>
                    @foreach ($report['summary'] as $item)
                        <td>
                            <div class="label">{{ $item['label'] }}</div>
                            <div class="value">{{ $item['value'] }}</div>
                            <div class="muted">{{ $item['description'] }}</div>
                        </td>
                    @endforeach
                </tr>
            </table>
        @endif

        <table class="data">
            <thead>
                <tr>
                    @foreach ($report['headings'] as $heading)
                        <th>{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($report['rows'] as $row)
                    <tr>
                        @foreach ($report['headings'] as $index => $heading)
                            <td>{{ $row[$index] ?? '-' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($report['headings']) }}">Belum ada data pada filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <footer class="footer">
            Dokumen ini dibuat otomatis dari sistem Platinum Gym.
        </footer>
    </main>
</body>
</html>
