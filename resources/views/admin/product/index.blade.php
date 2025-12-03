@extends('layouts.master')

@section('style')
<style>
:root {
    --gp-bg: #f4f7fb;
    --gp-card-border: #e5e9f2;
    --gp-success-bg: rgba(25, 135, 84, 0.15);
    --gp-danger-bg: rgba(220, 53, 69, 0.15);
}
.game-provider-page {
    background: var(--gp-bg);
    padding-bottom: 2rem;
}
.hero-card {
    background: linear-gradient(135deg, #0d6efd, #6610f2);
    color: #fff;
    border-radius: 20px;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}
.hero-card::after {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    background: radial-gradient(circle at top right, rgba(255,255,255,0.2), transparent 45%);
}
.hero-card h2 {
    font-weight: 600;
}
.stat-grid .stat-card {
    border-radius: 16px;
    border: 1px solid var(--gp-card-border);
    background: #fff;
    padding: 1.25rem;
    height: 100%;
    box-shadow: 0 15px 30px rgba(13, 110, 253, 0.08);
}
.stat-card h4 {
    font-size: 2rem;
    margin-bottom: 0.35rem;
    font-weight: 600;
}
.filter-card {
    border: none;
    border-radius: 18px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}
.filter-card .card-body {
    padding: 1.75rem;
}
.status-segment .btn {
    border-radius: 999px;
}
.status-segment .btn.active {
    background: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
}
.products-card {
    border-radius: 20px;
    border: 1px solid var(--gp-card-border);
    overflow: hidden;
}
.products-card .card-header {
    background: #fff;
    border-bottom: 1px solid var(--gp-card-border);
    padding: 1.5rem;
}
.products-table th {
    text-transform: uppercase;
    letter-spacing: 0.05rem;
    font-size: 0.75rem;
    color: #6c757d;
    border-top: none;
}
.product-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.product-thumb {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    object-fit: cover;
    border: 1px solid var(--gp-card-border);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
}
.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.85rem;
    gap: 0.35rem;
}
.status-pill--active {
    background: var(--gp-success-bg);
    color: #198754;
}
.status-pill--inactive {
    background: var(--gp-danger-bg);
    color: #dc3545;
}
.empty-row {
    padding: 2rem 1rem;
    color: #6c757d;
}
.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
}
@media (max-width: 767.98px) {
    .hero-card {
        padding: 1.5rem;
    }
    .product-info {
        flex-direction: column;
        text-align: center;
    }
}
</style>
@endsection

@section('content')
@php
    $allProducts = $gameTypes->pluck('products')->flatten();
    $totalProducts = $allProducts->count();
    $activeProducts = $allProducts->where('game_list_status', 1)->count();
    $inactiveProducts = $totalProducts - $activeProducts;
    $gameTypeCount = $gameTypes->count();
