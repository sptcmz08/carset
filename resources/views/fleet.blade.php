@extends('layouts.app')
@section('title', 'ฐานข้อมูลขบวน & Health Check')

@section('content')
<div x-data="fleetApp()">
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
            <a href="{{ route('fleet', ['filter' => 'minor_repair']) }}" class="filter-tab {{ $filter === 'minor_repair' ? 'active' : '' }}">🔧 Minor</a>
            <a href="{{ route('fleet', ['filter' => 'major_repair']) }}" class="filter-tab {{ $filter === 'major_repair' ? 'active' : '' }}">🛑 Major</a>
            <a href="{{ route('fleet', ['filter' => 'retired']) }}" class="filter-tab {{ $filter === 'retired' ? 'active' : '' }}">⚫ ปลดระวาง</a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 16px;">
        @foreach($trainSets as $trainSet)
            @php
                $mileagePct = $trainSet->next_service_mileage > 0 ? min(($trainSet->current_mileage / $trainSet->next_service_mileage) * 100, 100) : 0;
                $maintenanceBadgeClass = in_array($trainSet->maintenance_status, ['major_repair', 'retired'], true)
                    ? 'red'
                    : ($trainSet->maintenance_status === 'minor_repair' ? 'yellow' : 'green');
            @endphp
            <div class="card" style="padding: 0; overflow: hidden; cursor: pointer;" @click="showDetail({{ $trainSet->id }})">
                <div class="train-set-status-bar" data-color="{{ $trainSet->health_badge_class }}" style="height: 4px;"></div>
                <div style="padding: 20px;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: rgba(59,130,246,0.15); color: var(--blue); font-size: 20px;">
                                <i class="fas fa-train-subway"></i>
                            </div>
                            <div>
                                <div style="font-size: 18px; font-weight: 700;">{{ $trainSet->code }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $trainSet->consist_label }}</div>
                            </div>
                        </div>
                        <span class="badge badge-{{ $trainSet->health_badge_class }}">{{ $trainSet->health_icon }} {{ $trainSet->health_label }}</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 16px;">
                        <div style="padding: 10px; background: rgba(255,255,255,0.03); border-radius: 8px; text-align: center;">
                            <div style="font-size: 11px; color: var(--text-muted);">Type</div>
                            <div style="font-size: 13px; font-weight: 600; margin-top: 2px;">{{ $trainSet->default_consist_type }} Cars</div>
                        </div>
                        <div style="padding: 10px; background: rgba(255,255,255,0.03); border-radius: 8px; text-align: center;">
                            <div style="font-size: 11px; color: var(--text-muted);">ไมล์ปัจจุบัน</div>
                            <div style="font-size: 13px; font-weight: 600; margin-top: 2px;">{{ number_format($trainSet->current_mileage) }}</div>
                        </div>
                        <div style="padding: 10px; background: rgba(255,255,255,0.03); border-radius: 8px; text-align: center;">
                            <div style="font-size: 11px; color: var(--text-muted);">กำหนดถัดไป</div>
                            <div style="font-size: 13px; font-weight: 600; margin-top: 2px;">{{ number_format($trainSet->next_service_mileage) }}</div>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                            <span style="color: var(--text-muted);">Mileage Remaining</span>
                            <span style="font-weight: 600;">{{ number_format($trainSet->mileage_remaining) }} km</span>
                        </div>
                        <div class="progress-bar">
                            <div
                                class="fill mileage-fill"
                                data-width="{{ $mileagePct }}"
                                data-color="{{ $trainSet->health_status === 'out_of_service' ? 'red' : ($trainSet->health_status === 'warning' ? 'yellow' : 'green') }}"
                            ></div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; font-size: 12px; color: var(--text-muted);">
                        <span><i class="fas fa-calendar"></i> {{ $trainSet->next_maintenance_date?->format('d/m/Y') ?: 'ยังไม่ระบุวันซ่อม' }}</span>
                        <span class="badge badge-{{ $maintenanceBadgeClass }}">{{ $trainSet->maintenance_status_label }}</span>
                    </div>

                    @if($trainSet->repair_note)
                    <div style="margin-top: 8px; padding: 8px 12px; background: var(--red-bg); border-radius: 8px; font-size: 12px; color: var(--red);">
                        <i class="fas fa-circle-exclamation"></i> {{ $trainSet->repair_note }}
                    </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div x-show="showDetailModal" x-cloak class="modal-overlay" @click.self="showDetailModal = false">
        <div class="modal-content" style="max-width: 760px;">
            <div class="modal-header">
                <h3><span x-text="detail.health_icon"></span> <span x-text="detail.train_set?.code"></span> — Health Check</h3>
                <button class="modal-close" @click="showDetailModal = false"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div style="padding: 16px; border-radius: 12px; margin-bottom: 20px;"
                    :style="{ background: detail.health_status === 'available' ? 'var(--green-bg)' : detail.health_status === 'warning' ? 'var(--yellow-bg)' : 'var(--red-bg)' }">
                    <div style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">
                        <span x-text="detail.health_icon"></span>
                        <span x-text="detail.health_label"></span>
                    </div>
                    <div style="font-size: 13px; color: var(--text-muted);">
                        <template x-if="detail.health_status === 'available'">
                            <span>ระบบประเมินว่าขบวนนี้พร้อมให้บริการ สามารถนำเข้าวางแผนรายวันได้</span>
                        </template>
                        <template x-if="detail.health_status === 'warning'">
                            <span>ขบวนยังใช้งานได้ แต่ใกล้วาระซ่อมหรือมี minor issue ควรเฝ้าระวัง</span>
                        </template>
                        <template x-if="detail.health_status === 'out_of_service'">
                            <span>ขบวนนี้ต้องงดให้บริการจนกว่าจะเคลียร์ condition monitoring หรือซ่อมบำรุงเสร็จ</span>
                        </template>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px;">
                    <div style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 10px;">
                        <div style="font-size: 11px; color: var(--text-muted);">Type</div>
                        <div style="font-size: 14px; font-weight: 600;" x-text="(detail.train_set?.default_consist_type || '-') + ' Cars'"></div>
                    </div>
                    <div style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 10px;">
                        <div style="font-size: 11px; color: var(--text-muted);">ไมล์ปัจจุบัน</div>
                        <div style="font-size: 14px; font-weight: 600;" x-text="detail.train_set ? Number(detail.train_set.current_mileage).toLocaleString() + ' km' : '-'"></div>
                    </div>
                    <div style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 10px;">
                        <div style="font-size: 11px; color: var(--text-muted);">ถึงกำหนดเช็คระยะ</div>
                        <div style="font-size: 14px; font-weight: 600;" x-text="detail.train_set ? Number(detail.train_set.next_service_mileage).toLocaleString() + ' km' : '-'"></div>
                    </div>
                    <div style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 10px;">
                        <div style="font-size: 11px; color: var(--text-muted);">ซ่อมล่าสุด</div>
                        <div style="font-size: 14px; font-weight: 600;" x-text="detail.train_set?.last_maintenance_date || '-'"></div>
                    </div>
                    <div style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: 10px;">
                        <div style="font-size: 11px; color: var(--text-muted);">กำหนดซ่อมถัดไป</div>
                        <div style="font-size: 14px; font-weight: 600;" x-text="detail.train_set?.next_maintenance_date || '-'"></div>
                    </div>
                </div>

                <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
                    <button class="btn btn-secondary btn-sm" @click="showMileageForm = !showMileageForm"><i class="fas fa-tachometer-alt"></i> อัปเดตไมล์</button>
                    <button class="btn btn-secondary btn-sm" @click="showStatusForm = !showStatusForm"><i class="fas fa-screwdriver-wrench"></i> เปลี่ยนสถานะ</button>
                    <button class="btn btn-secondary btn-sm" @click="showScheduleForm = !showScheduleForm"><i class="fas fa-calendar-days"></i> อัปเดตกำหนดซ่อม</button>
                </div>

                <div x-show="showMileageForm" style="background: rgba(255,255,255,0.03); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <h4 style="font-size: 14px; margin-bottom: 12px;"><i class="fas fa-tachometer-alt" style="color: var(--amber);"></i> อัปเดตเลขไมล์</h4>
                    <div style="display: flex; gap: 8px;">
                        <input type="number" x-model="newMileage" class="form-input" placeholder="เลขไมล์ใหม่" style="flex: 1;">
                        <button class="btn btn-primary btn-sm" @click="updateMileage()">บันทึก</button>
                    </div>
                    <div x-show="mileageResult" x-text="mileageResult" style="margin-top: 8px; font-size: 13px; color: var(--green);"></div>
                </div>

                <div x-show="showStatusForm" style="background: rgba(255,255,255,0.03); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <h4 style="font-size: 14px; margin-bottom: 12px;"><i class="fas fa-screwdriver-wrench" style="color: var(--amber);"></i> บันทึกอาการ / เปลี่ยนสถานะ</h4>
                    <div class="form-group">
                        <select x-model="newStatus" class="form-select">
                            <option value="active">🟢 พร้อมใช้งาน</option>
                            <option value="minor_repair">🟡 Minor / ยังใช้งานได้</option>
                            <option value="major_repair">🔴 Major / หยุดวิ่ง</option>
                            <option value="retired">⚫ ปลดระวาง</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea x-model="repairNote" class="form-input" rows="2" placeholder="บันทึกอาการ..."></textarea>
                    </div>
                    <button class="btn btn-primary btn-sm" @click="updateStatus()">บันทึก</button>
                    <div x-show="statusResult" style="margin-top: 8px; font-size: 13px;">
                        <span x-text="statusResult"></span>
                    </div>
                </div>

                <div x-show="showScheduleForm" style="background: rgba(255,255,255,0.03); border-radius: 12px; padding: 16px; margin-bottom: 16px;">
                    <h4 style="font-size: 14px; margin-bottom: 12px;"><i class="fas fa-calendar-days" style="color: var(--amber);"></i> อัปเดตกำหนดซ่อม</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <input type="number" x-model="nextServiceMileage" class="form-input" placeholder="Next service mileage">
                        <input type="date" x-model="lastMaintenanceDate" class="form-input">
                        <input type="date" x-model="nextMaintenanceDate" class="form-input" style="grid-column: span 2;">
                    </div>
                    <div style="margin-top: 10px;">
                        <button class="btn btn-primary btn-sm" @click="updateSchedule()">บันทึกกำหนดซ่อม</button>
                    </div>
                    <div x-show="scheduleResult" x-text="scheduleResult" style="margin-top: 8px; font-size: 13px; color: var(--green);"></div>
                </div>

                <h4 style="font-size: 14px; margin-bottom: 12px;"><i class="fas fa-history" style="color: var(--amber);"></i> ประวัติซ่อมบำรุง</h4>
                <template x-if="detail.maintenance_logs && detail.maintenance_logs.length > 0">
                    <div style="max-height: 220px; overflow-y: auto;">
                        <template x-for="log in detail.maintenance_logs" :key="log.id">
                            <div style="display: flex; align-items: flex-start; gap: 12px; padding: 10px; border-bottom: 1px solid rgba(255,255,255,0.04);">
                                <div style="width: 8px; height: 8px; border-radius: 50%; margin-top: 6px;"
                                    :style="{ background: log.maintenance_type === 'scheduled' ? 'var(--blue)' : log.maintenance_type === 'minor_repair' ? 'var(--yellow)' : 'var(--red)' }"></div>
                                <div style="flex: 1;">
                                    <div style="font-size: 13px; font-weight: 600;" x-text="log.description"></div>
                                    <div style="font-size: 11px; color: var(--text-muted);" x-text="log.service_date + ' • ฿' + Number(log.cost).toLocaleString()"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="!detail.maintenance_logs || detail.maintenance_logs.length === 0">
                    <div class="empty-state" style="padding: 24px;">
                        <p style="font-size: 13px;">ยังไม่มีประวัติซ่อมบำรุง</p>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<style>[x-cloak] { display: none !important; }</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorMap = {
        green: 'var(--green)',
        yellow: 'var(--yellow)',
        red: 'var(--red)',
        blue: 'var(--blue)',
    };

    document.querySelectorAll('.train-set-status-bar').forEach(function(element) {
        element.style.background = colorMap[element.dataset.color] || 'var(--blue)';
    });

    document.querySelectorAll('.mileage-fill').forEach(function(element) {
        const width = Number(element.dataset.width || 0);
        element.style.width = width + '%';
        element.style.background = colorMap[element.dataset.color] || 'var(--blue)';
    });
});

