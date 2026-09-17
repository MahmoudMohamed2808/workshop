<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="{{ __('home.title') }}" />

    <title>{{ __('home.brand') }} — {{ __('home.title') }}</title>

    <link rel="icon" href="/favicon.ico" sizes="any" />
    <link rel="icon" href="/favicon.svg" type="image/svg+xml" />
    <link rel="apple-touch-icon" href="/apple-touch-icon.png" />

    {{-- Local build only: no webfonts, no CDN, works fully offline. --}}
    @vite(['resources/css/app.css'])

    <script>
        try {
            if (localStorage.getItem('workshop-theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        } catch (e) {}
    </script>
</head>
<body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">

    {{-- Header --}}
    <header class="sticky top-0 z-10 border-b border-zinc-200/70 bg-zinc-50/90 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/90">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <span class="flex size-9 items-center justify-center rounded-xl bg-amber-500 text-white shadow-sm">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                    </svg>
                </span>
                <span class="leading-tight">
                    <span class="block text-base font-bold">{{ __('home.brand') }}</span>
                    <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ __('home.brand_suffix') }}</span>
                </span>
            </a>

            <nav class="hidden items-center gap-6 text-sm font-medium text-zinc-600 md:flex dark:text-zinc-300">
                <a href="#features" class="transition hover:text-amber-600 dark:hover:text-amber-400">{{ __('home.nav.features') }}</a>
                <a href="#workflow" class="transition hover:text-amber-600 dark:hover:text-amber-400">{{ __('home.nav.workflow') }}</a>
            </nav>

            <div class="flex items-center gap-2">
                <button type="button" onclick="toggleTheme()" aria-label="theme"
                    class="flex size-9 items-center justify-center rounded-lg border border-zinc-200 text-zinc-500 transition hover:border-amber-400 hover:text-amber-600 dark:border-zinc-700 dark:text-zinc-400 dark:hover:border-amber-500 dark:hover:text-amber-400">
                    <svg class="size-4.5 hidden dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="4" /><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4" />
                    </svg>
                    <svg class="size-4.5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                    </svg>
                </button>

                @auth
                    @if (Route::has('filament.admin.pages.dashboard'))
                        <a href="{{ route('filament.admin.pages.dashboard') }}"
                            class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600">
                            {{ __('home.nav.dashboard') }}
                        </a>

                        <form class="hidden rounded-lg px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:text-amber-600 sm:block dark:text-zinc-200 dark:hover:text-amber-400"  action="{{ route('logout') }}" method="post">
                            @csrf
                            <button type="submit">{{__('home.nav.logout')  }}</button>
                        </form>
                    @endif
                @else
                    @if (Route::has('filament.admin.auth.login'))
                        <a href="{{ route('filament.admin.auth.login') }}"
                            class="hidden rounded-lg px-4 py-2 text-sm font-semibold text-zinc-700 transition hover:text-amber-600 sm:block dark:text-zinc-200 dark:hover:text-amber-400">
                            {{ __('home.nav.login') }}
                        </a>
                    @endif
                    @if (Route::has('filament.admin.auth.register'))
                        <a href="{{ route('filament.admin.auth.register') }}"
                            class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600">
                            {{ __('home.nav.register') }}
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    <main>
        {{-- Hero --}}
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -top-24 start-1/4 size-72 rounded-full bg-amber-400/20 blur-3xl dark:bg-amber-500/10"></div>
                <div class="absolute -bottom-32 end-1/4 size-72 rounded-full bg-amber-500/10 blur-3xl dark:bg-amber-400/5"></div>
            </div>

            <div class="relative mx-auto max-w-6xl px-4 py-16 text-center sm:px-6 sm:py-24">
                <p class="mx-auto inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-4 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-300">
                    <span class="size-1.5 rounded-full bg-amber-500"></span>
                    {{ __('home.hero.badge') }}
                </p>

                <h1 class="mx-auto mt-6 max-w-3xl text-3xl font-extrabold leading-tight sm:text-5xl sm:leading-tight">
                    {{ __('home.hero.heading') }}
                    <span class="mt-2 block text-amber-600 dark:text-amber-400">{{ __('home.hero.highlight') }}</span>
                </h1>

                <p class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-zinc-600 sm:text-lg dark:text-zinc-300">
                    {{ __('home.hero.description') }}
                </p>

                <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    @auth
                        @if (Route::has('filament.admin.pages.dashboard'))
                            <a href="{{ route('filament.admin.pages.dashboard') }}"
                                class="w-full rounded-xl bg-amber-500 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/25 transition hover:bg-amber-600 sm:w-auto">
                                {{ __('home.hero.dashboard') }}
                            </a>
                        @endif
                    @else
                        @if (Route::has('filament.admin.auth.login'))
                            <a href="{{ route('filament.admin.auth.login') }}"
                                class="w-full rounded-xl bg-amber-500 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/25 transition hover:bg-amber-600 sm:w-auto">
                                {{ __('home.hero.login') }}
                            </a>
                        @endif
                        @if (Route::has('filament.admin.auth.register'))
                            <a href="{{ route('filament.admin.auth.register') }}"
                                class="w-full rounded-xl border border-zinc-300 bg-white px-8 py-3 text-sm font-bold text-zinc-800 transition hover:border-amber-400 hover:text-amber-700 sm:w-auto dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-100 dark:hover:border-amber-500 dark:hover:text-amber-300">
                                {{ __('home.hero.register') }}
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </section>

        {{-- Stats --}}
        <section class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-sm font-bold">{{ __('home.stats.orders') }}</p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('home.stats.orders_hint') }}</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-sm font-bold">{{ __('home.stats.stock') }}</p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('home.stats.stock_hint') }}</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-sm font-bold">{{ __('home.stats.billing') }}</p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('home.stats.billing_hint') }}</p>
                </div>
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <p class="text-sm font-bold">{{ __('home.stats.warranty') }}</p>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('home.stats.warranty_hint') }}</p>
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section id="features" class="mx-auto max-w-6xl scroll-mt-20 px-4 py-16 sm:px-6">
            <div class="text-center">
                <h2 class="text-2xl font-extrabold sm:text-3xl">{{ __('home.features.heading') }}</h2>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-zinc-600 sm:text-base dark:text-zinc-300">
                    {{ __('home.features.description') }}
                </p>
            </div>

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 transition hover:border-amber-400 hover:shadow-lg hover:shadow-amber-500/10 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-500">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <svg class="size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="8" height="14" x="3" y="7" rx="1" /><path d="M7 7V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2M11 21v-6h4v6" /></svg>
                    </span>
                    <h3 class="mt-4 text-base font-bold">{{ __('home.features.orders_title') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __('home.features.orders_body') }}</p>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-6 transition hover:border-amber-400 hover:shadow-lg hover:shadow-amber-500/10 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-500">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <svg class="size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                    </span>
                    <h3 class="mt-4 text-base font-bold">{{ __('home.features.customers_title') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __('home.features.customers_body') }}</p>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-6 transition hover:border-amber-400 hover:shadow-lg hover:shadow-amber-500/10 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-500">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <svg class="size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m7.5 4.27 9 5.15M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" /><path d="m3.3 7 8.7 5 8.7-5M12 22V12" /></svg>
                    </span>
                    <h3 class="mt-4 text-base font-bold">{{ __('home.features.stock_title') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __('home.features.stock_body') }}</p>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-6 transition hover:border-amber-400 hover:shadow-lg hover:shadow-amber-500/10 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-500">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <svg class="size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" /><path d="M8 7h8M8 11h8M8 15h5" /></svg>
                    </span>
                    <h3 class="mt-4 text-base font-bold">{{ __('home.features.billing_title') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __('home.features.billing_body') }}</p>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-6 transition hover:border-amber-400 hover:shadow-lg hover:shadow-amber-500/10 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-500">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <svg class="size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" /><path d="m9 12 2 2 4-4" /></svg>
                    </span>
                    <h3 class="mt-4 text-base font-bold">{{ __('home.features.warranty_title') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __('home.features.warranty_body') }}</p>
                </div>

                <div class="rounded-2xl border border-zinc-200 bg-white p-6 transition hover:border-amber-400 hover:shadow-lg hover:shadow-amber-500/10 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-amber-500">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400">
                        <svg class="size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z" /><path d="M9 9h.01M12 9h.01M15 9h.01" /></svg>
                    </span>
                    <h3 class="mt-4 text-base font-bold">{{ __('home.features.whatsapp_title') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __('home.features.whatsapp_body') }}</p>
                </div>
            </div>
        </section>

        {{-- Workflow --}}
        <section id="workflow" class="scroll-mt-20 border-y border-zinc-200/70 bg-white dark:border-zinc-800 dark:bg-zinc-900/50">
            <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
                <div class="text-center">
                    <h2 class="text-2xl font-extrabold sm:text-3xl">{{ __('home.workflow.heading') }}</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-zinc-600 sm:text-base dark:text-zinc-300">
                        {{ __('home.workflow.description') }}
                    </p>
                </div>

                <ol class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <li class="relative rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <span class="flex size-8 items-center justify-center rounded-full bg-amber-500 text-sm font-extrabold text-white">1</span>
                        <h3 class="mt-3 text-sm font-bold">{{ __('home.workflow.intake_title') }}</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __('home.workflow.intake_body') }}</p>
                    </li>
                    <li class="relative rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <span class="flex size-8 items-center justify-center rounded-full bg-amber-500 text-sm font-extrabold text-white">2</span>
                        <h3 class="mt-3 text-sm font-bold">{{ __('home.workflow.diagnosis_title') }}</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __('home.workflow.diagnosis_body') }}</p>
                    </li>
                    <li class="relative rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <span class="flex size-8 items-center justify-center rounded-full bg-amber-500 text-sm font-extrabold text-white">3</span>
                        <h3 class="mt-3 text-sm font-bold">{{ __('home.workflow.approval_title') }}</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __('home.workflow.approval_body') }}</p>
                    </li>
                    <li class="relative rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <span class="flex size-8 items-center justify-center rounded-full bg-amber-500 text-sm font-extrabold text-white">4</span>
                        <h3 class="mt-3 text-sm font-bold">{{ __('home.workflow.repair_title') }}</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __('home.workflow.repair_body') }}</p>
                    </li>
                    <li class="relative rounded-2xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-800 dark:bg-zinc-900">
                        <span class="flex size-8 items-center justify-center rounded-full bg-amber-500 text-sm font-extrabold text-white">5</span>
                        <h3 class="mt-3 text-sm font-bold">{{ __('home.workflow.delivery_title') }}</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-zinc-600 dark:text-zinc-300">{{ __('home.workflow.delivery_body') }}</p>
                    </li>
                </ol>
            </div>
        </section>

        {{-- CTA --}}
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
            <div class="relative overflow-hidden rounded-3xl bg-zinc-900 px-6 py-12 text-center text-white sm:px-12 dark:bg-amber-500">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="absolute -top-20 start-10 size-56 rounded-full bg-amber-500/30 blur-3xl dark:bg-white/20"></div>
                    <div class="absolute -bottom-20 end-10 size-56 rounded-full bg-amber-500/20 blur-3xl dark:bg-zinc-900/20"></div>
                </div>
                <h2 class="relative text-2xl font-extrabold sm:text-3xl">{{ __('home.cta.heading') }}</h2>
                <p class="relative mx-auto mt-3 max-w-xl text-sm leading-relaxed text-zinc-300 sm:text-base dark:text-amber-50">
                    {{ __('home.cta.description') }}
                </p>
                <div class="relative mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    @if (Route::has('filament.admin.auth.login'))
                        <a href="{{ route('filament.admin.auth.login') }}"
                            class="w-full rounded-xl bg-amber-500 px-8 py-3 text-sm font-bold text-white transition hover:bg-amber-400 sm:w-auto dark:bg-zinc-900 dark:hover:bg-zinc-800">
                            {{ __('home.cta.login') }}
                        </a>
                    @endif
                    @if (Route::has('filament.admin.auth.register'))
                        <a href="{{ route('filament.admin.auth.register') }}"
                            class="w-full rounded-xl border border-white/30 px-8 py-3 text-sm font-bold text-white transition hover:border-white hover:bg-white/10 sm:w-auto">
                            {{ __('home.cta.register') }}
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </main>

    {{-- Footer --}}
    <footer class="border-t border-zinc-200/70 dark:border-zinc-800">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-6 text-xs text-zinc-500 sm:flex-row sm:px-6 dark:text-zinc-400">
            <p>{{ config('app.name') }} © {{ date('Y') }} — {{ __('home.footer.rights') }}</p>
            <p class="inline-flex items-center gap-1.5">
                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                {{ __('home.footer.offline') }}
            </p>
        </div>
    </footer>

    <script>
        function toggleTheme() {
            try {
                var dark = document.documentElement.classList.toggle('dark');
                localStorage.setItem('workshop-theme', dark ? 'dark' : 'light');
            } catch (e) {}
        }
    </script>
</body>
</html>
