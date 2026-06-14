<section class="member-card mt-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="member-eyebrow">Notifikasi</p>
            <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Pemberitahuan member</h3>
        </div>
        @if ($notifications->whereNull('read_at')->isNotEmpty())
            <form method="POST" action="{{ route('member.notifications.read-all') }}">
                @csrf
                <button type="submit" class="member-button-secondary">Tandai Semua Dibaca</button>
            </form>
        @endif
    </div>
    <div class="mt-5 space-y-3">
        @forelse ($notifications as $notification)
            @php($title = data_get($notification->data, 'title', class_basename($notification->type)))
            <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                <div class="flex items-start gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-gold-500/10 text-gold-600 dark:text-gold-400" aria-hidden="true">
                        @include('member.partials.icon', ['name' => 'bell', 'class' => 'h-5 w-5'])
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <p class="font-black text-zinc-950 dark:text-white">{{ $title }}</p>
                            @if (is_null($notification->read_at))
                                <span class="member-status-pill member-status-warning">Baru</span>
                            @else
                                <span class="member-status-pill member-status-neutral">Dibaca</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ data_get($notification->data, 'body', 'Notifikasi akun member.') }}</p>
                        <p class="mt-2 text-xs font-bold uppercase tracking-[0.14em] text-zinc-400">{{ $notification->created_at?->translatedFormat('d M Y H:i') }}</p>
                        @if (is_null($notification->read_at))
                            <form method="POST" action="{{ route('member.notifications.read', $notification) }}" class="mt-3">
                                @csrf
                                <button type="submit" class="member-button-secondary">Tandai Dibaca</button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="member-soft-panel text-center">
                @include('member.partials.icon', ['name' => 'bell', 'class' => 'mx-auto h-10 w-10 text-zinc-400'])
                <p class="mt-3 font-black text-zinc-950 dark:text-white">Belum ada notifikasi</p>
                <p class="mt-1 member-copy">Pemberitahuan membership, booking, dan pembayaran akan tampil di sini.</p>
            </div>
        @endforelse
    </div>
</section>
