@if ($paginator->hasPages())
    <nav class="mb-pagination-nav" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="mb-btn mb-btn-ghost" style="opacity:0.4;pointer-events:none">Previous</span>
        @else
            <button type="button" wire:click="previousPage" class="mb-btn mb-btn-ghost">Previous</button>
        @endif

        <span class="mb-pagination-info">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <button type="button" wire:click="nextPage" class="mb-btn mb-btn-ghost">Next</button>
        @else
            <span class="mb-btn mb-btn-ghost" style="opacity:0.4;pointer-events:none">Next</span>
        @endif
    </nav>
@endif
