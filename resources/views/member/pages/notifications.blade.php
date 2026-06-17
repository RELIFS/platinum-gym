<section class="member-card mt-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="member-eyebrow">Notifikasi</p>
            <h3 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Pemberitahuan member</h3>
        </div>
        @if (($portal['unreadNotificationsCount'] ?? 0) > 0)
            <form method="POST" action="{{ route('member.notifications.read-all') }}">
                @csrf
                <button type="submit" class="member-button-secondary">Tandai Semua Dibaca</button>
            </form>
        @endif
    </div>

    @include('member.partials.filter-toolbar', [
        'filters' => $portal['pageFilters'] ?? [],
        'showSearch' => false,
        'selects' => [
            [
                'name' => 'status',
                'label' => 'Filter status notifikasi',
                'placeholder' => 'Semua notifikasi',
                'options' => $portal['filterOptions']['notificationStatuses'] ?? [],
            ],
        ],
    ])

    @if ($notifications->count() > 0)
        <div class="mt-5 space-y-3">
            @foreach ($notifications as $notification)
                @php
                    $title = data_get($notification->data, 'title', class_basename($notification->type));
                    $notificationMeta = $notification->member_status_meta ?? [
                        'label' => is_null($notification->read_at) ? 'Baru' : 'Dibaca',
                        'class' => is_null($notification->read_at) ? 'member-status-warning' : 'member-status-neutral',
                    ];
                    $actionUrl = data_get($notification->data, 'action_url');
                    $actionLabel = (string) data_get($notification->data, 'action_label', 'Lihat');
                    $appBase = rtrim((string) url('/'), '/');
                    $isInternalAction = filled($actionUrl) && str_starts_with((string) $actionUrl, $appBase);
                @endphp
                <article class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-white/10 dark:bg-zinc-950/45">
                    <div class="flex items-start gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-gold-500/10 text-gold-600 dark:text-gold-400" aria-hidden="true">
                            @include('member.partials.icon', ['name' => 'bell', 'class' => 'h-5 w-5'])
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <p class="font-black text-zinc-950 dark:text-white">{{ $title }}</p>
                                <span class="member-status-pill {{ $notificationMeta['class'] }}">{{ $notificationMeta['label'] }}</span>
                            </div>
                            <p class="mt-1 text-sm font-medium text-zinc-600 dark:text-zinc-300">{{ data_get($notification->data, 'body', 'Notifikasi akun member.') }}</p>
                            <p class="mt-2 text-xs font-bold uppercase tracking-[0.14em] text-zinc-500 dark:text-zinc-400">{{ $notification->created_at?->translatedFormat('d M Y H:i') }}</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($isInternalAction)
                                    <a href="{{ $actionUrl }}" class="member-button-primary">{{ $actionLabel }}</a>
                                @endif
                                @if (is_null($notification->read_at))
                                    <form method="POST" action="{{ route('member.notifications.read', $notification) }}">
                                        @csrf
                                        <button type="submit" class="member-button-secondary">Tandai Dibaca</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        @include('member.partials.empty-state', [
            'icon' => 'bell',
            'title' => 'Belum ada notifikasi',
            'body' => 'Pemberitahuan membership, booking, dan pembayaran akan tampil di sini.',
            'class' => 'mt-5',
        ])
    @endif

    @include('member.partials.pagination', ['paginator' => $notifications, 'label' => 'notifikasi'])
</section>
