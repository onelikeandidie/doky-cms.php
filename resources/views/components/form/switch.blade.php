{{-- Simple checkbox --}}
@props([
    'checked' => false,
    'value' => null,
])
<div class="tw-relative {{ $attributes->get('class') }}"
     x-data="{ checked: {{ $checked ? 'true' : 'false' }} }"
     x-on:click="checked = !checked"
>
    <input
        type="checkbox"
        name="{{ $attributes->get('name') }}"
        class="tw-peer tw-sr-only"
        value="{{ $value ?? 1 }}"
        x-bind:checked="checked"
    />
    <div
        x-bind:class="{ 'tw-bg-blue-600': checked, 'tw-bg-neutral-200': !checked }"
        class="tw-block tw-h-8 tw-rounded-full tw-box tw-border tw-w-14 peer-checked:tw-bg-primary {{ $checked ? 'tw-bg-blue-600' : 'tw-bg-neutral-200' }}"
    ></div>
    <div
        x-bind:class="{ 'tw-translate-x-full': checked, 'tw-translate-x-0': !checked }"
        class="tw-absolute tw-flex tw-items-center tw-justify-center tw-w-6 tw-h-6 tw-transition tw-bg-white tw-rounded-full tw-border tw-left-1 tw-top-1 dark:tw-bg-dark-5 peer-checked:tw-translate-x-full peer-checked:tw-dark:bg-white {{ $checked ? 'tw-translate-x-full' : 'tw-translate-x-0' }}"
    ></div>
</div>
