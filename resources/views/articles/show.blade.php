<x-app-layout>
    <div class="tw-flex tw-items-stretch tw-h-[calc(100vh-3rem)]">
        <x-navigation.tree-side-bar/>
        <div class="tw-flex-1 tw-p-4 tw-overflow-auto tw-relative">
            <div class="tw-absolute tw-top-4 tw-right-4 tw-flex tw-justify-end">
                @can('update', $article)
                    <a href="{{ route('articles.edit', $article) }}"
                       title="{{ __("Edit Article") }}"
                       class="tw-px-2 tw-py-1 tw-text-sm">
                        <x-icons.heroicon.solid.pencil-square
                            class="tw-w-5 tw-h-5 tw-inline-block tw-text-neutral-800 dark:tw-text-neutral-400 hover:tw-text-white hover:dark:tw-text-white"/>
                    </a>
                @endcan
            </div>
            {!! $content !!}
        </div>
    </div>
</x-app-layout>
