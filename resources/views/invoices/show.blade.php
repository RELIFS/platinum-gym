@if ($layout === 'member')
    <x-member-layout :portal="$portal" :title="$title">
        @include('invoices.partials.document', ['document' => $document, 'backUrl' => route('member.transactions.show', $document['payment'])])
    </x-member-layout>
@elseif ($layout === 'admin')
    <x-admin-layout :portal="$portal" :navigation="$navigation" :title="$title">
        @include('invoices.partials.document', ['document' => $document, 'backUrl' => route('admin.payments')])
    </x-admin-layout>
@else
    <x-owner-layout :portal="$portal" :navigation="$navigation" :title="$title">
        @include('invoices.partials.document', ['document' => $document, 'backUrl' => route('owner.reports.finance')])
    </x-owner-layout>
@endif
