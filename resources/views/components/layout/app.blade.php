@props([
    'showNavigation' => true
])
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config("app.name") }}</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @vite('resources/css/app.css')
</head>
<body class="tw-min-h-screen tw-font-sans tw-antialiased tw-bg-gray-100 tw-text-neutral-900 dark:tw-bg-gray-800 dark:tw-text-white">
@if($showNavigation ?? true)
    <x-navigation.bar/>
@endif
<div>
    {{ $slot }}
</div>
</body>
</html>
