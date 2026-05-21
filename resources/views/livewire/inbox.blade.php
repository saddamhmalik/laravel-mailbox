<div
    class="mb-app"
    wire:poll.{{ $pollInterval }}ms
    x-data="{
        filtersOpen: @js($advancedFilterCount > 0),
        handleKey(e) {
            if (e.key === '/' && e.target.tagName !== 'INPUT') {
                e.preventDefault();
                this.filtersOpen = false;
                this.$refs.search?.focus();
            }
            if (e.key === 'f' && e.target.tagName !== 'INPUT' && !e.metaKey && !e.ctrlKey) {
                e.preventDefault();
                this.filtersOpen = !this.filtersOpen;
            }
            if (e.key === 'j' || e.key === 'k') {
                const items = @js($emails->pluck('id')->values());
                const current = @js($selected?->id);
                const idx = items.indexOf(current);
                if (idx === -1) return;
                const next = e.key === 'j' ? items[idx + 1] : items[idx - 1];
                if (next) $wire.selectEmail(next);
            }
        }
    }"
    @keydown.window="handleKey"
>
    <div wire:loading.flex class="mb-loading">
        <span class="mb-loading-dot"></span>
        <span class="mb-loading-dot"></span>
        <span class="mb-loading-dot"></span>
    </div>

    <aside class="mb-sidebar">
        <header class="mb-sidebar-header">
            <div class="mb-brand">
                <div class="mb-brand-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="mb-title">{{ $title }}</h1>
                    <p class="mb-subtitle">
                        <span class="mb-badge">{{ $emails->total() }}</span>
                        captured
                    </p>
                </div>
            </div>
            <button
                type="button"
                wire:click="clearInbox"
                wire:confirm="Clear all captured emails?"
                class="mb-btn mb-btn-ghost"
                title="Clear inbox"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Clear
            </button>
        </header>

        <div class="mb-filters">
            <div class="mb-filter-bar">
                <div class="mb-search-wrap">
                    <svg class="mb-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                    <input
                        x-ref="search"
                        wire:model.live.debounce.300ms="search"
                        type="search"
                        placeholder="Search…"
                        class="mb-input mb-input-search"
                        aria-label="Search emails"
                    />
                </div>
                <button
                    type="button"
                    class="mb-filter-toggle"
                    @click="filtersOpen = !filtersOpen"
                    :aria-expanded="filtersOpen"
                    title="Toggle filters (f)"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="15" height="15" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 2v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span class="mb-filter-toggle-label">Filters</span>
                    @if ($advancedFilterCount > 0)
                        <span class="mb-badge">{{ $advancedFilterCount }}</span>
                    @endif
                    <svg class="mb-filter-chevron" :class="{ 'is-open': filtersOpen }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            @if ($hasActiveFilters)
                <div class="mb-filter-pills" x-show="!filtersOpen" x-cloak>
                    @if ($search !== '')
                        <span class="mb-filter-pill">“{{ \Illuminate\Support\Str::limit($search, 20) }}”</span>
                    @endif
                    @if ($recipient !== '')
                        <span class="mb-filter-pill">→ {{ \Illuminate\Support\Str::limit($recipient, 18) }}</span>
                    @endif
                    @if ($subject !== '')
                        <span class="mb-filter-pill">§ {{ \Illuminate\Support\Str::limit($subject, 18) }}</span>
                    @endif
                    <button type="button" wire:click="clearFilters" class="mb-filter-pill-clear">Clear</button>
                </div>
            @endif

            <div
                class="mb-filter-panel"
                x-show="filtersOpen"
                x-transition:enter="mb-panel-enter"
                x-transition:enter-start="mb-panel-enter-start"
                x-transition:enter-end="mb-panel-enter-end"
                x-transition:leave="mb-panel-leave"
                x-transition:leave-start="mb-panel-leave-start"
                x-transition:leave-end="mb-panel-leave-end"
                x-cloak
                style="display: none;"
            >
                <div class="mb-filter-panel-inner">
                    <label class="mb-filter-field">
                        <span class="mb-filter-label">Recipient</span>
                        <input
                            wire:model.live.debounce.300ms="recipient"
                            type="text"
                            placeholder="email@example.com"
                            class="mb-input"
                        />
                    </label>
                    <label class="mb-filter-field">
                        <span class="mb-filter-label">Subject</span>
                        <input
                            wire:model.live.debounce.300ms="subject"
                            type="text"
                            placeholder="Contains…"
                            class="mb-input"
                        />
                    </label>
                    @if ($hasActiveFilters)
                        <button type="button" wire:click="clearFilters" class="mb-btn mb-btn-ghost mb-filter-clear-all">
                            Clear all filters
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-list">
            @forelse ($emails as $email)
                @php
                    $from = $email->from[0]['address'] ?? '?';
                    $initial = strtoupper(substr($from, 0, 1));
                @endphp
                <button
                    type="button"
                    wire:click="selectEmail({{ $email->id }})"
                    wire:key="email-{{ $email->id }}"
                    class="mb-item {{ $selected?->id === $email->id ? 'active' : '' }}"
                >
                    <span class="mb-avatar" aria-hidden="true">{{ $initial }}</span>
                    <span class="mb-item-body">
                        <span class="mb-item-row">
                            <span class="mb-item-subject">{{ $email->subject ?: '(no subject)' }}</span>
                            <span class="mb-item-time">{{ $email->created_at?->diffForHumans(short: true) }}</span>
                        </span>
                        <span class="mb-item-to">
                            {{ \LaravelMailbox\Support\AddressNormalizer::formatAddressList($email->to) ?: 'No recipients' }}
                        </span>
                    </span>
                    @if (! empty($email->attachments))
                        <span class="mb-item-attach" title="Has attachments">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        </span>
                    @endif
                </button>
            @empty
                <div class="mb-empty">
                    <div class="mb-empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="mb-empty-title">Inbox is empty</p>
                    <p class="mb-empty-desc">Outgoing mail will appear here when you use the mailbox mailer.</p>
                    <div class="mb-code-block">
                        <span class="mb-code-label">.env</span>
                        <code>MAIL_MAILER=mailbox</code>
                    </div>
                </div>
            @endforelse
        </div>

        @if ($emails->hasPages())
            <div class="mb-pagination">
                {{ $emails->links('mailbox::components.pagination') }}
            </div>
        @endif

        <footer class="mb-shortcuts">
            <span><kbd>j</kbd><kbd>k</kbd> navigate</span>
            <span><kbd>/</kbd> search</span>
            <span><kbd>f</kbd> filters</span>
        </footer>
    </aside>

    <main class="mb-main">
        @if ($selected)
            <header class="mb-preview-header">
                <div class="mb-preview-head">
                    <h2 class="mb-preview-title">{{ $selected->subject ?: '(no subject)' }}</h2>
                    <div class="mb-meta-row">
                        <span class="mb-meta-chip">
                            <span class="mb-meta-label">From</span>
                            {{ \LaravelMailbox\Support\AddressNormalizer::formatAddressList($selected->from) ?: '—' }}
                        </span>
                        <span class="mb-meta-chip">
                            <span class="mb-meta-label">To</span>
                            {{ \LaravelMailbox\Support\AddressNormalizer::formatAddressList($selected->to) ?: '—' }}
                        </span>
                        @if (! empty($selected->cc))
                            <span class="mb-meta-chip">
                                <span class="mb-meta-label">Cc</span>
                                {{ \LaravelMailbox\Support\AddressNormalizer::formatAddressList($selected->cc) }}
                            </span>
                        @endif
                        <span class="mb-meta-chip mb-meta-muted">
                            {{ $selected->created_at?->format('M j, Y · g:i A') }}
                        </span>
                    </div>
                </div>
                <div class="mb-preview-actions">
                    @if ($selected->mailer)
                        <span class="mb-tag">{{ $selected->mailer }}</span>
                    @endif
                    <button
                        type="button"
                        wire:click="deleteSelected"
                        wire:confirm="Delete this email?"
                        class="mb-btn mb-btn-danger"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </div>
            </header>

            <nav class="mb-tabs" role="tablist">
                <button type="button" role="tab" wire:click="setPreviewTab('html')" class="mb-tab {{ $previewTab === 'html' ? 'active' : '' }}" aria-selected="{{ $previewTab === 'html' ? 'true' : 'false' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Preview
                </button>
                <button type="button" role="tab" wire:click="setPreviewTab('text')" class="mb-tab {{ $previewTab === 'text' ? 'active' : '' }}" aria-selected="{{ $previewTab === 'text' ? 'true' : 'false' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="15" height="15"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    Plain
                </button>
                <button type="button" role="tab" wire:click="setPreviewTab('raw')" class="mb-tab {{ $previewTab === 'raw' ? 'active' : '' }}" aria-selected="{{ $previewTab === 'raw' ? 'true' : 'false' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                    Source
                </button>
                <button type="button" role="tab" wire:click="setPreviewTab('headers')" class="mb-tab {{ $previewTab === 'headers' ? 'active' : '' }}" aria-selected="{{ $previewTab === 'headers' ? 'true' : 'false' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="15" height="15"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Headers
                </button>
            </nav>

            <div class="mb-content-type-bar">
                <span class="mb-tag">{{ $selected->contentType() }}</span>
                <span class="mb-content-type-hint">{{ $selected->tabHint($previewTab) }}</span>
            </div>

            <div class="mb-content">
                @if ($previewTab === 'html')
                    <div class="mb-frame-wrap" wire:ignore.self>
                        <iframe
                            wire:key="preview-frame-{{ $selected->id }}"
                            sandbox="allow-same-origin"
                            srcdoc="{!! e($selected->previewHtmlDocument()) !!}"
                            class="mb-frame"
                            title="Email preview"
                        ></iframe>
                    </div>
                @elseif ($previewTab === 'text')
                    <div class="mb-plain-panel">
                        <pre class="mb-pre">{{ $selected->plainTextContent() ?: 'No plain text body.' }}</pre>
                    </div>
                @elseif ($previewTab === 'raw')
                    <pre class="mb-raw">{{ $selected->raw_source ?: 'No raw source available.' }}</pre>
                @else
                    <div class="mb-headers">
                        @forelse ($selected->displayHeaders() as $name => $value)
                            <div class="mb-header-row">
                                <span class="mb-header-name">{{ $name }}</span>
                                <span class="mb-header-value">{{ $value }}</span>
                            </div>
                        @empty
                            <p class="mb-empty-desc mb-headers-empty">No headers recorded.</p>
                        @endforelse
                    </div>
                @endif

                @if (! empty($selected->attachments))
                    <section class="mb-attachments">
                        <h3 class="mb-section-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            Attachments
                            <span class="mb-badge">{{ count($selected->attachments) }}</span>
                        </h3>
                        <div class="mb-attachment-grid">
                            @foreach ($selected->attachments as $attachment)
                                <div class="mb-attachment">
                                    <span class="mb-attachment-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    </span>
                                    <span class="mb-attachment-info">
                                        <span class="mb-attachment-name">{{ $attachment['name'] ?? 'attachment' }}</span>
                                        <span class="mb-attachment-meta">{{ $attachment['content_type'] ?? 'file' }} · {{ number_format(($attachment['size'] ?? 0) / 1024, 1) }} KB</span>
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        @else
            <div class="mb-placeholder">
                <div class="mb-placeholder-inner">
                    <div class="mb-empty-icon mb-empty-icon-lg" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <p class="mb-empty-title">Select an email</p>
                    <p class="mb-empty-desc">Choose a message from the inbox or press <kbd>j</kbd> and <kbd>k</kbd> to move through the list.</p>
                </div>
            </div>
        @endif
    </main>
</div>
