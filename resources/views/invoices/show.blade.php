@if ($layout === 'member')
    <x-member-layout :portal="$portal" :title="$title">
        @include('invoices.partials.document', ['document' => $document, 'backUrl' => route('member.transactions.show', $document['payment'])])
    </x-member-layout>
@else
    <x-owner-layout :portal="$portal" :navigation="$navigation" :title="$title">
        @include('invoices.partials.document', ['document' => $document, 'backUrl' => route('owner.reports.finance')])
    </x-owner-layout>
@endif
