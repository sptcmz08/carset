@extends('layouts.app')
@section('title', 'สมุดแพลนเดินรถรายวัน')

@section('content')
<style>
    .page-content {
        padding: 14px 16px 34px;
    }

    .service-book-shell {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .paper-fit-stage {
        width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        padding-bottom: 14px;
        scrollbar-width: auto;
        scrollbar-color: #f59e0b rgba(15, 23, 42, 0.45);
    }

    .paper-fit-stage::-webkit-scrollbar {
        height: 16px;
    }

    .paper-fit-stage::-webkit-scrollbar-track {
        background: rgba(15, 23, 42, 0.45);
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.08);
    }

    .paper-fit-stage::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
        border-radius: 999px;
        border: 2px solid rgba(15, 23, 42, 0.45);
    }

    .paper-fit-stage::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(90deg, #fbbf24, #fcd34d);
    }

    .paper-fit-canvas {
        width: 100%;
        margin: 0 auto;
    }

    .paper-fit-stage.paper-fit-active {
        overflow: hidden;
        padding-bottom: 0;
    }

    .paper-fit-stage.paper-fit-active .paper-fit-canvas {
        position: relative;
    }

    .paper-fit-stage.paper-fit-active .paper-card {
        position: absolute;
        top: 0;
        left: 0;
        width: 1880px;
        max-width: none;
        transform-origin: top left;
    }

    .paper-fit-stage.paper-fit-active .paper-table-wrap {
        overflow: visible;
        padding-bottom: 0;
        scrollbar-width: none;
    }

    .paper-fit-stage.paper-fit-active .paper-table-wrap::-webkit-scrollbar {
        display: none;
    }

    .service-book-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }

    .service-book-toolbar .book-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }

    .service-book-toolbar .book-date {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 14px;
        padding: 8px 10px;
    }

    .service-book-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(180px, 1fr));
        gap: 8px;
    }

    .service-book-summary .summary-card {
        border-radius: 14px;
        padding: 10px 14px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.04);
    }

    .service-book-summary .summary-card strong {
        display: block;
        font-size: 24px;
        line-height: 1;
        margin-bottom: 4px;
    }

    .service-book-summary .summary-card small {
        color: var(--text-muted);
        font-size: 12px;
    }

    .summary-card.available {
        background: rgba(34, 197, 94, 0.12);
        border-color: rgba(34, 197, 94, 0.2);
        color: #86efac;
    }

    .summary-card.warning {
        background: rgba(234, 179, 8, 0.12);
        border-color: rgba(234, 179, 8, 0.2);
        color: #fde047;
    }

    .summary-card.out {
        background: rgba(239, 68, 68, 0.12);
        border-color: rgba(239, 68, 68, 0.2);
        color: #fca5a5;
    }

    .paper-card {
        background: #f8fafc;
        color: #0f172a;
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.08);
        overflow: hidden;
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.25);
    }

    .paper-topline {
        display: grid;
        grid-template-columns: 1.1fr 1fr 1.1fr;
        align-items: stretch;
        border-bottom: 2px solid #111827;
        background: #fff;
    }

    .paper-topline > div {
        min-height: 38px;
        display: flex;
        align-items: center;
        padding: 6px 12px;
        font-size: 11px;
        border-right: 1px solid #111827;
    }

    .paper-topline > div:last-child {
        border-right: none;
    }

    .paper-topline .paper-doc-center {
        justify-content: center;
        font-weight: 700;
    }

    .paper-topline .paper-doc-right {
        justify-content: space-between;
        gap: 12px;
        font-weight: 600;
    }

    .paper-header {
        padding: 12px 16px;
        border-bottom: 2px solid #1e293b;
        display: grid;
        grid-template-columns: 220px 1fr 260px;
        gap: 10px;
        align-items: center;
        background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
    }

    .paper-brand {
        font-weight: 700;
        font-size: 24px;
        letter-spacing: 0.5px;
        color: #1e3a8a;
    }

    .paper-brand-mark {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .paper-brand-mark::before {
        content: '';
        display: inline-block;
        width: 34px;
        height: 14px;
        border-top: 4px solid #b91c1c;
        border-right: 4px solid #1d4ed8;
        transform: skewX(-28deg);
        margin-top: 2px;
    }

    .paper-subtitle {
        font-size: 11px;
        color: #475569;
        margin-top: 2px;
    }

    .paper-title {
        text-align: center;
    }

    .paper-title input {
        width: 100%;
        font-size: 24px;
        font-weight: 700;
        text-align: center;
        border: none;
        background: transparent;
        color: #111827;
    }

    .paper-title input:focus {
        outline: none;
    }

    .paper-meta {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
        justify-items: end;
    }

    .paper-meta-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
    }

    .paper-meta-row span {
        color: #334155;
    }

    .paper-meta-row strong {
        min-width: 48px;
        text-align: center;
        padding: 6px 8px;
        border: 1px solid #94a3b8;
        background: #fff;
    }

    .paper-meta-grid {
        display: grid;
        grid-template-columns: 68px 1fr 68px 1fr;
        border: 1px solid #111827;
        background: #fff;
    }

    .paper-meta-grid div {
        padding: 7px 8px;
        border-right: 1px solid #111827;
        border-bottom: 1px solid #111827;
        font-size: 11px;
        min-height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .paper-meta-grid div:nth-last-child(-n+4) {
        border-bottom: none;
    }

    .paper-meta-grid div:nth-child(4n) {
        border-right: none;
    }

    .paper-meta-grid .label {
        background: #e5e7eb;
        font-weight: 700;
    }

    .paper-meta-grid .value {
        font-weight: 700;
        background: #fff;
    }

    .paper-reference-bar {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr 0.9fr;
        border-bottom: 1px solid #1e293b;
        background: #fff;
    }

    .paper-reference-cell {
        display: grid;
        grid-template-columns: 92px 1fr;
        border-right: 1px solid #1e293b;
    }

    .paper-reference-cell:last-child {
        border-right: none;
    }

    .paper-reference-cell span,
    .paper-reference-cell strong {
        min-height: 34px;
        display: flex;
        align-items: center;
        padding: 6px 10px;
        font-size: 11px;
        border-right: 1px solid #1e293b;
    }

    .paper-reference-cell span {
        background: #e5e7eb;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .paper-reference-cell strong {
        border-right: none;
        justify-content: center;
    }

    .paper-toolbar {
        padding: 10px 16px;
        display: grid;
        grid-template-columns: 1fr 0.7fr;
        gap: 12px;
        border-bottom: 1px solid #cbd5e1;
        background: #fff;
    }

    .paper-toolbar .field-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .paper-toolbar label {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .paper-toolbar input {
        flex: 1;
        border: none;
        border-bottom: 1px solid #94a3b8;
        background: transparent;
        color: #111827;
        padding: 6px 2px;
    }

    .paper-toolbar input:focus {
        outline: none;
        border-bottom-color: #1d4ed8;
    }

    .paper-toolbar .field-row.compact {
        justify-content: flex-end;
    }

    .paper-table-wrap {
        overflow-x: auto;
        overflow-y: hidden;
        background: repeating-linear-gradient(
            0deg,
            rgba(148, 163, 184, 0.05),
            rgba(148, 163, 184, 0.05) 1px,
            transparent 1px,
            transparent 44px
        );
        padding-bottom: 10px;
        scrollbar-width: auto;
        scrollbar-color: #f59e0b rgba(15, 23, 42, 0.65);
    }

    .paper-table-wrap::-webkit-scrollbar {
        height: 18px;
    }

    .paper-table-wrap::-webkit-scrollbar-track {
        background: rgba(15, 23, 42, 0.65);
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.08);
    }

    .paper-table-wrap::-webkit-scrollbar-thumb {
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
        border-radius: 999px;
        border: 2px solid rgba(15, 23, 42, 0.65);
    }

    .paper-table-wrap::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(90deg, #fbbf24, #fde68a);
    }

    .service-plan-table {
        width: 100%;
        min-width: 1800px;
        border-collapse: collapse;
        table-layout: fixed;
        background: rgba(255,255,255,0.96);
        font-variant-numeric: tabular-nums;
    }

    .service-plan-table th,
    .service-plan-table td {
        border: 1px solid #1f2937;
        padding: 0;
        vertical-align: middle;
    }

    .service-plan-table thead th {
        background: #e5e7eb;
        color: #111827;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        text-align: center;
        padding: 6px 4px;
        line-height: 1.15;
    }

    .service-plan-table thead .group-head {
        background: #d1d5db;
    }

    .service-plan-table thead .small-head {
        font-size: 9px;
    }

    .service-plan-table tbody td {
        background: rgba(255,255,255,0.94);
    }

    .service-plan-table tbody tr {
        height: 56px;
    }

    .service-plan-table tbody tr:nth-child(even) td {
        background: rgba(248, 250, 252, 0.96);
    }

    .cell-input,
    .cell-select,
    .cell-textarea {
        width: 100%;
        border: none;
        background: transparent;
        color: #111827;
        font-size: 11px;
        padding: 6px 4px;
        min-height: 36px;
        font-family: inherit;
    }

    .service-plan-table td:nth-child(4) .cell-input,
    .service-plan-table td:nth-child(5) .cell-input,
    .service-plan-table td:nth-child(6) .cell-input,
    .service-plan-table td:nth-child(7) .cell-input,
    .service-plan-table td:nth-child(8) .cell-input,
    .service-plan-table td:nth-child(9) .cell-input,
    .service-plan-table td:nth-child(10) .cell-input,
    .service-plan-table td:nth-child(11) .cell-input,
    .service-plan-table td:nth-child(12) .cell-input,
    .service-plan-table td:nth-child(13) .cell-input,
    .service-plan-table td:nth-child(15) .cell-input,
    .service-plan-table td:nth-child(16) .cell-input {
        font-family: 'Inter', 'Sarabun', sans-serif;
        font-weight: 600;
        letter-spacing: 0.02em;
    }

    .cell-input,
    .cell-select {
        text-align: center;
    }

    .cell-textarea {
        resize: vertical;
        min-height: 46px;
        line-height: 1.35;
    }

    .cell-input:focus,
    .cell-select:focus,
    .cell-textarea:focus {
        outline: none;
        background: rgba(191, 219, 254, 0.35);
    }

    .train-set-cell {
        min-width: 140px;
    }

    .train-set-stack {
        display: flex;
        flex-direction: column;
        min-height: 70px;
    }

    .train-set-order {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 22px;
        border-bottom: 1px dashed #64748b;
        background: rgba(15, 23, 42, 0.08);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: #334155;
    }

    .train-set-stack .cell-select {
        font-weight: 700;
        min-height: 34px;
    }

    .service-status-select {
        border-top: 1px dashed #64748b;
        min-height: 32px;
        font-size: 10px;
        font-weight: 700;
    }

    .train-status-available {
        background: rgba(34, 197, 94, 0.28);
    }

    .train-status-warning {
        background: rgba(250, 204, 21, 0.32);
    }

    .train-status-out_of_service {
        background: rgba(248, 113, 113, 0.28);
    }

    .row-theme-green td { background: rgba(134, 239, 172, 0.45) !important; }
    .row-theme-pink td { background: rgba(244, 114, 182, 0.18) !important; }
    .row-theme-peach td { background: rgba(251, 191, 153, 0.35) !important; }
    .row-theme-blue td { background: rgba(147, 197, 253, 0.35) !important; }
    .row-theme-red td { background: rgba(248, 113, 113, 0.25) !important; }
    .row-theme-yellow td { background: rgba(250, 204, 21, 0.45) !important; }

    .paper-footer {
        padding: 12px 16px 14px;
        border-top: 2px solid #1e293b;
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 12px;
        background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
    }

    .paper-footer label {
        display: block;
        font-size: 12px;
        font-weight: 800;
        color: #334155;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .paper-footer textarea {
        width: 100%;
        min-height: 120px;
        border: 1px solid #94a3b8;
        border-radius: 10px;
        padding: 12px 14px;
        font-family: inherit;
        font-size: 13px;
        color: #0f172a;
        background:
            repeating-linear-gradient(
                to bottom,
                rgba(255,255,255,0.96),
                rgba(255,255,255,0.96) 27px,
                rgba(148,163,184,0.25) 27px,
                rgba(148,163,184,0.25) 28px
            );
        line-height: 28px;
    }

    .paper-footer textarea:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .status-legend {
        display: grid;
        gap: 10px;
        align-content: start;
    }

    .status-legend .legend-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        background: rgba(255,255,255,0.96);
        font-size: 13px;
        font-weight: 600;
    }

    .status-legend .swatch {
        width: 16px;
        height: 16px;
        border-radius: 999px;
        border: 1px solid rgba(15, 23, 42, 0.2);
    }

    .status-legend .swatch.available { background: #22c55e; }
    .status-legend .swatch.warning { background: #facc15; }
    .status-legend .swatch.out { background: #ef4444; }

    .status-legend .callout-box {
        border: 1px solid #1f2937;
        background: #dbeafe;
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 12px;
        line-height: 1.55;
    }

    .status-legend .callout-box strong {
        display: block;
        margin-bottom: 6px;
    }

    .paper-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .footer-block-grid {
        display: grid;
        grid-template-columns: 1.25fr 0.75fr;
        gap: 12px;
    }

    .footer-mini-table {
        width: 100%;
        border-collapse: collapse;
        background: rgba(255,255,255,0.96);
        table-layout: fixed;
    }

    .footer-mini-table th,
    .footer-mini-table td {
        border: 1px solid #64748b;
        padding: 6px 8px;
        font-size: 12px;
    }

    .footer-mini-table tbody tr {
        height: 34px;
    }

    .footer-mini-table th {
        background: #e2e8f0;
        font-weight: 700;
        text-transform: uppercase;
    }

    .footer-mini-table input {
        width: 100%;
        border: none;
        background: transparent;
        font: inherit;
        color: #0f172a;
    }

    .footer-mini-table input:focus {
        outline: none;
    }

    .highlight-notice-input {
        width: 100%;
        border: 1px solid #ca8a04;
        background: #fef08a;
        color: #7c2d12;
        padding: 10px 12px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 12px;
    }

    .page-helper-text {
        color: var(--text-muted);
        font-size: 12px;
    }

    .paper-helper-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        flex-wrap: wrap;
        padding: 6px 10px;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 12px;
    }

    .scrollbar-hint {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 6px 10px;
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 10px;
        background: rgba(245, 158, 11, 0.08);
        color: #f8fafc;
        font-size: 12px;
    }

    .scrollbar-hint strong {
        color: #fbbf24;
    }

    .paper-signoff-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-top: 10px;
    }

    .paper-signoff-box {
        border: 1px solid #64748b;
        border-radius: 10px;
        background: rgba(255,255,255,0.96);
        min-height: 74px;
        display: grid;
        grid-template-rows: auto 1fr;
    }

    .paper-signoff-box strong {
        padding: 8px 10px;
        border-bottom: 1px solid #cbd5e1;
        font-size: 11px;
        text-transform: uppercase;
        color: #334155;
        letter-spacing: 0.05em;
    }

    .paper-signoff-box span {
        display: flex;
        align-items: end;
        padding: 8px 10px;
        color: #64748b;
        font-size: 11px;
    }

    @media print {
        body {
            background: #fff !important;
        }

        .sidebar,
        .topbar,
        .service-book-toolbar,
        .service-book-summary,
        .paper-helper-bar,
        .flash-message {
            display: none !important;
        }

        .main-content {
            margin-left: 0 !important;
        }

        .page-content {
            padding: 0 !important;
        }

        .paper-fit-stage,
        .paper-fit-canvas {
            overflow: visible !important;
            zoom: 1 !important;
            width: 100% !important;
            height: auto !important;
        }

        .paper-card {
            box-shadow: none;
            border-radius: 0;
            border: none;
        }

        .service-book-shell {
            gap: 0;
        }

        @page {
            size: A3 landscape;
            margin: 8mm;
        }
    }

    @media (max-width: 1024px) {
        .page-content {
            padding: 12px;
        }

        .service-book-summary,
        .paper-topline,
        .paper-header,
        .paper-reference-bar,
        .paper-toolbar,
        .paper-footer,
        .footer-block-grid,
        .paper-signoff-grid {
            grid-template-columns: 1fr;
        }

        .paper-reference-cell {
            grid-template-columns: 92px 1fr;
        }
    }

</style>

<div class="service-book-shell">
    <div class="service-book-toolbar">
        <div class="book-nav">
            <a href="{{ route('daily-plan', ['date' => $previousDate]) }}" class="btn btn-secondary">
                <i class="fas fa-chevron-left"></i>
                หน้าก่อนหน้า
            </a>
            <div class="book-date">
                <i class="fas fa-book-open" style="color: var(--amber);"></i>
                <form method="GET" action="{{ route('daily-plan') }}">
                    <input type="date" name="date" value="{{ $date->toDateString() }}" class="form-input" onchange="this.form.submit()" style="min-width: 170px; padding: 8px 10px;">
                </form>
                <span style="color: var(--text-muted); font-size: 13px;">
                    {{ $date->locale('en')->translatedFormat('l, d F Y') }}
                </span>
            </div>
            <a href="{{ route('daily-plan', ['date' => $nextDate]) }}" class="btn btn-secondary">
                หน้าถัดไป
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <div class="paper-actions">
            <a href="{{ route('daily-plan.master') }}" class="btn btn-secondary">
                <i class="fas fa-sliders"></i>
                Master Data
            </a>
            <form method="POST" action="{{ route('daily-plan.copy-previous') }}" style="display: inline-flex;">
                @csrf
                <input type="hidden" name="service_date" value="{{ $date->toDateString() }}">
                <button type="submit" class="btn btn-secondary" {{ $hasPreviousDay ? '' : 'disabled' }}>
                    <i class="fas fa-copy"></i>
                    คัดลอกจากวันก่อน
                </button>
            </form>
            <a href="{{ route('daily-plan.pdf', ['date' => $date->toDateString()]) }}" target="_blank" class="btn btn-secondary">
                <i class="fas fa-file-pdf"></i>
                Export PDF
            </a>
            <button type="button" class="btn btn-secondary" onclick="window.print()">
                <i class="fas fa-print"></i>
                พิมพ์แบบฟอร์ม
            </button>
            <button type="button" class="btn btn-secondary" id="apply-master-defaults">
                <i class="fas fa-wand-magic-sparkles"></i>
                เติมค่ามาตรฐาน
            </button>
            <button type="submit" form="service-plan-form" class="btn btn-primary">
                <i class="fas fa-floppy-disk"></i>
                บันทึกหน้าปัจจุบัน
            </button>
        </div>
    </div>

    <div class="service-book-summary">
        <div class="summary-card available">
            <strong>{{ $statusSummary['available'] }}</strong>
            <small>ขบวนพร้อมให้บริการ</small>
        </div>
        <div class="summary-card warning">
            <strong>{{ $statusSummary['warning'] }}</strong>
            <small>ขบวนใกล้วาระซ่อม</small>
        </div>
        <div class="summary-card out">
            <strong>{{ $statusSummary['out_of_service'] }}</strong>
            <small>ขบวนงดให้บริการ</small>
        </div>
    </div>

    <div class="paper-helper-bar">
        <div class="page-helper-text">เลือกวันเพื่อเปิดหน้ากระดาษของวันนั้น, ใช้ Master Data เพื่อกำหนดค่าถาวรของ T01-T25 แล้วกด “เติมค่ามาตรฐาน” เมื่อต้องการกรอกฟอร์มเร็วขึ้น</div>
        <div class="page-helper-text">ค่าที่กรอกไว้แล้วจะไม่ถูกทับเมื่อใช้การเติมค่ามาตรฐาน และสามารถคัดลอกทั้งหน้าจากวันก่อนหน้าได้</div>
    </div>

    <div class="scrollbar-hint">
        <span><strong>การแสดงผล:</strong> จอใหญ่จะย่อทั้งหน้ากระดาษอัตโนมัติ ส่วนจอเล็กยังเลื่อนซ้าย-ขวาผ่านแถบสีส้มด้านล่างของตารางได้</span>
        <span>Horizontal Scroll</span>
    </div>

    <form id="service-plan-form" method="POST" action="{{ route('daily-plan.save') }}">
        @csrf
        <input type="hidden" name="service_date" value="{{ $date->toDateString() }}">

        <div class="paper-fit-stage">
            <div class="paper-fit-canvas" id="paper-fit-canvas">
                <div class="paper-card" id="paper-card">
                    <div class="paper-topline">
                        <div>FM-OCD-014</div>
                        <div class="paper-doc-center">Trainset Service Plan</div>
                        <div class="paper-doc-right">
                            <span>Rev: 00</span>
                            <span>Eff. Date: 6 ก.ย. 2565</span>
                        </div>
                    </div>

                    <div class="paper-header">
                        <div>
                            <div class="paper-brand paper-brand-mark">SRTET</div>
                            <div class="paper-subtitle">SRT Electrified Train จำกัด</div>
                        </div>

                        <div class="paper-title">
                            <input type="text" name="header_title" value="{{ old('header_title', $day->header_title) }}">
                        </div>

                        <div class="paper-meta">
                            <div class="paper-meta-grid">
                                <div class="label">Book</div>
                                <div class="value">{{ $bookReference }}</div>
                                <div class="label">Page</div>
                                <div class="value">{{ $pageReference }}</div>
                                <div class="label">Date</div>
                                <div class="value">{{ $date->format('d') }}</div>
                                <div class="label">Day</div>
                                <div class="value">{{ $dayName }}</div>
                                <div class="label">Month</div>
                                <div class="value">{{ $displayMonthYear }}</div>
                                <div class="label">Issued</div>
                                <div class="value">{{ $date->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="paper-reference-bar">
                        <div class="paper-reference-cell">
                            <span>Book / Page</span>
                            <strong>{{ $bookReference }} / {{ $pageReference }}</strong>
                        </div>
                        <div class="paper-reference-cell">
                            <span>Service Day</span>
                            <strong>{{ $dayName }}</strong>
                        </div>
                        <div class="paper-reference-cell">
                            <span>Service Date</span>
                            <strong>{{ $date->format('d M Y') }}</strong>
                        </div>
                    </div>

                    <div class="paper-toolbar">
                        <div class="field-row">
                            <label>Timetable</label>
                            <input type="text" name="timetable_label" value="{{ old('timetable_label', $day->timetable_label) }}">
                        </div>
                        <div class="field-row compact">
                            <label>Page Flow</label>
                            <input type="text" value="{{ $previousDate }}  →  {{ $date->toDateString() }}  →  {{ $nextDate }}" readonly>
                        </div>
                    </div>

                    <div class="paper-table-wrap" id="paper-table-wrap">
                        <table class="service-plan-table">
                            <colgroup>
                                <col style="width: 140px;">
                                <col style="width: 95px;">
                                <col style="width: 66px;">
                                <col style="width: 88px;">
                                <col style="width: 82px;">
                                <col style="width: 82px;">
                                <col style="width: 82px;">
                                <col style="width: 92px;">
                                <col style="width: 82px;">
                                <col style="width: 82px;">
                                <col style="width: 78px;">
                                <col style="width: 92px;">
                                <col style="width: 88px;">
                                <col style="width: 96px;">
                                <col style="width: 82px;">
                                <col style="width: 88px;">
                                <col style="width: 90px;">
                                <col style="width: 280px;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th rowspan="2">Train Set No.</th>
                                    <th rowspan="2">Berth No.</th>
                                    <th rowspan="2">Type</th>
                                    <th rowspan="2">Run No.</th>
                                    <th colspan="4" class="group-head">First Contact</th>
                                    <th colspan="2" class="group-head">Dep. Time</th>
                                    <th colspan="2" class="group-head">KTW</th>
                                    <th rowspan="2">Run No.</th>
                                    <th colspan="2" class="group-head">End</th>
                                    <th rowspan="2">End No.</th>
                                    <th rowspan="2">End Depot</th>
                                    <th rowspan="2">Special Instructions</th>
                                </tr>
                                <tr>
                                    <th class="small-head">Plan</th>
                                    <th class="small-head">Cab 1</th>
                                    <th class="small-head">Cab 4/6</th>
                                    <th class="small-head">Brake Test</th>
                                    <th class="small-head">Plan</th>
                                    <th class="small-head">Actual</th>
                                    <th class="small-head">Platform</th>
                                    <th class="small-head">Next Depart</th>
                                    <th class="small-head">Station</th>
                                    <th class="small-head">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entries as $entry)
                                    @php
                                        $selectedTrainSet = old("entries.{$entry->id}.train_set_id", $entry->train_set_id);
                                        $selectedStatus = old("entries.{$entry->id}.service_status", $entry->service_status);
                                        $selectedRowTheme = old("entries.{$entry->id}.row_theme", $entry->row_theme) ?? 'none';
                                    @endphp
                                    <tr class="{{ $selectedRowTheme !== 'none' ? 'row-theme-' . $selectedRowTheme : '' }}">
                                        <td class="train-set-cell train-status-{{ $selectedStatus }}" data-train-cell>
                                            <div class="train-set-stack">
                                                <div class="train-set-order">#{{ str_pad((string) $entry->display_order, 2, '0', STR_PAD_LEFT) }}</div>
                                                <select name="entries[{{ $entry->id }}][train_set_id]" class="cell-select train-set-select">
                                                    @foreach($trainSets as $trainSet)
                                                        <option
                                                            value="{{ $trainSet->id }}"
                                                            data-default-type="{{ $trainSet->default_consist_type }}"
                                                            data-default-berth="{{ $trainSet->default_berth_no }}"
                                                            data-default-platform="{{ $trainSet->default_ktw_platform }}"
                                                            data-default-end-station="{{ $trainSet->default_end_station }}"
                                                            data-default-end-no="{{ $trainSet->default_end_no }}"
                                                            data-default-end-depot="{{ $trainSet->default_end_depot }}"
                                                            data-default-special="{{ e($trainSet->default_special_instructions) }}"
                                                            data-default-out-run="{{ $trainSet->default_outbound_run_no }}"
                                                            data-default-fc-plan="{{ $trainSet->default_first_contact_plan }}"
                                                            data-default-dep-plan="{{ $trainSet->default_departure_plan_time }}"
                                                            data-default-next-depart="{{ $trainSet->default_ktw_next_depart_time }}"
                                                            data-default-in-run="{{ $trainSet->default_inbound_run_no }}"
                                                            data-default-end-time="{{ $trainSet->default_end_time }}"
                                                            data-default-row-theme="{{ $trainSet->default_row_theme }}"
                                                            @selected((string) $selectedTrainSet === (string) $trainSet->id)
                                                        >{{ $trainSet->code }}</option>
                                                    @endforeach
                                                </select>
                                                <select name="entries[{{ $entry->id }}][service_status]" class="cell-select service-status-select">
                                                    <option value="available" @selected($selectedStatus === 'available')>เขียว • พร้อมใช้</option>
                                                    <option value="warning" @selected($selectedStatus === 'warning')>เหลือง • ใกล้ซ่อม</option>
                                                    <option value="out_of_service" @selected($selectedStatus === 'out_of_service')>แดง • งดใช้</option>
                                                </select>
                                                <select name="entries[{{ $entry->id }}][row_theme]" class="cell-select row-theme-select">
                                                    @foreach($rowThemes as $value => $label)
                                                        <option value="{{ $value }}" @selected($selectedRowTheme === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="entries[{{ $entry->id }}][berth_no]" value="{{ old("entries.{$entry->id}.berth_no", $entry->berth_no) }}" class="cell-input berth-input" list="berth-options">
                                        </td>
                                        <td>
                                            <select name="entries[{{ $entry->id }}][consist_type]" class="cell-select consist-type-select">
                                                <option value="4" @selected(old("entries.{$entry->id}.consist_type", $entry->consist_type) === '4')>4</option>
                                                <option value="6" @selected(old("entries.{$entry->id}.consist_type", $entry->consist_type) === '6')>6</option>
                                            </select>
                                        </td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][outbound_run_no]" value="{{ old("entries.{$entry->id}.outbound_run_no", $entry->outbound_run_no) }}" class="cell-input out-run-input"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][first_contact_plan]" value="{{ old("entries.{$entry->id}.first_contact_plan", $entry->first_contact_plan) }}" class="cell-input fc-plan-input"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][cab_one_time]" value="{{ old("entries.{$entry->id}.cab_one_time", $entry->cab_one_time) }}" class="cell-input"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][cab_four_six_time]" value="{{ old("entries.{$entry->id}.cab_four_six_time", $entry->cab_four_six_time) }}" class="cell-input"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][brake_test_time]" value="{{ old("entries.{$entry->id}.brake_test_time", $entry->brake_test_time) }}" class="cell-input"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][departure_plan_time]" value="{{ old("entries.{$entry->id}.departure_plan_time", $entry->departure_plan_time) }}" class="cell-input dep-plan-input"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][departure_actual_time]" value="{{ old("entries.{$entry->id}.departure_actual_time", $entry->departure_actual_time) }}" class="cell-input"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][ktw_platform]" value="{{ old("entries.{$entry->id}.ktw_platform", $entry->ktw_platform) }}" class="cell-input ktw-platform-input" list="platform-options"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][ktw_next_depart_time]" value="{{ old("entries.{$entry->id}.ktw_next_depart_time", $entry->ktw_next_depart_time) }}" class="cell-input next-depart-input"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][inbound_run_no]" value="{{ old("entries.{$entry->id}.inbound_run_no", $entry->inbound_run_no) }}" class="cell-input in-run-input"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][end_station]" value="{{ old("entries.{$entry->id}.end_station", $entry->end_station) }}" class="cell-input end-station-input" list="end-station-options"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][end_time]" value="{{ old("entries.{$entry->id}.end_time", $entry->end_time) }}" class="cell-input end-time-input"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][end_no]" value="{{ old("entries.{$entry->id}.end_no", $entry->end_no) }}" class="cell-input end-no-input" list="end-no-options"></td>
                                        <td><input type="text" name="entries[{{ $entry->id }}][end_depot]" value="{{ old("entries.{$entry->id}.end_depot", $entry->end_depot) }}" class="cell-input end-depot-input" list="end-depot-options"></td>
                                        <td>
                                            <textarea name="entries[{{ $entry->id }}][special_instructions]" class="cell-textarea special-instructions-input">{{ old("entries.{$entry->id}.special_instructions", $entry->special_instructions) }}</textarea>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <datalist id="berth-options">
                        @foreach($masterOptions['berths'] as $value)
                            <option value="{{ $value }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="platform-options">
                        @foreach($masterOptions['platforms'] as $value)
                            <option value="{{ $value }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="end-station-options">
                        @foreach($masterOptions['endStations'] as $value)
                            <option value="{{ $value }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="end-no-options">
                        @foreach($masterOptions['endNos'] as $value)
                            <option value="{{ $value }}"></option>
                        @endforeach
                    </datalist>
                    <datalist id="end-depot-options">
                        @foreach($masterOptions['depots'] as $value)
                            <option value="{{ $value }}"></option>
                        @endforeach
                    </datalist>

                    <div class="paper-footer">
                        <div>
                            <label>Highlight Notice</label>
                            <input type="text" class="highlight-notice-input" name="highlight_notice" value="{{ old('highlight_notice', $day->highlight_notice) }}">
                            <div style="height: 12px;"></div>
                            <label>Notes / Daily Report</label>
                            <textarea name="footer_notes" placeholder="บันทึกหมายเหตุประจำวัน, call on, งานซ่อม, event พิเศษ หรือ handover สำหรับวันถัดไป">{{ old('footer_notes', $day->footer_notes) }}</textarea>
                            <div style="height: 12px;"></div>
                            <div class="footer-block-grid">
                                <div>
                                    <label>Notes Timeline</label>
                                    <table class="footer-mini-table">
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
                                            @foreach(collect(old('note_blocks', $noteBlocks))->pad(count($noteBlocks), ['time' => '', 'from' => '', 'location' => '', 'arrow' => '>>>', 'to' => '', 'flag' => ''])->all() as $index => $block)
                                                <tr>
                                                    <td><input type="text" name="note_blocks[{{ $index }}][time]" value="{{ $block['time'] ?? '' }}"></td>
                                                    <td><input type="text" name="note_blocks[{{ $index }}][from]" value="{{ $block['from'] ?? '' }}"></td>
                                                    <td><input type="text" name="note_blocks[{{ $index }}][location]" value="{{ $block['location'] ?? '' }}"></td>
                                                    <td><input type="text" name="note_blocks[{{ $index }}][arrow]" value="{{ $block['arrow'] ?? '>>>' }}"></td>
                                                    <td><input type="text" name="note_blocks[{{ $index }}][to]" value="{{ $block['to'] ?? '' }}"></td>
                                                    <td><input type="text" name="note_blocks[{{ $index }}][flag]" value="{{ $block['flag'] ?? '' }}"></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <label>TWP / Handover List</label>
                                    <table class="footer-mini-table">
                                        <thead>
                                            <tr>
                                                <th>Set</th>
                                                <th>Target</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(collect(old('handover_blocks', $handoverBlocks))->pad(count($handoverBlocks), ['set' => '', 'target' => ''])->all() as $index => $block)
                                                <tr>
                                                    <td><input type="text" name="handover_blocks[{{ $index }}][set]" value="{{ $block['set'] ?? '' }}"></td>
                                                    <td><input type="text" name="handover_blocks[{{ $index }}][target]" value="{{ $block['target'] ?? '' }}"></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="paper-signoff-grid">
                                <div class="paper-signoff-box">
                                    <strong>Dispatcher</strong>
                                    <span>ลงชื่อ / เวลา</span>
                                </div>
                                <div class="paper-signoff-box">
                                    <strong>OCC Supervisor</strong>
                                    <span>ตรวจสอบ / รับทราบ</span>
                                </div>
                                <div class="paper-signoff-box">
                                    <strong>Handover</strong>
                                    <span>กะถัดไป / ผู้รับช่วง</span>
                                </div>
                            </div>
                        </div>

                        <div class="status-legend">
                            <label>Legend</label>
                            <div class="legend-item">
                                <span class="swatch available"></span>
                                พร้อมให้บริการ
                            </div>
                            <div class="legend-item">
                                <span class="swatch warning"></span>
                                ใกล้วาระซ่อม
                            </div>
                            <div class="legend-item">
                                <span class="swatch out"></span>
                                งดให้บริการ
                            </div>
                            <div class="legend-item" style="display: block; line-height: 1.5; font-weight: 500;">
                                ใช้งานแบบสมุด: เลือกวันที่ด้านบน แล้วบันทึกข้อมูลของวันนั้น เมื่อเปิดวันถัดไป ระบบจะสร้างหน้ากระดาษใหม่ให้ต่อเนื่องทันที
                            </div>
                            <div class="callout-box">
                                <strong>TWP / Handover</strong>
                                ใช้กล่องนี้สำหรับจดขบวนที่ส่งต่อเข้าวันถัดไป, งานค้าง, call on และรายการพิเศษที่ต้องเฝ้าระวัง เพื่อให้รูปแบบการใช้งานใกล้กับสมุดจริงมากขึ้น
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fitStage = document.querySelector('.paper-fit-stage');
    const fitCanvas = document.getElementById('paper-fit-canvas');
    const paperCard = document.getElementById('paper-card');
    const statusClassMap = {
        available: 'train-status-available',
        warning: 'train-status-warning',
        out_of_service: 'train-status-out_of_service',
    };

    const desktopFitQuery = window.matchMedia('(min-width: 1025px)');

    const resetPaperFit = () => {
        if (!fitStage || !fitCanvas || !paperCard) {
            return;
        }

        fitStage.classList.remove('paper-fit-active');
        fitCanvas.style.width = '';
        fitCanvas.style.height = '';
        paperCard.style.width = '';
        paperCard.style.transform = '';
    };

    const applyPaperFit = () => {
        if (!fitStage || !fitCanvas || !paperCard) {
            return;
        }

        resetPaperFit();

        if (!desktopFitQuery.matches) {
            return;
        }

        paperCard.style.width = '1880px';

        requestAnimationFrame(() => {
            const stageWidth = fitStage.clientWidth;
            const naturalWidth = paperCard.offsetWidth;
            const naturalHeight = paperCard.offsetHeight;

            if (!stageWidth || !naturalWidth || !naturalHeight) {
                return;
            }

            const fitPadding = 28;
            const availableWidth = Math.max(stageWidth - fitPadding, 0);
            const scale = Math.min(1, availableWidth / naturalWidth);

            fitStage.classList.add('paper-fit-active');
            fitCanvas.style.width = `${Math.ceil(naturalWidth * scale)}px`;
            fitCanvas.style.height = `${Math.ceil(naturalHeight * scale)}px`;
            paperCard.style.transform = `scale(${scale})`;
        });
    };

    document.querySelectorAll('.service-plan-table tbody tr').forEach((row) => {
        const trainSetSelect = row.querySelector('.train-set-select');
        const statusSelect = row.querySelector('.service-status-select');
        const rowThemeSelect = row.querySelector('.row-theme-select');
        const berthInput = row.querySelector('.berth-input');
        const consistTypeSelect = row.querySelector('.consist-type-select');
        const outRunInput = row.querySelector('.out-run-input');
        const fcPlanInput = row.querySelector('.fc-plan-input');
        const depPlanInput = row.querySelector('.dep-plan-input');
        const platformInput = row.querySelector('.ktw-platform-input');
        const nextDepartInput = row.querySelector('.next-depart-input');
        const inRunInput = row.querySelector('.in-run-input');
        const endStationInput = row.querySelector('.end-station-input');
        const endTimeInput = row.querySelector('.end-time-input');
        const endNoInput = row.querySelector('.end-no-input');
        const endDepotInput = row.querySelector('.end-depot-input');
        const specialInstructionsInput = row.querySelector('.special-instructions-input');
        const trainCell = row.querySelector('[data-train-cell]');

        const applyStatusColor = () => {
            Object.values(statusClassMap).forEach((className) => trainCell.classList.remove(className));
            trainCell.classList.add(statusClassMap[statusSelect.value] || statusClassMap.available);
        };

        const applyRowTheme = () => {
            ['green', 'pink', 'peach', 'blue', 'red', 'yellow'].forEach((theme) => row.classList.remove('row-theme-' + theme));
            if (rowThemeSelect.value && rowThemeSelect.value !== 'none') {
                row.classList.add('row-theme-' + rowThemeSelect.value);
            }
        };

        const syncTrainDefaults = (forceFill = false) => {
            const selectedOption = trainSetSelect.options[trainSetSelect.selectedIndex];

            if (! selectedOption) {
                return;
            }

            if (forceFill || ! berthInput.value) {
                berthInput.value = selectedOption.dataset.defaultBerth || '';
            }

            if (forceFill || ! consistTypeSelect.value) {
                consistTypeSelect.value = selectedOption.dataset.defaultType || '6';
            }

            if (forceFill || ! outRunInput.value) {
                outRunInput.value = selectedOption.dataset.defaultOutRun || '';
            }

            if (forceFill || ! fcPlanInput.value) {
                fcPlanInput.value = selectedOption.dataset.defaultFcPlan || '';
            }

            if (forceFill || ! depPlanInput.value) {
                depPlanInput.value = selectedOption.dataset.defaultDepPlan || '';
            }

            if (forceFill || ! platformInput.value) {
                platformInput.value = selectedOption.dataset.defaultPlatform || '';
            }

            if (forceFill || ! nextDepartInput.value) {
                nextDepartInput.value = selectedOption.dataset.defaultNextDepart || '';
            }

            if (forceFill || ! inRunInput.value) {
                inRunInput.value = selectedOption.dataset.defaultInRun || '';
            }

            if (forceFill || ! endStationInput.value) {
                endStationInput.value = selectedOption.dataset.defaultEndStation || '';
            }

            if (forceFill || ! endTimeInput.value) {
                endTimeInput.value = selectedOption.dataset.defaultEndTime || '';
            }

            if (forceFill || ! endNoInput.value) {
                endNoInput.value = selectedOption.dataset.defaultEndNo || '';
            }

            if (forceFill || ! endDepotInput.value) {
                endDepotInput.value = selectedOption.dataset.defaultEndDepot || '';
            }

            if (forceFill || ! specialInstructionsInput.value) {
                specialInstructionsInput.value = selectedOption.dataset.defaultSpecial || '';
            }

            if (forceFill || ! rowThemeSelect.value || rowThemeSelect.value === 'none') {
                rowThemeSelect.value = selectedOption.dataset.defaultRowTheme || 'none';
            }

            applyRowTheme();
        };

        trainSetSelect.addEventListener('change', syncTrainDefaults);
        statusSelect.addEventListener('change', applyStatusColor);
        rowThemeSelect.addEventListener('change', applyRowTheme);

        syncTrainDefaults();
        applyStatusColor();
        applyRowTheme();

        row.dataset.bindReady = '1';
        row.syncTrainDefaults = syncTrainDefaults;
    });

    const applyMasterDefaultsButton = document.getElementById('apply-master-defaults');
    if (applyMasterDefaultsButton) {
        applyMasterDefaultsButton.addEventListener('click', () => {
            document.querySelectorAll('.service-plan-table tbody tr').forEach((row) => {
                if (typeof row.syncTrainDefaults === 'function') {
                    row.syncTrainDefaults(false);
                }
            });
        });
    }

    applyPaperFit();

    if (typeof desktopFitQuery.addEventListener === 'function') {
        desktopFitQuery.addEventListener('change', applyPaperFit);
    } else if (typeof desktopFitQuery.addListener === 'function') {
        desktopFitQuery.addListener(applyPaperFit);
    }

    window.addEventListener('resize', applyPaperFit);
});
</script>
@endsection
