<x-app-layout>
    <div class="tw-flex tw-items-stretch tw-h-[calc(100vh-3rem)]">
        <div class="tw-flex-1 tw-p-4">
            <h1 class="tw-text-3xl tw-font-bold">
                {{ __("Account Dashboard") }}
            </h1>
            <p class="tw-text-lg tw-text-neutral-600 dark:tw-text-neutral-200">
                Logged in as: {{ auth()->user()->name }}
            </p>
            <div class="tw-flex tw-flex-col tw-gap-4 tw-items-stretch">
                @if(auth()->user()->hasPermission('sync.download'))
                    <form action="{{ route('dashboard.sync.download') }}" method="POST">
                        @csrf
                        <button
                            class="tw-px-2 tw-py-1 tw-border tw-rounded tw-border-neutral-600 dark:tw-border-neutral-200"
                            type="submit">
                            <x-icons.heroicon.solid.arrow-down-tray class="tw-w-5 tw-h-5 tw-inline-block tw-mr-1"/>
                            {{ __("Pull Changes") }}
                        </button>
                    </form>
                @endif
                @if(auth()->user()->hasPermission('sync.upload'))
                    <form action="{{ route('dashboard.sync.upload') }}" method="POST">
                        @csrf
                        <button
                            class="tw-px-2 tw-py-1 tw-border tw-rounded tw-border-neutral-600 dark:tw-border-neutral-200"
                            type="submit">
                            <x-icons.heroicon.solid.arrow-up-tray class="tw-w-5 tw-h-5 tw-inline-block tw-mr-1"/>
                            {{ __("Push Changes") }}
                        </button>
                    </form>
                @endif
            </div>
            <div class="tw-flex tw-flex-col tw-gap-2 tw-items-start">
                <p class="tw-text-3xl tw-font-bold">
                    {{ __("Images that have been uploaded") }}
                </p>
                <p class="tw-text-lg tw-text-neutral-600 dark:tw-text-neutral-200">
                    {{ __("This panel is a work in progress please don't hate") }}
                </p>
                @unless($images)
                    <p class="tw-text-lg tw-text-neutral-600 dark:tw-text-neutral-200">
                        {{ __("No images uploaded") }}
                    </p>
                @endunless
                @foreach($images as $image)
                    @php
                        // The image is a asset path, we need to convert it to a relative path
                        $image_name = basename($image);
                        $image_relative_path = '/' . \Illuminate\Support\Str::after($image, 'sync/');
                    @endphp
                    <div x-data="{ show: false, loaded: false, copied: false }"
                         class="tw-flex tw-items-center tw-gap-2 tw-relative"
                         x-on:mouseenter="show = true; if (!loaded) { $refs.img.src = $refs.img.dataset.src; loaded = true }"
                         x-on:mouseleave="show = false">
                        <a href="{{ $image }}" class="tw-underline">
                            {{ $image_name }}
                        </a>
                        <img data-src="{{ $image }}"
                             class="tw-hidden tw-absolute tw-left-0 tw-top-full tw-z-10 tw-w-96 tw-h-64 tw-object-top tw-object-contain"
                             x-ref="img"
                             x-show="show"
                             x-bind:class="{ 'tw-hidden': !show }"
                        />
                        <span class="tw-cursor-pointer tw-relative"
                              x-on:click="navigator.clipboard.writeText('{{ $image_relative_path }}'); copied = true; setTimeout(() => { copied = false }, 1000)"
                              title="{{ __("Copy to clipboard") }}">
                            <x-icons.heroicon.solid.clipboard-document class="tw-w-5 tw-h-5"/>
                            <span x-bind:class="{ 'tw-opacity-0': !copied, 'tw-translate-y-0': copied, 'tw-translate-y-2': !copied, 'tw-opacity-100': copied }"
                                  class="tw-opacity-0 tw-absolute tw-left-1/2 tw-bottom-full tw-z-10 -tw-translate-x-1/2 tw-bg-neutral-200 dark:tw-bg-neutral-800 tw-rounded tw-py-1 tw-px-2 tw-shadow tw-transition-all">
                                {{ __("Copied!") }}
                            </span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
