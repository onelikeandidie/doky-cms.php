<div class="tw-relative"
     x-data="{open: false}"
     {{-- Custom event to toggle side bar --}}
     x-on:toggle-side-bar.window="open = !open">
    <div
        class="md:tw-w-64 tw-w-48 tw-border-r-2 tw-border-neutral-200 dark:tw-border-gray-600 sm:tw-relative tw-fixed tw-bg-gray-100 tw-h-full dark:tw-bg-gray-800 sm:!tw-left-0 -tw-left-full tw-top-0 tw-z-10 tw-transition-[left]"
        x-bind:class="open ? '!tw-left-0' : ''">
        <div class="sm:tw-hidden tw-block tw-p-2 tw-cursor-pointer" role="button"
             title="{{ __('Toggle Navigation') }}"
             x-on:click="open = !open">
            <x-icons.heroicon.solid.x-mark class="tw-inline-block tw-h-6 tw-w-6"/>
        </div>
        <div class="tw-py-4">
            <x-navigation.article-tree :articles="$articles"/>
        </div>
        @can('create', \App\Models\Article::class)
            <hr class="tw-border tw-border-neutral-200 dark:tw-border-gray-600 tw-my-2"/>
            <div class="tw-mt-2 tw-ml-2">
                <a href="{{ route('articles.create.orphan') }}"
                   class="tw-px-2 tw-py-1 tw-text-sm">
                    {{ __("Create Root Article") }}
                    <x-icons.heroicon.solid.plus-circle
                        class="tw-w-5 tw-h-5 tw-inline-block tw-text-neutral-600 dark:tw-text-neutral-400 hover:tw-text-black hover:dark:tw-text-white"/>
                </a>
            </div>
        @endcan
    </div>
</div>
