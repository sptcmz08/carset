@extends('layouts.app')
@section('title', 'ฐานข้อมูลขบวน & Health Check')

@section('content')
<div x-data="fleetTableApp()">
    <div class="stat-grid">
        <div class="stat-card amber">
            <div class="stat-icon"><i class="fas fa-train-subway"></i></div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">ขบวนทั้งหมด</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
            <div class="stat-value">{{ $stats['available'] }}</div>
            <div class="stat-label">พร้อมให้บริการ</div>
        </div>
        <div class="stat-card blue">
            <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="stat-value">{{ $stats['warning'] }}</div>
            <div class="stat-label">ใกล้วาระซ่อม</div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon"><i class="fas fa-ban"></i></div>
            <div class="stat-value">{{ $stats['out_of_service'] }}</div>
            <div class="stat-label">งดให้บริการ</div>
        </div>
    </div>

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div class="filter-tabs">
            <a href="{{ route('fleet', ['filter' => 'all']) }}" class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">ทั้งหมด</a>
            <a href="{{ route('fleet', ['filter' => 'active']) }}" class="filter-tab {{ $filter === 'active' ? 'active' : '' }}">🟢 พร้อมใช้</a>
            <a href="{{ route('fleet', ['filter' => 'warning']) }}" class="filter-tab {{ $filter === 'warning' ? 'active' : '' }}">🟡 ใกล้ครบรอบ</a>
            <a href="{{ route('fleet', ['filter' => 'out_of_service']) }}" class="filter-tab {{ $filter === 'out_of_service' ? 'active' : '' }}">🛑 งดให้บริการ</a>
            <a href="{{ route('fleet', ['filter' => 'retired']) }}" class="filter-tab {{ $filter === 'retired' ? 'active' : '' }}">⚫ ปลดระวาง</a>
        </div>
    </div>

    @php
        $groupedTrainSets = $trainSets->groupBy('default_consist_type');
        $typeOrder = ['6', '4'];
    @endphp

    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table class="data-table fleet-table" style="min-width: 980px;">
                <thead>
                    <tr>
                        <th colspan="2" class="fleet-head" style="width: 160px; text-align: center;">Train</th>
                        <th rowspan="2" class="fleet-head" style="width: 120px; text-align: center;">Status</th>
                        <th rowspan="2" class="fleet-head" style="width: 160px; text-align: center;">KM.</th>
                        <th rowspan="2" class="fleet-head" style="text-align: center;">Note</th>
                        <th rowspan="2" class="fleet-head" style="width: 80px; text-align: center;">Fault</th>
                    </tr>
                    <tr>
                        <th class="fleet-subhead" style="width: 90px; text-align: center;">Type</th>
                        <th class="fleet-subhead" style="width: 70px; text-align: center;">No.</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($typeOrder as $type)
                        @php
                            $sets = $groupedTrainSets->get($type, collect())->sortBy('display_order')->values();
                            $typeCount = $sets->count();
                            $typeMidIdx = (int) floor(($typeCount - 1) / 2);
                        @endphp
                        @if($typeCount > 0)
                            @foreach($sets as $idx => $trainSet)
                                @php
                                    $isFirst = ($idx === 0);
                                    $isLast  = ($idx === $typeCount - 1);
                                    $isMid   = ($idx === $typeMidIdx);

                                    $statusLabel = match ($trainSet->health_status) {
                                        'available'    => 'Ready',
                                        'warning'      => 'Caution',
                                        'out_of_service' => 'Off Service',
                                        default        => 'Ready',
                                    };
                                    $statusClass = match ($trainSet->health_status) {
                                        'available'    => 'green',
                                        'warning'      => 'yellow',
                                        'out_of_service' => 'red',
                                        default        => 'green',
                                    };
                                    $mileageRank  = $topMileageRanks[$trainSet->id] ?? null;
                                    $mileageClass = $mileageRank ? ('mileage-top mileage-top-' . $mileageRank) : '';
                                @endphp

                                {{-- Main data row --}}
                                <tr class="fleet-data-row{{ $isFirst ? ' type-row-first' : '' }}{{ $isLast ? ' type-row-last' : '' }}"
                                    data-train-id="{{ $trainSet->id }}">

                                    {{-- Type grouped cell --}}
                                    <td class="type-group-cell{{ $isFirst ? ' tgc-first' : '' }}{{ $isLast ? ' tgc-last' : '' }}">
                                        @if($isMid)
                                            <span class="type-group-label">{{ $type }}</span>
                                        @endif
                                    </td>

                                    {{-- No. --}}
                                    <td style="font-weight: 700; text-align: center;">{{ $trainSet->display_order }}</td>

                                    {{-- Status (clickable dropdown) --}}
                                    <td style="text-align: center; position: relative;" x-data="{ statusOpen: false }">
                                        <button
                                            type="button"
                                            class="badge badge-{{ $statusClass }} status-badge-btn"
                                            data-status-badge="{{ $trainSet->id }}"
                                            data-status-val="{{ $trainSet->health_status }}"
                                            style="min-width: 110px; justify-content: center; cursor: pointer;"
                                            @click="statusOpen = !statusOpen"
                                            @click.outside="statusOpen = false"
                                        >
                                            <span class="status-dot"></span>
                                            <span class="status-text">{{ $statusLabel }}</span>
                                            <i class="fas fa-chevron-down" style="font-size: 9px; margin-left: 2px;"></i>
                                        </button>
                                        <div class="status-dropdown" x-show="statusOpen" x-cloak @click.outside="statusOpen = false">
                                            <button type="button" class="status-opt status-opt-green"
                                                @click="updateStatus({{ $trainSet->id }}, 'active'); statusOpen = false">
                                                <span class="status-dot-sm green"></span> Ready
                                            </button>
                                            <button type="button" class="status-opt status-opt-yellow"
                                                @click="updateStatus({{ $trainSet->id }}, 'minor_repair'); statusOpen = false">
                                                <span class="status-dot-sm yellow"></span> Caution
                                            </button>
                                            <button type="button" class="status-opt status-opt-red"
                                                @click="updateStatus({{ $trainSet->id }}, 'major_repair'); statusOpen = false">
                                                <span class="status-dot-sm red"></span> Off Service
                                            </button>
                                        </div>
                                    </td>

                                    {{-- KM --}}
                                    <td class="{{ $mileageClass }}" data-km-cell="{{ $trainSet->id }}" style="position: relative;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <input
                                                type="number"
                                                class="form-input km-input"
                                                style="padding: 8px 10px; border-radius: 10px; flex: 1; text-align: right; font-weight: 700; letter-spacing: 0.5px;"
                                                value="{{ $trainSet->current_mileage }}"
                                                min="0"
                                                data-km-input="{{ $trainSet->id }}"
                                                @change="updateMileage({{ $trainSet->id }}, $event.target.value)"
                                                @keydown.enter="$event.target.blur()"
                                            >
                                            <span
                                                x-show="mileageSaveState[{{ $trainSet->id }}] === 'saving'"
                                                x-cloak
                                                style="font-size: 11px; color: var(--amber); white-space: nowrap;"
                                            ><i class="fas fa-spinner fa-spin"></i></span>
                                            <span
                                                x-show="mileageSaveState[{{ $trainSet->id }}] === 'saved'"
                                                x-cloak
                                                style="font-size: 11px; color: var(--green); white-space: nowrap;"
                                            ><i class="fas fa-check"></i></span>
                                            <span
                                                x-show="mileageSaveState[{{ $trainSet->id }}] === 'error'"
                                                x-cloak
                                                style="font-size: 11px; color: var(--red); white-space: nowrap;"
                                            ><i class="fas fa-times"></i></span>
                                        </div>
                                    </td>

                                    {{-- Note --}}
                                    <td>
                                        <input
                                            type="text"
                                            class="form-input"
                                            style="padding: 8px 10px; border-radius: 10px;"
                                            value="{{ $trainSet->planning_note }}"
                                            placeholder="—"
                                            @input="queuePlanningNote({{ $trainSet->id }}, $event.target.value)"
                                        >
                                    </td>

                                    {{-- Fault icon --}}
                                    <td style="text-align: center;">
                                        <button
                                            type="button"
                                            class="fault-info-btn"
                                            :class="{ 'fault-info-btn-active': isFaultOpen({{ $trainSet->id }}) }"
                                            @click="toggleFault({{ $trainSet->id }})"
                                            aria-label="Fault info"
                                        ><i class="fas fa-circle-info"></i></button>
                                    </td>
                                </tr>

                                {{-- Fault sub-panel row --}}
                                <tr x-show="isFaultOpen({{ $trainSet->id }})" x-cloak class="fault-panel-row">
                                    <td colspan="6" style="padding: 0; background: rgba(15,23,42,0.6);">
                                        <div class="fault-panel-inner">

                                            {{-- Panel header --}}
                                            <div class="fault-panel-header">
                                                <div style="display: flex; align-items: center; gap: 8px;">
                                                    <i class="fas fa-circle-info" style="color: var(--amber);"></i>
                                                    <span style="font-weight: 700; font-size: 13px;">
                                                        ข้อมูล Fault — ขบวนที่ {{ $trainSet->display_order }}
                                                        (Type {{ $type }})
                                                    </span>
                                                    <span class="badge badge-{{ $statusClass }}" style="font-size: 11px;">{{ $statusLabel }}</span>
                                                </div>
                                                <button type="button" class="fault-close-btn" @click="toggleFault({{ $trainSet->id }})">
                                                    <i class="fas fa-chevron-up"></i>
                                                </button>
                                            </div>

                                            {{-- Panel body --}}
                                            <div class="fault-panel-body">

                                                {{-- Left: Fault History --}}
                                                <div class="fault-col">
                                                    <div class="fault-col-title">
                                                        <i class="fas fa-clock-rotate-left"></i> ประวัติ Fault
                                                    </div>
                                                    <div class="fault-history-list">
                                                        <template x-if="faultState[{{ $trainSet->id }}]?.loading">
                                                            <div class="fault-history-empty"><i class="fas fa-spinner fa-spin"></i> กำลังโหลด...</div>
                                                        </template>
                                                        <template x-if="!faultState[{{ $trainSet->id }}]?.loading && (!faultState[{{ $trainSet->id }}]?.logs || faultState[{{ $trainSet->id }}]?.logs.length === 0)">
                                                            <div class="fault-history-empty"><i class="fas fa-inbox"></i> ยังไม่มีประวัติ</div>
                                                        </template>
                                                        <template x-for="log in (faultState[{{ $trainSet->id }}]?.logs || [])" :key="log.id">
                                                            <div class="fault-history-item">
                                                                <div class="fault-history-desc" x-text="log.description"></div>
                                                                <div class="fault-history-date" x-text="formatDateTime(log.created_at || log.service_date)"></div>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>

                                                {{-- Right: Edit Fault --}}
                                                <div class="fault-col">
                                                    <div class="fault-col-title">
                                                        <i class="fas fa-pen-to-square"></i> บันทึก Fault
                                                    </div>
                                                    <div class="fault-edit-grid">
                                                        <div>
                                                            <div class="form-label">Fault Minor</div>
                                                            <input
                                                                type="number" min="0"
                                                                class="form-input minor-input"
                                                                style="padding: 8px 10px; text-align: center; font-weight: 700;"
                                                                value="{{ $trainSet->minor_fault_count ?? 0 }}"
                                                            >
                                                        </div>
                                                        <div>
                                                            <div class="form-label">Fault Major</div>
                                                            <input
                                                                type="number" min="0"
                                                                class="form-input major-input"
                                                                style="padding: 8px 10px; text-align: center; font-weight: 700;"
                                                                value="{{ $trainSet->major_fault_count ?? 0 }}"
                                                            >
                                                        </div>
                                                        <div>
                                                            <div class="form-label">Overhaul</div>
                                                            <input
                                                                type="number" min="0"
                                                                class="form-input overhaul-input"
                                                                style="padding: 8px 10px; text-align: center; font-weight: 700;"
                                                                value="{{ $trainSet->overhaul_required ? 1 : 0 }}"
                                                            >
                                                        </div>
                                                        <div style="grid-column: span 3;">
                                                            <div class="form-label">รายละเอียด / อาการ / หมายเหตุเพิ่มเติม</div>
                                                            <textarea
                                                                class="form-input repair-note-input"
                                                                rows="2"
                                                                style="padding: 8px 10px; resize: vertical;"
                                                            >{{ $trainSet->repair_note }}</textarea>
                                                        </div>
                                                        <div style="grid-column: span 3; text-align: right; margin-top: 8px;">
                                                            <span class="fault-save-msg" x-text="faultState[{{ $trainSet->id }}]?.saveMessage || ''" style="margin-right: 12px; font-weight: 600;"></span>
                                                            <button 
                                                                type="button" 
                                                                class="btn btn-primary" 
                                                                style="padding: 8px 16px; border-radius: 8px; border: none; background: #0ea5e9; color: white; font-weight: 700; cursor: pointer; transition: background 0.15s;"
                                                                onmouseover="this.style.background='#0284c7'"
                                                                onmouseout="this.style.background='#0ea5e9'"
                                                                @click="updateFault({{ $trainSet->id }}, $event)"
                                                            >
                                                                <i class="fas fa-save"></i> Save/บันทึก
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>{{-- /.fault-panel-body --}}
                                        </div>{{-- /.fault-panel-inner --}}
                                    </td>
                                </tr>

                            @endforeach
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }

    /* ── Table header ───────────────────────── */
    .fleet-head {
        background: rgba(255,255,255,0.06);
        color: var(--text-muted);
        font-weight: 800;
        letter-spacing: 1px;
    }
    .fleet-subhead {
        background: rgba(255,255,255,0.04);
        color: var(--text-muted);
        font-weight: 800;
        letter-spacing: 1px;
    }

    /* ── Type grouped column ────────────────── */
    .type-group-cell {
        background: rgba(255,255,255,0.025);
        border-right: 2px solid rgba(255,255,255,0.1) !important;
        border-top: 1px solid transparent !important;
        border-bottom: 1px solid transparent !important;
        text-align: center;
        vertical-align: middle;
        width: 90px;
        min-width: 70px;
    }
    .tgc-first {
        border-top: 2px solid rgba(255,255,255,0.14) !important;
    }
    .tgc-last {
        border-bottom: 2px solid rgba(255,255,255,0.14) !important;
    }
    .type-row-first td:not(.type-group-cell) {
        border-top: 2px solid rgba(255,255,255,0.1) !important;
    }
    .type-group-label {
        font-size: 26px;
        font-weight: 900;
        color: var(--text);
        display: block;
        letter-spacing: 1px;
    }

    /* ── Fault icon button ─────────────────── */
    .fault-info-btn {
        width: 30px; height: 30px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.22);
        background: rgba(255,255,255,0.04);
        color: rgba(255,255,255,0.75);
        font-size: 14px;
        cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center;
        transition: all 0.18s;
    }
    .fault-info-btn:hover {
        background: rgba(245,158,11,0.15);
        border-color: rgba(245,158,11,0.4);
        color: var(--amber);
    }
    .fault-info-btn-active {
        background: rgba(245,158,11,0.18) !important;
        border-color: rgba(245,158,11,0.5) !important;
        color: var(--amber) !important;
    }
    .fault-close-btn {
        background: none; border: none;
        color: var(--text-muted); font-size: 14px;
        cursor: pointer; padding: 4px 8px; border-radius: 6px;
        transition: color 0.15s;
    }
    .fault-close-btn:hover { color: var(--text); }

    /* ── Mileage top-3 highlight (premium) ─── */
    td.mileage-top { background: rgba(255,255,255,0.03); }

    /* Rank 1-3 — Yellow Background + Red Text */
    td.mileage-top-1, td.mileage-top-2, td.mileage-top-3 {
        background: rgba(250, 204, 21, 0.15) !important;
        border-left: 3px solid #facc15 !important;
    }
    td.mileage-top-1 .km-input, 
    td.mileage-top-2 .km-input, 
    td.mileage-top-3 .km-input {
        background: rgba(250, 204, 21, 0.08) !important;
        border-color: rgba(250, 204, 21, 0.35) !important;
        color: #ef4444 !important; /* Red text */
        font-weight: 900 !important;
        text-shadow: 0 0 10px rgba(239, 68, 68, 0.25);
    }

    /* rank badge overlay (top-right corner of cell) */
    td.mileage-top-1::after { content: '🥇'; position: absolute; top: 4px; right: 6px; font-size: 11px; opacity: .7; pointer-events: none; }
    td.mileage-top-2::after { content: '🥈'; position: absolute; top: 4px; right: 6px; font-size: 11px; opacity: .7; pointer-events: none; }
    td.mileage-top-3::after { content: '🥉'; position: absolute; top: 4px; right: 6px; font-size: 11px; opacity: .7; pointer-events: none; }

    /* ── Status badge & dropdown ─────────────── */
    .status-badge-btn {
        border: none;
        font-family: inherit;
        letter-spacing: 0.5px;
        transition: all 0.18s;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .status-badge-btn:hover { filter: brightness(1.15); }
    .status-dot {
        width: 7px; height: 7px; border-radius: 50%;
        background: currentColor; flex-shrink: 0;
        opacity: 0.85;
    }
    .status-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 50%; transform: translateX(-50%);
        background: #1e293b;
        border: 1px solid rgba(255,255,255,0.12);
        border-radius: 10px;
        padding: 6px;
        z-index: 99;
        min-width: 140px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.4);
        display: flex; flex-direction: column; gap: 2px;
    }
    .status-opt {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 12px; border-radius: 7px;
        border: none; background: transparent;
        color: var(--text); font-size: 13px; font-weight: 600;
        font-family: inherit; cursor: pointer;
        transition: background 0.15s;
        text-align: left; white-space: nowrap;
    }
    .status-opt:hover { background: rgba(255,255,255,0.08); }
    .status-dot-sm {
        width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
    }
    .status-dot-sm.green  { background: var(--green); box-shadow: 0 0 6px var(--green); }
    .status-dot-sm.yellow { background: var(--yellow); box-shadow: 0 0 6px var(--yellow); }
    .status-dot-sm.red    { background: var(--red); box-shadow: 0 0 6px var(--red); }

    /* ── Fault sub-panel ───────────────────── */
    .fault-panel-row > td {
        border-bottom: 2px solid rgba(245,158,11,0.18) !important;
    }
    .fault-panel-inner {
        border-top: 1px solid rgba(245,158,11,0.2);
    }
    .fault-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 20px;
        background: rgba(245,158,11,0.06);
        border-bottom: 1px solid rgba(245,158,11,0.12);
    }
    .fault-panel-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }
    @media (max-width: 768px) {
        .fault-panel-body { grid-template-columns: 1fr; }
    }
    .fault-col {
        padding: 16px 20px;
    }
    .fault-col:first-child {
        border-right: 1px solid rgba(255,255,255,0.06);
    }
    .fault-col-title {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 1.2px;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Fault history list */
    .fault-history-list {
        max-height: 180px;
        overflow-y: auto;
        border: 1px solid rgba(255,255,255,0.07);
        border-radius: 10px;
    }
    .fault-history-empty {
        padding: 14px 16px;
        font-size: 12px;
        color: var(--text-muted);
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .fault-history-item {
        padding: 10px 14px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: background 0.15s;
    }
    .fault-history-item:last-child { border-bottom: none; }
    .fault-history-item:hover { background: rgba(255,255,255,0.03); }
    .fault-history-desc {
        font-size: 12px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 2px;
    }
    .fault-history-date {
        font-size: 11px;
        color: var(--text-muted);
    }

    /* Fault edit grid */
    .fault-edit-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
    }
    .fault-save-msg {
        margin-top: 8px;
        font-size: 12px;
        color: var(--green);
        min-height: 18px;
    }
</style>

<script>
function fleetTableApp() {
    return {
        openFaultIds: {},
        faultState: {},
        planningNoteTimers: {},
        faultTimers: {},
        faultBuffers: {},
        mileageSaveState: {},

        async updateStatus(id, maintenanceStatus) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            try {
                const res = await fetch('/fleet/' + id + '/status', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ status: maintenanceStatus }),
                });
                if (!res.ok) throw new Error('Status update failed');
                const data = await res.json();
                // Update badge
                const badge = document.querySelector('[data-status-badge="' + id + '"]');
                if (badge && data.health_status) {
                    const mapped = this.statusFromHealth(data.health_status);
                    const dot  = badge.querySelector('.status-dot');
                    const text = badge.querySelector('.status-text');
                    if (text) text.textContent = mapped.statusLabel;
                    badge.className = badge.className
                        .replace(/badge-(green|yellow|red)/g, '')
                        .trim() + ' badge-' + mapped.statusClass;
                    badge.setAttribute('data-status-val', data.health_status);
                }
            } catch (e) {
                console.error(e);
            }
        },

        statusFromHealth(healthStatus) {
            const statusLabel = healthStatus === 'available'
                ? 'Ready'
                : (healthStatus === 'warning' ? 'Caution' : 'Off Service');
            const statusClass = healthStatus === 'available'
                ? 'green'
                : (healthStatus === 'warning' ? 'yellow' : 'red');
            return { statusLabel, statusClass };
        },

        refreshMileageHighlights() {
            const cells = Array.from(document.querySelectorAll('[data-km-cell]'));
            const items = cells
                .map((cell) => {
                    const id = cell.getAttribute('data-km-cell');
                    const input = document.querySelector('[data-km-input="' + id + '"]');
                    const mileage = input ? parseInt(input.value, 10) : 0;
                    return { id, mileage: Number.isNaN(mileage) ? 0 : mileage };
                })
                .sort((a, b) => b.mileage - a.mileage);

            const top = items.slice(0, 3).map((item) => item.id);

            cells.forEach((cell) => {
                cell.classList.remove('mileage-top', 'mileage-top-1', 'mileage-top-2', 'mileage-top-3');
            });

            top.forEach((id, idx) => {
                const cell = document.querySelector('[data-km-cell="' + id + '"]');
                if (!cell) return;
                cell.classList.add('mileage-top', 'mileage-top-' + (idx + 1));
            });
        },

        isFaultOpen(id) {
            return !!this.openFaultIds[id];
        },

        formatDateTime(dateStr) {
            if (!dateStr) return '';
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('th-TH', { year: 'numeric', month: 'short', day: 'numeric' }) + ' ' + d.toLocaleTimeString('th-TH', { hour: '2-digit', minute:'2-digit' });
        },

        async toggleFault(id, forceReload = false) {
            if (this.openFaultIds[id] && !forceReload) {
                this.openFaultIds[id] = false;
                return;
            }

            this.openFaultIds[id] = true;

            if (this.faultState[id]?.loaded && !forceReload) return;

            this.faultState[id] = { loading: true, loaded: false, logs: [], saveMessage: '' };
            try {
                const res = await fetch('/fleet/' + id);
                const data = await res.json();
                this.faultState[id].logs = (data.maintenance_logs || []).slice(0, 12);
                this.faultState[id].loaded = true;
            } catch (e) {
                this.faultState[id].logs = [];
            } finally {
                this.faultState[id].loading = false;
            }
        },

        async updateMileage(id, mileage) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const parsed = parseInt(mileage, 10);
            if (Number.isNaN(parsed)) return;

            this.mileageSaveState[id] = 'saving';

            try {
                const res = await fetch('/fleet/' + id + '/mileage', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({ mileage: parsed }),
                });

                if (!res.ok) throw new Error('Mileage update failed');

                const data = await res.json();
                const badge = document.querySelector('[data-status-badge="' + id + '"]');
                if (badge && data && data.health_status) {
                    const mapped = this.statusFromHealth(data.health_status);
                    badge.textContent = mapped.statusLabel;
                    badge.classList.remove('badge-green', 'badge-yellow', 'badge-red');
                    badge.classList.add('badge-' + mapped.statusClass);
                }

                this.refreshMileageHighlights();
                this.mileageSaveState[id] = 'saved';
                setTimeout(() => { this.mileageSaveState[id] = null; }, 1500);
            } catch (e) {
                console.error(e);
                this.mileageSaveState[id] = 'error';
                setTimeout(() => { this.mileageSaveState[id] = null; }, 2000);
            }
        },

        queuePlanningNote(id, note) {
            clearTimeout(this.planningNoteTimers[id]);
            this.planningNoteTimers[id] = setTimeout(() => this.updatePlanningNote(id, note), 350);
        },

        async updatePlanningNote(id, note) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            try {
                const res = await fetch('/fleet/' + id + '/planning-note', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify({ planning_note: note }),
                });
                if (!res.ok) throw new Error('Planning note update failed');
            } catch (e) {
                console.error(e);
            }
        },

        // Replaced by direct extraction in updateFault
        queueFault(id, field, value) {},

        async updateFault(id, event) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            
            const panel = event.target.closest('.fault-edit-grid');
            if(!panel) return;

            const minor = panel.querySelector('.minor-input').value;
            const major = panel.querySelector('.major-input').value;
            const overhaul = panel.querySelector('.overhaul-input').value;
            const note = panel.querySelector('.repair-note-input').value;

            const normalized = {
                minor_fault_count: parseInt(minor, 10) || 0,
                major_fault_count: parseInt(major, 10) || 0,
                overhaul_required: parseInt(overhaul, 10) > 0,
                repair_note: note,
            };

            if (!this.faultState[id]) {
                this.faultState[id] = { loading: false, loaded: false, logs: [], saveMessage: '' };
            }
            this.faultState[id].saveMessage = 'กำลังบันทึก...';

            try {
                const res = await fetch('/fleet/' + id + '/fault', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify(normalized),
                });

                if (!res.ok) throw new Error('Fault update failed');

                const data = await res.json();

                const badge = document.querySelector('[data-status-badge="' + id + '"]');
                if (badge && data && data.health_status) {
                    const mapped = this.statusFromHealth(data.health_status);
                    badge.textContent = mapped.statusLabel;
                    badge.classList.remove('badge-green', 'badge-yellow', 'badge-red');
                    badge.classList.add('badge-' + mapped.statusClass);
                }

                this.faultState[id].saveMessage = '✓ บันทึกสำเร็จ';
                setTimeout(() => {
                    if (this.faultState[id]) this.faultState[id].saveMessage = '';
                }, 2000);
                
                // Reload history
                await this.toggleFault(id, true);

            } catch (e) {
                if (!this.faultState[id]) {
                    this.faultState[id] = { loading: false, loaded: false, logs: [], saveMessage: '' };
                }
                this.faultState[id].saveMessage = '✗ บันทึกไม่สำเร็จ';
            }
        },
    }
}
</script>
@endsection
