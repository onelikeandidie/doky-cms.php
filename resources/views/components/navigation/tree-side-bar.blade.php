<div class="tw-w-64 tw-border-r-2 tw-border-neutral-200 dark:tw-border-gray-600">
    <div class="tw-my-4">
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
        {{-- Form to upload images --}}
        <form class="tw-mt-2 tw-ml-2" action="{{ route('upload.image') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="type" value="image"/>
            <input type="file" name="file" id="file" class="tw-hidden"
                   x-ref="file"
                   x-on:change="$event.target.form.submit()"/>
            <button class="tw-px-2 tw-py-1 tw-text-sm tw-mt-2"
                    x-on:click.prevent="$refs.file.click()">
                {{ __("Upload Image") }}
                <x-icons.heroicon.solid.plus-circle
                    class="tw-w-5 tw-h-5 tw-inline-block tw-text-neutral-600 dark:tw-text-neutral-400 hover:tw-text-black hover:dark:tw-text-white"/>
            </button>
        </form>
    @endcan
</div>
