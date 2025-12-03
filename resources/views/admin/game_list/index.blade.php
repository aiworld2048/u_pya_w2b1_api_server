@extends('layouts.master')

@section('styles')
<style>
:root {
    --gl-bg: #f5f6fb;
    --gl-card-border: #e5e9f2;
    --gl-accent: #4f46e5;
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
    pointer-events: none;
}
.gl-hero h2 {
    font-weight: 600;
}
.gl-hero .hero-meta {
    letter-spacing: 0.08rem;
    text-transform: uppercase;
    opacity: 0.75;
}
.gl-stat-card {
    border-radius: 18px;
    border: 1px solid var(--gl-card-border);
    background: #fff;
    padding: 1.25rem 1.4rem;
    box-shadow: 0 20px 35px rgba(79, 70, 229, 0.08);
    height: 100%;
}
.gl-stat-card h4 {
    font-size: 2rem;
    margin-bottom: 0.35rem;
    font-weight: 600;
}
.gl-stat-card small {
    text-transform: uppercase;
    letter-spacing: 0.08rem;
    color: #6b7280;
    font-weight: 600;
}
.gl-filter-card {
    border-radius: 20px;
    border: none;
    box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
    margin-bottom: 1.5rem;
}
.gl-filter-card .card-body {
    padding: 1.75rem;
}
.filter-pill-group .btn {
    border-radius: 999px;
    border: 1px solid var(--gl-card-border);
    color: #4b5563;
}
.filter-pill-group .btn.active {
    background: var(--gl-accent);
    border-color: var(--gl-accent);
    color: #fff;
    box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
}
.gl-table-card {
    border-radius: 22px;
    border: 1px solid var(--gl-card-border);
    overflow: hidden;
}
.gl-table-card .card-header {
    border-bottom: 1px solid var(--gl-card-border);
    background: #fff;
    padding: 1.5rem;
}
.gl-table {
    margin-bottom: 0;
}
.gl-table thead th {
    text-transform: uppercase;
    letter-spacing: 0.07rem;
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
.gl-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.9rem;
    border-radius: 999px;
    font-weight: 600;
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
.gl-thumb {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    object-fit: cover;
    border: 1px solid var(--gl-card-border);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
}
.gl-product-meta {
    font-size: 0.85rem;
    color: #94a3b8;
}
.gl-action-group .btn {
    border-radius: 999px;
    min-width: 110px;
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
    $isPaginator = $games instanceof \Illuminate\Pagination\AbstractPaginator;
    $gameItems = $isPaginator ? collect($games->items()) : collect($games);
    $totalGames = $isPaginator ? $games->total() : $gameItems->count();
    $runningGames = $gameItems->where('status', 1)->count();
    $closedGames = $gameItems->where('status', '!=', 1)->count();
    $hotGames = $gameItems->where('hot_status', 1)->count();
    $normalGames = $gameItems->where('hot_status', '!=', 1)->count();
@endphp
<div class="game-list-page">
    <div class="container-fluid px-0 px-lg-3">
        <div class="gl-hero shadow-sm">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                <div class="mb-3 mb-lg-0">
                    <p class="hero-meta mb-2">Live Catalog Overview</p>
                    <h2 class="mb-2">Game Lists Dashboard</h2>
                    <p class="mb-3 text-white-50">Monitor every product running in your platform, toggle statuses instantly, and surface trending titles faster.</p>
                    <span class="badge bg-white text-dark text-uppercase">Last synced {{ now()->format('d M Y, h:i A') }}</span>
                </div>
                <div class="text-lg-right">
                    <p class="text-white-50 mb-1">Quick links</p>
                    <div class="d-flex flex-lg-column gap-2">
                        <a href="{{ route('home') }}" class="btn btn-light">
                            <i class="fas fa-home mr-2"></i>Admin Dashboard
                        </a>
                        <a href="{{ url()->current() }}" class="btn btn-outline-light">
                            <i class="fas fa-sync mr-2"></i>Refresh View
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="gl-stat-card">
                    <small>Total games</small>
                    <h4>{{ number_format($totalGames) }}</h4>
                    <span class="text-muted">Across catalog</span>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="gl-stat-card">
                    <small>Running</small>
                    <h4 class="text-success">{{ number_format($runningGames) }}</h4>
                    <span class="text-muted">Active right now</span>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="gl-stat-card">
                    <small>Hot titles</small>
                    <h4 class="text-warning">{{ number_format($hotGames) }}</h4>
                    <span class="text-muted">Marked as trending</span>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="gl-stat-card">
                    <small>Closed</small>
                    <h4 class="text-danger">{{ number_format($closedGames) }}</h4>
                    <span class="text-muted">Currently disabled</span>
                </div>
            </div>
        </div>

        @can('admin_access')
        <div class="card gl-filter-card">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                    <div>
                        <h5 class="mb-1">Find specific games faster</h5>
                        <p class="mb-0 text-muted">Search by name, product, or type. Combine with quick filters for instant visibility.</p>
                    </div>
                    <div class="filter-pill-group btn-group mt-3 mt-lg-0" role="group">
                        <button class="btn btn-light active" data-status-filter="all">All</button>
                        <button class="btn btn-light" data-status-filter="running">Running</button>
                        <button class="btn btn-light" data-status-filter="closed">Closed</button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6 mb-3 mb-lg-0">
                        <label for="gameSearch" class="text-muted text-uppercase small mb-1">Search games</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control border-left-0 shadow-sm" id="gameSearch" placeholder="Game name, product, or type...">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <label class="text-muted text-uppercase small mb-1">Hot status</label>
                        <div class="filter-pill-group btn-group w-100" role="group">
                            <button class="btn btn-light active" data-hot-filter="all">All</button>
                            <button class="btn btn-light" data-hot-filter="hot">Hot</button>
                            <button class="btn btn-light" data-hot-filter="normal">Normal</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card gl-table-card shadow-sm">
            <div class="card-header d-flex flex-column flex-lg-row align-items-lg-center justify-content-between">
                <div>
                    <h5 class="mb-1">Running catalog</h5>
                    <p class="text-muted mb-0">Review performance and manage statuses without leaving the page.</p>
                </div>
                <span class="badge bg-light text-dark mt-3 mt-lg-0">{{ $totalGames }} total entries</span>
            </div>
            <div class="table-responsive">
                <table class="table gl-table align-middle" id="gamesTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Game</th>
                            <th>Game type</th>
                            <th>Product</th>
                            <th class="text-center">Running status</th>
                            <th class="text-center">Hot label</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($games as $index => $game)
                            @php
                                $isRunning = (int) $game->status === 1;
                                $isHot = (int) $game->hot_status === 1;
                                $rowNumber = ($games instanceof \Illuminate\Pagination\AbstractPaginator)
                                    ? ($games->firstItem() ?? 0) + $index
                                    : $index + 1;
                            @endphp
                            <tr
                                data-name="{{ strtolower($game->name) }}"
                                data-product="{{ strtolower($game->product->name ?? '') }}"
                                data-type="{{ strtolower($game->gameType->name ?? '') }}"
                                data-status="{{ $isRunning ? 'running' : 'closed' }}"
                                data-hot="{{ $isHot ? 'hot' : 'normal' }}"
                            >
                                <td class="text-muted">{{ $rowNumber }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $game->image_url }}" alt="{{ $game->name }}" class="gl-thumb">
                                        <div>
                                            <span class="font-weight-bold">{{ $game->name }}</span>
                                            <div class="gl-product-meta">ID #{{ $game->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $game->gameType->name ?? 'N/A' }}</td>
                                <td>{{ $game->product->name ?? 'N/A' }}</td>
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
                                    <div class="d-flex flex-column flex-lg-row justify-content-center gap-2 gl-action-group">
                                        <form action="{{ route('admin.gameLists.toggleStatus', $game->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-sync-alt mr-1"></i> {{ $isRunning ? 'Close Game' : 'Activate' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.HotGame.toggleStatus', $game->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-warning btn-sm">
                                                <i class="fas fa-fire-alt mr-1"></i> {{ $isHot ? 'Unset Hot' : 'Mark Hot' }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="gl-empty-row">
                                    <h6>No games available</h6>
                                    <p class="mb-0">Once games are added they will appear in this dashboard.</p>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="gamesEmptyState" class="gl-empty-row d-none">
                            <td colspan="7">
                                <h6>No games match your filters</h6>
                                <p class="mb-0">Try clearing the search box or using a different status.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @if ($isPaginator)
                <div class="gl-pagination d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div class="text-muted">
                        Showing
                        <strong>{{ $games->firstItem() ?? 0 }}</strong>
                        -
                        <strong>{{ $games->lastItem() ?? 0 }}</strong>
                        of
                        <strong>{{ $games->total() }}</strong>
                        games
                    </div>
                    <div class="mb-0">
                        {{ $games->onEachSide(1)->withQueryString()->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
        @else
            <div class="alert alert-warning">You do not have permission to view this page.</div>
        @endcan
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('gameSearch');
    const statusButtons = document.querySelectorAll('[data-status-filter]');
    const hotButtons = document.querySelectorAll('[data-hot-filter]');
    const rows = document.querySelectorAll('#gamesTable tbody tr');
    const emptyState = document.getElementById('gamesEmptyState');

    const applyFilters = function () {
        const query = searchInput ? searchInput.value.trim().toLowerCase() : '';
        const statusFilter = document.querySelector('[data-status-filter].active')?.getAttribute('data-status-filter') ?? 'all';
        const hotFilter = document.querySelector('[data-hot-filter].active')?.getAttribute('data-hot-filter') ?? 'all';
        let visible = 0;

        rows.forEach(function (row) {
            if (row.id === 'gamesEmptyState') {
                return;
            }

            const name = row.getAttribute('data-name') ?? '';
            const product = row.getAttribute('data-product') ?? '';
            const type = row.getAttribute('data-type') ?? '';
            const status = row.getAttribute('data-status') ?? 'running';
            const hot = row.getAttribute('data-hot') ?? 'normal';

            const matchesQuery = !query || name.includes(query) || product.includes(query) || type.includes(query);
            const matchesStatus = statusFilter === 'all' || status === statusFilter;
            const matchesHot = hotFilter === 'all' || hot === hotFilter;

            if (matchesQuery && matchesStatus && matchesHot) {
                row.style.display = '';
                visible += 1;
            } else {
                row.style.display = 'none';
            }
        });

        if (emptyState) {
            emptyState.classList.toggle('d-none', visible > 0);
        }
    };

    const bindToggle = function (buttons, attr) {
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                buttons.forEach(function (b) {
                    b.classList.remove('active');
                });
                btn.classList.add('active');
                applyFilters();
            });
        });
    };

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    bindToggle(statusButtons, 'data-status-filter');
    bindToggle(hotButtons, 'data-hot-filter');

    applyFilters();
});
</script>
@endsection
