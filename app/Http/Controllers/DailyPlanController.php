<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\TrainSet;
use App\Models\ServicePlanEntry;
use App\Models\ServicePlanDay;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DailyPlanController extends Controller
{
    public function index(Request $request)
    {
        return view('daily-plan', $this->buildDailyPlanViewData($request));
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'service_date' => 'required|date',
            'header_title' => 'required|string|max:255',
            'timetable_label' => 'required|string|max:255',
            'footer_notes' => 'nullable|string',
            'highlight_notice' => 'nullable|string',
            'entries' => 'array',
            'note_blocks' => 'array',
            'handover_blocks' => 'array',
        ]);

        $date = $this->resolveDate($validated['service_date']);
        $trainSets = $this->ensureTrainSets();

        $day = ServicePlanDay::firstOrCreate(
            ['service_date' => $date->toDateString()],
            [
                'header_title' => $validated['header_title'],
                'timetable_label' => $validated['timetable_label'],
            ]
        );

        $this->ensureEntries($day, $trainSets);

        DB::transaction(function () use ($request, $validated, $day): void {
            $day->update([
                'header_title' => $validated['header_title'],
                'timetable_label' => $validated['timetable_label'],
                'footer_notes' => $validated['footer_notes'] ?? null,
                'highlight_notice' => $validated['highlight_notice'] ?? null,
                'note_blocks' => $this->sanitizeBlocks($request->input('note_blocks', [])),
                'handover_blocks' => $this->sanitizeBlocks($request->input('handover_blocks', [])),
            ]);

            $rows = $request->input('entries', []);
            $entries = $day->entries()->get()->keyBy('id');

            foreach ($rows as $entryId => $row) {
                /** @var ServicePlanEntry|null $entry */
                $entry = $entries->get((int) $entryId);

                if (! $entry) {
                    continue;
                }

                $trainSetId = isset($row['train_set_id']) && $row['train_set_id'] !== ''
                    ? (int) $row['train_set_id']
                    : null;

                $trainSet = $trainSetId ? TrainSet::find($trainSetId) : null;

                $entry->update([
                    'train_set_id' => $trainSetId,
                    'service_status' => $row['service_status'] ?? 'available',
                    'row_theme' => $this->stringOrNull($row['row_theme'] ?? null) ?? $trainSet?->default_row_theme,
                    'berth_no' => $this->stringOrNull($row['berth_no'] ?? null) ?? $trainSet?->default_berth_no,
                    'consist_type' => $this->normalizeConsistType($row['consist_type'] ?? null, $trainSet?->default_consist_type),
                    'outbound_run_no' => $this->stringOrNull($row['outbound_run_no'] ?? null) ?? $trainSet?->default_outbound_run_no,
                    'first_contact_plan' => $this->stringOrNull($row['first_contact_plan'] ?? null) ?? $trainSet?->default_first_contact_plan,
                    'cab_one_time' => $this->stringOrNull($row['cab_one_time'] ?? null),
                    'cab_four_six_time' => $this->stringOrNull($row['cab_four_six_time'] ?? null),
                    'brake_test_time' => $this->stringOrNull($row['brake_test_time'] ?? null),
                    'departure_plan_time' => $this->stringOrNull($row['departure_plan_time'] ?? null) ?? $trainSet?->default_departure_plan_time,
                    'departure_actual_time' => $this->stringOrNull($row['departure_actual_time'] ?? null),
                    'ktw_platform' => $this->stringOrNull($row['ktw_platform'] ?? null) ?? $trainSet?->default_ktw_platform,
                    'ktw_next_depart_time' => $this->stringOrNull($row['ktw_next_depart_time'] ?? null) ?? $trainSet?->default_ktw_next_depart_time,
                    'inbound_run_no' => $this->stringOrNull($row['inbound_run_no'] ?? null) ?? $trainSet?->default_inbound_run_no,
                    'end_station' => $this->stringOrNull($row['end_station'] ?? null) ?? $trainSet?->default_end_station,
                    'end_time' => $this->stringOrNull($row['end_time'] ?? null) ?? $trainSet?->default_end_time,
                    'end_no' => $this->stringOrNull($row['end_no'] ?? null) ?? $trainSet?->default_end_no,
                    'end_depot' => $this->stringOrNull($row['end_depot'] ?? null) ?? $trainSet?->default_end_depot,
                    'special_instructions' => $this->stringOrNull($row['special_instructions'] ?? null) ?? $trainSet?->default_special_instructions,
                ]);
            }
        });

        return redirect()->route('daily-plan', ['date' => $date->toDateString()])
            ->with('success', 'บันทึกสมุดแผนงานประจำวันเรียบร้อย');
    }

    public function master()
    {
        return view('daily-plan-master', [
            'trainSets' => $this->ensureTrainSets(),
        ]);
    }

    public function saveMaster(Request $request)
    {
        $validated = $request->validate([
            'train_sets' => 'required|array',
        ]);

        foreach ($validated['train_sets'] as $trainSetId => $row) {
            $trainSet = TrainSet::find((int) $trainSetId);

            if (! $trainSet) {
                continue;
            }

            $trainSet->update([
                'default_berth_no' => $this->stringOrNull($row['default_berth_no'] ?? null),
                'default_consist_type' => $this->normalizeConsistType($row['default_consist_type'] ?? null, $trainSet->default_consist_type),
                'default_outbound_run_no' => $this->stringOrNull($row['default_outbound_run_no'] ?? null),
                'default_first_contact_plan' => $this->stringOrNull($row['default_first_contact_plan'] ?? null),
                'default_departure_plan_time' => $this->stringOrNull($row['default_departure_plan_time'] ?? null),
                'default_ktw_platform' => $this->stringOrNull($row['default_ktw_platform'] ?? null),
                'default_ktw_next_depart_time' => $this->stringOrNull($row['default_ktw_next_depart_time'] ?? null),
                'default_inbound_run_no' => $this->stringOrNull($row['default_inbound_run_no'] ?? null),
                'default_end_station' => $this->stringOrNull($row['default_end_station'] ?? null),
                'default_end_time' => $this->stringOrNull($row['default_end_time'] ?? null),
                'default_end_no' => $this->stringOrNull($row['default_end_no'] ?? null),
                'default_end_depot' => $this->stringOrNull($row['default_end_depot'] ?? null),
                'default_special_instructions' => $this->stringOrNull($row['default_special_instructions'] ?? null),
                'default_row_theme' => $this->stringOrNull($row['default_row_theme'] ?? null),
            ]);
        }

        return redirect()->route('daily-plan.master')
            ->with('success', 'อัปเดต master data ของขบวนเรียบร้อย');
    }

    public function exportPdf(Request $request)
    {
        $data = $this->buildDailyPlanViewData($request);

        return Pdf::loadView('exports.service-plan-pdf', $data)
            ->setPaper('a3', 'landscape')
            ->stream('service-plan-' . $data['date']->format('Y-m-d') . '.pdf');
    }

    public function copyPreviousDay(Request $request)
    {
        $validated = $request->validate([
            'service_date' => 'required|date',
        ]);

        $date = $this->resolveDate($validated['service_date']);
        $currentDay = $this->resolveOrCreatePlanDay($date);
        $previousDay = ServicePlanDay::query()
            ->whereDate('service_date', $date->copy()->subDay()->toDateString())
            ->with('entries.trainSet')
            ->first();

        if (! $previousDay) {
            return redirect()->route('daily-plan', ['date' => $date->toDateString()])
                ->with('success', 'ไม่พบข้อมูลของวันก่อนหน้าให้คัดลอก');
        }

        DB::transaction(function () use ($currentDay, $previousDay): void {
            $currentDay->update([
                'header_title' => $previousDay->header_title,
                'timetable_label' => $previousDay->timetable_label,
                'footer_notes' => $previousDay->footer_notes,
                'highlight_notice' => $previousDay->highlight_notice,
                'note_blocks' => $previousDay->note_blocks,
                'handover_blocks' => $previousDay->handover_blocks,
            ]);

            $currentEntries = $currentDay->entries()->get()->keyBy('display_order');
            $previousEntries = $previousDay->entries->keyBy('display_order');

            foreach ($currentEntries as $displayOrder => $currentEntry) {
                $previousEntry = $previousEntries->get($displayOrder);

                if (! $previousEntry) {
                    continue;
                }

                $currentEntry->update([
                    'train_set_id' => $previousEntry->train_set_id,
                    'service_status' => $previousEntry->service_status,
                    'row_theme' => $previousEntry->row_theme,
                    'berth_no' => $previousEntry->berth_no,
                    'consist_type' => $previousEntry->consist_type,
                    'outbound_run_no' => $previousEntry->outbound_run_no,
                    'first_contact_plan' => $previousEntry->first_contact_plan,
                    'cab_one_time' => $previousEntry->cab_one_time,
                    'cab_four_six_time' => $previousEntry->cab_four_six_time,
                    'brake_test_time' => $previousEntry->brake_test_time,
                    'departure_plan_time' => $previousEntry->departure_plan_time,
                    'departure_actual_time' => $previousEntry->departure_actual_time,
                    'ktw_platform' => $previousEntry->ktw_platform,
                    'ktw_next_depart_time' => $previousEntry->ktw_next_depart_time,
                    'inbound_run_no' => $previousEntry->inbound_run_no,
                    'end_station' => $previousEntry->end_station,
                    'end_time' => $previousEntry->end_time,
                    'end_no' => $previousEntry->end_no,
                    'end_depot' => $previousEntry->end_depot,
                    'special_instructions' => $previousEntry->special_instructions,
                ]);
            }
        });

        return redirect()->route('daily-plan', ['date' => $date->toDateString()])
            ->with('success', 'คัดลอกข้อมูลจากวันก่อนหน้ามายังหน้าปัจจุบันแล้ว');
    }

    private function buildDailyPlanViewData(Request $request): array
    {
        $date = $this->resolveDate($request->get('date'));
        $day = $this->resolveOrCreatePlanDay($date);
        $trainSets = $this->ensureTrainSets();

        $day->load(['entries.trainSet']);
        $statusSummary = $day->entries->countBy('service_status')->all();
        $hasPreviousDay = ServicePlanDay::query()
            ->whereDate('service_date', $date->copy()->subDay()->toDateString())
            ->exists();

        return [
            'day' => $day,
            'entries' => $day->entries,
            'trainSets' => $trainSets,
            'date' => $date,
            'bookReference' => $date->format('Y'),
            'pageReference' => str_pad((string) $date->dayOfYear, 3, '0', STR_PAD_LEFT),
            'dayName' => $date->locale('en')->translatedFormat('l'),
            'displayMonthYear' => $date->locale('en')->translatedFormat('F Y'),
            'previousDate' => $date->copy()->subDay()->toDateString(),
            'nextDate' => $date->copy()->addDay()->toDateString(),
            'statusSummary' => [
                'available' => $statusSummary['available'] ?? 0,
                'warning' => $statusSummary['warning'] ?? 0,
                'out_of_service' => $statusSummary['out_of_service'] ?? 0,
            ],
            'masterOptions' => $this->buildMasterOptions($trainSets),
            'hasPreviousDay' => $hasPreviousDay,
            'rowThemes' => [
                'none' => 'None',
                'green' => 'Green',
                'pink' => 'Pink',
                'peach' => 'Peach',
                'blue' => 'Blue',
                'red' => 'Red',
                'yellow' => 'Yellow',
            ],
        ];
    }

    private function resolveOrCreatePlanDay(Carbon $date): ServicePlanDay
    {
        $trainSets = $this->ensureTrainSets();

        $day = ServicePlanDay::firstOrCreate(
            ['service_date' => $date->toDateString()],
            [
                'header_title' => 'Trainset Service Plan',
                'timetable_label' => 'Week Day (05:00 - 00:00)',
                'highlight_notice' => '* T08, T24 ห้ามนำขบวนจาก RST ซ่อมขึ้นไปทางฝั่ง DMG โดยไม่ตรวจสอบงานให้จบก่อน',
                'note_blocks' => $this->defaultNoteBlocks(),
                'handover_blocks' => $this->defaultHandoverBlocks(),
            ]
        );

        $this->ensureEntries($day, $trainSets);

        return $day;
    }

    private function ensureTrainSets()
    {
        if (TrainSet::count() === 0) {
            $fourCarSets = ['T16', 'T17', 'T18', 'T19', 'T20', 'T21', 'T22', 'T23', 'T25'];

            for ($i = 1; $i <= 25; $i++) {
                $code = 'T' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);

                TrainSet::create([
                    'code' => $code,
                    'default_consist_type' => in_array($code, $fourCarSets, true) ? '4' : '6',
                    'display_order' => $i,
                ]);
            }
        }

        return TrainSet::query()->orderBy('display_order')->get();
    }

    private function ensureEntries(ServicePlanDay $day, $trainSets): void
    {
        foreach ($trainSets as $index => $trainSet) {
            ServicePlanEntry::firstOrCreate(
                [
                    'service_plan_day_id' => $day->id,
                    'display_order' => $index + 1,
                ],
                [
                    'train_set_id' => $trainSet->id,
                    'service_status' => 'available',
                    'row_theme' => $trainSet->default_row_theme,
                    'berth_no' => $trainSet->default_berth_no,
                    'consist_type' => $trainSet->default_consist_type,
                    'outbound_run_no' => $trainSet->default_outbound_run_no,
                    'first_contact_plan' => $trainSet->default_first_contact_plan,
                    'departure_plan_time' => $trainSet->default_departure_plan_time,
                    'ktw_platform' => $trainSet->default_ktw_platform,
                    'ktw_next_depart_time' => $trainSet->default_ktw_next_depart_time,
                    'inbound_run_no' => $trainSet->default_inbound_run_no,
                    'end_station' => $trainSet->default_end_station,
                    'end_time' => $trainSet->default_end_time,
                    'end_no' => $trainSet->default_end_no,
                    'end_depot' => $trainSet->default_end_depot,
                    'special_instructions' => $trainSet->default_special_instructions,
                ]
            );
        }
    }

    private function buildMasterOptions(Collection $trainSets): array
    {
        return [
            'berths' => $trainSets->pluck('default_berth_no')->filter()->unique()->values()->all(),
            'platforms' => $trainSets->pluck('default_ktw_platform')->filter()->unique()->values()->all(),
            'endStations' => $trainSets->pluck('default_end_station')->filter()->unique()->values()->all(),
            'endNos' => $trainSets->pluck('default_end_no')->filter()->unique()->values()->all(),
            'depots' => $trainSets->pluck('default_end_depot')->filter()->unique()->values()->all(),
            'themes' => $trainSets->pluck('default_row_theme')->filter()->unique()->values()->all(),
        ];
    }

    private function defaultNoteBlocks(): array
    {
        return [
            ['time' => '8:00', 'from' => 'T01', 'location' => 'CT09B', 'arrow' => '>>>', 'to' => 'MW02', 'flag' => 'Y'],
            ['time' => '9:00', 'from' => 'T03', 'location' => 'CT06B', 'arrow' => '>>>', 'to' => 'WRT', 'flag' => 'Y'],
            ['time' => '14:05', 'from' => 'T22', 'location' => 'CT13B', 'arrow' => '>>>', 'to' => 'MW01', 'flag' => 'Y'],
            ['time' => '14:10', 'from' => 'T01', 'location' => 'MW02', 'arrow' => '>>>', 'to' => 'TWP, CT09B', 'flag' => 'Y'],
            ['time' => '21:00', 'from' => 'T22', 'location' => 'MW01', 'arrow' => '>>>', 'to' => 'CT13B', 'flag' => 'Y'],
        ];
    }

    private function defaultHandoverBlocks(): array
    {
        return [
            ['set' => 'T01', 'target' => 'MW02'],
            ['set' => 'T05', 'target' => 'RN156'],
            ['set' => 'T20', 'target' => 'CT15A'],
            ['set' => 'T25', 'target' => 'CT17B'],
        ];
    }

    private function sanitizeBlocks(array $blocks): array
    {
        return array_values(array_map(function ($row) {
            return array_map(function ($value) {
                return $this->stringOrNull(is_string($value) ? $value : null) ?? '';
            }, is_array($row) ? $row : []);
        }, $blocks));
    }

    private function resolveDate(?string $date): Carbon
    {
        if (! $date) {
            return Carbon::today();
        }

        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return Carbon::today();
        }
    }

    private function stringOrNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizeConsistType(?string $value, ?string $fallback): ?string
    {
        $candidate = $this->stringOrNull($value) ?? $fallback;

        return in_array($candidate, ['4', '6'], true) ? $candidate : null;
    }
}
