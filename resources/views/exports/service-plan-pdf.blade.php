<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>{{ $day->header_title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
        .sheet { border: 1px solid #111; }
        .topline, .header, .toolbar, .footer, .reference { width: 100%; border-collapse: collapse; }
        .topline td, .header td, .toolbar td, .footer td, .reference td { border: 1px solid #111; padding: 6px; }
        .topline td { font-size: 8px; }
        .title { text-align: center; font-weight: bold; font-size: 18px; }
        .brand { font-size: 16px; font-weight: bold; color: #1e3a8a; }
        .meta { font-weight: bold; text-align: center; }
        .meta-label { background: #e5e7eb; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        .summary { width: 100%; border-collapse: collapse; }
        .summary td { border: 1px solid #111; padding: 8px; text-align: center; font-weight: bold; }
        .summary .available { background: #dcfce7; }
        .summary .warning { background: #fef9c3; }
        .summary .out { background: #fee2e2; }
        .plan-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-variant-numeric: tabular-nums; }
        .plan-table th, .plan-table td { border: 1px solid #111; padding: 3px; text-align: center; vertical-align: middle; }
        .plan-table th { background: #e5e7eb; font-size: 8px; }
        .plan-table tbody tr { height: 28px; }
        .group { background: #d1d5db; }
        .left { text-align: left; }
        .train-cell { padding: 0 !important; }
        .train-order { display: block; padding: 2px 0; border-bottom: 1px dashed #111; font-size: 7px; font-weight: bold; background: #f8fafc; }
        .train-code { display: block; padding: 4px 0; font-weight: bold; }
        .status-available { background: #dcfce7; }
        .status-warning { background: #fef9c3; }
        .status-out_of_service { background: #fee2e2; }
        .theme-green td { background: #dcfce7; }
        .theme-pink td { background: #fce7f3; }
        .theme-peach td { background: #ffedd5; }
        .theme-blue td { background: #dbeafe; }
        .theme-red td { background: #fee2e2; }
        .theme-yellow td { background: #fef3c7; }
        .notes { min-height: 110px; white-space: pre-wrap; }
        .legend-box { border: 1px solid #111; padding: 6px; margin-bottom: 8px; }
        .mini-table { width: 100%; border-collapse: collapse; margin-top: 8px; table-layout: fixed; }
        .mini-table th, .mini-table td { border: 1px solid #111; padding: 4px; font-size: 8px; }
        .mini-table tbody tr { height: 18px; }
        .notice { margin-top: 8px; padding: 6px; background: #fef08a; border: 1px solid #ca8a04; font-weight: bold; }
        .signoff { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .signoff td { border: 1px solid #111; padding: 0; width: 33.33%; }
        .signoff .head { padding: 6px; border-bottom: 1px solid #111; font-weight: bold; text-transform: uppercase; font-size: 8px; background: #f8fafc; }
        .signoff .body { min-height: 34px; padding: 18px 6px 6px; color: #555; font-size: 8px; }
    </style>
</head>
<body>
    <div class="sheet">
        <table class="topline">
            <tr>
                <td style="width: 33%;">FM-OCD-014</td>
                <td style="width: 34%; text-align: center; font-weight: bold;">Trainset Service Plan</td>
                <td style="width: 33%; text-align: right;">Rev: 00 | Eff. Date: 6 ก.ย. 2565</td>
            </tr>
        </table>

        <table class="header">
            <tr>
                <td style="width: 20%;">
                    <div class="brand">SRTET</div>
                    <div>SRT Electrified Train จำกัด</div>
                </td>
                <td class="title" style="width: 50%;">{{ $day->header_title }}</td>
                <td style="width: 30%; padding: 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td class="meta-label" style="border: 1px solid #111; width: 22%;">Book</td>
                            <td class="meta" style="border: 1px solid #111; width: 28%;">{{ $bookReference }}</td>
                            <td class="meta-label" style="border: 1px solid #111; width: 22%;">Page</td>
                            <td class="meta" style="border: 1px solid #111; width: 28%;">{{ $pageReference }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label" style="border: 1px solid #111;">Date</td>
                            <td class="meta" style="border: 1px solid #111;">{{ $date->format('d') }}</td>
                            <td class="meta-label" style="border: 1px solid #111;">Day</td>
                            <td class="meta" style="border: 1px solid #111;">{{ $dayName }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label" style="border: 1px solid #111;">Month</td>
                            <td class="meta" style="border: 1px solid #111;" colspan="3">{{ $displayMonthYear }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="reference">
            <tr>
                <td style="width: 34%;"><strong>Book / Page:</strong> {{ $bookReference }} / {{ $pageReference }}</td>
                <td style="width: 33%;"><strong>Service Day:</strong> {{ $dayName }}</td>
                <td style="width: 33%;"><strong>Service Date:</strong> {{ $date->format('d M Y') }}</td>
            </tr>
        </table>

        <table class="toolbar">
            <tr>
                <td style="width: 60%;"><strong>Timetable:</strong> {{ $day->timetable_label }}</td>
                <td style="width: 40%;"><strong>Page Flow:</strong> {{ $previousDate }} → {{ $date->toDateString() }} → {{ $nextDate }}</td>
            </tr>
        </table>

        <table class="summary">
            <tr>
                <td class="available">พร้อมให้บริการ {{ $statusSummary['available'] }}</td>
                <td class="warning">ใกล้วาระซ่อม {{ $statusSummary['warning'] }}</td>
                <td class="out">งดให้บริการ {{ $statusSummary['out_of_service'] }}</td>
            </tr>
        </table>

        <table class="plan-table">
            <colgroup>
                <col style="width: 7%;">
                <col style="width: 5%;">
                <col style="width: 3%;">
                <col style="width: 4%;">
                <col style="width: 3.5%;">
                <col style="width: 3.5%;">
                <col style="width: 3.5%;">
                <col style="width: 3.5%;">
                <col style="width: 3.5%;">
                <col style="width: 3.5%;">
                <col style="width: 3%;">
                <col style="width: 3%;">
                <col style="width: 4%;">
                <col style="width: 3.5%;">
                <col style="width: 3.5%;">
                <col style="width: 4%;">
                <col style="width: 5%;">
                <col style="width: 29%;">
            </colgroup>
            <thead>
                <tr>
                    <th rowspan="2">Train Set No.</th>
                    <th rowspan="2">Berth No.</th>
                    <th rowspan="2">Type</th>
                    <th rowspan="2">Run No.</th>
                    <th colspan="4" class="group">First Contact</th>
                    <th colspan="2" class="group">Dep. Time</th>
                    <th colspan="2" class="group">KTW</th>
                    <th rowspan="2">Run No.</th>
                    <th colspan="2" class="group">End</th>
                    <th rowspan="2">End No.</th>
                    <th rowspan="2">End Depot</th>
                    <th rowspan="2">Special Instructions</th>
                </tr>
                <tr>
                    <th>Plan</th>
                    <th>Cab 1</th>
                    <th>Cab 4/6</th>
                    <th>Brake Test</th>
                    <th>Plan</th>
                    <th>Actual</th>
                    <th>Platform</th>
                    <th>Next Depart</th>
                    <th>Station</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    <tr class="{{ $entry->row_theme ? 'theme-' . $entry->row_theme : '' }}">
                        <td class="status-{{ $entry->effective_status }} train-cell"><span class="train-order">#{{ str_pad((string) $entry->display_order, 2, '0', STR_PAD_LEFT) }}</span><span class="train-code">{{ $entry->trainSet?->code }}</span></td>
                        <td>{{ $entry->berth_no }}</td>
                        <td>{{ $entry->consist_type }}</td>
                        <td>{{ $entry->outbound_run_no }}</td>
                        <td>{{ $entry->first_contact_plan }}</td>
                        <td>{{ $entry->cab_one_time }}</td>
                        <td>{{ $entry->cab_four_six_time }}</td>
                        <td>{{ $entry->brake_test_time }}</td>
                        <td>{{ $entry->departure_plan_time }}</td>
                        <td>{{ $entry->departure_actual_time }}</td>
                        <td>{{ $entry->ktw_platform }}</td>
                        <td>{{ $entry->ktw_next_depart_time }}</td>
                        <td>{{ $entry->inbound_run_no }}</td>
                        <td>{{ $entry->end_station }}</td>
                        <td>{{ $entry->end_time }}</td>
                        <td>{{ $entry->end_no }}</td>
                        <td>{{ $entry->end_depot }}</td>
                        <td class="left">{{ $entry->special_instructions }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="footer">
            <tr>
                <td style="width: 75%; vertical-align: top;">
                    <strong>Notes / Daily Report</strong>
                    <div class="notes">{{ $day->footer_notes }}</div>
                    @if($day->highlight_notice)
                        <div class="notice">{{ $day->highlight_notice }}</div>
                    @endif
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Set</th>
                                <th>Location</th>
                                <th></th>
                                <th>Target</th>
                                <th>Flag</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($noteBlocks as $block)
                                <tr>
                                    <td>{{ $block['time'] ?? '' }}</td>
                                    <td>{{ $block['from'] ?? '' }}</td>
                                    <td>{{ $block['location'] ?? '' }}</td>
                                    <td>{{ $block['arrow'] ?? '' }}</td>
                                    <td>{{ $block['to'] ?? '' }}</td>
                                    <td>{{ $block['flag'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <table class="signoff">
                        <tr>
                            <td>
                                <div class="head">Dispatcher</div>
                                <div class="body">ลงชื่อ / เวลา</div>
                            </td>
                            <td>
                                <div class="head">OCC Supervisor</div>
                                <div class="body">ตรวจสอบ / รับทราบ</div>
                            </td>
                            <td>
                                <div class="head">Handover</div>
                                <div class="body">กะถัดไป / ผู้รับช่วง</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width: 25%; vertical-align: top;">
                    <div class="legend-box"><strong>Green</strong> พร้อมให้บริการ</div>
                    <div class="legend-box"><strong>Yellow</strong> ใกล้วาระซ่อม</div>
                    <div class="legend-box"><strong>Red</strong> งดให้บริการ</div>
                    <div class="legend-box"><strong>TWP / Handover</strong><br>ใช้พื้นที่นี้สำหรับรายการส่งต่อเข้ากะหรือเข้าวันถัดไป</div>
                    <table class="mini-table">
                        <thead>
                            <tr>
                                <th>Set</th>
                                <th>Target</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($handoverBlocks as $block)
                                <tr>
                                    <td>{{ $block['set'] ?? '' }}</td>
                                    <td>{{ $block['target'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>