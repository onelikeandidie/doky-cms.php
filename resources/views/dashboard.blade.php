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
        </div>
    </div>
</x-app-layout>
