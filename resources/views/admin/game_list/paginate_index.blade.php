@extends('layouts.master')

@section('styles')
<style>
:root {
    --gl-bg: #f5f6fb;
    --gl-card-border: #e5e9f2;
    --gl-primary: #4f46e5;
    --gl-success: #16a34a;
    --gl-danger: #dc2626;
    --gl-warning: #f97316;
}
.game-list-page {
    background: var(--gl-bg);
    padding-bottom: 2rem;
}
.gl-hero {
    background: linear-gradient(135deg, #0d6efd, #6610f2);
    color: #fff;
    border-radius: 24px;
    padding: 2rem;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}
.gl-hero::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at top right, rgba(255,255,255,0.25), transparent 45%);
}
.gl-hero h2 {
    font-weight: 600;
}
.gl-stat-card {
    border-radius: 18px;
    border: 1px solid var(--gl-card-border);
    background: #fff;
    padding: 1.25rem;
    box-shadow: 0 20px 35px rgba(79, 70, 229, 0.08);
    height: 100%;
}
.gl-stat-card p {
    margin-bottom: 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.08rem;
    color: #94a3b8;
    font-weight: 600;
    font-size: 0.75rem;
}
.gl-stat-card h4 {
    font-size: 2rem;
    font-weight: 600;
}
.gl-filter-card,
.gl-table-card {
    border-radius: 22px;
    border: 1px solid var(--gl-card-border);
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
    background: #fff;
}
.gl-filter-card .card-body,
.gl-table-card .card-body {
    padding: 1.75rem;
}
.filter-pill-group .btn {
    border-radius: 999px;
    border: 1px solid var(--gl-card-border);
    color: #4b5563;
    padding: 0.35rem 1.5rem;
}
.filter-pill-group .btn.active {
    background: var(--gl-primary);
    border-color: var(--gl-primary);
    color: #fff;
    box-shadow: 0 0 0 0.15rem rgba(79, 70, 229, 0.25);
}
.gl-table thead th {
    text-transform: uppercase;
    letter-spacing: 0.05rem;
    font-size: 0.75rem;
    color: #94a3b8;
    border-top: none;
    border-bottom: 1px solid var(--gl-card-border);
}
.gl-table tbody tr {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.gl-table tbody tr:hover {
    transform: translateY(-2px);
    box-shadow: inset 0 0 0 9999px rgba(79, 70, 229, 0.025);
}
.gl-thumb {
    width: 50px;
    height: 50px;
    border-radius: 18px;
    object-fit: cover;
    border: 1px solid var(--gl-card-border);
    box-shadow: 0 10px 20px rgba(15, 23, 42, 0.12);
}
.gl-action-group {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.4rem;
}
.gl-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.85rem;
}
.gl-badge--running {
    background: rgba(22, 163, 74, 0.15);
    color: var(--gl-success);
}
.gl-badge--closed {
    background: rgba(220, 38, 38, 0.15);
    color: var(--gl-danger);
}
.gl-badge--hot {
    background: rgba(249, 115, 22, 0.15);
    color: var(--gl-warning);
}
.gl-badge--normal {
    background: rgba(148, 163, 184, 0.25);
    color: #475569;
}
.gl-action-group .btn {
    border-radius: 999px;
}
.gl-empty-row {
    text-align: center;
    padding: 2.5rem 1rem;
    color: #94a3b8;
}
.gl-pagination {
    padding: 1rem 1.5rem;
    border-top: 1px solid var(--gl-card-border);
    background: #fff;
}
@media (max-width: 991.98px) {
    .gl-hero {
        padding: 1.5rem;
    }
    .gl-action-group {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>
@endsection

@section('content')
@php
    $pageItems = collect($game_lists->items());
    $totalGames = $game_lists->total();
    $runningGames = $pageItems->filter(function ($game) {
        $status = strtolower((string) $game->status);
        return in_array($status, ['1', 'running game', 'running', 'open'], true) || (int) $game->status === 1;
    })->count();
    $closedGames = $pageItems->count() - $runningGames;
    $hotGames = $pageItems->where('hot_status', 1)->count();
@endphp
<div class="game-list-page">
    <div class="container-fluid px-0 px-lg-3">
        <div class="gl-hero shadow-sm">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                <div class="mb-3 mb-lg-0">
                    <p class="text-uppercase small mb-2 opacity-75">Live catalog overview</p>
                    <h2 class="mb-2">GSC Plus Game Lists</h2>
                    <p class="mb-3 text-white-50">Track every product, flip statuses in one click, and keep hot titles on top of the list.</p>
                    <span class="badge bg-white text-dark text-uppercase">Last synced {{ now()->format('d M Y, h:i A') }}</span>
                </div>
                <div class="text-lg-right">
                    <p class="text-white-50 mb-1">Quick links</p>
                    <div class="d-flex flex-lg-column gap-2">
                        <a href="{{ route('home') }}" class="btn btn-light">
                            <i class="fas fa-home mr-2"></i> Dashboard
                        </a>
                        <a href="{{ url()->current() }}" class="btn btn-outline-light">
                            <i class="fas fa-sync mr-2"></i> Refresh page
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="gl-stat-card">
                    <p>Total games</p>
                    <h4>{{ number_format($totalGames) }}</h4>
                    <span class="text-muted">All catalogue entries</span>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="gl-stat-card">
                    <p>Running</p>
                    <h4 class="text-success">{{ number_format($runningGames) }}</h4>
                    <span class="text-muted">Active on this page</span>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="gl-stat-card">
                    <p>Hot titles</p>
                    <h4 class="text-warning">{{ number_format($hotGames) }}</h4>
                    <span class="text-muted">Highlighted picks</span>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="gl-stat-card">
                    <p>Closed</p>
                    <h4 class="text-danger">{{ number_format($closedGames) }}</h4>
                    <span class="text-muted">Hidden right now</span>
                </div>
            </div>
        </div>

            <div class="card gl-filter-card mb-4">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between mb-3">
                        <div>
                            <h5 class="mb-1">Find the exact game faster</h5>
                            <p class="text-muted mb-0">Search by name, provider, or type. Combine quick filters to focus on what matters.</p>
                        </div>
                        <div class="filter-pill-group btn-group mt-3 mt-lg-0" role="group">
                            <button type="button" class="btn btn-light active" data-status-filter="all">All</button>
                            <button type="button" class="btn btn-light" data-status-filter="running">Running</button>
                            <button type="button" class="btn btn-light" data-status-filter="closed">Closed</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 mb-3 mb-lg-0">
                            <label class="text-muted text-uppercase small mb-1" for="gameSearchInput">Search catalog</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" class="form-control border-left-0 shadow-sm" id="gameSearchInput" placeholder="Game name, product, or type...">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <label class="text-muted text-uppercase small mb-1">Hot filter</label>
                            <div class="filter-pill-group btn-group w-100" role="group">
                                <button type="button" class="btn btn-light active" data-hot-filter="all">All</button>
                                <button type="button" class="btn btn-light" data-hot-filter="hot">Hot only</button>
                                <button type="button" class="btn btn-light" data-hot-filter="normal">Normal</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card gl-table-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table gl-table align-middle mb-0" id="gameListTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Game</th>
                                    <th>Type</th>
                                    <th>Provider</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Hot label</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($game_lists as $index => $game_list)
                                    @php
                                        $statusValue = strtolower((string) $game_list->status);
                                        $isRunning = in_array($statusValue, ['1', 'running game', 'running', 'open'], true) || (int) $game_list->status === 1;
                                        $isHot = (int) $game_list->hot_status === 1;
                                        $rowNumber = ($game_lists->firstItem() ?? 0) + $index;
                                    @endphp
                                    <tr
                                        data-name="{{ strtolower($game_list->game_name ?? '') }}"
                                        data-type="{{ strtolower($game_list->game_type ?? '') }}"
                                        data-provider="{{ strtolower($game_list->provider ?? '') }}"
                                        data-status="{{ $isRunning ? 'running' : 'closed' }}"
                                        data-hot="{{ $isHot ? 'hot' : 'normal' }}"
                                    >
                                        <td class="text-muted">{{ $rowNumber }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ $game_list->image_url }}" alt="{{ $game_list->game_name }}" class="gl-thumb">
                                                <div>
                                                    <div class="font-weight-bold">{{ $game_list->game_name }}</div>
                                                    <small class="text-muted text-uppercase">Code #{{ $game_list->game_code ?? $game_list->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $game_list->game_type ?? '—' }}</td>
                                        <td>{{ $game_list->provider ?? '—' }}</td>
                                        <td class="text-center">
                                            <span class="gl-badge {{ $isRunning ? 'gl-badge--running' : 'gl-badge--closed' }}">
                                                <i class="fas {{ $isRunning ? 'fa-bolt' : 'fa-power-off' }}"></i>
                                                {{ $isRunning ? 'Running' : 'Closed' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="gl-badge {{ $isHot ? 'gl-badge--hot' : 'gl-badge--normal' }}">
                                                <i class="fas {{ $isHot ? 'fa-fire' : 'fa-thermometer-half' }}"></i>
                                                {{ $isHot ? 'Hot game' : 'Normal' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column flex-xl-row gap-2 gl-action-group justify-content-center">
                                                <form action="{{ route('admin.gameLists.toggleStatus', $game_list->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-sync-alt mr-1"></i>{{ $isRunning ? 'Close Game' : 'Activate' }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.HotGame.toggleStatus', $game_list->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-outline-warning btn-sm">
                                                        <i class="fas fa-fire mr-1"></i>{{ $isHot ? 'Unset Hot' : 'Mark Hot' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="gl-empty-row">
                                            <h6>No games available</h6>
                                            <p class="mb-0">Once games are added, they will appear in this list.</p>
                                        </td>
                                    </tr>
                                @endforelse
                                <tr id="gameEmptyState" class="gl-empty-row d-none">
                                    <td colspan="7">
                                        <h6>No games match your filters</h6>
                                        <p class="mb-0">Try clearing the search box or selecting a different filter.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="gl-pagination d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div class="text-muted">
                            Showing
                            <strong>{{ $game_lists->firstItem() ?? 0 }}</strong>
                            -
                            <strong>{{ $game_lists->lastItem() ?? 0 }}</strong>
                            of
                            <strong>{{ $game_lists->total() }}</strong>
                            games
                        </div>
                        <div>
                            {{ $game_lists->onEachSide(1)->withQueryString()->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('gameSearchInput');
    const statusButtons = document.querySelectorAll('[data-status-filter]');
    const hotButtons = document.querySelectorAll('[data-hot-filter]');
    const rows = document.querySelectorAll('#gameListTable tbody tr[data-name]');
    const emptyRow = document.getElementById('gameEmptyState');

    const applyFilters = function () {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const statusFilter = document.querySelector('[data-status-filter].active')?.getAttribute('data-status-filter') ?? 'all';
        const hotFilter = document.querySelector('[data-hot-filter].active')?.getAttribute('data-hot-filter') ?? 'all';
        let visible = 0;

        rows.forEach(function (row) {
            const name = row.getAttribute('data-name') ?? '';
            const type = row.getAttribute('data-type') ?? '';
            const provider = row.getAttribute('data-provider') ?? '';
            const status = row.getAttribute('data-status') ?? 'running';
            const hot = row.getAttribute('data-hot') ?? 'normal';

            const matchesQuery = !query || name.includes(query) || type.includes(query) || provider.includes(query);
            const matchesStatus = statusFilter === 'all' || status === statusFilter;
            const matchesHot = hotFilter === 'all' || hot === hotFilter;

            if (matchesQuery && matchesStatus && matchesHot) {
                row.style.display = '';
                visible += 1;
            } else {
                row.style.display = 'none';
            }
        });

        if (emptyRow) {
            emptyRow.classList.toggle('d-none', visible > 0);
        }
    };

    const wireToggleGroup = function (buttons) {
        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                buttons.forEach(function (btn) {
                    btn.classList.remove('active');
                });
                button.classList.add('active');
                applyFilters();
            });
        });
    };

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    wireToggleGroup(statusButtons);
    wireToggleGroup(hotButtons);

    applyFilters();
});
</script>
@endsection