function fleetApp() {
    return {
        showDetailModal: false,
        showMileageForm: false,
        showStatusForm: false,
        showScheduleForm: false,
        currentTrainSetId: null,
        detail: {},
        newMileage: '',
        newStatus: 'active',
        repairNote: '',
        nextServiceMileage: '',
        lastMaintenanceDate: '',
        nextMaintenanceDate: '',
        mileageResult: '',
        statusResult: '',
        scheduleResult: '',

        async fetchDetail() {
            const res = await fetch('/fleet/' + this.currentTrainSetId);
            this.detail = await res.json();
            this.newMileage = this.detail.train_set?.current_mileage || '';
            this.newStatus = this.detail.train_set?.maintenance_status || 'active';
            this.repairNote = this.detail.train_set?.repair_note || '';
            this.nextServiceMileage = this.detail.train_set?.next_service_mileage || '';
            this.lastMaintenanceDate = this.detail.train_set?.last_maintenance_date || '';
            this.nextMaintenanceDate = this.detail.train_set?.next_maintenance_date || '';
        },

        async showDetail(trainSetId) {
            try {
                this.currentTrainSetId = trainSetId;
                await this.fetchDetail();
                this.showMileageForm = false;
                this.showStatusForm = false;
                this.showScheduleForm = false;
                this.mileageResult = '';
                this.statusResult = '';
                this.scheduleResult = '';
                this.showDetailModal = true;
            } catch (e) {
                console.error(e);
            }
        },

        async updateMileage() {
            try {
                const res = await fetch('/fleet/' + this.currentTrainSetId + '/mileage', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ mileage: parseInt(this.newMileage, 10) }),
                });

                if (!res.ok) {
                    throw new Error('Mileage update failed');
                }

                await this.fetchDetail();
                this.mileageResult = 'อัปเดตเลขไมล์เรียบร้อย';
            } catch (e) {
                this.mileageResult = 'เกิดข้อผิดพลาดในการอัปเดตเลขไมล์';
            }
        },

        async updateStatus() {
            try {
                const res = await fetch('/fleet/' + this.currentTrainSetId + '/status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ status: this.newStatus, repair_note: this.repairNote }),
                });

                if (!res.ok) {
                    throw new Error('Status update failed');
                }

                await this.fetchDetail();
                this.statusResult = 'บันทึกสถานะเรียบร้อย';
            } catch (e) {
                this.statusResult = 'เกิดข้อผิดพลาดในการอัปเดตสถานะ';
            }
        },

        async updateSchedule() {
            try {
                const res = await fetch('/fleet/' + this.currentTrainSetId + '/schedule', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        next_service_mileage: parseInt(this.nextServiceMileage, 10),
                        last_maintenance_date: this.lastMaintenanceDate || null,
                        next_maintenance_date: this.nextMaintenanceDate || null,
                    }),
                });

                if (!res.ok) {
                    throw new Error('Schedule update failed');
                }

                await this.fetchDetail();
                this.scheduleResult = 'อัปเดตกำหนดซ่อมเรียบร้อย';
            } catch (e) {
                this.scheduleResult = 'เกิดข้อผิดพลาดในการอัปเดตกำหนดซ่อม';
            }
        },
    }
}
</script>
@endsection