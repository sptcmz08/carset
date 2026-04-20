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
            <table class="data-table" style="min-width: 980px;">
                <thead>
                    <tr>
                        <th colspan="2" class="fleet-head" style="width: 160px; text-align: center;">Train</th>
                        <th rowspan="2" class="fleet-head" style="width: 120px; text-align: center;">Status</th>
                        <th rowspan="2" class="fleet-head" style="width: 140px; text-align: center;">KM.</th>
                        <th rowspan="2" class="fleet-head" style="text-align: center;">Note</th>
                        <th rowspan="2" class="fleet-head" style="width: 80px; text-align: center;">Fault</th>
                    </tr>
                    <tr>
                        <th class="fleet-subhead" style="width: 90px; text-align: center;">Type</th>
                        <th class="fleet-subhead" style="width: 70px; text-align: center;">No</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($typeOrder as $type)
                        @php
                            $sets = $groupedTrainSets->get($type, collect())->sortBy('display_order')->values();
                        @endphp
                        @if($sets->count() > 0)
                            @foreach($sets as $idx => $trainSet)
                                @php
                                    $statusLabel = match ($trainSet->health_status) {
                                        'available' => 'Ready',
                                        'warning' => 'Caution',
                                        'out_of_service' => 'Off Service',
                                        default => 'Ready',
                                    };
                                    $statusClass = match ($trainSet->health_status) {
                                        'available' => 'green',
                                        'warning' => 'yellow',
                                        'out_of_service' => 'red',
                                        default => 'green',
                                    };
                                    $mileageRank = $topMileageRanks[$trainSet->id] ?? null;
                                    $mileageClass = $mileageRank ? ('mileage-top mileage-top-' . $mileageRank) : '';
                                @endphp
                                <tr style="background: rgba(255,255,255,0.01);" data-train-id="{{ $trainSet->id }}">
                                    <td style="text-align: center; font-weight: 800; color: var(--text); vertical-align: middle; font-size: 16px;">
                                        {{ $type }}
                                    </td>
                                    <td style="font-weight: 700; text-align: center;">{{ $trainSet->display_order }}</td>
                                    <td>
                                        <span class="badge badge-{{ $statusClass }}" data-status-badge="{{ $trainSet->id }}" style="min-width: 110px; justify-content: center;">
                                            {{ $statusLabel }}
                                        </span>
                                    </td>
                                    <td class="{{ $mileageClass }}" data-km-cell="{{ $trainSet->id }}" style="position: relative;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <input
                                                type="number"
                                                class="form-input"
                                                style="padding: 8px 10px; border-radius: 10px; flex: 1;"
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
                                    <td>
                                        <input
                                            type="text"
                                            class="form-input"
                                            style="padding: 8px 10px; border-radius: 10px;"
                                            value="{{ $trainSet->planning_note }}"
                                            placeholder="-"
                                            @input="queuePlanningNote({{ $trainSet->id }}, $event.target.value)"
                                        >
                                    </td>
                                    <td style="text-align: center;">
                                        <button
                                            type="button"
                                            class="fault-info-btn"
                                            @click="toggleFault({{ $trainSet->id }})"
                                            aria-label="Fault info"
                                        >i</button>
                                    </td>
                                </tr>
                                <tr x-show="isFaultOpen({{ $trainSet->id }})" x-cloak>
                                    <td colspan="6" style="background: rgba(255,255,255,0.02);">
                                        <div style="padding: 14px 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                            <div>
                                                <div style="font-size: 12px; font-weight: 700; margin-bottom: 10px;">ประวัติ Fault</div>
                                                <div style="max-height: 190px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px;">
                                                    <template x-if="faultState[{{ $trainSet->id }}]?.loading">
                                                        <div style="padding: 12px; font-size: 12px; color: var(--text-muted);">กำลังโหลด...</div>
                                                    </template>
                                                    <template x-if="!faultState[{{ $trainSet->id }}]?.loading && (!faultState[{{ $trainSet->id }}]?.logs || faultState[{{ $trainSet->id }}]?.logs.length === 0)">
                                                        <div style="padding: 12px; font-size: 12px; color: var(--text-muted);">ยังไม่มีประวัติ</div>
                                                    </template>
                                                    <template x-for="log in (faultState[{{ $trainSet->id }}]?.logs || [])" :key="log.id">
                                                        <div style="padding: 10px 12px; border-bottom: 1px solid rgba(255,255,255,0.06);">
                                                            <div style="font-size: 12px; font-weight: 600;" x-text="log.description"></div>
                                                            <div style="font-size: 11px; color: var(--text-muted);" x-text="log.service_date"></div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            <div>
                                                <div style="font-size: 12px; font-weight: 700; margin-bottom: 10px;">บันทึก Fault</div>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                                    <div>
                                                        <div class="form-label">Fault Minor</div>
                                                        <input type="number" min="0" class="form-input" style="padding: 8px 10px;" value="{{ $trainSet->minor_fault_count ?? 0 }}" @input="queueFault({{ $trainSet->id }}, 'minor_fault_count', $event.target.value)">
                                                    </div>
                                                    <div>
                                                        <div class="form-label">Fault Major</div>
                                                        <input type="number" min="0" class="form-input" style="padding: 8px 10px;" value="{{ $trainSet->major_fault_count ?? 0 }}" @input="queueFault({{ $trainSet->id }}, 'major_fault_count', $event.target.value)">
                                                    </div>
                                                    <div style="grid-column: span 2;">
                                                        <div class="form-label">Overhaul</div>
                                                        <select class="form-select" style="padding: 8px 10px;" @change="queueFault({{ $trainSet->id }}, 'overhaul_required', $event.target.value)">
                                                            <option value="0" {{ $trainSet->overhaul_required ? '' : 'selected' }}>No</option>
                                                            <option value="1" {{ $trainSet->overhaul_required ? 'selected' : '' }}>Yes</option>
                                                        </select>
                                                    </div>
                                                    <div style="grid-column: span 2;">
                                                        <div class="form-label">รายละเอียด / อาการ / หมายเหตุเพิ่มเติม</div>
                                                        <textarea class="form-input" rows="2" style="padding: 8px 10px;" @input="queueFault({{ $trainSet->id }}, 'repair_note', $event.target.value)">{{ $trainSet->repair_note }}</textarea>
                                                    </div>
                                                </div>
                                                <div style="margin-top: 8px; font-size: 12px; color: var(--text-muted);" x-text="faultState[{{ $trainSet->id }}]?.saveMessage || ''"></div>
                                            </div>
                                        </div>
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

    .fault-info-btn {
        width: 26px;
        height: 26px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.22);
        background: rgba(255,255,255,0.02);
        color: rgba(255,255,255,0.85);
        font-weight: 800;
        font-size: 12px;
        line-height: 1;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .fault-info-btn:hover {
        background: rgba(255,255,255,0.08);
    }

    td.mileage-top {
        background: rgba(255,255,255,0.03);
    }

    td.mileage-top-1 {
        background: #fde047;
        color: #b91c1c;
        font-weight: 800;
    }

    td.mileage-top-2,
    td.mileage-top-3 {
        background: #ef4444;
        color: #fde047;
        font-weight: 800;
    }
