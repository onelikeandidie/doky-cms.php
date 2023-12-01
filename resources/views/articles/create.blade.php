<x-app-layout>
    <div class="tw-flex tw-items-stretch tw-h-[calc(100vh-3rem)]">
        <x-navigation.tree-side-bar/>
        <div class="tw-flex-1 tw-p-4 tw-overflow-auto tw-relative">
            <form class="tw-space-x-2 tw-space-y-2"
                  @if($article->exists)
                      action="{{ route('articles.store', $article) }}"
                  @else
                      action="{{ route('articles.store.orphan') }}"
                  @endif
                  method="POST"
                  x-data="{ slug: '', title: '', parentSlug: '{{ $parentSlug }}' }">
                @csrf
                <h1 class="tw-text-3xl">
                    <span class="tw-text-neutral-600 dark:tw-text-neutral-200">
                        {{ __("Create Article") }}
                    </span>
                    @if($article->exists)
                        <span class="tw-font-bold tw-text-neutral-900 dark:tw-text-white">
                        @if($article->parent)
                            {{ $article->parent->meta()->get('title')->unwrap() }}
                            <span class="tw-text-neutral-600 dark:tw-text-neutral-200">
                                {{ __("/") }}
                            </span>
                        @endif
                        {{ $article->meta()->get('title')->unwrap() }}
                        </span>
                    @else
                        <span class="tw-font-bold tw-text-neutral-900 dark:tw-text-white">
                            {{ __("Root Article") }}
                        </span>
                    @endif
                </h1>
                <hr/>
                <div class="tw-flex tw-flex-col tw-items-start tw-gap-2">
                    <label for="title">
                        {{ __("Title") }}
                        @if ($errors->has('title'))
                            <span class="tw-text-red-600 tw-text-lg">
                            {{ $errors->first('title') }}
                        </span>
                        @endif
                    </label>
                    <input class="tw-rounded tw-py-1 tw-px-2 tw-border dark:tw-bg-gray-800"
                           type="text" name="title"
                           id="title" tabindex="2"
                           maxlength="255"
                           placeholder="Title"
                           x-on:input="title = $event.target.value"
                           x-bind:value="title"/>
                    <label for="slug">
                        {{ __("SEO Handle") }}
                        @if ($errors->has('slug'))
                            <span class="tw-text-red-600 tw-text-lg">
                            {{ $errors->first('slug') }}
                        </span>
                        @endif
                    </label>
                    <div class="tw-flex">
                        <input class="tw-rounded tw-py-1 tw-px-2 tw-border tw-rounded-r-none dark:tw-bg-gray-800"
                               type="text" name="slug"
                               tabindex="1" id="slug"
                               maxlength="255"
                               placeholder="seo-handle"
                               x-on:input="slug = $event.target.value.toLowerCase().replace(/[^a-z0-9-//]/g, '-')"
                               x-bind:value="slug"/>
                        {{-- Button to generate slug from title --}}
                        <button type="button"
                                class="tw-px-2 tw-py-1 tw-rounded tw-rounded-l-none tw-border tw-border-gray-300 tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50"
                                x-on:click="slug = ''; if(parentSlug) slug += parentSlug + '/'; slug += title.toLowerCase().replace(/[^a-z0-9-//]/g, '-')">
                            <x-icons.heroicon.solid.bolt class="tw-w-5 tw-h-5 tw-inline-block"/>
                            {{ __("Generate") }}
                        </button>
                    </div>
                </div>
                <hr/>
                <button type="submit"
                        class="tw-px-2 tw-py-1 tw-rounded tw-border tw-border-gray-300 tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50">
                    {{ __("Create Article") }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