@endphp
<div class="game-provider-page">
    <div class="container-fluid px-0 px-md-3">
        <div class="hero-card mb-4 shadow-sm">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div class="mb-3 mb-md-0">
                    <p class="text-uppercase small mb-1">Catalog Overview</p>
                    <h2 class="mb-2">GSC Plus Game Providers</h2>
                    <p class="mb-3 text-white-50">Manage every provider, toggle availability, and keep the catalog fresh for players.</p>
                    <span class="badge bg-white text-dark text-uppercase">Last synced {{ now()->format('d M Y, h:i A') }}</span>
                </div>
                <div class="text-md-right">
                    <p class="text-white-50 mb-1">Quick Actions</p>
                    <div class="d-flex flex-md-column gap-2">
                        <a href="{{ route('home') }}" class="btn btn-light btn-icon">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        <a href="{{ route('admin.gametypes.index') }}" class="btn btn-outline-light btn-icon">
                            <i class="fas fa-sync"></i> Refresh List
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row stat-grid mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stat-card h-100">
                    <p class="text-muted text-uppercase small mb-1">Game Types</p>
                    <h4>{{ $gameTypeCount }}</h4>
                    <span class="text-muted">Distinct categories</span>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stat-card h-100">
                    <p class="text-muted text-uppercase small mb-1">Total Products</p>
                    <h4>{{ $totalProducts }}</h4>
                    <span class="text-muted">Across all providers</span>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stat-card h-100">
                    <p class="text-muted text-uppercase small mb-1">Active</p>
                    <h4 class="text-success">{{ $activeProducts }}</h4>
                    <span class="text-muted">Live for players</span>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="stat-card h-100">
                    <p class="text-muted text-uppercase small mb-1">Inactive</p>
                    <h4 class="text-danger">{{ $inactiveProducts }}</h4>
                    <span class="text-muted">Hidden from catalog</span>
                </div>
            </div>
        </div>

        <div class="card filter-card mb-4">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                    <div class="mb-3 mb-md-0">
                        <h5 class="mb-1">Find the product you need</h5>
                        <p class="text-muted mb-0">Search by name, code, or game type. Filter by status in a click.</p>
                    </div>
                    <div class="status-segment btn-group mt-2 mt-md-0" role="group">
                        <button type="button" class="btn btn-outline-secondary active" data-filter="all">All</button>
                        <button type="button" class="btn btn-outline-secondary" data-filter="active">Active</button>
                        <button type="button" class="btn btn-outline-secondary" data-filter="inactive">Inactive</button>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="text-muted small text-uppercase mb-1" for="productSearch">Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control border-left-0" id="productSearch" placeholder="Search product name, type, or code...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase mb-1">Shortcuts</label>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge bg-light text-dark">Faster status checks</span>
                            <span class="badge bg-light text-dark">One-click filtering</span>
                            <span class="badge bg-light text-dark">Inline actions</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card products-card shadow-sm">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between">
                <div>
                    <h5 class="mb-1">Product catalog</h5>
                    <p class="text-muted mb-0">Updated {{ now()->diffForHumans() }}. Use the filters above to narrow results.</p>
                </div>
                <span class="badge bg-primary-subtle text-primary mt-3 mt-md-0">{{ $totalProducts }} items</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 products-table" id="productsTable">
                    <thead>
                        <tr>
                            <th>Game Type</th>
                            <th>Product</th>
                            <th class="text-center">Code</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($gameTypes as $gameType)
                            @forelse ($gameType->products as $product)
                                @php
                                    $isActive = (int) $product->game_list_status === 1;
                                @endphp
                                <tr
                                    data-name="{{ strtolower($product->product_title) }}"
                                    data-code="{{ strtolower($product->product_code) }}"
                                    data-type="{{ strtolower($gameType->name) }}"
                                    data-status="{{ $isActive ? 'active' : 'inactive' }}"
                                >
                                    <td>
                                        <span class="badge bg-light text-dark px-3 py-2">{{ $gameType->name }}</span>
                                    </td>
                                    <td>
                                        <div class="product-info">
                                            <img src="{{ $product->getImgUrlAttribute() }}" alt="{{ $product->product_title }}" class="product-thumb">
                                            <div>
                                                <div class="font-weight-bold">{{ $product->product_title }}</div>
                                                <small class="text-muted">Provider ID #{{ $product->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center font-weight-bold">{{ $product->product_code }}</td>
                                    <td class="text-center">
                                        <span class="status-pill {{ $isActive ? 'status-pill--active' : 'status-pill--inactive' }}">
                                            <i class="fas {{ $isActive ? 'fa-check-circle' : 'fa-times-circle' }}"></i>
                                            <span class="status-label">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-outline-primary btn-sm btn-icon toggle-status-btn"
                                                data-product-id="{{ $product->id }}"
                                                data-status="{{ $product->status }}"
                                                title="Toggle status">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                            <a href="{{ route('admin.gametypes.edit', [$gameType->id, $product->id]) }}"
                                                class="btn btn-outline-secondary btn-sm btn-icon"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No products found for {{ $gameType->name }}.
                                    </td>
                                </tr>
                            @endforelse
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No game types available yet.
                                </td>
                            </tr>
                        @endforelse
                        <tr id="productEmptyState" class="empty-row {{ $totalProducts ? 'd-none' : '' }}">
                            <td colspan="5" class="text-center">
                                <h6 class="mb-1">No products match your filters</h6>
                                <p class="mb-0 text-muted">Try clearing the search or selecting a different status.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = '{{ csrf_token() }}';
    const searchInput = document.getElementById('productSearch');
    const statusButtons = document.querySelectorAll('.status-segment button');
    const rows = document.querySelectorAll('#productsTable tbody tr');
    const emptyStateRow = document.getElementById('productEmptyState');

    const applyFilters = function () {
        const query = searchInput.value.trim().toLowerCase();
        const activeStatusBtn = document.querySelector('.status-segment button.active');
        const statusFilter = activeStatusBtn ? activeStatusBtn.getAttribute('data-filter') : 'all';
        let visibleCount = 0;

        rows.forEach(function (row) {
            if (row.id === 'productEmptyState') {
                return;
            }

            const name = row.getAttribute('data-name') || '';
            const code = row.getAttribute('data-code') || '';
            const type = row.getAttribute('data-type') || '';
            const status = row.getAttribute('data-status') || '';

            const matchesQuery = !query || name.includes(query) || code.includes(query) || type.includes(query);
            const matchesStatus = statusFilter === 'all' || status === statusFilter;

            if (matchesQuery && matchesStatus) {
                row.style.display = '';
                visibleCount += 1;
            } else {
                row.style.display = 'none';
            }
        });

        if (emptyStateRow) {
            emptyStateRow.classList.toggle('d-none', visibleCount > 0);
        }
    };

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    statusButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            statusButtons.forEach(function (b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            applyFilters();
        });
    });

    document.querySelectorAll('.toggle-status-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const productId = this.getAttribute('data-product-id');
            const row = this.closest('tr');
            const statusPill = row ? row.querySelector('.status-pill') : null;
            const statusLabel = row ? row.querySelector('.status-label') : null;

            fetch('{{ route('admin.gametypes.toggle-status', ':productId') }}'.replace(':productId', productId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({}),
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (!data.success) {
                        alert(data.message || 'Unable to update status.');
                        return;
                    }

                    const newStatus = data.newStatus === 'ACTIVATED';
                    const statusText = newStatus ? 'Active' : 'Inactive';
                    const statusClass = newStatus ? 'status-pill--active' : 'status-pill--inactive';

                    button.setAttribute('data-status', data.newStatus);

                    if (row) {
                        row.setAttribute('data-status', newStatus ? 'active' : 'inactive');
                    }

                    if (statusPill && statusLabel) {
                        statusPill.classList.remove('status-pill--active', 'status-pill--inactive');
                        statusPill.classList.add(statusClass);
                        statusLabel.textContent = statusText;
                        const icon = statusPill.querySelector('i');
                        if (icon) {
                            icon.className = newStatus ? 'fas fa-check-circle' : 'fas fa-times-circle';
                        }
                    }

                    applyFilters();
                })
                .catch(function (error) {
                    console.error('Error:', error);
                    alert('An error occurred while updating the status.');
                });
        });
    });

    applyFilters();
});
</script>
@endsection
