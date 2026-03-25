@extends('layouts.app')
@section('title', 'แผนงานรายวัน')

@section('content')
<div x-data="dailyPlan()">

    <!-- Date Picker & Actions -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('daily-plan', ['date' => \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d')]) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-chevron-left"></i>
            </a>
            <form method="GET" action="{{ route('daily-plan') }}" style="display: flex; align-items: center; gap: 8px;">
                <input type="date" name="date" value="{{ $date }}" class="form-input" style="width: auto;" onchange="this.form.submit()">
            </form>
            <a href="{{ route('daily-plan', ['date' => \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d')]) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-chevron-right"></i>
            </a>
            <a href="{{ route('daily-plan') }}" class="btn btn-secondary btn-sm">วันนี้</a>
        </div>
        <button class="btn btn-primary" @click="showAddModal = true">
            <i class="fas fa-plus"></i> เพิ่มแผนงาน
        </button>
    </div>

    <!-- Summary cards -->
    <div class="stat-grid" style="margin-bottom: 24px;">
        <div class="stat-card blue">
            <div class="stat-icon"><i class="fas fa-list-check"></i></div>
            <div class="stat-value">{{ $plans->count() }}</div>
            <div class="stat-label">แผนงานทั้งหมด</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
            <div class="stat-value">{{ $plans->where('status', 'completed')->count() }}</div>
            <div class="stat-label">เสร็จสิ้น</div>
        </div>
        <div class="stat-card amber">
            <div class="stat-icon"><i class="fas fa-spinner"></i></div>
            <div class="stat-value">{{ $plans->where('status', 'in_progress')->count() }}</div>
            <div class="stat-label">กำลังวิ่ง</div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon"><i class="fas fa-ban"></i></div>
            <div class="stat-value">{{ $plans->where('status', 'cancelled')->count() }}</div>
            <div class="stat-label">ยกเลิก</div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 300px; gap: 20px;">
        <!-- Plans Table -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-table-list" style="color: var(--amber)"></i> รายการเที่ยววิ่ง — {{ \Carbon\Carbon::parse($date)->locale('th')->translatedFormat('d F Y') }}</h3>
            </div>

            @if($plans->count() > 0)
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>รถ</th>
                            <th>คนขับ</th>
                            <th>เส้นทาง</th>
                            <th>เวลาออก</th>
                            <th>เวลากลับ</th>
                            <th>ผู้โดยสาร</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $i => $plan)
                        <tr>
                            <td style="color: var(--text-muted);">{{ $i + 1 }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div class="vehicle-icon {{ $plan->vehicle->vehicle_type }}" style="width: 30px; height: 30px; font-size: 12px;">
                                        <i class="fas fa-{{ $plan->vehicle->vehicle_type === 'bus' ? 'bus' : 'van-shuttle' }}"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; font-size: 13px;">{{ $plan->vehicle->vehicle_code }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">{{ $plan->vehicle->license_plate }}</div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-size: 13px;">{{ $plan->driver_name }}</td>
                            <td style="font-size: 13px;">{{ $plan->route }}</td>
                            <td style="font-size: 13px;">{{ \Carbon\Carbon::parse($plan->departure_time)->format('H:i') }}</td>
                            <td style="font-size: 13px;">{{ $plan->return_time ? \Carbon\Carbon::parse($plan->return_time)->format('H:i') : '—' }}</td>
                            <td style="font-size: 13px;">{{ $plan->passengers }} คน</td>
                            <td><span class="badge badge-{{ $plan->status_color }}">{{ $plan->status_thai }}</span></td>
                            <td>
                                <div style="display: flex; gap: 4px;">
                                    <button class="btn btn-secondary btn-sm" @click="editPlan({{ $plan->toJson() }})" title="แก้ไข">
                                        <i class="fas fa-pen"></i>
                                    </button>
                                    <form method="POST" action="{{ route('daily-plan.destroy', $plan) }}" onsubmit="return confirm('ลบแผนงานนี้?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm" title="ลบ"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <i class="fas fa-calendar-xmark"></i>
                <p>ไม่มีแผนงานในวันที่เลือก</p>
            </div>
            @endif
        </div>

        <!-- Vehicle Status Sidebar -->
        <div class="card" style="align-self: start;">
            <div class="card-header">
                <h3 style="font-size: 14px;"><i class="fas fa-truck" style="color: var(--amber)"></i> สถานะรถ</h3>
            </div>
            <div style="space-y: 8px;">
                @foreach($vehicles as $v)
                <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; margin-bottom: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04);">
                    <span style="font-size: 14px;">{{ $v->health_icon }}</span>
                    <div style="flex: 1;">
                        <div style="font-size: 13px; font-weight: 600;">{{ $v->vehicle_code }}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">{{ $v->vehicle_type_thai }} • {{ number_format($v->current_mileage) }} km</div>
                    </div>
                    @if($v->health_status === 'green')
                        <span style="font-size: 10px; color: var(--green);">ว่าง</span>
                    @elseif($v->health_status === 'yellow')
                        <span style="font-size: 10px; color: var(--yellow);">ใกล้ซ่อม</span>
                    @else
                        <span style="font-size: 10px; color: var(--red);">ซ่อม</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div x-show="showAddModal" x-cloak class="modal-overlay" @click.self="showAddModal = false">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle" style="color: var(--amber)"></i> เพิ่มแผนงาน</h3>
                <button class="modal-close" @click="showAddModal = false"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" action="{{ route('daily-plan.store') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="plan_date" value="{{ $date }}">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">รถ *</label>
                            <select name="vehicle_id" class="form-select" required>
                                <option value="">— เลือกรถ —</option>
                                @foreach($vehicles->filter(fn($v) => $v->isAvailable()) as $v)
                                    <option value="{{ $v->id }}">{{ $v->vehicle_code }} — {{ $v->license_plate }} ({{ $v->vehicle_type_thai }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">คนขับ *</label>
                            <input type="text" name="driver_name" class="form-input" required placeholder="ชื่อคนขับ">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">เส้นทาง *</label>
                        <input type="text" name="route" class="form-input" required placeholder="เช่น กรุงเทพฯ - เชียงใหม่">
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">เวลาออก *</label>
                            <input type="time" name="departure_time" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">เวลากลับ</label>
                            <input type="time" name="return_time" class="form-input">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">จำนวนผู้โดยสาร</label>
                            <input type="number" name="passengers" class="form-input" min="0" value="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">หมายเหตุ</label>
                            <input type="text" name="note" class="form-input" placeholder="(ไม่บังคับ)">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showAddModal = false">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="showEditModal" x-cloak class="modal-overlay" @click.self="showEditModal = false">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-pen" style="color: var(--amber)"></i> แก้ไขแผนงาน</h3>
                <button class="modal-close" @click="showEditModal = false"><i class="fas fa-times"></i></button>
            </div>
            <form :action="editAction" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">คนขับ *</label>
                            <input type="text" name="driver_name" class="form-input" required x-model="editData.driver_name">
                        </div>
                        <div class="form-group">
                            <label class="form-label">เส้นทาง *</label>
                            <input type="text" name="route" class="form-input" required x-model="editData.route">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">เวลาออก *</label>
                            <input type="time" name="departure_time" class="form-input" required x-model="editData.departure_time">
                        </div>
                        <div class="form-group">
                            <label class="form-label">เวลากลับ</label>
                            <input type="time" name="return_time" class="form-input" x-model="editData.return_time">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">จำนวนผู้โดยสาร</label>
                            <input type="number" name="passengers" class="form-input" min="0" x-model="editData.passengers">
                        </div>
                        <div class="form-group">
                            <label class="form-label">สถานะ</label>
                            <select name="status" class="form-select" x-model="editData.status">
                                <option value="planned">วางแผนแล้ว</option>
                                <option value="in_progress">กำลังวิ่ง</option>
                                <option value="completed">เสร็จสิ้น</option>
                                <option value="cancelled">ยกเลิก</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">หมายเหตุ</label>
                        <input type="text" name="note" class="form-input" x-model="editData.note">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showEditModal = false">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> อัปเดต</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>

<script>
function dailyPlan() {
    return {
        showAddModal: false,
        showEditModal: false,
        editAction: '',
        editData: {},
        editPlan(plan) {
            this.editData = {
                driver_name: plan.driver_name,
                route: plan.route,
                departure_time: plan.departure_time ? plan.departure_time.substring(11, 16) : '',
                return_time: plan.return_time ? plan.return_time.substring(11, 16) : '',
                passengers: plan.passengers,
                status: plan.status,
                note: plan.note || '',
            };
            this.editAction = '/daily-plan/' + plan.id;
            this.showEditModal = true;
        }
    }
}
</script>
@endsection
