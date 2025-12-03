@extends('layouts.master')

@section('style')
<style>
:root {
    --report-bg: #f4f6fb;
    --card-border: #e2e6f0;
    --text-muted: #6c757d;
    --success-soft: rgba(15, 157, 88, 0.12);
    --danger-soft: rgba(217, 48, 37, 0.12);
}
.report-page {
    background: var(--report-bg);
    padding-bottom: 2rem;
}
.page-header {
    background: linear-gradient(135deg, #0d6efd, #6610f2);
    border-radius: 20px;
    padding: 2rem;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.page-header:after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.2), transparent 45%);
    pointer-events: none;
}
.page-header h2 {
    font-weight: 600;
}
.page-header .page-label {
    letter-spacing: 0.15rem;
    font-size: 0.75rem;
    opacity: 0.75;
}
.digital-clock {
    font-family: 'Courier New', Courier, monospace;
    min-width: 160px;
    text-align: center;
    background: rgba(0, 0, 0, 0.35);
    border: none;
    border-radius: 12px;
    padding: 0.65rem 1.25rem;
    font-size: 1.75rem;
    letter-spacing: 3px;
    box-shadow: none;
    color: #fff;
}
.stat-card {
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--card-border);
    padding: 1.5rem;
    height: 100%;
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.05);
}
.stat-card h4 {
    font-weight: 600;
    margin-bottom: 0.25rem;
}
.stat-trend {
    font-size: 0.85rem;
}
.stat-trend.positive {
    color: #0f9d58;
}
.stat-trend.negative {
    color: #d93025;
}
.filter-card {
    border: none;
    border-radius: 16px;
}
.filter-card .card-body {
    padding: 1.75rem;
}
.quick-range-group {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}
.quick-range-group .btn {
    border-radius: 999px;
    border: 1px solid var(--card-border);
    color: #495057;
    background: transparent;
    margin-left: 0.35rem;
    padding: 0.35rem 1rem;
}
.quick-range-group .btn.active,
.quick-range-group .btn:focus {
    background: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.25);
}
.table-card {
    border-radius: 16px;
}
.table-modern {
    border-collapse: separate;
    border-spacing: 0;
}
.table-modern thead th {
    background: #f8f9fb;
    border-bottom: 1px solid var(--card-border);
    font-weight: 600;
}
.table-modern tbody tr {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.table-modern tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
}
.net-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.65rem;
    border-radius: 999px;
    font-weight: 600;
    border: 1px solid transparent;
    font-size: 0.85rem;
}
.net-badge--positive {
    color: #0f9d58;
    background: var(--success-soft);
    border-color: rgba(15, 157, 88, 0.2);
}
.net-badge--negative {
    color: #d93025;
    background: var(--danger-soft);
    border-color: rgba(217, 48, 37, 0.2);
}
.empty-state {
    padding: 2rem;
    text-align: center;
    color: var(--text-muted);
}
.meta-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.9rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.2);
    font-size: 0.85rem;
}
.meta-pill--muted {
    background: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}
