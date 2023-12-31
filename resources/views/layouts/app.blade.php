@props([
    'showNavigation' => true,
    'showTreeSideBarToggle' => false,
    'scripts' => [],
    'styles' => [],
])
@php
    $classes = [];
    // Check if the user has enabled dark mode on their session
    $darkMode = session()->get('dark_mode', false);
    if ($darkMode) {
        $classes[] = 'tw-dark';
    }
@endphp
<!doctype html>
<html class="{{ implode(" ", $classes) }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config("app.name") }}</title>
    @vite('resources/js/app.js')
    @vite('resources/css/app.css')

    @foreach($scripts as $script)
        @vite($script)
    @endforeach

    @foreach($styles as $style)
        @vite($style)
    @endforeach

    {{-- This is to make clients that have javascript disabled not able to
        interact with things that require javascript --}}
    <noscript><style> .js-only { display: none } </style></noscript>

    {{-- Routes in the application to be accessible through javascript --}}
    <script type="text/javascript">
        window.app = {
            debug: {{ config('app.debug') ? 'true' : 'false' }},
            csrf: '{{ csrf_token() }}',
            routes: {
                'upload.image': '{{ route('upload.image') }}',
            }
        };
    </script>
</head>
<body class="tw-min-h-screen tw-font-sans tw-antialiased tw-bg-gray-100 tw-text-neutral-900 dark:tw-bg-gray-800 dark:tw-text-white"
      x-data="{ darkMode: {{ $darkMode ? 'true' : 'false' }} }"
>
@if($showNavigation ?? true)
    <x-navigation.bar :showTreeSideBarToggle="$showTreeSideBarToggle"/>
@endif
{{ $slot }}
</body>
</html>
