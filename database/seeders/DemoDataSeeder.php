<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vehicle;
use App\Models\DailyPlan;
use App\Models\MaintenanceLog;
use App\Models\MileageLog;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $vehicles = [
            ['vehicle_code' => 'BUS-001', 'vehicle_type' => 'bus', 'license_plate' => '1กก 1234', 'brand' => 'Mercedes-Benz', 'model' => 'Tourismo', 'capacity' => 45, 'current_mileage' => 85200, 'next_service_mileage' => 90000, 'status' => 'active', 'last_maintenance_date' => '2026-02-15', 'next_maintenance_date' => '2026-04-15'],
            ['vehicle_code' => 'BUS-002', 'vehicle_type' => 'bus', 'license_plate' => '2กข 5678', 'brand' => 'Scania', 'model' => 'Touring', 'capacity' => 50, 'current_mileage' => 120500, 'next_service_mileage' => 121000, 'status' => 'active', 'last_maintenance_date' => '2026-01-20', 'next_maintenance_date' => '2026-03-28'],
            ['vehicle_code' => 'BUS-003', 'vehicle_type' => 'bus', 'license_plate' => '3กค 9012', 'brand' => 'Volvo', 'model' => '9700', 'capacity' => 48, 'current_mileage' => 95000, 'next_service_mileage' => 100000, 'status' => 'minor_repair', 'repair_note' => 'ระบบแอร์มีปัญหา ทำความเย็นไม่พอ', 'last_maintenance_date' => '2026-03-01', 'next_maintenance_date' => '2026-05-01'],
            ['vehicle_code' => 'VAN-001', 'vehicle_type' => 'van', 'license_plate' => '4กง 3456', 'brand' => 'Toyota', 'model' => 'Commuter', 'capacity' => 12, 'current_mileage' => 45000, 'next_service_mileage' => 50000, 'status' => 'active', 'last_maintenance_date' => '2026-03-10', 'next_maintenance_date' => '2026-06-10'],
            ['vehicle_code' => 'VAN-002', 'vehicle_type' => 'van', 'license_plate' => '5กจ 7890', 'brand' => 'Toyota', 'model' => 'Hiace', 'capacity' => 14, 'current_mileage' => 78000, 'next_service_mileage' => 80000, 'status' => 'active', 'last_maintenance_date' => '2026-02-28', 'next_maintenance_date' => '2026-04-28'],
            ['vehicle_code' => 'VAN-003', 'vehicle_type' => 'van', 'license_plate' => '6กฉ 1357', 'brand' => 'Hyundai', 'model' => 'H350', 'capacity' => 15, 'current_mileage' => 110000, 'next_service_mileage' => 115000, 'status' => 'major_repair', 'repair_note' => 'เครื่องยนต์มีปัญหา ต้องเปลี่ยนชุดหัวฉีด', 'last_maintenance_date' => '2026-03-18', 'next_maintenance_date' => '2026-04-01'],
            ['vehicle_code' => 'VAN-004', 'vehicle_type' => 'van', 'license_plate' => '7กช 2468', 'brand' => 'Toyota', 'model' => 'Commuter', 'capacity' => 12, 'current_mileage' => 32000, 'next_service_mileage' => 40000, 'status' => 'active', 'last_maintenance_date' => '2026-01-15', 'next_maintenance_date' => '2026-07-15'],
            ['vehicle_code' => 'MINI-001', 'vehicle_type' => 'minibus', 'license_plate' => '8กซ 3690', 'brand' => 'Hino', 'model' => 'Poncho', 'capacity' => 25, 'current_mileage' => 67000, 'next_service_mileage' => 70000, 'status' => 'active', 'last_maintenance_date' => '2026-02-01', 'next_maintenance_date' => '2026-05-01'],
            ['vehicle_code' => 'MINI-002', 'vehicle_type' => 'minibus', 'license_plate' => '9กฌ 4812', 'brand' => 'Isuzu', 'model' => 'Journey', 'capacity' => 28, 'current_mileage' => 55000, 'next_service_mileage' => 60000, 'status' => 'active', 'last_maintenance_date' => '2026-03-05', 'next_maintenance_date' => '2026-03-30'],
            ['vehicle_code' => 'MINI-003', 'vehicle_type' => 'minibus', 'license_plate' => '1กญ 5934', 'brand' => 'Mitsubishi', 'model' => 'Rosa', 'capacity' => 22, 'current_mileage' => 89500, 'next_service_mileage' => 90000, 'status' => 'active', 'last_maintenance_date' => '2026-03-12', 'next_maintenance_date' => '2026-04-12'],
            ['vehicle_code' => 'BUS-004', 'vehicle_type' => 'bus', 'license_plate' => '2กฎ 6078', 'brand' => 'MAN', 'model' => "Lion's Coach", 'capacity' => 52, 'current_mileage' => 200000, 'next_service_mileage' => 205000, 'status' => 'retired', 'repair_note' => 'ปลดระวาง - สภาพเก่ามาก', 'last_maintenance_date' => '2025-12-01', 'next_maintenance_date' => null],
            ['vehicle_code' => 'VAN-005', 'vehicle_type' => 'van', 'license_plate' => '3กฏ 7190', 'brand' => 'Ford', 'model' => 'Transit', 'capacity' => 15, 'current_mileage' => 25000, 'next_service_mileage' => 30000, 'status' => 'active', 'last_maintenance_date' => '2026-03-20', 'next_maintenance_date' => '2026-09-20'],
        ];

        foreach ($vehicles as $v) {
            Vehicle::create($v);
        }

        // Create daily plans for the past 30 days
        $routes = [
            'กรุงเทพฯ - เชียงใหม่', 'กรุงเทพฯ - พัทยา', 'กรุงเทพฯ - หัวหิน',
            'กรุงเทพฯ - นครราชสีมา', 'กรุงเทพฯ - ขอนแก่น', 'กรุงเทพฯ - อยุธยา',
            'กรุงเทพฯ - กาญจนบุรี', 'กรุงเทพฯ - ระยอง', 'สนามบิน - โรงแรม',
            'รับส่งพนักงาน โซน A', 'รับส่งพนักงาน โซน B', 'ทัวร์วันเดียว สมุทรสงคราม',
        ];
        $drivers = [
            'สมชาย จันทร์ดี', 'สุรศักดิ์ แก้วมณี', 'ประสิทธิ์ สุขใจ',
            'วิชัย พลายงาม', 'อนุชา รักดี', 'พงศ์ภัค วงศ์ใหญ่',
            'ธนกร สันติสุข', 'กิตติ ศรีสมบัติ',
        ];

        $activeVehicles = Vehicle::whereIn('status', ['active', 'minor_repair'])->get();

        for ($d = 30; $d >= 0; $d--) {
            $date = Carbon::now()->subDays($d)->format('Y-m-d');
            $plansPerDay = rand(3, 7);

            $usedVehicles = $activeVehicles->random(min($plansPerDay, $activeVehicles->count()));

            foreach ($usedVehicles as $i => $vehicle) {
                $hour = rand(5, 9);
                $status = $d == 0
                    ? ['planned', 'in_progress', 'planned'][rand(0, 2)]
                    : ['completed', 'completed', 'completed', 'cancelled'][rand(0, 3)];

                DailyPlan::create([
                    'plan_date' => $date,
                    'vehicle_id' => $vehicle->id,
                    'driver_name' => $drivers[array_rand($drivers)],
                    'route' => $routes[array_rand($routes)],
                    'departure_time' => sprintf('%02d:%02d', $hour, [0, 15, 30, 45][rand(0, 3)]),
                    'return_time' => $status === 'completed' ? sprintf('%02d:%02d', $hour + rand(4, 10), [0, 15, 30, 45][rand(0, 3)]) : null,
                    'passengers' => rand(5, $vehicle->capacity),
                    'status' => $status,
                    'note' => rand(0, 3) === 0 ? 'ลูกค้า VIP' : null,
                ]);
            }
        }

        // Create maintenance logs
        $maintenanceDescriptions = [
            'scheduled' => ['เปลี่ยนน้ำมันเครื่อง', 'เช็คระยะ 10,000 km', 'เปลี่ยนผ้าเบรก', 'ตรวจเช็คสภาพทั่วไป', 'เปลี่ยนไส้กรองอากาศ'],
            'minor_repair' => ['ซ่อมระบบแอร์', 'เปลี่ยนหลอดไฟหน้า', 'ซ่อมกระจกข้าง', 'ซ่อมระบบเสียงประกาศ', 'เปลี่ยนยาง'],
            'major_repair' => ['ซ่อมเครื่องยนต์', 'เปลี่ยนคลัตช์', 'ซ่อมระบบเกียร์', 'เปลี่ยนชุดหัวฉีด'],
        ];

        foreach (Vehicle::all() as $vehicle) {
            $logCount = rand(2, 5);
            for ($i = 0; $i < $logCount; $i++) {
                $type = ['scheduled', 'scheduled', 'minor_repair', 'major_repair'][rand(0, 3)];
                $descriptions = $maintenanceDescriptions[$type];
                $serviceDate = Carbon::now()->subDays(rand(10, 180));

                MaintenanceLog::create([
                    'vehicle_id' => $vehicle->id,
                    'maintenance_type' => $type,
                    'description' => $descriptions[array_rand($descriptions)],
                    'cost' => match($type) {
                        'scheduled' => rand(2000, 8000),
                        'minor_repair' => rand(3000, 15000),
                        'major_repair' => rand(20000, 80000),
                    },
                    'mileage_at_service' => $vehicle->current_mileage - rand(1000, 20000),
                    'service_date' => $serviceDate,
                    'completed_date' => rand(0, 1) ? $serviceDate->copy()->addDays(rand(1, 5)) : null,
                    'status' => ['pending', 'completed', 'completed', 'completed'][rand(0, 3)],
                ]);
            }
        }

        // Create mileage logs
        foreach (Vehicle::all() as $vehicle) {
            $baseMileage = $vehicle->current_mileage - 5000;
            for ($d = 30; $d >= 0; $d -= rand(1, 3)) {
                $baseMileage += rand(50, 300);
                MileageLog::create([
                    'vehicle_id' => $vehicle->id,
                    'log_date' => Carbon::now()->subDays($d)->format('Y-m-d'),
                    'mileage' => $baseMileage,
                ]);
            }
        }
    }
}
