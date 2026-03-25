@extends('layouts.app')
@section('title', 'รายงาน')

@section('content')
<!-- Period Selector -->
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

<!-- Summary Stats -->
<div class="stat-grid">
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-route"></i></div>
        <div class="stat-value">{{ $totalTrips }}</div>
        <div class="stat-label">เที่ยววิ่งทั้งหมด</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-check-double"></i></div>
        <div class="stat-value">{{ $completionRate }}%</div>
        <div class="stat-label">อัตราสำเร็จ ({{ $completedTrips }} เที่ยว)</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-ban"></i></div>
        <div class="stat-value">{{ $cancelledTrips }}</div>
        <div class="stat-label">ยกเลิก</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-icon"><i class="fas fa-baht-sign"></i></div>
        <div class="stat-value">{{ number_format($totalMaintenanceCost) }}</div>
        <div class="stat-label">ค่าซ่อมบำรุง (บาท)</div>
    </div>
</div>

<div class="grid-2">
    <!-- Trips Chart -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-bar" style="color: var(--amber)"></i> กราฟเที่ยววิ่ง</h3>
        </div>
        <div style="position: relative; height: 300px;">
            <canvas id="reportChart"></canvas>
        </div>
    </div>

    <!-- Maintenance Cost Breakdown -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-coins" style="color: var(--amber)"></i> ค่าซ่อมบำรุง</h3>
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

            <!-- Donut Chart -->
            <div style="position: relative; height: 200px;">
                <canvas id="costChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Vehicle Utilization -->
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3><i class="fas fa-chart-simple" style="color: var(--amber)"></i> อัตราการใช้งานรถ</h3>
        <span class="badge badge-blue" style="font-size: 11px;">ช่วง {{ $days }} วัน</span>
    </div>
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>รถ</th>
                    <th>ประเภท</th>
                    <th>จำนวนเที่ยว</th>
                    <th>อัตราการใช้งาน</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vehicleUtilization as $vu)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="vehicle-icon {{ $vu['vehicle']->vehicle_type }}" style="width: 32px; height: 32px; font-size: 13px;">
                                <i class="fas fa-{{ $vu['vehicle']->vehicle_type === 'bus' ? 'bus' : 'van-shuttle' }}"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 13px;">{{ $vu['vehicle']->vehicle_code }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $vu['vehicle']->license_plate }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size: 13px;">{{ $vu['vehicle']->vehicle_type_thai }}</td>
                    <td style="font-size: 13px; font-weight: 600;">{{ $vu['trips'] }} เที่ยว</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="progress-bar" style="flex: 1; max-width: 120px;">
                                <div class="fill" style="width: {{ $vu['utilization'] }}%; background: {{ $vu['utilization'] > 70 ? 'var(--green)' : ($vu['utilization'] > 30 ? 'var(--amber)' : 'var(--red)') }};"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; min-width: 40px;">{{ $vu['utilization'] }}%</span>
                        </div>
                    </td>
                    <td><span class="badge badge-{{ $vu['vehicle']->health_status }}">{{ $vu['vehicle']->health_icon }} {{ $vu['vehicle']->health_label }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Problem Vehicles -->
@if($problemVehicles->count() > 0)
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3><i class="fas fa-triangle-exclamation" style="color: var(--red)"></i> รถที่มีปัญหาบ่อย</h3>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 12px;">
        @foreach($problemVehicles as $pv)
        <div style="padding: 16px; border-radius: 12px; background: var(--red-bg); border: 1px solid rgba(239,68,68,0.15);">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                <div class="vehicle-icon {{ $pv['vehicle']->vehicle_type }}" style="width: 36px; height: 36px;">
                    <i class="fas fa-{{ $pv['vehicle']->vehicle_type === 'bus' ? 'bus' : 'van-shuttle' }}"></i>
                </div>
                <div>
                    <div style="font-weight: 700;">{{ $pv['vehicle']->vehicle_code }}</div>
                    <div style="font-size: 11px; color: var(--text-muted);">{{ $pv['vehicle']->brand }} {{ $pv['vehicle']->model }}</div>
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                <span>ซ่อม {{ $pv['count'] }} ครั้ง</span>
                <span style="font-weight: 600; color: var(--red);">฿{{ number_format($pv['total_cost']) }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Trips chart
    const ctx = document.getElementById('reportChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'เที่ยวทั้งหมด',
                    data: @json($chartTrips),
                    borderColor: 'rgba(245, 158, 11, 1)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: {{ $days <= 30 ? 3 : 0 }},
                },
                {
                    label: 'สำเร็จ',
                    data: @json($chartCompleted),
                    borderColor: 'rgba(34, 197, 94, 1)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: {{ $days <= 30 ? 3 : 0 }},
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

    // Cost donut
    const costCtx = document.getElementById('costChart').getContext('2d');
    new Chart(costCtx, {
        type: 'doughnut',
        data: {
            labels: ['ซ่อมตามกำหนด', 'ซ่อมฉุกเฉิน'],
            datasets: [{
                data: [{{ $scheduledCost }}, {{ $repairCost }}],
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
});
</script>
@endsection