.totals-card {
    border-radius: 16px;
}
.totals-card .progress {
    height: 6px;
    border-radius: 999px;
    background: #e9ecef;
}
.btn-icon {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
@media (max-width: 767.98px) {
    .page-header {
        padding: 1.5rem;
    }
    .quick-range-group {
        margin-top: 1rem;
    }
    .quick-range-group .btn {
        margin-top: 0.35rem;
    }
    .digital-clock {
        font-size: 1.25rem;
        letter-spacing: 2px;
    }
}
</style>
@endsection


@section('content')
<div class="container-fluid report-page">
    <div class="page-header shadow-sm mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <div class="page-header__left mb-3 mb-md-0">
                <p class="page-label text-uppercase mb-2">Performance Overview</p>
                <h2 class="mb-2">Player Report Dashboard</h2>
                <p class="mb-3 text-white-50">Monitor betting activity, payouts, and net positions in real-time.</p>
                <span class="meta-pill">Last synced {{ now()->format('d M Y, h:i A') }}</span>
            </div>
            <div class="header-clock text-md-right">
                <p class="text-white-50 mb-1">Live Clock</p>
                <div id="digitalClock" class="digital-clock">--:--:--</div>
            </div>
        </div>
    </div>

    @php
        $net = $total['totalWinAmt'] - $total['totalBetAmt'];
        $reportCount = method_exists($report, 'count') ? $report->count() : (is_array($report) ? count($report) : 0);
        $winRate = $total['totalBetAmt'] > 0 ? ($total['totalWinAmt'] / $total['totalBetAmt']) * 100 : 0;
        $winRateClamped = max(0, min(100, $winRate));
    @endphp

    <div class="row mb-4">
        <div class="col-12 col-md-6 col-xl-3 mb-3">
            <div class="stat-card">
                <p class="text-uppercase small text-muted mb-1">Total Bet Amount</p>
                <h4 class="mb-0">{{ number_format($total['totalBetAmt'], 2) }}</h4>
                <span class="stat-trend">Across {{ $reportCount }} player{{ $reportCount === 1 ? '' : 's' }}</span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3 mb-3">
            <div class="stat-card">
                <p class="text-uppercase small text-muted mb-1">Total Payout Amount</p>
                <h4 class="mb-0">{{ number_format($total['totalWinAmt'], 2) }}</h4>
                <span class="stat-trend positive">Updated {{ now()->diffForHumans() }}</span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3 mb-3">
            <div class="stat-card">
                <p class="text-uppercase small text-muted mb-1">Net Position</p>
                <h4 class="mb-0 {{ $net >= 0 ? 'text-success' : 'text-danger' }}">{{ $net >= 0 ? '+' : '-' }} {{ number_format(abs($net), 2) }}</h4>
                <span class="stat-trend {{ $net >= 0 ? 'positive' : 'negative' }}">{{ $net >= 0 ? 'In profit' : 'In loss' }}</span>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl-3 mb-3">
            <div class="stat-card">
                <p class="text-uppercase small text-muted mb-1">Win Rate</p>
                <h4 class="mb-0">{{ number_format($winRate, 2) }}%</h4>
                <span class="stat-trend">Based on total bet volume</span>
            </div>
        </div>
    </div>

    <div class="card filter-card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3">
                <div>
                    <h5 class="mb-1">Refine Results</h5>
                    <p class="mb-0 text-muted">Pinpoint specific players or custom time ranges.</p>
                </div>
                <div class="quick-range-group mt-3 mt-md-0">
                    <span class="text-muted mr-2">Quick range:</span>
                    <button type="button" class="btn btn-light btn-sm" data-range="today">Today</button>
                    <button type="button" class="btn btn-light btn-sm" data-range="7d">Last 7 days</button>
                    <button type="button" class="btn btn-light btn-sm" data-range="30d">Last 30 days</button>
                </div>
            </div>
            <form method="GET" action="" id="reportFilterForm">
                <div class="form-row">
                    <div class="col-sm-6 col-md-4 mb-3">
                        <label for="start_date" class="text-muted small">Start Date</label>
                        <input type="date" class="form-control shadow-sm" name="start_date" id="start_date" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-sm-6 col-md-4 mb-3">
                        <label for="end_date" class="text-muted small">End Date</label>
                        <input type="date" class="form-control shadow-sm" name="end_date" id="end_date" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-sm-12 col-md-4 mb-3">
                        <label for="member_account" class="text-muted small">Player Username</label>
                        <input type="text" class="form-control shadow-sm" name="member_account" id="member_account" value="{{ request('member_account') }}" placeholder="e.g. player001">
                    </div>
                </div>
                <div class="d-flex flex-column flex-sm-row justify-content-end mt-2">
                    <button type="submit" class="btn btn-primary mr-sm-2 mb-2 mb-sm-0">
                        Apply Filters
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="resetFilters">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm table-card mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div>
                    <h5 class="mb-1">Player Report Summary</h5>
                    <p class="mb-0 text-muted">Detailed breakdown of bets, payouts, and net position per player.</p>
                </div>
                <span class="meta-pill meta-pill--muted mt-3 mt-md-0">Updated {{ now()->format('M d, Y H:i') }}</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="mytable" class="table table-modern table-hover align-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Agent Name</th>
                            <th class="text-right">Total Bet</th>
                            <th class="text-right">Total Payout</th>
                            <th class="text-center">Win / Lose</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report as $row)
                            @php
                                $playerNet = $row->total_win - $row->total_bet;
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $row->name }}</div>
                                    <small class="text-muted">Account: {{ $row->member_account }}</small>
                                </td>
                                <td class="text-right font-weight-bold">{{ number_format($row->total_bet, 2) }}</td>
                                <td class="text-right font-weight-bold">{{ number_format($row->total_win, 2) }}</td>
                                <td class="text-center">
                                    <span class="net-badge {{ $playerNet >= 0 ? 'net-badge--positive' : 'net-badge--negative' }}">
                                        {!! $playerNet >= 0 ? '&#9650;' : '&#9660;' !!}
                                        {{ $playerNet >= 0 ? '+' : '-' }} {{ number_format(abs($playerNet), 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.report.detail', ['member_account' => $row->member_account]) }}" class="btn btn-sm btn-primary">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <h6 class="mb-1">No records found</h6>
                                        <p class="mb-0">Try adjusting your filters or date range.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-6">
            <div class="card totals-card shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Performance Snapshot</h5>
                            <p class="mb-0 text-muted">Aggregated view for the applied filters.</p>
                        </div>
                        <span class="meta-pill meta-pill--muted mt-3 mt-sm-0">{{ $reportCount }} active player{{ $reportCount === 1 ? '' : 's' }}</span>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <p class="text-muted mb-1">Net Position</p>
                            <h3 class="{{ $net >= 0 ? 'text-success' : 'text-danger' }} mb-0">{{ $net >= 0 ? '+' : '-' }} {{ number_format(abs($net), 2) }}</h3>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted mb-1">Win Rate</p>
                            <div class="d-flex align-items-center mb-1">
                                <strong class="mr-2">{{ number_format($winRate, 2) }}%</strong>
                                <span class="text-muted">success</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $winRateClamped }}%;"></div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-1">
                            <span>Total Bet Amount</span>
                            <strong>{{ number_format($total['totalBetAmt'], 2) }}</strong>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span>Total Payout Amount</span>
                            <strong>{{ number_format($total['totalWinAmt'], 2) }}</strong>
                        </li>
                        <li class="d-flex justify-content-between py-1">
                            <span>Win / Lose</span>
                            <strong class="{{ $net >= 0 ? 'text-success' : 'text-danger' }}">{{ $net >= 0 ? '+' : '-' }} {{ number_format(abs($net), 2) }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const clockEl = document.getElementById('digitalClock');

    const pad = function (num) {
        return String(num).padStart(2, '0');
    };

    const updateClock = function () {
        if (!clockEl) {
            return;
        }
        const now = new Date();
        clockEl.textContent = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
    };

    updateClock();
    setInterval(updateClock, 1000);

    const form = document.getElementById('reportFilterForm');
    const resetBtn = document.getElementById('resetFilters');
    const quickRangeButtons = document.querySelectorAll('.quick-range-group [data-range]');
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const memberInput = document.getElementById('member_account');

    const formatDate = function (date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    };

    const setActiveRange = function (target) {
        quickRangeButtons.forEach(function (btn) {
            btn.classList.remove('active');
        });
        if (target) {
            target.classList.add('active');
        }
    };

    quickRangeButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!startInput || !endInput) {
                return;
            }

            const today = new Date();
            let start = new Date(today);
            const range = this.getAttribute('data-range');

            if (range === '7d') {
                start.setDate(start.getDate() - 6);
            } else if (range === '30d') {
                start.setDate(start.getDate() - 29);
            }

            startInput.value = formatDate(start);
            endInput.value = formatDate(today);

            setActiveRange(this);

            if (form) {
                form.submit();
            }
        });
    });

    if (resetBtn && form) {
        resetBtn.addEventListener('click', function () {
            if (startInput) {
                startInput.value = '';
            }
            if (endInput) {
                endInput.value = '';
            }
            if (memberInput) {
                memberInput.value = '';
            }
            setActiveRange(null);
            form.submit();
        });
    }
});
</script>
@endsection
