@extends('layouts.app')
@section('title', 'แดชบอร์ด')

@section('content')
<div class="stat-grid">
    <div class="stat-card amber">
        <div class="stat-icon"><i class="fas fa-train-subway"></i></div>
        <div class="stat-value">{{ $totalTrainSets }}</div>
        <div class="stat-label">ขบวนทั้งหมด</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
        <div class="stat-value">{{ $availableCount }}</div>
        <div class="stat-label">พร้อมให้บริการ</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="stat-value">{{ $warningCount }}</div>
        <div class="stat-label">ใกล้วาระซ่อม</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-ban"></i></div>
        <div class="stat-value">{{ $outOfServiceCount }}</div>
        <div class="stat-label">งดให้บริการ</div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-calendar-check" style="color: var(--amber);"></i> แผนเดินรถวันนี้</h3>
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
            <div style="text-align: center; padding: 16px; background: var(--green-bg); border-radius: 12px;">
                <div style="font-size: 24px; font-weight: 700; color: var(--green);">{{ $todayPlanned }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">พร้อมใช้งาน</div>
            </div>
            <div style="text-align: center; padding: 16px; background: var(--yellow-bg); border-radius: 12px;">
                <div style="font-size: 24px; font-weight: 700; color: var(--yellow);">{{ $todayWarning }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">เฝ้าระวัง</div>
            </div>
            <div style="text-align: center; padding: 16px; background: var(--red-bg); border-radius: 12px;">
                <div style="font-size: 24px; font-weight: 700; color: var(--red);">{{ $todayOut }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">งดให้บริการ</div>
            </div>
        </div>

        @if($todayEntries->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Train Set</th>
                    <th>Berth</th>
                    <th>Dep. Plan</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($todayEntries->take(8) as $entry)
                <tr>
                    <td style="font-size: 13px; font-weight: 700;">{{ $entry->trainSet?->code }}</td>
                    <td style="font-size: 13px;">{{ $entry->berth_no ?: '-' }}</td>
                    <td style="font-size: 13px;">{{ $entry->departure_plan_time ?: '-' }}</td>
                    <td><span class="badge badge-{{ $entry->status_color }}">{{ $entry->status_label }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state">
            <i class="fas fa-calendar-xmark"></i>
            <p>ยังไม่มีการบันทึกสมุดแผนวันนี้</p>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-bell" style="color: var(--amber);"></i> การแจ้งเตือนสำคัญ</h3>
            <span class="badge badge-red" style="font-size: 11px;">{{ count($alerts) }} รายการ</span>
        </div>
        <div style="max-height: 380px; overflow-y: auto;">
            @forelse($alerts as $alert)
                <div class="alert-item {{ $alert['level'] }}">
                    <div class="alert-icon">
                        @if($alert['type'] === 'mileage' && $alert['level'] === 'danger')
                            <i class="fas fa-tachometer-alt" style="color: var(--red);"></i>
                        @elseif($alert['type'] === 'mileage')
                            <i class="fas fa-tachometer-alt" style="color: var(--yellow);"></i>
                        @elseif($alert['type'] === 'schedule' && $alert['level'] === 'danger')
                            <i class="fas fa-calendar-exclamation" style="color: var(--red);"></i>
                        @elseif($alert['type'] === 'schedule')
                            <i class="fas fa-calendar-exclamation" style="color: var(--yellow);"></i>
                        @elseif($alert['level'] === 'danger')
                            <i class="fas fa-screwdriver-wrench" style="color: var(--red);"></i>
                        @else
                            <i class="fas fa-screwdriver-wrench" style="color: var(--yellow);"></i>
                        @endif
                    </div>
                    <div class="alert-text">
                        <div class="alert-vehicle">{{ $alert['trainSet']->code }} — {{ $alert['trainSet']->consist_label }}</div>
                        <div class="alert-message">{{ $alert['message'] }}</div>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <i class="fas fa-check-circle" style="color: var(--green);"></i>
                    <p>ไม่มีการแจ้งเตือน</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3><i class="fas fa-chart-line" style="color: var(--amber);"></i> แนวโน้มสถานะ 7 วันล่าสุด</h3>
    </div>
    <div style="position: relative; height: 280px;">
        <canvas
            id="tripsChart"
            data-labels='@json($chartLabels)'
            data-available='@json($chartAvailable)'
            data-warning='@json($chartWarning)'
            data-out='@json($chartOut)'
        ></canvas>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3><i class="fas fa-th-large" style="color: var(--amber);"></i> ภาพรวมขบวน</h3>
        <a href="{{ route('fleet') }}" class="btn btn-secondary btn-sm">ดูทั้งหมด <i class="fas fa-arrow-right"></i></a>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px;">
        @foreach($trainSets as $trainSet)
        <div style="padding: 16px; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; background: rgba(59,130,246,0.15); color: var(--blue); font-size: 18px;">
                <i class="fas fa-train-subway"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 14px; font-weight: 700;">{{ $trainSet->code }}</div>
                <div style="font-size: 11px; color: var(--text-muted);">{{ $trainSet->consist_label }} • {{ $trainSet->default_berth_no ?: '-' }}</div>
            </div>
            <span class="badge badge-{{ $trainSet->health_badge_class }}">{{ $trainSet->health_icon }}</span>
        </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartElement = document.getElementById('tripsChart');

    if (!chartElement) {
        return;
    }

    const labels = JSON.parse(chartElement.dataset.labels || '[]');
    const availableData = JSON.parse(chartElement.dataset.available || '[]');
    const warningData = JSON.parse(chartElement.dataset.warning || '[]');
    const outData = JSON.parse(chartElement.dataset.out || '[]');

    new Chart(chartElement.getContext('2d'), {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'พร้อมให้บริการ',
                    data: availableData,
                    borderColor: 'rgba(34, 197, 94, 1)',
                    backgroundColor: 'rgba(34, 197, 94, 0.10)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                },
                {
                    label: 'ใกล้วาระซ่อม',
                    data: warningData,
                    borderColor: 'rgba(234, 179, 8, 1)',
                    backgroundColor: 'rgba(234, 179, 8, 0.10)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                },
                {
                    label: 'งดให้บริการ',
                    data: outData,
                    borderColor: 'rgba(239, 68, 68, 1)',
                    backgroundColor: 'rgba(239, 68, 68, 0.10)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.35,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#94a3b8' } },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#94a3b8', stepSize: 1 },
                    grid: { color: 'rgba(255,255,255,0.04)' },
                },
                x: {
                    ticks: { color: '#94a3b8' },
                    grid: { display: false },
                }
            }
        }
    });
});
</script>
@endsection