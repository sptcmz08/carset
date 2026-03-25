@extends('layouts.app')
@section('title', 'ฐานข้อมูลรถ & Health Check')

@section('content')
<div x-data="fleetApp()">

    <!-- Stats -->
    <div class="stat-grid">
        <div class="stat-card amber">
            <div class="stat-icon"><i class="fas fa-bus"></i></div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">รถทั้งหมด</div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
            <div class="stat-value">{{ $stats['active'] }}</div>
            <div class="stat-label">พร้อมใช้งาน</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon"><i class="fas fa-wrench"></i></div>
            <div class="stat-value">{{ $stats['minor'] }}</div>
            <div class="stat-label">ซ่อมเล็กน้อย (Minor)</div>
        </div>
        <div class="stat-card red">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-value">{{ $stats['major'] }}</div>
            <div class="stat-label">ซ่อมหนัก (Major)</div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div class="filter-tabs">
            <a href="{{ route('fleet', ['filter' => 'all']) }}" class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">ทั้งหมด</a>
            <a href="{{ route('fleet', ['filter' => 'active']) }}" class="filter-tab {{ $filter === 'active' ? 'active' : '' }}">🟢 พร้อมใช้</a>
            <a href="{{ route('fleet', ['filter' => 'warning']) }}" class="filter-tab {{ $filter === 'warning' ? 'active' : '' }}">🟡 ใกล้ครบรอบ</a>
            <a href="{{ route('fleet', ['filter' => 'minor_repair']) }}" class="filter-tab {{ $filter === 'minor_repair' ? 'active' : '' }}">🟠 Minor</a>
            <a href="{{ route('fleet', ['filter' => 'major_repair']) }}" class="filter-tab {{ $filter === 'major_repair' ? 'active' : '' }}">🔴 Major</a>
            <a href="{{ route('fleet', ['filter' => 'retired']) }}" class="filter-tab {{ $filter === 'retired' ? 'active' : '' }}">⚫ ปลดระวาง</a>
        </div>
    </div>

    <!-- Vehicle Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px;">
        @foreach($vehicles as $vehicle)
        <div class="card" style="padding: 0; overflow: hidden; cursor: pointer;" @click="showDetail({{ $vehicle->id }})">
            <!-- Health Status Bar -->
            <div style="height: 4px; background: var(--{{ $vehicle->health_status }});"></div>
            <div style="padding: 20px;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="vehicle-icon {{ $vehicle->vehicle_type }}" style="width: 48px; height: 48px; font-size: 20px;">
                            <i class="fas fa-{{ $vehicle->vehicle_type === 'bus' ? 'bus' : ($vehicle->vehicle_type === 'van' ? 'van-shuttle' : 'bus-simple') }}"></i>
                        </div>
                        <div>
                            <div style="font-size: 18px; font-weight: 700;">{{ $vehicle->vehicle_code }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $vehicle->brand }} {{ $vehicle->model }} • {{ $vehicle->license_plate }}</div>
                        </div>
                    </div>
                    <span class="badge badge-{{ $vehicle->health_status }}">
                        {{ $vehicle->health_icon }} {{ $vehicle->health_label }}
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px;">
                    <div style="padding: 10px; background: rgba(255,255,255,0.03); border-radius: 8px; text-align: center;">
                        <div style="font-size: 11px; color: var(--text-muted);">ประเภท</div>
                        <div style="font-size: 13px; font-weight: 600; margin-top: 2px;">{{ $vehicle->vehicle_type_thai }}</div>
                    </div>
                    <div style="padding: 10px; background: rgba(255,255,255,0.03); border-radius: 8px; text-align: center;">
                        <div style="font-size: 11px; color: var(--text-muted);">ที่นั่ง</div>
                        <div style="font-size: 13px; font-weight: 600; margin-top: 2px;">{{ $vehicle->capacity }} ที่นั่ง</div>
                    </div>
                    <div style="padding: 10px; background: rgba(255,255,255,0.03); border-radius: 8px; text-align: center;">
                        <div style="font-size: 11px; color: var(--text-muted);">เที่ยววิ่ง</div>
                        <div style="font-size: 13px; font-weight: 600; margin-top: 2px;">{{ $vehicle->daily_plans_count }}</div>
                    </div>
                </div>

                <!-- Mileage Progress -->
                <div style="margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <span style="color: var(--text-muted);">ไมล์ปัจจุบัน</span>
                        <span style="font-weight: 600;">{{ number_format($vehicle->current_mileage) }} / {{ number_format($vehicle->next_service_mileage) }} km</span>
                    </div>
                    @php $mileagePct = min(($vehicle->current_mileage / max($vehicle->next_service_mileage, 1)) * 100, 100); @endphp
                    <div class="progress-bar">
                        <div class="fill" style="width: {{ $mileagePct }}%; background: {{ $mileagePct > 90 ? 'var(--red)' : ($mileagePct > 75 ? 'var(--yellow)' : 'var(--green)') }};"></div>
                    </div>
                </div>

                <!-- Maintenance date -->
                @if($vehicle->next_maintenance_date)
                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-muted);">
                    <i class="fas fa-calendar"></i>
                    กำหนดซ่อม: {{ $vehicle->next_maintenance_date->format('d/m/Y') }}
                    @php $daysLeft = \Carbon\Carbon::now()->diffInDays($vehicle->next_maintenance_date, false); @endphp
                    @if($daysLeft < 0)
                        <span class="badge badge-red" style="font-size: 10px;">เลยกำหนด {{ abs($daysLeft) }} วัน</span>
                    @elseif($daysLeft <= 7)
                        <span class="badge badge-yellow" style="font-size: 10px;">อีก {{ $daysLeft }} วัน</span>
                    @endif
                </div>
                @endif

                @if($vehicle->repair_note)
                <div style="margin-top: 8px; padding: 8px 12px; background: var(--red-bg); border-radius: 8px; font-size: 12px; color: var(--red);">
                    <i class="fas fa-exclamation-circle"></i> {{ $vehicle->repair_note }}
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- Detail Modal -->
    <div x-show="showDetailModal" x-cloak class="modal-overlay" @click.self="showDetailModal = false">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3>
                    <span x-text="detail.health_icon"></span>
                    <span x-text="detail.vehicle?.vehicle_code"></span> — รายละเอียด
                </h3>
                <button class="modal-close" @click="showDetailModal = false"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <!-- Health Decision -->
                <div style="padding: 16px; border-radius: 12px; margin-bottom: 20px;"
                    :style="{ background: detail.health_status === 'green' ? 'var(--green-bg)' : detail.health_status === 'yellow' ? 'var(--yellow-bg)' : detail.health_status === 'orange' ? 'var(--orange-bg)' : 'var(--red-bg)' }">
                    <div style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">
                        <span x-text="detail.health_icon"></span>
                        <span x-text="detail.health_label"></span>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted);">
                        <template x-if="detail.health_status === 'green'">
                            <span>✅ ระบบประเมินว่ารถคันนี้พร้อมใช้งาน สามารถจัดเข้าแผนงานได้</span>
                        </template>
                        <template x-if="detail.health_status === 'yellow'">
                            <span>⚠️ ใกล้ถึงกำหนดซ่อมบำรุง ควรวางแผนนำเข้าศูนย์ซ่อม</span>
                        </template>
                        <template x-if="detail.health_status === 'orange'">
                            <span>🔧 มีปัญหาเล็กน้อย ยังสามารถวิ่งได้แต่ควรซ่อมโดยเร็ว</span>
                        </template>
                        <template x-if="detail.health_status === 'red'">
                            <span>🚫 ต้องหยุดวิ่งเพื่อซ่อมบำรุง ห้ามจัดเข้าแผนงาน</span>
                        </template>
                    </div>
                </div>

                <!-- Info Grid -->
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px;">
                    <div style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 10px;">
                        <div style="font-size: 11px; color: var(--text-muted);">ยี่ห้อ / รุ่น</div>
                        <div style="font-size: 14px; font-weight: 600;" x-text="(detail.vehicle?.brand || '') + ' ' + (detail.vehicle?.model || '')"></div>
                    </div>
                    <div style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 10px;">
                        <div style="font-size: 11px; color: var(--text-muted);">ทะเบียน</div>
                        <div style="font-size: 14px; font-weight: 600;" x-text="detail.vehicle?.license_plate"></div>
                    </div>
                    <div style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 10px;">
                        <div style="font-size: 11px; color: var(--text-muted);">ไมล์ปัจจุบัน</div>
                        <div style="font-size: 14px; font-weight: 600;" x-text="detail.vehicle ? Number(detail.vehicle.current_mileage).toLocaleString() + ' km' : ''"></div>
                    </div>
                    <div style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 10px;">
                        <div style="font-size: 11px; color: var(--text-muted);">ถึงกำหนดเช็คระยะ</div>
                        <div style="font-size: 14px; font-weight: 600;" x-text="detail.vehicle ? Number(detail.vehicle.next_service_mileage).toLocaleString() + ' km' : ''"></div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
                    <button class="btn btn-secondary btn-sm" @click="showMileageForm = !showMileageForm">
                        <i class="fas fa-tachometer-alt"></i> อัปเดตไมล์
                    </button>
                    <button class="btn btn-secondary btn-sm" @click="showStatusForm = !showStatusForm">
                        <i class="fas fa-wrench"></i> เปลี่ยนสถานะ
                    </button>
                </div>

                <!-- Mileage Update Form -->
                <div x-show="showMileageForm" style="background: rgba(255,255,255,0.03); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <h4 style="font-size: 14px; margin-bottom: 12px;"><i class="fas fa-tachometer-alt" style="color: var(--amber)"></i> อัปเดตเลขไมล์</h4>
                    <div style="display: flex; gap: 8px;">
                        <input type="number" x-model="newMileage" class="form-input" placeholder="เลขไมล์ใหม่" style="flex: 1;">
                        <button class="btn btn-primary btn-sm" @click="updateMileage()">บันทึก</button>
                    </div>
                    <div x-show="mileageResult" x-text="mileageResult" style="margin-top: 8px; font-size: 13px; color: var(--green);"></div>
                </div>

                <!-- Status Update Form -->
                <div x-show="showStatusForm" style="background: rgba(255,255,255,0.03); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <h4 style="font-size: 14px; margin-bottom: 12px;"><i class="fas fa-wrench" style="color: var(--amber)"></i> บันทึกอาการ / เปลี่ยนสถานะ</h4>
                    <div class="form-group">
                        <select x-model="newStatus" class="form-select">
                            <option value="active">🟢 พร้อมใช้งาน</option>
                            <option value="minor_repair">🟠 ซ่อมเล็กน้อย (Minor)</option>
                            <option value="major_repair">🔴 ซ่อมหนัก (Major)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea x-model="repairNote" class="form-input" rows="2" placeholder="บันทึกอาการ..."></textarea>
                    </div>
                    <button class="btn btn-primary btn-sm" @click="updateStatus()">บันทึก</button>
                    <div x-show="statusResult" style="margin-top: 8px; font-size: 13px;">
                        <span x-text="statusResultIcon"></span> <span x-text="statusResult"></span>
                    </div>
                </div>

                <!-- Maintenance History -->
                <h4 style="font-size: 14px; margin-bottom: 12px;">
                    <i class="fas fa-history" style="color: var(--amber)"></i> ประวัติซ่อมบำรุง
                </h4>
                <template x-if="detail.maintenance_logs && detail.maintenance_logs.length > 0">
                    <div style="max-height: 200px; overflow-y: auto;">
                        <template x-for="log in detail.maintenance_logs" :key="log.id">
                            <div style="display: flex; align-items: flex-start; gap: 12px; padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <div style="width: 8px; height: 8px; border-radius: 50%; margin-top: 6px;"
                                    :style="{ background: log.maintenance_type === 'scheduled' ? 'var(--blue)' : log.maintenance_type === 'minor_repair' ? 'var(--orange)' : 'var(--red)' }"></div>
                                <div style="flex: 1;">
                                    <div style="font-size: 13px; font-weight: 600;" x-text="log.description"></div>
                                    <div style="font-size: 11px; color: var(--text-muted);" x-text="log.service_date + ' • ฿' + Number(log.cost).toLocaleString()"></div>
                                </div>
                                <span class="badge" :class="{
                                    'badge-blue': log.maintenance_type === 'scheduled',
                                    'badge-orange': log.maintenance_type === 'minor_repair',
                                    'badge-red': log.maintenance_type === 'major_repair',
                                }" style="font-size: 10px;" x-text="log.maintenance_type === 'scheduled' ? 'ตามกำหนด' : log.maintenance_type === 'minor_repair' ? 'Minor' : 'Major'"></span>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="!detail.maintenance_logs || detail.maintenance_logs.length === 0">
                    <div class="empty-state" style="padding: 24px;">
                        <p style="font-size: 13px;">ไม่มีประวัติซ่อมบำรุง</p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>

