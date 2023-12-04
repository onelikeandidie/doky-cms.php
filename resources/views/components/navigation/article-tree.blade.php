@props([
    'articles' => [],
    'selected' => '',
    'depth' => 0,
])
<ul class="tw-mx-2">
    @foreach($articles as $article)
        @php
            /** @var \App\Models\Article $article */
            $classes = [];
            if ($selected === $article->id) {
                $classes[] = 'tw-text-neutral-950 dark:tw-text-white';
            } else {
                $classes[] = 'tw-text-neutral-700 dark:tw-text-neutral-200';
            }

            if ($depth === 0) {
                $classes[] = 'tw-font-bold';
            }

            if ($depth > 0) {
                $classes[] = 'tw-text-sm';
            }
        @endphp
        <li class="tw-mb-2 tw-border-l tw-border-transparent hover:tw-border-gray-600">
            <div class="tw-pl-2 tw-w-full tw-block hover:tw-text-neutral-950 {{ implode(' ', $classes) }}">
                <a href="{{ route('articles.show', $article) }}">
                    {{ $article->meta()->get('title')->unwrap() }}
                </a>
                @if($depth < 3)
                    @can('create', \App\Models\Article::class)
                        <a href="{{ route('articles.create', $article) }}"
                           title="{{ __("Create Article Inside") }} {{ $article->meta()->get('title')->unwrap() }}"
                           class="tw-inline-block">
                            <x-icons.heroicon.solid.plus-circle
                                class="tw-w-5 tw-h-5 tw-inline-block tw-text-neutral-600 dark:tw-text-neutral-400 hover:tw-text-black hover:dark:tw-text-white"/>
                        </a>
                    @endcan
                @endif
            </div>
            @if($article->children->count() > 0)
                <div class="tw-mt-2">
                    <x-navigation.article-tree :articles="$article->children" :selected="$selected"
                                               :depth="$depth + 1"/>
                </div>
            @endif
        </li>
    @endforeach
</ul>
