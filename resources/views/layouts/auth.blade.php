<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('template/img/logo.svg') }}">
    <title>@yield('title', 'เข้าสู่ระบบ') | {{ config('app.name', 'EBRS') }}</title>
    <link rel="stylesheet" href="{{ asset('template/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('template/assets/css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
    @livewireStyles
    @stack('styles')
    <script src="{{ asset('template/assets/js/app.js') }}"></script>
</head>
<body>
    @yield('content')

    <script src="{{ asset('template/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('template/vendor/lucide/lucide.min.js') }}"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
