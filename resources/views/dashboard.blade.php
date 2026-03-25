@extends('layouts.app')
@section('title', 'แดชบอร์ด')

@section('content')
<!-- Summary Stats -->
<div class="stat-grid">
    <div class="stat-card amber">
        <div class="stat-icon"><i class="fas fa-bus"></i></div>
        <div class="stat-value">{{ $totalVehicles }}</div>
        <div class="stat-label">รถทั้งหมด</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value">{{ $activeCount }}</div>
        <div class="stat-label">พร้อมใช้งาน</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-route"></i></div>
        <div class="stat-value">{{ $todayTrips }}</div>
        <div class="stat-label">เที่ยววิ่งวันนี้</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-icon"><i class="fas fa-wrench"></i></div>
        <div class="stat-value">{{ $minorCount + $majorCount }}</div>
        <div class="stat-label">อยู่ระหว่างซ่อม</div>
    </div>
</div>

<div class="grid-2">
    <!-- Trip Status Today -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-calendar-check" style="color: var(--amber)"></i> สถานะเที่ยววิ่งวันนี้</h3>
        </div>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 20px;">
            <div style="text-align: center; padding: 16px; background: var(--blue-bg); border-radius: 12px;">
                <div style="font-size: 24px; font-weight: 700; color: var(--blue);">{{ $todayTrips }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">ทั้งหมด</div>
            </div>
            <div style="text-align: center; padding: 16px; background: rgba(245,158,11,0.15); border-radius: 12px;">
                <div style="font-size: 24px; font-weight: 700; color: var(--amber);">{{ $inProgress }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">กำลังวิ่ง</div>
            </div>
            <div style="text-align: center; padding: 16px; background: var(--green-bg); border-radius: 12px;">
                <div style="font-size: 24px; font-weight: 700; color: var(--green);">{{ $completed }}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">เสร็จแล้ว</div>
            </div>
        </div>

        @if($todayPlans->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>รถ</th>
                    <th>เส้นทาง</th>
                    <th>เวลา</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($todayPlans->take(5) as $plan)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div class="vehicle-icon {{ $plan->vehicle->vehicle_type }}">
                                <i class="fas fa-{{ $plan->vehicle->vehicle_type === 'bus' ? 'bus' : 'van-shuttle' }}"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 13px;">{{ $plan->vehicle->vehicle_code }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $plan->vehicle->license_plate }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size: 13px;">{{ $plan->route }}</td>
                    <td style="font-size: 13px;">{{ \Carbon\Carbon::parse($plan->departure_time)->format('H:i') }}</td>
                    <td><span class="badge badge-{{ $plan->status_color }}">{{ $plan->status_thai }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="empty-state">
            <i class="fas fa-calendar-xmark"></i>
            <p>ยังไม่มีแผนงานวันนี้</p>
        </div>
        @endif
    </div>

    <!-- Alerts -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-bell" style="color: var(--amber)"></i> การแจ้งเตือน</h3>
            <span class="badge badge-red" style="font-size: 11px;">{{ count($alerts) }} รายการ</span>
        </div>
        <div style="max-height: 380px; overflow-y: auto;">
            @forelse($alerts as $alert)
            <div class="alert-item {{ $alert['level'] }}">
                <div class="alert-icon">
                    @if($alert['type'] === 'mileage')
                        <i class="fas fa-tachometer-alt" style="color: var(--yellow)"></i>
                    @elseif($alert['type'] === 'schedule')
                        <i class="fas fa-calendar-exclamation" style="color: {{ $alert['level'] === 'danger' ? 'var(--red)' : 'var(--yellow)' }}"></i>
                    @else
                        <i class="fas fa-tools" style="color: {{ $alert['level'] === 'danger' ? 'var(--red)' : 'var(--orange)' }}"></i>
                    @endif
                </div>
                <div class="alert-text">
                    <div class="alert-vehicle">{{ $alert['vehicle']->vehicle_code }} — {{ $alert['vehicle']->license_plate }}</div>
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

<!-- Chart -->
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3><i class="fas fa-chart-line" style="color: var(--amber)"></i> สถิติเที่ยววิ่ง 7 วันล่าสุด</h3>
    </div>
    <div style="position: relative; height: 280px;">
        <canvas id="tripsChart"></canvas>
    </div>
</div>

<!-- Vehicle Fleet Overview -->
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3><i class="fas fa-th-large" style="color: var(--amber)"></i> ภาพรวมสถานะรถ</h3>
        <a href="{{ route('fleet') }}" class="btn btn-secondary btn-sm">ดูทั้งหมด <i class="fas fa-arrow-right"></i></a>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px;">
        @php $dashVehicles = \App\Models\Vehicle::where('status','!=','retired')->get(); @endphp
        @foreach($dashVehicles as $v)
        <div style="padding: 16px; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; gap: 12px;">
            <div class="vehicle-icon {{ $v->vehicle_type }}">
                <i class="fas fa-{{ $v->vehicle_type === 'bus' ? 'bus' : ($v->vehicle_type === 'van' ? 'van-shuttle' : 'bus-simple') }}"></i>
            </div>
            <div style="flex: 1;">
                <div style="font-size: 14px; font-weight: 600;">{{ $v->vehicle_code }}</div>
                <div style="font-size: 11px; color: var(--text-muted);">{{ $v->brand }} {{ $v->model }}</div>
            </div>
            <span class="badge badge-{{ $v->health_status }}">{{ $v->health_icon }}</span>
        </div>
        @endforeach
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('tripsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'เที่ยววิ่ง',
                data: @json($chartData),
                backgroundColor: 'rgba(245, 158, 11, 0.3)',
                borderColor: 'rgba(245, 158, 11, 1)',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
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
