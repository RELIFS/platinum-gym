@php
    $isMobileFooter = $mobile ?? false;
    $identityLabel = $isMobileFooter ? 'Identitas owner mobile' : 'Identitas owner';
    $logoutContext = $isMobileFooter ? 'mobile' : 'desktop';
@endphp

<div class="{{ $isMobileFooter ? 'shrink-0 ' : '' }}border-t border-zinc-200 p-4 dark:border-white/10">
    <div class="mb-3 flex items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50/90 p-3 dark:border-white/10 dark:bg-white/[0.045]" aria-label="{{ $identityLabel }}">
        @include('owner.partials.avatar', ['owner' => $owner, 'class' => 'h-10 w-10 text-sm', 'ariaHidden' => true])
        <div class="min-w-0">
            <p class="truncate text-sm type-control text-zinc-950 dark:text-zinc-100">{{ $ownerName }}</p>
            <p class="mt-0.5 truncate text-[0.7rem] type-control uppercase tracking-[0.1em] text-gold-text">Owner</p>
        </div>
    </div>
    <form method="POST" action="{{ route('logout') }}" data-owner-sidebar-logout="{{ $logoutContext }}">
        @csrf
        <button type="submit" class="owner-button-primary w-full">Keluar</button>
    </form>
</div>
