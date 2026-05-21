<?php

declare(strict_types=1);

namespace LaravelMailbox\Http\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use LaravelMailbox\Contracts\MailboxContract;
use LaravelMailbox\Data\EmailFilterData;
use LaravelMailbox\Models\MailboxEmail;
use LaravelMailbox\Services\EmailQueryService;

#[Layout('mailbox::layouts.app')]
final class Inbox extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $recipient = '';

    #[Url]
    public string $subject = '';

    public ?int $selectedId = null;

    public string $previewTab = 'html';

    public function mount(): void
    {
        $this->selectedId ??= $this->firstEmailId();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selectedId = $this->firstEmailId();
    }

    public function updatedRecipient(): void
    {
        $this->resetPage();
        $this->selectedId = $this->firstEmailId();
    }

    public function updatedSubject(): void
    {
        $this->resetPage();
        $this->selectedId = $this->firstEmailId();
    }

    public function selectEmail(int $id): void
    {
        $this->selectedId = $id;

        $email = MailboxEmail::query()->find($id);
        $this->previewTab = $email?->defaultPreviewTab() ?? 'text';
    }

    public function setPreviewTab(string $tab): void
    {
        $this->previewTab = $tab;
    }

    public function clearInbox(MailboxContract $mailbox): void
    {
        $mailbox->flush();
        $this->selectedId = null;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->recipient = '';
        $this->subject = '';
        $this->resetPage();
        $this->selectedId = $this->firstEmailId();
    }

    public function hasActiveFilters(): bool
    {
        return $this->search !== '' || $this->recipient !== '' || $this->subject !== '';
    }

    public function advancedFilterCount(): int
    {
        return ($this->recipient !== '' ? 1 : 0) + ($this->subject !== '' ? 1 : 0);
    }

    public function deleteSelected(EmailQueryService $queryService): void
    {
        if ($this->selectedId === null) {
            return;
        }

        $email = $queryService->find((string) $this->selectedId);

        if ($email !== null) {
            $email->delete();
        }

        $this->selectedId = $this->firstEmailId();
    }

    public function render(EmailQueryService $queryService): View
    {
        $emails = $queryService->paginate(
            EmailFilterData::fromArray([
                'search' => $this->search ?: null,
                'recipient' => $this->recipient ?: null,
                'subject' => $this->subject ?: null,
            ]),
            (int) config('mailbox.ui.per_page', 25),
        );

        return view('mailbox::livewire.inbox', [
            'emails' => $emails,
            'selected' => $this->resolveSelected($emails),
            'title' => config('mailbox.ui.title', 'Mailbox'),
            'pollInterval' => (int) config('mailbox.ui.poll_interval_ms', 3000),
            'hasActiveFilters' => $this->hasActiveFilters(),
            'advancedFilterCount' => $this->advancedFilterCount(),
        ]);
    }

    private function resolveSelected(LengthAwarePaginator $emails): ?MailboxEmail
    {
        if ($this->selectedId === null) {
            return null;
        }

        $selected = $emails->firstWhere('id', $this->selectedId);

        if ($selected instanceof MailboxEmail) {
            return $selected;
        }

        return MailboxEmail::query()->find($this->selectedId);
    }

    private function firstEmailId(): ?int
    {
        $paginator = app(EmailQueryService::class)->paginate(
            EmailFilterData::fromArray([
                'search' => $this->search ?: null,
                'recipient' => $this->recipient ?: null,
                'subject' => $this->subject ?: null,
            ]),
            1,
        );

        $first = $paginator->first();

        return $first instanceof MailboxEmail ? $first->id : null;
    }
}
