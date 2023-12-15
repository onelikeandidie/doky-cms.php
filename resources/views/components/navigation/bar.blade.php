@props([
    'showTreeSideBarToggle' => false
])
<nav class="tw-h-12 tw-bg-neutral-100 tw-border-b-2 tw-border-neutral-200 dark:tw-bg-gray-800 dark:tw-border-gray-600">
    <div class="tw-h-12 tw-container tw-m-auto tw-flex tw-items-stretch">
        <div class="tw-flex tw-flex-1 tw-items-center tw-justify-start">
            {{-- Toggle for side bar --}}
            @if($showTreeSideBarToggle ?? true)
                <div class="sm:tw-hidden tw-block tw-p-2" role="button"
                     title="{{ __('Toggle Navigation') }}"
                     x-on:click="$dispatch('toggle-side-bar')">
                    <x-icons.heroicon.solid.bars-3 class="tw-inline-block tw-h-6 tw-w-6"/>
                </div>
            @endif
            <a href="{{ route('articles.index') }}"
               class="tw-flex tw-items-center tw-justify-center hover:tw-underline">
                <span class="tw-ml-2">
                    <x-icons.heroicon.solid.book-open
                            class="tw-inline-block tw-w-6 tw-h-6 tw-text-gray-800 dark:tw-text-gray-200"/>
                    {{ __('Docs') }}
                </span>
            </a>
        </div>
        <div class="tw-flex tw-items-center tw-justify-end tw-gap-2 tw-pr-2">
            {{-- Toggle for dark mode --}}
            <div class="tw-bg-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-cursor-pointer"
                 @click="darkMode = !darkMode; document.documentElement.classList.toggle('tw-dark'); axios.post('/dark-mode/toggle', { dark_mode: darkMode });"
                 x-data="">
                <x-icons.heroicon.solid.moon class="tw-w-6 tw-h-6 tw-inline-block dark:tw-hidden tw-text-white"/>
                <x-icons.heroicon.solid.sun class="tw-w-6 tw-h-6 tw-hidden dark:tw-inline-block tw-text-gray-800"/>
            </div>
            {{-- User Profile Thingy --}}
            @if(auth()->check())
                <div class="tw-flex tw-items-center tw-justify-center">
                    <a class="tw-flex tw-items-center tw-justify-center tw-group"
                       href="{{ route('dashboard') }}">
                        <div class="tw-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-rounded-full tw-bg-gray-300">
                            <x-icons.heroicon.solid.user-circle
                                    class="tw-w-6 tw-h-6 tw-text-white dark:tw-text-gray-800"/>
                        </div>
                        <div class="tw-ml-2 group-hover:tw-underline">
                            <div class="tw-text-xs tw-font-medium tw-text-gray-600 dark:tw-text-gray-400">
                                {{ __('Logged in as') }}
                            </div>
                            <div class="tw-text-sm tw-font-medium tw-text-gray-800 dark:tw-text-gray-200">
                                {{ auth()->user()->name }}
                            </div>
                        </div>
                    </a>
                </div>
                <a href="{{ route('logout') }}"
                   title="{{ __('Logout') }}"
                   class="tw-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-rounded-full tw-bg-gray-300">
                    <x-icons.heroicon.solid.arrow-right-on-rectangle
                            class="tw-w-6 tw-h-6 tw-text-white dark:tw-text-gray-800"/>
                </a>
            @else
                <a href="{{ route('login') }}"
                   title="{{ __('Login') }}"
                   class="tw-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-rounded-full tw-bg-gray-300">
                    <x-icons.heroicon.solid.arrow-right-on-rectangle
                            class="tw-w-6 tw-h-6 tw-text-white dark:tw-text-gray-800"/>
                </a>
            @endif
        </div>
    </div>
</nav>