<script>
function fleetApp() {
    return {
        showDetailModal: false,
        showMileageForm: false,
        showStatusForm: false,
        detail: {},
        newMileage: '',
        newStatus: 'active',
        repairNote: '',
        mileageResult: '',
        statusResult: '',
        statusResultIcon: '',

        async showDetail(vehicleId) {
            try {
                const res = await fetch('/fleet/' + vehicleId);
                this.detail = await res.json();
                this.newMileage = this.detail.vehicle?.current_mileage || '';
                this.newStatus = this.detail.vehicle?.status || 'active';
                this.repairNote = this.detail.vehicle?.repair_note || '';
                this.showMileageForm = false;
                this.showStatusForm = false;
                this.mileageResult = '';
                this.statusResult = '';
                this.showDetailModal = true;
            } catch (e) {
                console.error(e);
            }
        },

        async updateMileage() {
            try {
                const res = await fetch('/fleet/' + this.detail.vehicle.id + '/mileage', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ mileage: parseInt(this.newMileage) }),
                });
                const data = await res.json();
                if (data.success) {
                    this.detail.health_status = data.health_status;
                    this.detail.health_label = data.health_label;
                    this.detail.health_icon = data.health_icon;
                    this.detail.vehicle.current_mileage = this.newMileage;
                    this.mileageResult = '✅ อัปเดตเรียบร้อย — สถานะ: ' + data.health_icon + ' ' + data.health_label;
                }
            } catch (e) {
                this.mileageResult = '❌ เกิดข้อผิดพลาด';
            }
        },

        async updateStatus() {
            try {
                const res = await fetch('/fleet/' + this.detail.vehicle.id + '/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ status: this.newStatus, repair_note: this.repairNote }),
                });
                const data = await res.json();
                if (data.success) {
                    this.detail.health_status = data.health_status;
                    this.detail.health_label = data.health_label;
                    this.detail.health_icon = data.health_icon;
                    this.detail.vehicle.status = this.newStatus;
                    this.statusResultIcon = data.health_icon;
                    this.statusResult = 'บันทึกเรียบร้อย — ' + data.health_label;
                }
            } catch (e) {
                this.statusResult = '❌ เกิดข้อผิดพลาด';
            }
        }
    }
}
</script>
@endsection
