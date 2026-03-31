@extends('layouts.app')
@section('title', 'ตั้งค่า Master Train Set')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-sliders" style="color: var(--amber)"></i> ค่ามาตรฐานประจำขบวน</h3>
        <a href="{{ route('daily-plan') }}" class="btn btn-secondary btn-sm">กลับไปสมุดรายวัน</a>
    </div>

    <form method="POST" action="{{ route('daily-plan.master.save') }}">
        @csrf
        <div style="overflow-x: auto;">
            <table class="data-table" style="min-width: 1200px;">
                <thead>
                    <tr>
                        <th>Train</th>
                        <th>Berth</th>
                        <th>Type</th>
                        <th>Out Run</th>
                        <th>FC Plan</th>
                        <th>Dep Plan</th>
                        <th>KTW Platform</th>
                        <th>Next Depart</th>
                        <th>In Run</th>
                        <th>End Station</th>
                        <th>End Time</th>
                        <th>End No.</th>
                        <th>End Depot</th>
                        <th>Row Theme</th>
                        <th>Special Instructions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trainSets as $trainSet)
                        <tr>
                            <td style="font-weight: 700;">{{ $trainSet->code }}</td>
                            <td><input class="form-input" name="train_sets[{{ $trainSet->id }}][default_berth_no]" value="{{ old("train_sets.{$trainSet->id}.default_berth_no", $trainSet->default_berth_no) }}"></td>
                            <td>
                                <select class="form-select" name="train_sets[{{ $trainSet->id }}][default_consist_type]">
                                    <option value="4" @selected(old("train_sets.{$trainSet->id}.default_consist_type", $trainSet->default_consist_type) === '4')>4</option>
                                    <option value="6" @selected(old("train_sets.{$trainSet->id}.default_consist_type", $trainSet->default_consist_type) === '6')>6</option>
                                </select>
                            </td>
                            <td><input class="form-input" name="train_sets[{{ $trainSet->id }}][default_outbound_run_no]" value="{{ old("train_sets.{$trainSet->id}.default_outbound_run_no", $trainSet->default_outbound_run_no) }}"></td>
                            <td><input class="form-input" name="train_sets[{{ $trainSet->id }}][default_first_contact_plan]" value="{{ old("train_sets.{$trainSet->id}.default_first_contact_plan", $trainSet->default_first_contact_plan) }}"></td>
                            <td><input class="form-input" name="train_sets[{{ $trainSet->id }}][default_departure_plan_time]" value="{{ old("train_sets.{$trainSet->id}.default_departure_plan_time", $trainSet->default_departure_plan_time) }}"></td>
                            <td><input class="form-input" name="train_sets[{{ $trainSet->id }}][default_ktw_platform]" value="{{ old("train_sets.{$trainSet->id}.default_ktw_platform", $trainSet->default_ktw_platform) }}"></td>
                            <td><input class="form-input" name="train_sets[{{ $trainSet->id }}][default_ktw_next_depart_time]" value="{{ old("train_sets.{$trainSet->id}.default_ktw_next_depart_time", $trainSet->default_ktw_next_depart_time) }}"></td>
                            <td><input class="form-input" name="train_sets[{{ $trainSet->id }}][default_inbound_run_no]" value="{{ old("train_sets.{$trainSet->id}.default_inbound_run_no", $trainSet->default_inbound_run_no) }}"></td>
                            <td><input class="form-input" name="train_sets[{{ $trainSet->id }}][default_end_station]" value="{{ old("train_sets.{$trainSet->id}.default_end_station", $trainSet->default_end_station) }}"></td>
                            <td><input class="form-input" name="train_sets[{{ $trainSet->id }}][default_end_time]" value="{{ old("train_sets.{$trainSet->id}.default_end_time", $trainSet->default_end_time) }}"></td>
                            <td><input class="form-input" name="train_sets[{{ $trainSet->id }}][default_end_no]" value="{{ old("train_sets.{$trainSet->id}.default_end_no", $trainSet->default_end_no) }}"></td>
                            <td><input class="form-input" name="train_sets[{{ $trainSet->id }}][default_end_depot]" value="{{ old("train_sets.{$trainSet->id}.default_end_depot", $trainSet->default_end_depot) }}"></td>
                            <td>
                                <select class="form-select" name="train_sets[{{ $trainSet->id }}][default_row_theme]">
                                    <option value="">None</option>
                                    @foreach(['green', 'pink', 'peach', 'blue', 'red', 'yellow'] as $theme)
                                        <option value="{{ $theme }}" @selected(old("train_sets.{$trainSet->id}.default_row_theme", $trainSet->default_row_theme) === $theme)>{{ ucfirst($theme) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><textarea class="form-textarea" rows="2" name="train_sets[{{ $trainSet->id }}][default_special_instructions]">{{ old("train_sets.{$trainSet->id}.default_special_instructions", $trainSet->default_special_instructions) }}</textarea></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="display: flex; justify-content: flex-end; margin-top: 18px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> บันทึก Master Data</button>
        </div>
    </form>
</div>
@endsection