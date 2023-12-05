<x-app-layout :scripts="['resources/js/pages/articles/edit.js']">
    <div class="tw-flex tw-items-stretch tw-h-[calc(100vh-3rem)]">
        <x-navigation.tree-side-bar/>
        <div class="tw-flex-1 tw-p-4 tw-overflow-auto tw-relative">
            @php
                $visibility = $article->meta()->get('visibility')->unwrapOrDefault('private');
            @endphp
            <form class="tw-space-x-2 tw-space-y-2"
                  action="{{ route('articles.update', $article) }}"
                  method="POST"
                  {{-- Legends say that the desire to hate JavaScript, is equal to the desire to love it --}}
                  {{-- I have made a mess of this code. Please don't hate --}}
                  x-data="{
                      slug: '{{ $article->slug }}',
                      title: '{{ str_replace('\'', '\\\'', $article->meta()->get('title')->unwrap()) }}',
                      visibility: '{{ $visibility }}',
                      parentSlug: '{{ $parentSlug }}'
                  }">
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
                    {{-- Title --}}
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
                           x-bind:value="title"
                           value="{{ $article->meta()->get('title')->unwrap() }}"/>
                    <label for="slug">
                        {{ __("SEO Handle") }}
                        @if ($errors->has('slug'))
                            <span class="tw-text-red-600 tw-text-lg">
                            {{ $errors->first('slug') }}
                        </span>
                        @endif
                    </label>
                    {{-- Using alpineJS we can make a slug input that automatically generates a slug from the title --}}
                    <div class="tw-flex">
                        <input class="tw-rounded tw-py-1 tw-px-2 tw-border tw-rounded-r-none dark:tw-bg-gray-800"
                               type="text" name="slug"
                               tabindex="1" id="slug"
                               maxlength="255"
                               placeholder="seo-handle"
                               x-on:input="slug = $event.target.value.toLowerCase().replace(/[^a-z0-9-//]/g, '-')"
                               x-bind:value="slug"
                               value="{{ $article->slug }}"/>
                        {{-- Button to generate slug from title --}}
                        <button type="button"
                                class="tw-px-2 tw-py-1 tw-rounded tw-rounded-l-none tw-border tw-border-gray-300 tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50 js-only"
                                x-on:click="slug = ''; if(parentSlug) slug += parentSlug + '/'; slug += title.toLowerCase().replace(/[^a-z0-9-//]/g, '-')">
                            <x-icons.heroicon.solid.bolt class="tw-w-5 tw-h-5 tw-inline-block"/>
                            {{ __("Generate") }}
                        </button>
                    </div>
                    {{-- Visibility --}}
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
                        <option value="public" @selected($visibility=='public')>{{ __("Public") }}</option>
                        <option value="restricted" @selected($visibility=='restricted')>{{ __("Restricted") }}</option>
                        <option value="private" @selected($visibility=='private')>{{ __("Private") }}</option>
                    </select>
                    {{-- Priority Number --}}
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
                           value="{{ old('priority') ?? $article->meta()->get('priority')->unwrapOrDefault(1) }}"/>
                    <label for="tag-input">
                        {{ __("Tags") }}
                        @if ($errors->has('tags'))
                            <span class="tw-text-red-600 tw-text-lg">
                                {{ $errors->first('tags') }}
                            </span>
                        @endif
                    </label>
                    @php
                        $tags = $article->meta()->get('tags')->unwrapOrDefault([]);
                        $tagsStr = null;
                        if (!empty($tags)) {
                            $tagsStr = "'" . implode('\',\'', $tags) . "'";
                        }
                    @endphp
                    {{-- Using alpineJS we can make a simple tag input --}}
                    <noscript>
                        <div class="tw-flex tw-flex-col">
                            <span class="tw-text-red-600 tw-text-lg">
                                {{ __("You need to enable javascript to use the tag input") }}
                            </span>
                            <span class="">
                                {{ __("Don't worry, you can use this field. Just put commas between each tag") }}
                            </span>
                            <input type="text" name="tags" id="tags" tabindex="5"
                                   class="tw-rounded tw-py-1 tw-px-2 tw-border tw-bg-white dark:tw-bg-gray-800"
                                   value="{{ implode(',', $tags) }}"/>
                        </div>
                    </noscript>
                    <div class="tw-flex tw-flex-col tw-items-start tw-gap-2 js-only"
                         x-on:click="$refs.tagInput.focus()"
                         x-data="{ tags: [{{ $tagsStr }}], tagInput: '{{ old('tag-input') }}' }">
                        <input type="hidden" x-init="$el.name = 'tags'" x-bind:value="tags.join(',')"/>
                        <div class="tw-flex tw-gap-2 tw-flex-wrap tw-items-center">
                            {{-- This is what you call a for loop in the frontend --}}
                            <template x-for="(tag, index) in tags" :key="index">
                                    <span class="tw-py-1 tw-px-2 tw-bg-gray-200 tw-rounded tw-text-gray-700">
                                        <span x-text="tag"></span>
                                        <button type="button" class="tw-ml-2 tw-text-gray-500 hover:tw-text-gray-700"
                                                x-on:click="tags.splice(index, 1)">
                                            <x-icons.heroicon.solid.x-mark class="tw-w-5 tw-h-5 tw-inline-block"/>
                                        </button>
                                    </span>
                            </template>
                        </div>
                        <input type="text" name="tag-input" id="tag-input" tabindex="5"
                               class="tw-rounded tw-py-1 tw-px-2 tw-border tw-bg-white dark:tw-bg-gray-800"
                               placeholder="{{ __('Write tag and press enter') }}"
                               x-model="tagInput"
                               {{-- Whenever you press enter, it pushes the tag to the list --}}
                               x-on:keydown.enter.prevent="tags.push(tagInput); tagInput = ''"
                               x-ref="tagInput"/>
                    </div>
                    {{-- Allowed Users --}}
                    <label for="allowed_users">
                        {{ __("Allowed Users") }}
                        <span class="tw-block tw-text-neutral-600 dark:tw-text-neutral-200">
                            {{ __("Users that are allowed to view this article if restricted visibility is selected") }}
                        </span>
                        @if ($errors->has('allowed_users'))
                            <span class="tw-text-red-600 tw-text-lg">
                                {{ $errors->first('allowed_users') }}
                            </span>
                        @endif
                    </label>
                    @php
                        $allowedUsers = $article->meta()->get('allowed_users')->unwrapOrDefault([]);
                        $allUsers = \App\Models\User::all()->pluck('name');
                        $allowedUsersStr = null;
                        if (!empty($allowedUsers)) {
                            $allowedUsersStr = "'" . implode('\',\'', $allowedUsers) . "'";
                        }
                    @endphp
                    {{-- Using alpineJS we can make a simple user input --}}
                    <noscript>
                        <div class="tw-flex tw-flex-col">
                            <span class="tw-text-red-600 tw-text-lg">
                                {{ __("You need to enable javascript to use the user input") }}
                            </span>
                            <span class="">
                                {{ __("Don't worry, you can use this field. Just put commas between each user") }}
                            </span>
                            <input type="text" name="allowed_users" id="allowed_users" tabindex="6"
                                   class="tw-rounded tw-py-1 tw-px-2 tw-border tw-bg-white dark:tw-bg-gray-800"
                                   value="{{ implode(',', $allowedUsers) }}"/>
                        </div>
                    </noscript>
                    <div class="tw-flex tw-flex-col tw-items-start tw-gap-2 js-only"
                         x-on:click="$refs.userInput.focus()"
                         x-data="{ allowedUsers: [{{ $allowedUsersStr }}], userInput: '{{ old('allowed_users') }}', allUsers: ['{{ $allUsers->implode('\',\'') }}'] }">
                        <input type="hidden" x-init="$el.name = 'allowed_users'" x-bind:value="allowedUsers.join(',')"/>
                        <div class="tw-flex tw-gap-2 tw-flex-wrap tw-items-center">
                            {{-- This is what you call a for loop in the frontend --}}
                            <template x-for="(user, index) in allowedUsers" :key="index">
                                    <span class="tw-py-1 tw-px-2 tw-bg-gray-200 tw-rounded tw-text-gray-700">
                                        <span x-text="user"></span>
                                        <button type="button" class="tw-ml-2 tw-text-gray-500 hover:tw-text-gray-700"
                                                x-on:click="allowedUsers.splice(index, 1)">
                                            <x-icons.heroicon.solid.x-mark class="tw-w-5 tw-h-5 tw-inline-block"/>
                                        </button>
                                    </span>
                            </template>
                        </div>
                        <select name="allowed_users" id="allowed_users" tabindex="6"
                                class="tw-rounded tw-py-1 tw-px-2 tw-border tw-bg-white dark:tw-bg-gray-800"
                                x-model="userInput"
                                x-ref="userInput">
                            <option value="" disabled selected>{{ __('Select a user') }}</option>
                            {{-- This is what you call a for loop in the frontend --}}
                            <template x-for="(user, index) in allUsers" :key="index">
                                <option x-bind:value="user" x-text="user"></option>
                            </template>
                        </select>
                        <button type="button"
                                class="tw-px-2 tw-py-1 tw-rounded tw-border tw-border-gray-300 tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50"
                                x-on:click="if(!userInput){return false} allowedUsers.push(userInput); userInput = ''">
                            <x-icons.heroicon.solid.plus class="tw-w-5 tw-h-5 tw-inline-block"/>
                            {{ __("Add User") }}
                        </button>
                    </div>
                    {{-- Allowed Roles --}}
                    <label for="allowed_roles">
                        {{ __("Allowed Roles") }}
                        <span class="tw-block tw-text-neutral-600 dark:tw-text-neutral-200">
                            {{ __("Roles that are allowed to view this article if restricted visibility is selected") }}
                        </span>
                        @if ($errors->has('allowed_roles'))
                            <span class="tw-text-red-600 tw-text-lg">
                                {{ $errors->first('allowed_roles') }}
                            </span>
                        @endif
                    </label>
                    @php
                        $allowedRoles = $article->meta()->get('allowed_roles')->unwrapOrDefault([]);
                        $allRoles = \App\Models\Role::all()->pluck('name');
                        $allowedRolesStr = null;
                        if (!empty($allowedRoles)) {
                            $allowedRolesStr = "'" . implode('\',\'', $allowedRoles) . "'";
                        }
                    @endphp
                    {{-- Using alpineJS we can make a simple role input --}}
                    <noscript>
                        <div class="tw-flex tw-flex-col">
                            <span class="tw-text-red-600 tw-text-lg">
                                {{ __("You need to enable javascript to use the role input") }}
                            </span>
                            <span class="">
                                {{ __("Don't worry, you can use this field. Just put commas between each role") }}
                            </span>
                            <input type="text" name="allowed_roles" id="allowed_roles" tabindex="7"
                                   class="tw-rounded tw-py-1 tw-px-2 tw-border tw-bg-white dark:tw-bg-gray-800"
                                   value="{{ implode(',', $allowedRoles) }}"/>
                        </div>
                    </noscript>
                    <div class="tw-flex tw-flex-col tw-items-start tw-gap-2 js-only"
                         x-on:click="$refs.roleInput.focus()"
                         x-data="{ allowedRoles: [{{ $allowedRolesStr }}], roleInput: '{{ old('allowed_roles') }}', allRoles: ['{{ $allRoles->implode('\',\'') }}'] }">
                        <input type="hidden" x-init="$el.name = 'allowed_roles'" x-bind:value="allowedRoles.join(',')"/>
                        <div class="tw-flex tw-gap-2 tw-flex-wrap tw-items-center">
                            {{-- This is what you call a for loop in the frontend --}}
                            <template x-for="(role, index) in allowedRoles" :key="index">
                                    <span class="tw-py-1 tw-px-2 tw-bg-gray-200 tw-rounded tw-text-gray-700">
                                        <span x-text="role"></span>
                                        <button type="button" class="tw-ml-2 tw-text-gray-500 hover:tw-text-gray-700"
                                                x-on:click="allowedRoles.splice(index, 1)">
                                            <x-icons.heroicon.solid.x-mark class="tw-w-5 tw-h-5 tw-inline-block"/>
                                        </button>
                                    </span>
                            </template>
                        </div>
                        <select name="allowed_roles" id="allowed_roles" tabindex="7"
                                class="tw-rounded tw-py-1 tw-px-2 tw-border tw-bg-white dark:tw-bg-gray-800"
                                x-model="roleInput"
                                x-ref="roleInput">
                            <option value="" disabled selected>{{ __('Select a role') }}</option>
                            {{-- This is what you call a for loop in the frontend --}}
                            <template x-for="(role, index) in allRoles" :key="index">
                                <option x-bind:value="role" x-text="role"></option>
                            </template>
                        </select>
                        <button type="button"
                                class="tw-px-2 tw-py-1 tw-rounded tw-border tw-border-gray-300 tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50"
                                x-on:click="if(!roleInput){return false} allowedRoles.push(roleInput); roleInput = ''">
                            <x-icons.heroicon.solid.plus class="tw-w-5 tw-h-5 tw-inline-block"/>
                            {{ __("Add Role") }}
                        </button>
                    </div>
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
                <textarea id="editor" name="content"
                          class="">{{ old('content') ?? $article->content }}</textarea>
                <hr/>
                <button type="submit"
                        class="tw-px-2 tw-py-1 tw-rounded tw-border tw-border-gray-300 tw-bg-white tw-text-gray-700 hover:tw-bg-gray-50">
                    {{ __("Save Article") }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
