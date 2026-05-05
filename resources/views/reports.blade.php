@extends('layouts.app')
@section('title', 'รายงาน')

@section('content')
<style>
    .report-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .status-matrix-wrap {
        overflow-x: auto;
        border: 1px solid rgba(255,255,255,0.06);
        border-radius: 12px;
    }

    .status-matrix {
        width: 100%;
        min-width: 980px;
        border-collapse: collapse;
    }

    .status-matrix th,
    .status-matrix td {
        border-bottom: 1px solid rgba(255,255,255,0.05);
        border-right: 1px solid rgba(255,255,255,0.05);
        padding: 8px 10px;
        font-size: 12px;
        text-align: center;
        white-space: nowrap;
    }

    .status-matrix th {
        background: rgba(255,255,255,0.06);
        color: var(--text-muted);
        font-weight: 800;
    }

    .status-matrix .train-col {
        position: sticky;
        left: 0;
        z-index: 2;
        min-width: 100px;
        background: #111c31;
        text-align: left;
        color: var(--text);
        font-weight: 800;
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 76px;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
    }

    .status-chip.available {
        background: rgba(34,197,94,0.16);
        color: #86efac;
    }

    .status-chip.warning {
        background: rgba(234,179,8,0.16);
        color: #fde047;
    }

    .status-chip.out_of_service {
        background: rgba(239,68,68,0.18);
        color: #fca5a5;
    }

    .damage-row {
        vertical-align: top;
    }

    .damage-log-list {
        display: grid;
        gap: 8px;
    }

    .damage-log {
        padding: 10px 12px;
        border-radius: 10px;
        border: 1px solid rgba(255,255,255,0.07);
        background: rgba(255,255,255,0.035);
    }

    .damage-log.not-fit {
        background: rgba(239,68,68,0.12);
        border-color: rgba(239,68,68,0.22);
    }

    .damage-log-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 4px;
        font-size: 12px;
        font-weight: 800;
    }

    .damage-log-desc {
        color: var(--text-muted);
        font-size: 12px;
        line-height: 1.45;
    }
</style>

<div class="report-toolbar">
    <div class="filter-tabs">
        @foreach(['7' => '7 วัน', '14' => '14 วัน', '30' => '1 เดือน', '90' => '3 เดือน', '180' => '6 เดือน'] as $val => $label)
            <a href="{{ route('reports', ['period' => $val]) }}" class="filter-tab {{ $period == $val ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>
    <div style="font-size: 13px; color: var(--text-muted);">
        <i class="fas fa-calendar-range"></i>
        {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
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
        <div class="stat-label">สถานะ Caution</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-ban"></i></div>
        <div class="stat-value">{{ $outOfServiceAssignments }}</div>
        <div class="stat-label">Not Service</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-table-cells-large" style="color: var(--amber);"></i> แนวโน้มสถานะรายวัน 25 ขบวน</h3>
        <span class="badge badge-blue" style="font-size: 11px;">ช่วง {{ $periodDays }} วัน</span>
    </div>
    <div class="status-matrix-wrap">
        <table class="status-matrix">
            <thead>
                <tr>
                    <th class="train-col">Train Set</th>
                    @foreach($dates as $date)
                        <th>{{ $date->format('d/m') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($statusMatrix as $row)
                    <tr>
                        <td class="train-col">
                            {{ $row['train_set']->code }}
                            <span style="display: block; color: var(--text-muted); font-size: 10px; font-weight: 600;">{{ $row['train_set']->consist_label }}</span>
                        </td>
                        @foreach($row['statuses'] as $status)
                            <td>
                                <span class="status-chip {{ $status['status'] }}">{{ $status['label'] }}</span>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <h3><i class="fas fa-clock-rotate-left" style="color: var(--amber);"></i> ประวัติความเสียหายของแต่ละคัน</h3>
        <span class="badge badge-red" style="font-size: 11px;">{{ $damageHistoryTotal }} รายการ / {{ $affectedTrainSets }} ขบวน</span>
    </div>
    <div style="overflow-x: auto;">
        <table class="data-table" style="min-width: 920px;">
            <thead>
                <tr>
                    <th style="width: 130px;">Train Set</th>
                    <th>ประวัติในช่วง {{ $periodDays }} วัน</th>
                </tr>
            </thead>
            <tbody>
                @foreach($damageHistory as $item)
                    <tr class="damage-row">
                        <td>
                            <div style="font-weight: 800;">{{ $item['train_set']->code }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $item['train_set']->consist_label }}</div>
                        </td>
                        <td>
                            @if($item['logs']->count() > 0)
                                <div class="damage-log-list">
                                    @foreach($item['logs'] as $log)
                                        <div class="damage-log {{ $log['status'] === 'not_fit' ? 'not-fit' : '' }}">
                                            <div class="damage-log-title">
                                                <span>{{ $log['title'] }}</span>
                                                <span style="color: var(--text-muted); font-weight: 600;">{{ \Carbon\Carbon::parse($log['date'])->format('d/m/Y H:i') }}</span>
                                            </div>
                                            <div class="damage-log-desc">{{ $log['description'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span style="color: var(--text-muted); font-size: 13px;">ไม่มีประวัติความเสียหายในช่วงนี้</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
