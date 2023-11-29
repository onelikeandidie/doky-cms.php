<x-app-layout :scripts="['resources/js/pages/articles/edit.js']">
    <div class="tw-flex tw-items-stretch tw-h-[calc(100vh-3rem)]">
        <x-navigation.tree-side-bar/>
        <div class="tw-flex-1 tw-p-4 tw-overflow-auto tw-relative">
            <form class="tw-space-x-2 tw-space-y-2"
                  action="{{ route('articles.update', $article) }}"
                  method="POST"
                  x-data="{ slug: '{{ $article->slug }}', title: '{{ $article->meta()->get('title')->unwrap() }}', visibility: '{{ $article->meta()->get('visibility')->getOk() }}' }">
                @csrf
                @method('PATCH')
                <h1 class="tw-text-3xl">
                    <span class="tw-text-neutral-600 dark:tw-text-neutral-200">
                        {{ __("Editing Article") }}
                    </span>
                    <span class="tw-font-bold tw-text-neutral-900 dark:tw-text-white">
                        {{ $article->meta()->get('title')->unwrap() }}
                    </span>
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
                                x-on:click="console.log(title); slug = title.toLowerCase().replace(/[^a-z0-9-//]/g, '-')">
                            <x-icons.heroicon.solid.bolt class="tw-w-5 tw-h-5 tw-inline-block"/>
                            {{ __("Generate") }}
                        </button>
                    </div>
                    <label for="visibility">
                        {{ __("Visibility") }}
                        @if ($errors->has('visibility'))
                            <span class="tw-text-red-600 tw-text-lg">
                            {{ $errors->first('visibility') }}
                        </span>
                        @endif
                    </label>
                    <select class="tw-rounded tw-py-1 tw-px-2 tw-border tw-bg-white dark:tw-bg-gray-800"
                            name="visibility" id="visibility" tabindex="3" x-model="visibility">
                        <option value="public">{{ __("Public") }}</option>
                        <option value="private">{{ __("Private") }}</option>
                    </select>
                    <label for="priority">
                        {{ __("Priority") }}
                        <span class="tw-block tw-text-neutral-600 dark:tw-text-neutral-200">
                            {{ __("A higher priority means this article will sort higher on the list") }}
                        </span>
                        @if ($errors->has('priority'))
                            <span class="tw-text-red-600 tw-text-lg">
                                {{ $errors->first('priority') }}
                            </span>
                        @endif
                    </label>
                    <input type="number" name="priority" id="priority" tabindex="4"
                           class="tw-rounded tw-py-1 tw-px-2 tw-border tw-bg-white dark:tw-bg-gray-800"
                           value="{{ old('priority') ?? $article->meta()->get('priority')->getOkOrDefault(1) }}"/>
                </div>
                <hr/>
                {{-- Markdown editor --}}
                <label for="editor">
                    {{ __("Article Content") }}
                    @if ($errors->has('content'))
                        <span class="tw-text-red-600 tw-text-lg">
                            {{ $errors->first('content') }}
                        </span>
                    @endif
                </label>
                <textarea id="editor" name="content" class="tw-hidden">{{ old('content') ?? $article->content }}</textarea>
                <hr/>
                <button type="submit"
                        class="tw-px-2 tw-py-1 tw-rounded tw-border tw-border-gray-300 tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50">
                    {{ __("Save Article") }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