</style>

<script>
function fleetTableApp() {
    return {
        openFaultIds: new Set(),
        faultState: {},
        planningNoteTimers: {},
        faultTimers: {},
        faultBuffers: {},
        mileageSaveState: {},

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
                if (!cell) {
                    return;
                }
                const rank = idx + 1;
                cell.classList.add('mileage-top', 'mileage-top-' + rank);
            });
        },

        isFaultOpen(id) {
            return this.openFaultIds.has(id);
        },

        async toggleFault(id) {
            if (this.openFaultIds.has(id)) {
                this.openFaultIds.delete(id);
                return;
            }

            this.openFaultIds.add(id);

            if (this.faultState[id]?.loaded) {
                return;
            }

            this.faultState[id] = { loading: true, loaded: false, logs: [], saveMessage: '' };
            try {
                const res = await fetch('/fleet/' + id);
                const data = await res.json();
                this.faultState[id].logs = (data.maintenance_logs || []).slice(0, 10);
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
            if (Number.isNaN(parsed)) {
                return;
            }

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

                if (!res.ok) {
                    throw new Error('Mileage update failed');
                }

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

                if (!res.ok) {
                    throw new Error('Planning note update failed');
                }
            } catch (e) {
                console.error(e);
            }
        },

        queueFault(id, field, value) {
            this.faultBuffers[id] = this.faultBuffers[id] || {};
            this.faultBuffers[id][field] = value;

            clearTimeout(this.faultTimers[id]);
            this.faultTimers[id] = setTimeout(() => this.updateFault(id), 400);
        },

        async updateFault(id) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            const payload = this.faultBuffers[id] || {};
            const normalized = {
                minor_fault_count: payload.minor_fault_count !== undefined ? parseInt(payload.minor_fault_count, 10) || 0 : undefined,
                major_fault_count: payload.major_fault_count !== undefined ? parseInt(payload.major_fault_count, 10) || 0 : undefined,
                overhaul_required: payload.overhaul_required !== undefined ? (String(payload.overhaul_required) === '1' || String(payload.overhaul_required).toLowerCase() === 'true') : undefined,
                repair_note: payload.repair_note !== undefined ? payload.repair_note : undefined,
            };

            Object.keys(normalized).forEach((key) => normalized[key] === undefined && delete normalized[key]);

            try {
                const res = await fetch('/fleet/' + id + '/fault', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                    body: JSON.stringify(normalized),
                });

                if (!res.ok) {
                    throw new Error('Fault update failed');
                }

                const data = await res.json();

                const badge = document.querySelector('[data-status-badge="' + id + '"]');
                if (badge && data && data.health_status) {
                    const mapped = this.statusFromHealth(data.health_status);
                    badge.textContent = mapped.statusLabel;
                    badge.classList.remove('badge-green', 'badge-yellow', 'badge-red');
                    badge.classList.add('badge-' + mapped.statusClass);
                }

                if (!this.faultState[id]) {
                    this.faultState[id] = { loading: false, loaded: false, logs: [], saveMessage: '' };
                }
                this.faultState[id].saveMessage = 'บันทึกแล้ว';
                setTimeout(() => {
                    if (this.faultState[id]) {
                        this.faultState[id].saveMessage = '';
                    }
                }, 1200);
            } catch (e) {
                if (!this.faultState[id]) {
                    this.faultState[id] = { loading: false, loaded: false, logs: [], saveMessage: '' };
                }
                this.faultState[id].saveMessage = 'บันทึกไม่สำเร็จ';
            }
        },
    }
}
</script>
@endsection
