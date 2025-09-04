<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>Templates</title>
    @livewireStyles
</head>
<body style="font-family: system-ui, sans-serif; max-width: 1000px; margin: 2rem auto;">
    <h1>{{ app()->getLocale() === 'de' ? 'Vorlagen' : 'Templates' }}</h1>

    <livewire:templates-index />

    @livewireScripts
</body>
</html>
