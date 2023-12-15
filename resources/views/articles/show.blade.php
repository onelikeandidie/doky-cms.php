<x-app-layout :showTreeSideBarToggle="true">
    <div class="tw-flex tw-items-stretch tw-h-[calc(100vh-3rem)]">
        <x-navigation.tree-side-bar/>
        <div class="tw-flex-1 tw-p-4 tw-overflow-auto tw-relative">
            <div class="tw-absolute tw-top-4 tw-right-4 tw-flex tw-justify-end tw-z-10">
                @can('update', $article)
                    <a href="{{ route('articles.edit', $article) }}"
                       title="{{ __("Edit Article") }}"
                       class="tw-px-2 tw-py-1 tw-text-sm">
                        <x-icons.heroicon.solid.pencil-square
                            class="tw-w-5 tw-h-5 tw-inline-block tw-text-neutral-800 dark:tw-text-neutral-400 hover:tw-text-white hover:dark:tw-text-white"/>
                    </a>
                @endcan
            </div>
            {{-- breadcrumbs --}}
            <div class="tw-flex tw-items-center tw-gap-2 tw-relative">
                @foreach($article->breadcrumb() as $breadcrumb)
                    <a href="{{ route('articles.show', $breadcrumb) }}"
                       class="tw-text-neutral-600 dark:tw-text-neutral-200 hover:tw-text-neutral-800 dark:hover:tw-text-white">
                        {{ $breadcrumb->meta()->get('title')->unwrap() }}
                        {{-- Add a slash after each breadcrumb except the last one --}}
                    </a>
                    @if(!$loop->last)
                        <span class="tw-text-neutral-400 dark:tw-text-neutral-600">/</span>
                    @endif
                @endforeach
            </div>
            {!! $content !!}
        </div>
    </div>
</x-app-layout>
