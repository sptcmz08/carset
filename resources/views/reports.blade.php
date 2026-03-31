@extends('layouts.app')
@section('title', 'รายงาน')

@section('content')
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
    <div class="filter-tabs">
        @foreach(['7' => '7 วัน', '14' => '14 วัน', '30' => '1 เดือน', '90' => '3 เดือน', '180' => '6 เดือน'] as $val => $label)
            <a href="{{ route('reports', ['period' => $val]) }}" class="filter-tab {{ $period == $val ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
    <div style="font-size: 13px; color: var(--text-muted);">
        <i class="fas fa-calendar-range"></i>
        {{ $startDate->format('d/m/Y') }} — {{ $endDate->format('d/m/Y') }}
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
        <div class="stat-value">{{ $totalAssignments }}</div>
        <div class="stat-label">รายการแผนทั้งหมด</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
        <div class="stat-value">{{ $availabilityRate }}%</div>
        <div class="stat-label">อัตราพร้อมให้บริการ</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <div class="stat-value">{{ $warningAssignments }}</div>
        <div class="stat-label">สถานะเฝ้าระวัง</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-ban"></i></div>
        <div class="stat-value">{{ $outOfServiceAssignments }}</div>
        <div class="stat-label">งดให้บริการ</div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line" style="color: var(--amber);"></i> แนวโน้มสถานะรายวัน</h3>
        </div>
        <div style="position: relative; height: 300px;">
            <canvas
                id="reportChart"
                data-labels='@json($chartLabels)'
                data-available='@json($chartAvailable)'
                data-warning='@json($chartWarning)'
                data-out='@json($chartOut)'
                data-point-radius="{{ $days <= 30 ? 3 : 0 }}"
            ></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-coins" style="color: var(--amber);"></i> ค่าซ่อมบำรุง</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div style="text-align: center; padding: 24px; background: rgba(245,158,11,0.08); border-radius: 12px;">
                <div style="font-size: 32px; font-weight: 700; color: var(--amber);">฿{{ number_format($totalMaintenanceCost) }}</div>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">ค่าซ่อมบำรุงรวม</div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div style="padding: 16px; background: var(--blue-bg); border-radius: 12px; text-align: center;">
                    <div style="font-size: 20px; font-weight: 700; color: var(--blue);">฿{{ number_format($scheduledCost) }}</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">ซ่อมตามกำหนด</div>
                </div>
                <div style="padding: 16px; background: var(--red-bg); border-radius: 12px; text-align: center;">
                    <div style="font-size: 20px; font-weight: 700; color: var(--red);">฿{{ number_format($repairCost) }}</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">ซ่อมฉุกเฉิน</div>
                </div>
            </div>
            <div style="position: relative; height: 200px;">
                <canvas
                    id="costChart"
                    data-scheduled="{{ $scheduledCost }}"
                    data-repair="{{ $repairCost }}"
                ></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3><i class="fas fa-chart-simple" style="color: var(--amber);"></i> การใช้งาน Train Set</h3>
        <span class="badge badge-blue" style="font-size: 11px;">ช่วง {{ $periodDays }} วัน</span>
    </div>
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Train Set</th>
                    <th>Type</th>
                    <th>วันใช้งาน</th>
                    <th>Utilization</th>
                    <th>Health</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trainSetUtilization as $item)
                    <tr>
                        <td style="font-size: 13px; font-weight: 700;">{{ $item['train_set']->code }}</td>
                        <td style="font-size: 13px;">{{ $item['train_set']->consist_label }}</td>
                        <td style="font-size: 13px; font-weight: 600;">{{ $item['planned_days'] }} วัน</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="progress-bar" style="flex: 1; max-width: 120px;">
                                    <div
                                        class="fill utilization-fill"
                                        data-width="{{ $item['utilization'] }}"
                                        data-color="{{ $item['utilization'] > 70 ? 'green' : ($item['utilization'] > 30 ? 'amber' : 'red') }}"
                                    ></div>
                                </div>
                                <span style="font-size: 12px; font-weight: 600; min-width: 40px;">{{ $item['utilization'] }}%</span>
                            </div>
                        </td>
                        <td><span class="badge badge-{{ $item['train_set']->health_badge_class }}">{{ $item['train_set']->health_icon }} {{ $item['train_set']->health_label }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($problemTrainSets->count() > 0)
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3><i class="fas fa-triangle-exclamation" style="color: var(--red);"></i> ขบวนที่มีปัญหาบ่อย</h3>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
        @foreach($problemTrainSets as $item)
        <div style="padding: 16px; border-radius: 12px; background: var(--red-bg); border: 1px solid rgba(239,68,68,0.15);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(59,130,246,0.15); color: var(--blue);">
                    <i class="fas fa-train-subway"></i>
                </div>
                <div>
                    <div style="font-weight: 700;">{{ $item['train_set']?->code ?? '-' }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ $item['train_set']?->consist_label ?? 'N/A' }}</div>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                <span>เกิดปัญหา {{ $item['count'] }} ครั้ง</span>
                <span style="font-weight: 600; color: var(--red);">฿{{ number_format($item['total_cost']) }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reportChartElement = document.getElementById('reportChart');
    const costChartElement = document.getElementById('costChart');

    document.querySelectorAll('.utilization-fill').forEach(function(element) {
        const width = Number(element.dataset.width || 0);
        const colorMap = {
            green: 'var(--green)',
            amber: 'var(--amber)',
            red: 'var(--red)',
        };

        element.style.width = width + '%';
        element.style.background = colorMap[element.dataset.color] || 'var(--blue)';
    });

    if (reportChartElement) {
        const labels = JSON.parse(reportChartElement.dataset.labels || '[]');
        const availableData = JSON.parse(reportChartElement.dataset.available || '[]');
        const warningData = JSON.parse(reportChartElement.dataset.warning || '[]');
        const outData = JSON.parse(reportChartElement.dataset.out || '[]');
        const pointRadius = Number(reportChartElement.dataset.pointRadius || 0);

        new Chart(reportChartElement.getContext('2d'), {
            type: 'line',
            data: {
                labels,
                datasets: [
                    {
                        label: 'พร้อมให้บริการ',
                        data: availableData,
                        borderColor: 'rgba(34, 197, 94, 1)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius,
                    },
                    {
                        label: 'ใกล้วาระซ่อม',
                        data: warningData,
                        borderColor: 'rgba(234, 179, 8, 1)',
                        backgroundColor: 'rgba(234, 179, 8, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius,
                    },
                    {
                        label: 'งดให้บริการ',
                        data: outData,
                        borderColor: 'rgba(239, 68, 68, 1)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#94a3b8', font: { size: 12 } } },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#94a3b8', stepSize: 1 },
                        grid: { color: 'rgba(255,255,255,0.04)' },
                    },
                    x: {
                        ticks: { color: '#94a3b8', maxTicksLimit: 15 },
                        grid: { display: false },
                    }
                }
            }
        });
    }

    if (costChartElement) {
        const scheduledCost = Number(costChartElement.dataset.scheduled || 0);
        const repairCost = Number(costChartElement.dataset.repair || 0);

        new Chart(costChartElement.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['ซ่อมตามกำหนด', 'ซ่อมฉุกเฉิน'],
                datasets: [{
                    data: [scheduledCost, repairCost],
                    backgroundColor: ['rgba(59, 130, 246, 0.7)', 'rgba(239, 68, 68, 0.7)'],
                    borderColor: ['rgba(59, 130, 246, 1)', 'rgba(239, 68, 68, 1)'],
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: '#94a3b8', font: { size: 12 }, padding: 16 },
                    }
                }
            }
        });
    }
});
</script>
@endsection