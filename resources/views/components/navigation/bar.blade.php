<div class="tw-h-12 tw-bg-neutral-100 tw-border-b-2 tw-border-neutral-200 dark:tw-bg-gray-800 dark:tw-border-gray-600">
    <div class="tw-h-12 tw-container tw-m-auto tw-flex tw-items-center">
        {{-- Toggle for dark mode --}}
        <div class="tw-bg-gray-300 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-w-8 tw-h-8"
             @click="document.documentElement.classList.toggle('tw-dark')"
             x-data="">
            <x-icons.heroicon.solid.moon class="tw-w-6 tw-h-6 tw-inline-block dark:tw-hidden tw-text-white"/>
            <x-icons.heroicon.solid.sun class="tw-w-6 tw-h-6 tw-hidden dark:tw-inline-block tw-text-gray-800"/>
        </div>
    </div>
</div>
