<!doctype html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- SEO Meta Tags -->
    <title>{{ app()->getLocale() === 'de' ? 'Premium WordPress Design Repository | Entdecken Sie Unsere Projekte' : 'Premium WordPress Design Repository | Explore Our Projects' }}</title>
    <meta name="description" content="{{ app()->getLocale() === 'de' ? 'Entdecken Sie unsere komplette Sammlung professionell gestalteter WordPress-Designs und Projekte, automatisch aktualisiert von unserem Sandbox-Server. Durchsuchen, Vorschau und entdecken Sie unsere Design-Repository — schnell, modern und inspirierend.' : 'Discover our complete collection of professionally crafted WordPress designs and projects, automatically updated from our sandbox server. Browse, preview, and explore our design repository — fast, modern, and inspiring.' }}">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ app()->getLocale() === 'de' ? 'Premium WordPress Design Repository' : 'Premium WordPress Design Repository' }}">
    <meta property="og:description" content="{{ app()->getLocale() === 'de' ? 'Entdecken Sie unsere komplette Sammlung professionell gestalteter WordPress-Designs und Projekte, automatisch aktualisiert von unserem Sandbox-Server.' : 'Discover our complete collection of professionally crafted WordPress designs and projects, automatically updated from our sandbox server.' }}">
    <meta property="og:url" content="{{ url()->current() }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ app()->getLocale() === 'de' ? 'Premium WordPress Design Repository' : 'Premium WordPress Design Repository' }}">
    <meta name="twitter:description" content="{{ app()->getLocale() === 'de' ? 'Entdecken Sie professionell gestaltete WordPress-Designs und Projekte.' : 'Discover professionally crafted WordPress designs and projects.' }}">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Language Alternates -->
    <link rel="alternate" hreflang="en" href="{{ route('templates.index') }}">
    <link rel="alternate" hreflang="de" href="{{ route('templates.index.de') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/watermark.png') }}">
    
    <!-- Flag Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />

    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full">
    <livewire:templates-index />
    @livewireScripts
    <script>
        // Debug Livewire loading
        document.addEventListener('livewire:init', () => {
            console.log('✅ Livewire initialized successfully');
        });

        document.addEventListener('livewire:navigating', () => {
            console.log('🔄 Livewire navigating...');
        });

        document.addEventListener('livewire:navigated', () => {
            console.log('✅ Livewire navigation complete');
        });

        // Log any Livewire errors
        window.addEventListener('error', (e) => {
            if (e.message.includes('Livewire') || e.message.includes('wire:')) {
                console.error('❌ Livewire error:', e);
            }
        });
    </script>
</body>
</html>
