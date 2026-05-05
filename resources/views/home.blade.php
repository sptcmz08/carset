@extends('layouts.app')
@section('title', 'Home')

@section('content')
<style>
    .home-shell {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .home-hero {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
        gap: 20px;
        align-items: stretch;
    }

    .home-hero-panel {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.16), rgba(59, 130, 246, 0.08));
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 14px;
        padding: 28px;
        overflow: hidden;
        position: relative;
    }

    .home-hero-panel::after {
        content: '';
        position: absolute;
        right: -90px;
        bottom: -90px;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(245, 158, 11, 0.08);
    }

    .home-kicker {
        color: var(--amber);
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    .home-title {
        font-size: 32px;
        line-height: 1.25;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .home-subtitle {
        color: var(--text-muted);
        font-size: 15px;
        max-width: 680px;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 24px;
        position: relative;
        z-index: 1;
    }

    .quick-action {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(15, 23, 42, 0.55);
        color: var(--text);
        text-decoration: none;
        transition: all 0.2s ease;
        min-height: 76px;
    }

    .quick-action:hover {
        transform: translateY(-2px);
        border-color: rgba(245, 158, 11, 0.35);
        background: rgba(15, 23, 42, 0.72);
    }

    .quick-action-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(245, 158, 11, 0.16);
        color: var(--amber);
        flex: 0 0 auto;
    }

    .quick-action strong {
        display: block;
        font-size: 14px;
        margin-bottom: 2px;
    }

    .quick-action span {
        color: var(--text-muted);
        display: block;
        font-size: 12px;
    }

    .today-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        height: 100%;
    }

    .today-summary-item {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 130px;
        border-radius: 14px;
        padding: 18px;
        border: 1px solid rgba(255,255,255,0.08);
    }

    .today-summary-item strong {
        font-size: 34px;
        line-height: 1;
        margin-bottom: 8px;
    }

    .today-summary-item span {
        color: var(--text-muted);
        font-size: 13px;
    }

    .today-summary-item.available {
        background: rgba(34, 197, 94, 0.12);
        color: #86efac;
    }

    .today-summary-item.warning {
        background: rgba(234, 179, 8, 0.12);
        color: #fde047;
    }

    .today-summary-item.out {
        background: rgba(239, 68, 68, 0.12);
        color: #fca5a5;
    }

    .home-section-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(320px, 0.55fr);
        gap: 20px;
    }

    .fleet-mini-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 10px;
    }

    .fleet-mini-card {
        padding: 14px;
        border-radius: 12px;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.06);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .fleet-mini-card .vehicle-icon {
        flex: 0 0 auto;
    }

    @media (max-width: 1100px) {
        .home-hero,
        .home-section-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .home-title {
            font-size: 24px;
        }

        .quick-actions,
        .today-summary {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="home-shell">
    <section class="home-hero">
        <div class="home-hero-panel">
            <div class="home-kicker">SetCar Operation</div>
            <h1 class="home-title">ภาพรวมแผนเดินรถรายวัน</h1>
            <p class="home-subtitle">
                {{ \Carbon\Carbon::today()->locale('th')->translatedFormat('วันl ที่ d F Y') }}
            </p>

            <div class="quick-actions" aria-label="Quick actions">
                <a href="{{ route('daily-plan') }}" class="quick-action">
                    <span class="quick-action-icon"><i class="fas fa-calendar-day"></i></span>
                    <span>
                        <strong>แผนเดินรถรายวัน</strong>
                        <span>เปิดสมุดแผนวันนี้</span>
                    </span>
                </a>
                <a href="{{ route('fleet') }}" class="quick-action">
                    <span class="quick-action-icon"><i class="fas fa-train-subway"></i></span>
                    <span>
                        <strong>ฐานข้อมูลขบวน</strong>
                        <span>ตรวจสถานะและข้อมูลขบวน</span>
                    </span>
                </a>
                <a href="{{ route('reports') }}" class="quick-action">
                    <span class="quick-action-icon"><i class="fas fa-chart-bar"></i></span>
                    <span>
                        <strong>รายงาน</strong>
                        <span>ดูสรุปการใช้งาน</span>
                    </span>
                </a>
            </div>
        </div>

        <div class="today-summary">
            <div class="today-summary-item available">
                <strong>{{ $todayPlanned }}</strong>
                <span>พร้อมให้บริการ</span>
            </div>
            <div class="today-summary-item warning">
                <strong>{{ $todayWarning }}</strong>
                <span>เฝ้าระวัง</span>
            </div>
            <div class="today-summary-item out">
                <strong>{{ $todayOut }}</strong>
                <span>งดให้บริการ</span>
            </div>
        </div>
    </section>

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

    <section class="home-section-grid">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-check" style="color: var(--amber);"></i> แผนเดินรถวันนี้</h3>
                <a href="{{ route('daily-plan') }}" class="btn btn-secondary btn-sm">เปิดแผน <i class="fas fa-arrow-right"></i></a>
            </div>

            @if($todayEntries->count() > 0)
                <div style="overflow-x: auto;">
                    <table class="data-table" style="min-width: 680px;">
                        <thead>
                            <tr>
                                <th>Train Set</th>
                                <th>Berth</th>
                                <th>Run</th>
                                <th>Dep. Plan</th>
                                <th>Platform</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($todayEntries->take(12) as $entry)
                                <tr>
                                    <td style="font-size: 13px; font-weight: 700;">{{ $entry->trainSet?->code ?? '-' }}</td>
                                    <td style="font-size: 13px;">{{ $entry->berth_no ?: '-' }}</td>
                                    <td style="font-size: 13px;">{{ $entry->outbound_run_no ?: '-' }}</td>
                                    <td style="font-size: 13px;">{{ $entry->departure_plan_time ?: '-' }}</td>
                                    <td style="font-size: 13px;">{{ $entry->ktw_platform ?: '-' }}</td>
                                    <td><span class="badge badge-{{ $entry->status_color }}">{{ $entry->status_label }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-calendar-xmark"></i>
                    <p>ยังไม่มีการบันทึกสมุดแผนวันนี้</p>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-bell" style="color: var(--amber);"></i> แจ้งเตือนสำคัญ</h3>
                <span class="badge badge-red" style="font-size: 11px;">{{ count($alerts) }} รายการ</span>
            </div>
            <div style="max-height: 430px; overflow-y: auto;">
                @forelse($alerts as $alert)
                    <div class="alert-item {{ $alert['level'] }}">
                        <div class="alert-icon">
                            @if($alert['type'] === 'mileage' && $alert['level'] === 'danger')
                                <i class="fas fa-tachometer-alt" style="color: var(--red);"></i>
                            @elseif($alert['type'] === 'mileage')
                                <i class="fas fa-tachometer-alt" style="color: var(--yellow);"></i>
                            @elseif($alert['level'] === 'danger')
                                <i class="fas fa-screwdriver-wrench" style="color: var(--red);"></i>
                            @else
                                <i class="fas fa-screwdriver-wrench" style="color: var(--yellow);"></i>
                            @endif
                        </div>
                        <div class="alert-text">
                            <div class="alert-vehicle">{{ $alert['trainSet']->code }} - {{ $alert['trainSet']->consist_label }}</div>
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
    </section>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-th-large" style="color: var(--amber);"></i> ภาพรวมขบวน</h3>
            <a href="{{ route('fleet') }}" class="btn btn-secondary btn-sm">ดูทั้งหมด <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="fleet-mini-grid">
            @foreach($trainSets as $trainSet)
                <div class="fleet-mini-card">
                    <div class="vehicle-icon bus">
                        <i class="fas fa-train-subway"></i>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 14px; font-weight: 700;">{{ $trainSet->code }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $trainSet->consist_label }} - {{ $trainSet->default_berth_no ?: '-' }}</div>
                    </div>
                    <span class="badge badge-{{ $trainSet->health_badge_class }}">{{ $trainSet->health_icon }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
