<div class="min-h-screen overflow-x-hidden bg-white">
    <!-- Minimalist Header Section -->
    <header class="sticky top-0 z-50 bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo Section -->
                <div class="flex items-center">
                    <a href="https://metanow.dev" class="transition-opacity duration-200 hover:opacity-80">
                        <img src="{{ asset('storage/img/logo/Metanow.webp') }}"
                            width="140"
                            class="sm:w-[160px]"
                            alt="Metanow Logo">
                    </a>
                </div>

                <!-- Navigation Section -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- Language Selector -->
                    <div class="flex items-center gap-1 bg-gray-50 rounded-lg px-1.5 py-1.5">
                        <a href="{{ route('templates.index') }}"
                            class="flex items-center justify-center px-2.5 py-1.5 rounded-md transition-all duration-200 @if(app()->getLocale() === 'en') bg-brand text-white @else text-gray-600 hover:bg-white @endif">
                            <span class="fi fi-gb" style="width: 20px; height: 15px;"></span>
                        </a>
                        <a href="{{ route('templates.index.de') }}"
                            class="flex items-center justify-center px-2.5 py-1.5 rounded-md transition-all duration-200 @if(app()->getLocale() === 'de') bg-brand text-white @else text-gray-600 hover:bg-white @endif">
                            <span class="fi fi-de" style="width: 20px; height: 15px;"></span>
                        </a>
                    </div>

                    <!-- CTA Button -->
                    <a href="https://metanow.dev"
                        class="bg-brand hover:bg-brand-dark text-white px-4 py-2 sm:px-6 sm:py-2.5 text-sm font-semibold rounded-lg transition-colors duration-200 whitespace-nowrap">
                        <span class="hidden sm:inline">Back to Metanow</span>
                        <span class="sm:hidden">Home</span>
                    </a>
                </div>
            </div>
        </div>
    </header>


    <!-- Hero Section -->
    <section id="hero-section" class="relative overflow-hidden" style="background: linear-gradient(to right, #fff1ed, #ffb0b5); transition: opacity 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);">
        {{-- Illustration Background with Genie Effect --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="genie-container" style="width: 100%; height: 100%;">
                <img src="{{ asset('img/screen-collage-showing-business-advertisement.jpg') }}"
                     alt="Template Illustration"
                     class="genie-image"
                     style="width: 100%; height: 100%; object-fit: cover; opacity: 0.15;">
            </div>
        </div>

        {{-- Overlay for better text readability --}}
        <div class="absolute inset-0 bg-gradient-to-b from-white/30 via-transparent to-white/20"></div>

        <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="flex flex-col items-center justify-center min-h-[600px] lg:min-h-[700px] text-center py-20 lg:py-24">

                <!-- Badge -->
                <div class="mb-8 animate-fade-in-down">
                    <span class="inline-flex items-center gap-2 px-4 py-2 bg-white/60 backdrop-blur-sm border border-white/80 rounded-full text-gray-800 text-sm font-medium shadow-sm">
                        <svg class="w-4 h-4 text-brand" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        {{ app()->getLocale() === 'de' ? 'Professionell Handgefertigt' : 'Professionally Handcrafted' }}
                    </span>
                </div>

                <!-- Main Heading -->
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-gray-900 mb-6 leading-tight max-w-5xl animate-fade-in-up">
                    {{ app()->getLocale() === 'de' ? 'Premium WordPress Vorlagen für Ihr' : 'Premium WordPress Templates for Your' }}
                    <span class="block mt-2 bg-gradient-to-r from-brand via-rose-600 to-pink-600 bg-clip-text text-transparent animate-gradient">
                        {{ app()->getLocale() === 'de' ? 'Erfolgreiches Business' : 'Successful Business' }}
                    </span>
                </h1>

                <!-- Subheading -->
                <p class="text-lg md:text-xl lg:text-2xl text-gray-700 mb-8 max-w-3xl mx-auto leading-relaxed animate-fade-in-up animation-delay-200">
                    {{ app()->getLocale() === 'de'
                        ? 'Entdecken Sie über 1500+ professionell entwickelte WordPress-Templates. Jede Vorlage wird von Experten handgefertigt und für maximale Performance optimiert.'
                        : 'Discover 1500+ professionally crafted WordPress templates. Each template is handmade by experts and optimized for maximum performance.'
                    }}
                </p>

                <!-- Stats Counter -->
                <div class="flex flex-wrap items-center justify-center gap-8 mb-10 animate-fade-in-up animation-delay-400">
                    <div class="text-center">
                        <div class="text-4xl md:text-5xl font-bold text-gray-900 mb-2">
                            1500<span class="text-brand">+</span>
                        </div>
                        <div class="text-sm text-gray-600 uppercase tracking-wider font-semibold">
                            {{ app()->getLocale() === 'de' ? 'Premium Vorlagen' : 'Premium Templates' }}
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="text-4xl md:text-5xl font-bold text-gray-900 mb-2">
                            100<span class="text-brand">%</span>
                        </div>
                        <div class="text-sm text-gray-600 uppercase tracking-wider font-semibold">
                            {{ app()->getLocale() === 'de' ? 'Handgefertigt' : 'Handcrafted' }}
                        </div>
                    </div>
                </div>

                <!-- Features Row -->
                <div class="flex flex-wrap items-center justify-center gap-6 text-sm text-gray-700 animate-fade-in-up animation-delay-800">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">{{ app()->getLocale() === 'de' ? 'Mobile Responsive' : 'Mobile Responsive' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">{{ app()->getLocale() === 'de' ? 'Schnelle Ladezeit' : 'Fast Loading' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">{{ app()->getLocale() === 'de' ? 'Einfach Anpassbar' : 'Easy Customization' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">{{ app()->getLocale() === 'de' ? 'SEO Optimiert' : 'SEO Optimized' }}</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Bottom Wave -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg class="w-full h-auto" viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0 0L60 10C120 20 240 40 360 46.7C480 53 600 47 720 43.3C840 40 960 40 1080 46.7C1200 53 1320 67 1380 73.3L1440 80V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0V0Z" fill="#F9FAFB"/>
            </svg>
        </div>

        <style>
            @keyframes fade-in-down {
                0% { opacity: 0; transform: translateY(-20px); }
                100% { opacity: 1; transform: translateY(0); }
            }

            @keyframes fade-in-up {
                0% { opacity: 0; transform: translateY(20px); }
                100% { opacity: 1; transform: translateY(0); }
            }

            @keyframes gradient {
                0%, 100% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
            }

            .animate-fade-in-down {
                animation: fade-in-down 0.6s ease-out forwards;
            }

            .animate-fade-in-up {
                animation: fade-in-up 0.6s ease-out forwards;
            }

            .animate-gradient {
                background-size: 200% auto;
                animation: gradient 3s linear infinite;
            }

            .animation-delay-200 {
                animation-delay: 0.2s;
            }

            .animation-delay-400 {
                animation-delay: 0.4s;
            }

            .animation-delay-600 {
                animation-delay: 0.6s;
            }

            .animation-delay-800 {
                animation-delay: 0.8s;
            }

            /* Genie summoning effect */
            @keyframes genie-summon {
                0% {
                    transform: scale(0.3) rotate(15deg);
                    opacity: 0;
                }
                50% {
                    transform: scale(0.7) rotate(5deg);
                    opacity: 0.6;
                }
                100% {
                    transform: scale(1) rotate(0deg);
                    opacity: 1;
                }
            }

            @keyframes genie-float {
                0%, 100% {
                    transform: translateY(0) translateX(0) rotate(0deg);
                }
                25% {
                    transform: translateY(-10px) translateX(5px) rotate(1deg);
                }
                50% {
                    transform: translateY(-5px) translateX(-5px) rotate(-1deg);
                }
                75% {
                    transform: translateY(-15px) translateX(3px) rotate(0.5deg);
                }
            }

            .genie-container {
                animation: genie-summon 2s ease-out forwards;
                transform-origin: center;
            }

            .genie-image {
                animation: genie-float 6s ease-in-out infinite;
                animation-delay: 2s;
            }
        </style>

        <script>
            // Smooth scroll animations for hero fade and templates slide-up
            let ticking = false;

            function updateScrollAnimations() {
                const heroSection = document.getElementById('hero-section');
                const templatesSection = document.getElementById('templates-grid');
                const scrollPosition = window.scrollY;

                // Hero fade animation - longer range for smoother effect
                const fadeStart = 100;
                const fadeEnd = 800;

                if (scrollPosition < fadeStart) {
                    heroSection.style.opacity = '1';
                } else if (scrollPosition >= fadeEnd) {
                    heroSection.style.opacity = '0';
                } else {
                    // Easing function for smoother fade
                    const fadeProgress = (scrollPosition - fadeStart) / (fadeEnd - fadeStart);
                    const easedProgress = 1 - Math.pow(1 - fadeProgress, 3); // Cubic easing
                    heroSection.style.opacity = (1 - easedProgress).toString();
                }

                // Templates slide-up animation
                const slideStart = 0;
                const slideEnd = 500;

                if (scrollPosition < slideStart) {
                    templatesSection.style.transform = 'translateY(60px)';
                } else if (scrollPosition >= slideEnd) {
                    templatesSection.style.transform = 'translateY(0)';
                } else {
                    const slideProgress = (scrollPosition - slideStart) / (slideEnd - slideStart);
                    const easedSlide = Math.pow(slideProgress, 2); // Quadratic easing
                    const translateY = 60 - (60 * easedSlide);
                    templatesSection.style.transform = `translateY(${translateY}px)`;
                }

                ticking = false;
            }

            window.addEventListener('scroll', function() {
                if (!ticking) {
                    window.requestAnimationFrame(updateScrollAnimations);
                    ticking = true;
                }
            });

            // Initial call
            updateScrollAnimations();
        </script>
    </section>

    <!-- Main Content Area (Livewire Reactive) -->
    <div id="templates-grid" class="relative bg-gray-50 w-full py-16 -mt-20 z-20" style="transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">


            <!-- Clean Search & Controls Bar -->
            <div class="mb-8" wire:key="search-controls">
                <div class="bg-white shadow-lg rounded-lg p-4 sm:p-6">
                    <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">

                        <!-- Search Section -->
                        <div class="flex-1 max-w-2xl w-full">
                            <div class="relative">
                                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text" wire:model.live.debounce.300ms="search"
                                    placeholder="{{ app()->getLocale() === 'de' ? 'Nach Vorlagen suchen...' : 'Search templates...' }}"
                                    class="w-full pl-10 pr-10 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:border-brand focus:ring-2 focus:ring-brand/20 transition-all placeholder:text-gray-400">

                                @if($search)
                                <button wire:click="$set('search', '')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </div>

                        <!-- Controls Section -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Filter Toggle -->
                            <button wire:click="toggleFilters"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors {{ $showFilters ? 'bg-brand text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z" />
                                </svg>
                                <span>{{ $this->filterToggleText }}</span>
                                <span wire:loading wire:target="toggleFilters" class="ml-1">
                                    <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>

                            <!-- View Toggle -->
                            <div class="flex items-center bg-gray-100 p-1 rounded-lg">
                                <button wire:click="setView('grid')"
                                    class="flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ $view === 'grid' ? 'bg-white text-brand shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    <span class="hidden sm:inline">Grid</span>
                                </button>
                                <button wire:click="setView('list')"
                                    class="flex items-center gap-1.5 px-3 py-2 rounded-md text-sm font-semibold transition-colors {{ $view === 'list' ? 'bg-white text-brand shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="hidden sm:inline">List</span>
                                </button>
                            </div>

                            <!-- Sort Dropdown -->
                            <select wire:model.live="sort"
                                class="px-3 py-2.5 rounded-lg border border-gray-300 bg-white hover:border-gray-400 focus:border-brand focus:ring-2 focus:ring-brand/20 transition-all text-sm font-medium cursor-pointer">
                                <option value="recent">{{ app()->getLocale() === 'de' ? 'Neueste' : 'Recent' }}</option>
                                <option value="az">A → Z</option>
                                <option value="za">Z → A</option>
                                <option value="category">{{ app()->getLocale() === 'de' ? 'Kategorie' : 'Category' }}</option>
                            </select>

                            <!-- Per Page -->
                            <select wire:model.live="perPage"
                                class="px-3 py-2.5 rounded-lg border border-gray-300 bg-white hover:border-gray-400 focus:border-brand focus:ring-2 focus:ring-brand/20 transition-all text-sm font-medium cursor-pointer">
                                <option value="20">20</option>
                                <option value="40">40</option>
                                <option value="80">80</option>
                            </select>
                        </div>
                    </div>

                    <!-- Results Stats -->
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-200">
                        <div class="text-sm text-gray-600">
                            <span class="font-semibold text-gray-900">{{ $templates->total() }}</span> {{ app()->getLocale() === 'de' ? 'Vorlagen gefunden' : 'templates found' }}
                            @if($search || !empty($selectedCategories) || !empty($selectedTags) || $onlyWithScreenshots || $onlyClassified)
                            <span class="text-gray-500">• {{ app()->getLocale() === 'de' ? 'Gefiltert von' : 'Filtered from' }} {{ $stats['total'] }} {{ app()->getLocale() === 'de' ? 'insgesamt' : 'total' }}</span>
                            @endif
                        </div>

                        <button wire:click="clearFilters"
                            class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors {{ ($search || !empty($selectedCategories) || !empty($selectedTags) || $onlyWithScreenshots || $onlyClassified) ? 'bg-brand text-white hover:bg-brand-dark' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">
                            {{ app()->getLocale() === 'de' ? 'Alle löschen' : 'Clear all' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Clean Filters Panel -->
            <div id="filters-panel" wire:key="filters-panel">
                @if($showFilters)
                <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6 mb-8">
                    <!-- Categories & Tags Layout -->
                    <div class="space-y-6">

                        <!-- Categories Section -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-brand flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">{{ app()->getLocale() === 'de' ? 'Kategorien' : 'Categories' }}</h3>
                                        <span class="text-xs text-gray-500">({{ count($selectedCategories) }}/{{ count($categories) }})</span>
                                    </div>
                                </div>
                                @if(!empty($selectedCategories))
                                <button wire:click="$set('selectedCategories', [])"
                                    class="px-3 py-1.5 rounded-lg text-xs bg-brand text-white hover:bg-brand-dark font-semibold transition-colors">
                                    {{ app()->getLocale() === 'de' ? 'Alle löschen' : 'Clear all' }}
                                </button>
                                @endif
                            </div>

                            <!-- Categories Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2">
                                @foreach($categories as $key => $category)
                                <button wire:click="toggleCategory('{{ $key }}')"
                                    class="px-3 py-2 text-sm font-medium border rounded-lg transition-colors text-left
                                        {{ in_array($key, $selectedCategories)
                                            ? 'border-brand bg-brand text-white'
                                            : 'border-gray-300 bg-white text-gray-700 hover:border-brand hover:bg-brand/5' }}">
                                    <div class="flex items-center justify-between">
                                        <span class="truncate">{{ $category }}</span>
                                        <div class="flex items-center ml-2 space-x-1">
                                            @if(($categoryCounts[$key] ?? 0) > 0)
                                            <span class="text-xs px-1.5 py-0.5 rounded {{ in_array($key, $selectedCategories) ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                                                {{ $categoryCounts[$key] }}
                                            </span>
                                            @endif
                                            @if(in_array($key, $selectedCategories))
                                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            @endif
                                        </div>
                                    </div>
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Tags Section -->
                        <div class="bg-white border border-gray-200 rounded-lg p-4 sm:p-6">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-brand flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">{{ app()->getLocale() === 'de' ? 'Features & Technologien' : 'Features & Technologies' }}</h3>
                                        <span class="text-xs text-gray-500">({{ count($selectedTags) }}/{{ count($tags) }})</span>
                                    </div>
                                </div>
                                @if(!empty($selectedTags))
                                <button wire:click="$set('selectedTags', [])"
                                    class="px-3 py-1.5 rounded-lg text-xs bg-brand text-white hover:bg-brand-dark font-semibold transition-colors">
                                    {{ app()->getLocale() === 'de' ? 'Alle löschen' : 'Clear all' }}
                                </button>
                                @endif
                            </div>

                            @php
                            $popularTags = ['woocommerce', 'responsive', 'elementor', 'contact_form', 'multilingual'];
                            $otherTags = array_diff($tags, $popularTags);
                            @endphp

                            <!-- Popular Tags -->
                            <div class="mb-4">
                                <div class="flex items-center mb-2">
                                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ app()->getLocale() === 'de' ? 'Beliebt' : 'Popular' }}</span>
                                    <div class="flex-1 h-px bg-gray-200 ml-2"></div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($popularTags as $tag)
                                    @if(in_array($tag, $tags))
                                    <button wire:click="toggleTag('{{ $tag }}')"
                                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium border rounded-lg transition-colors
                                                {{ in_array($tag, $selectedTags)
                                                    ? 'border-brand bg-brand text-white'
                                                    : 'border-gray-300 bg-white text-gray-700 hover:border-brand hover:bg-brand/5' }}">
                                        {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                        @if(in_array($tag, $selectedTags))
                                        <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                        @endif
                                    </button>
                                    @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- All Other Tags -->
                            <div>
                                <div class="flex items-center mb-2">
                                    <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ app()->getLocale() === 'de' ? 'Alle Features' : 'All Features' }}</span>
                                    <div class="flex-1 h-px bg-gray-200 ml-2"></div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($otherTags as $tag)
                                    <button wire:click="toggleTag('{{ $tag }}')"
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium border rounded-md transition-colors
                                            {{ in_array($tag, $selectedTags)
                                                ? 'border-brand bg-brand text-white'
                                                : 'border-gray-200 bg-gray-50 text-gray-600 hover:border-brand hover:bg-brand/10' }}">
                                        {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                    </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Filters Summary -->
                    @if(!empty($selectedCategories) || !empty($selectedTags))
                    <div class="bg-brand/5 border border-brand/20 rounded-lg p-4 mt-6">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                <span class="text-sm font-semibold text-brand">{{ app()->getLocale() === 'de' ? 'Aktive Filter' : 'Active Filters' }}</span>
                                <span class="text-xs text-gray-600">({{ count($selectedCategories) + count($selectedTags) }})</span>
                            </div>
                            <button wire:click="clearFilters"
                                class="text-xs font-semibold text-brand hover:text-brand-dark transition-colors">
                                {{ app()->getLocale() === 'de' ? 'Alle löschen' : 'Clear all' }}
                            </button>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @foreach($selectedCategories as $category)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs bg-brand text-white font-medium rounded-md">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                {{ \App\Helpers\CategoryHelper::getCategoryName($category, app()->getLocale()) }}
                                <button wire:click="removeCategory('{{ $category }}')" class="ml-1.5 hover:bg-white/20 rounded-full p-0.5 transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </span>
                            @endforeach

                            @foreach($selectedTags as $tag)
                            <span class="inline-flex items-center px-2.5 py-1 text-xs bg-gray-700 text-white font-medium rounded-md">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                <button wire:click="removeTag('{{ $tag }}')" class="ml-1.5 hover:bg-white/20 rounded-full p-0.5 transition-colors">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>

            <!-- Results -->
            @if($templates->count())
            <div class="container mx-auto" style="margin-bottom:50px" wire:key="templates-container">

                <!-- Grid View -->
                @if($view === 'grid')
                <!-- Clean Grid View -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-12" wire:key="grid-view">
                    @foreach($templates as $template)
                    <div wire:key="grid-{{ $template->id ?? $template->slug }}" class="group bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg hover:border-gray-300 transition-all duration-200">
                        <!-- Screenshot -->
                        <div class="aspect-video bg-gray-100 relative overflow-hidden">
                            @if($template->screenshot_url)
                            @php
                            $isCritical = $loop->first;
                            $isLocalShot = str_contains($template->screenshot_url, '/storage/screenshots/');
                            $slug = $template->slug;
                            $webpSet = [];
                            $pngSet = [];
                            if ($isLocalShot) {
                            foreach ([480,768,1024] as $w) {
                            $p = storage_path('app/public/screenshots/'.$slug.'-'.$w.'.webp');
                            if (file_exists($p)) {
                            $webpSet[] = asset('storage/screenshots/'.$slug.'-'.$w.'.webp')." {$w}w";
                            }
                            $pp = storage_path('app/public/screenshots/'.$slug.'-'.$w.'.png');
                            if (file_exists($pp)) {
                            $pngSet[] = asset('storage/screenshots/'.$slug.'-'.$w.'.png')." {$w}w";
                            }
                            }
                            }
                            @endphp
                            @if(!empty($webpSet) || !empty($pngSet))
                            <picture>
                                @if(!empty($webpSet))
                                <source type="image/webp" srcset="{{ implode(', ', $webpSet) }}"
                                    sizes="(min-width: 1280px) 25vw, (min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw">
                                @endif
                                @if(!empty($pngSet))
                                <source type="image/png" srcset="{{ implode(', ', $pngSet) }}"
                                    sizes="(min-width: 1280px) 25vw, (min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw">
                                @endif
                                <img src="{{ $template->screenshot_url }}" alt="{{ $template->name ?? $template->slug }}"
                                    class="screenshot-img w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                    decoding="async"
                                    loading="{{ $isCritical ? 'eager' : 'lazy' }}"
                                    fetchpriority="{{ $isCritical ? 'high' : 'low' }}">
                            </picture>
                            @else
                            <img src="{{ $template->screenshot_url }}" alt="{{ $template->name ?? $template->slug }}"
                                class="screenshot-img w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                decoding="async"
                                loading="{{ $isCritical ? 'eager' : 'lazy' }}"
                                fetchpriority="{{ $isCritical ? 'high' : 'low' }}">
                            @endif
                            @else
                            <div class="flex items-center justify-center h-full text-gray-400">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            @endif

                            <!-- Category Badge -->
                            @if($template->primary_category)
                            <div class="absolute top-2 left-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-brand text-white rounded-md">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    {{ \App\Helpers\CategoryHelper::getCategoryName($template->primary_category, app()->getLocale()) }}
                                </span>
                            </div>
                            @endif
                        </div>

                        <!-- Card Content -->
                        <div class="p-4 flex flex-col">
                            <!-- Title -->
                            <a href="{{ $template->demo_url }}" target="_blank" class="block mb-2">
                                <h3 class="text-base font-semibold text-gray-900 hover:text-brand transition-colors line-clamp-1">
                                    {{ $template->name ?? ucfirst(str_replace('-', ' ', $template->slug)) }}
                                </h3>
                            </a>

                            <!-- Description -->
                            <div class="mb-3">
                                @if($template->description_en || $template->description_de)
                                <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                                    {{ app()->getLocale() === 'de' ? $template->description_de : $template->description_en }}
                                </p>
                                @endif
                            </div>

                            <!-- Tags -->
                            <div class="mb-3">
                                @if($template->tags && count($template->tags) > 0)
                                @php
                                $visibleTags = array_slice($template->tags, 0, 3);
                                $remainingCount = count($template->tags) - 3;
                                @endphp

                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($visibleTags as $tag)
                                    <button wire:click="toggleTag('{{ $tag }}')"
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer {{ in_array($tag, $selectedTags) ? 'bg-brand text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                        {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                    </button>
                                    @endforeach

                                    @if($remainingCount > 0)
                                    <div class="relative group/tags">
                                        <button class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md bg-gray-200 text-gray-600 hover:bg-gray-300 transition-colors">
                                            +{{ $remainingCount }}
                                        </button>

                                        <!-- Tooltip -->
                                        <div class="absolute bottom-full left-0 mb-2 hidden group-hover/tags:block z-50">
                                            <div class="bg-gray-900 text-white text-xs py-2 px-3 rounded-md whitespace-nowrap">
                                                @foreach(array_slice($template->tags, 3) as $tag)
                                                <span class="inline-block mr-1 mb-1">{{ ucfirst(str_replace('_', ' ', $tag)) }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>

                            <!-- CTA Button -->
                            <div class="mt-auto pt-2">
                                <a href="{{ $template->demo_url }}" target="_blank"
                                    class="block text-center text-sm font-semibold py-2.5 px-4 rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-brand hover:text-white hover:border-brand transition-all">
                                    {{ app()->getLocale() === 'de' ? 'Demo ansehen' : 'View Demo' }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <!-- Clean List View -->
                <div class="space-y-4 mb-12" wire:key="list-view">
                    @foreach($templates as $template)
                    <div wire:key="list-{{ $template->id ?? $template->slug }}" class="group bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg hover:border-gray-300 transition-all duration-200">
                        <div class="flex flex-col sm:flex-row">
                            <!-- Screenshot -->
                            <div class="sm:w-80 aspect-video bg-gray-100 relative overflow-hidden">
                                @if($template->screenshot_url)
                                @php
                                $isCritical = $loop->first;
                                $isLocalShot = str_contains($template->screenshot_url, '/storage/screenshots/');
                                $slug = $template->slug;
                                $webpSet = [];
                                $pngSet = [];
                                if ($isLocalShot) {
                                foreach ([480,768,1024] as $w) {
                                $p = storage_path('app/public/screenshots/'.$slug.'-'.$w.'.webp');
                                if (file_exists($p)) {
                                $webpSet[] = asset('storage/screenshots/'.$slug.'-'.$w.'.webp')." {$w}w";
                                }
                                $pp = storage_path('app/public/screenshots/'.$slug.'-'.$w.'.png');
                                if (file_exists($pp)) {
                                $pngSet[] = asset('storage/screenshots/'.$slug.'-'.$w.'.png')." {$w}w";
                                }
                                }
                                }
                                @endphp
                                @if(!empty($webpSet) || !empty($pngSet))
                                <picture>
                                    @if(!empty($webpSet))
                                    <source type="image/webp" srcset="{{ implode(', ', $webpSet) }}"
                                        sizes="(min-width: 640px) 320px, 100vw">
                                    @endif
                                    @if(!empty($pngSet))
                                    <source type="image/png" srcset="{{ implode(', ', $pngSet) }}"
                                        sizes="(min-width: 640px) 320px, 100vw">
                                    @endif
                                    <img src="{{ $template->screenshot_url }}" alt="{{ $template->name ?? $template->slug }}"
                                        class="screenshot-img w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        decoding="async"
                                        loading="{{ $isCritical ? 'eager' : 'lazy' }}"
                                        fetchpriority="{{ $isCritical ? 'high' : 'low' }}">
                                </picture>
                                @else
                                <img src="{{ $template->screenshot_url }}" alt="{{ $template->name ?? $template->slug }}"
                                    class="screenshot-img w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                    decoding="async"
                                    loading="{{ $isCritical ? 'eager' : 'lazy' }}"
                                    fetchpriority="{{ $isCritical ? 'high' : 'low' }}">
                                @endif
                                @else
                                <div class="flex items-center justify-center h-full text-gray-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                @endif

                                <!-- Category Badge -->
                                @if($template->primary_category)
                                <div class="absolute top-2 left-2">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold bg-brand text-white rounded-md">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        {{ \App\Helpers\CategoryHelper::getCategoryName($template->primary_category, app()->getLocale()) }}
                                    </span>
                                </div>
                                @endif
                            </div>

                            <!-- Content -->
                            <div class="flex-1 p-4 sm:p-6">
                                <div class="flex items-start justify-between mb-3">
                                    <a href="{{ $template->demo_url }}" target="_blank">
                                        <h3 class="text-lg font-semibold text-gray-900 hover:text-brand transition-colors">
                                            {{ $template->name ?? ucfirst(str_replace('-', ' ', $template->slug)) }}
                                        </h3>
                                    </a>

                                    <a href="{{ $template->demo_url }}" target="_blank"
                                        class="bg-brand hover:bg-brand-dark text-white px-4 py-2 text-sm font-semibold rounded-lg transition-colors whitespace-nowrap ml-4">
                                        {{ app()->getLocale() === 'de' ? 'Demo ansehen' : 'View Demo' }}
                                    </a>
                                </div>

                                @if($template->description_en || $template->description_de)
                                <p class="text-sm text-gray-600 mb-3 leading-relaxed">
                                    {{ app()->getLocale() === 'de' ? $template->description_de : $template->description_en }}
                                </p>
                                @endif

                                <!-- Tags -->
                                @if($template->tags && count($template->tags) > 0)
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($template->tags as $tag)
                                    <button wire:click="toggleTag('{{ $tag }}')"
                                        class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-md transition-colors cursor-pointer {{ in_array($tag, $selectedTags) ? 'bg-brand text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                                        {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                    </button>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Pagination -->
                <div class="flex justify-center" style="margin-top: 50px" wire:key="pagination">
                    <div class="pagination-wrapper">
                        {{ $templates->links() }}
                    </div>
                </div>

                <style>
                    /* Hint the browser about offscreen image size to reduce work */
                    .screenshot-img[loading="lazy"] {
                        content-visibility: auto;
                        contain-intrinsic-size: 400px 225px;
                        /* ~16:9 placeholder */
                    }

                    /* Pagination layout: keep elements together with small gap */
                    .pagination-wrapper nav {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        gap: 16px !important;
                        flex-wrap: wrap !important;
                    }

                    /* Desktop container (results text + numbered buttons) */
                    .pagination-wrapper .hidden.sm\:flex-1.sm\:flex.sm\:items-center.sm\:justify-between,
                    .pagination-wrapper .hidden.sm\:flex-1.sm\:flex.sm\:items-center.md\:justify-between {
                        width: auto !important;
                        flex-direction: row !important;
                        align-items: center !important;
                        justify-content: center !important;
                        gap: 16px !important;
                        flex-wrap: wrap !important;
                    }

                    /* Mobile container (Prev / Next) */
                    .pagination-wrapper .flex.justify-between.flex-1.sm\:hidden {
                        justify-content: center !important;
                        gap: 12px !important;
                    }

                    /* Simple hover improvements for pagination links - match category red */
                    .pagination-wrapper a {
                        transition: all 0.2s ease !important;
                        cursor: pointer !important;
                    }

                    /* Hover: faint red like category hover */
                    .pagination-wrapper nav a.relative.inline-flex:hover {
                        background-color: rgba(213, 55, 65, 0.06) !important;
                        /* ~bg-[#D53741]/5 */
                        border-color: rgba(213, 55, 65, 0.3) !important;
                        color: #374151 !important;
                        /* text-gray-700 */
                        text-decoration: none !important;
                    }

                    /* Focus: remove blue ring and use red */
                    .pagination-wrapper nav a.relative.inline-flex:focus {
                        outline: none !important;
                        box-shadow: 0 0 0 3px rgba(213, 55, 65, 0.25) !important;
                        border-color: rgba(213, 55, 65, 0.4) !important;
                    }

                    /* Pressed state */
                    .pagination-wrapper nav a.relative.inline-flex:active {
                        background-color: #D53741 !important;
                        color: #ffffff !important;
                        border-color: #D53741 !important;
                    }

                    /* Current page: solid category red */
                    .pagination-wrapper [aria-current="page"]>span {
                        background-color: #D53741 !important;
                        color: #ffffff !important;
                        border-color: #D53741 !important;
                    }

                    /* Optimize image rendering for crisp text in screenshots */
                    img[src*="/screenshots/"] {
                        image-rendering: auto;
                        -webkit-backface-visibility: hidden;
                        backface-visibility: hidden;
                        -webkit-transform: translate3d(0, 0, 0);
                        transform: translate3d(0, 0, 0);
                        -webkit-font-smoothing: subpixel-antialiased;
                        -moz-osx-font-smoothing: auto;
                    }
                </style>
            </div>

            @else
            <!-- Clean Empty State -->
            <div class="text-center py-16">
                <div class="bg-white border border-gray-200 rounded-lg p-8 max-w-md mx-auto">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-lg bg-gray-100 flex items-center justify-center">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                        {{ app()->getLocale() === 'de' ? 'Keine Vorlagen gefunden' : 'No templates found' }}
                    </h3>
                    <p class="text-gray-600 mb-6 text-sm">
                        {{ app()->getLocale() === 'de' ? 'Versuchen Sie, Ihre Suchkriterien zu ändern.' : 'Try adjusting your search criteria.' }}
                    </p>
                    <button wire:click="clearFilters"
                        class="bg-brand hover:bg-brand-dark text-white px-6 py-2.5 rounded-lg font-semibold transition-colors">
                        {{ app()->getLocale() === 'de' ? 'Filter zurücksetzen' : 'Clear filters' }}
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>


    <!-- Modern CTA Section -->
    <section class="relative bg-gradient-to-r from-[#fff1ed] to-[#ffb0b5] py-24 mt-0 overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <svg class="absolute w-full h-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="cta-grid" width="50" height="50" patternUnits="userSpaceOnUse">
                        <circle cx="25" cy="25" r="1.5" fill="#D53741"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#cta-grid)"/>
            </svg>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute top-10 left-10 w-20 h-20 bg-white/20 rounded-full blur-2xl"></div>
        <div class="absolute bottom-10 right-10 w-32 h-32 bg-brand/20 rounded-full blur-3xl"></div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/80 backdrop-blur-sm rounded-full text-sm font-semibold text-gray-800 mb-6 shadow-md">
                    <svg class="w-4 h-4 text-brand" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    {{ app()->getLocale() === 'de' ? 'Starten Sie Jetzt' : 'Get Started Now' }}
                </div>

                <!-- Main Headline -->
                <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                    {{ app()->getLocale() === 'de'
                        ? 'Verwandeln Sie Ihre Vision in'
                        : 'Transform Your Vision Into'
                    }}
                    <span class="block mt-2 bg-gradient-to-r from-brand via-rose-600 to-pink-600 bg-clip-text text-transparent">
                        {{ app()->getLocale() === 'de' ? 'Digitale Realität' : 'Digital Reality' }}
                    </span>
                </h2>

                <!-- Subheadline -->
                <p class="text-xl md:text-2xl text-gray-700 mb-10 max-w-3xl mx-auto leading-relaxed">
                    {{ app()->getLocale() === 'de'
                        ? 'Wählen Sie aus über 1500 professionellen WordPress-Templates oder lassen Sie uns Ihre individuelle Website erstellen.'
                        : 'Choose from over 1500 professional WordPress templates or let us build your custom website.'
                    }}
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
                    <!-- Primary CTA -->
                    <a href="https://metanow.dev/contact"
                        class="group relative inline-flex items-center justify-center px-10 py-5 bg-gradient-to-r from-brand to-rose-600 hover:from-brand-dark hover:to-rose-700 text-white text-lg font-bold rounded-2xl transition-all duration-300 shadow-2xl shadow-brand/40 hover:shadow-brand/60 hover:scale-105 overflow-hidden">
                        <!-- Shimmer effect -->
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                        <svg class="w-6 h-6 mr-3 relative" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span class="relative">{{ app()->getLocale() === 'de' ? 'Kostenlose Beratung' : 'Free Consultation' }}</span>
                        <svg class="w-5 h-5 ml-3 group-hover:translate-x-1 transition-transform relative" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </a>

                    <!-- Secondary CTA -->
                    <a href="#templates-grid"
                        onclick="event.preventDefault(); document.getElementById('templates-grid').scrollIntoView({behavior: 'smooth'})"
                        class="group inline-flex items-center justify-center px-10 py-5 bg-white/90 backdrop-blur-md border-2 border-gray-300 hover:bg-white hover:border-brand text-gray-900 text-lg font-bold rounded-2xl transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105">
                        <svg class="w-6 h-6 mr-3 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ app()->getLocale() === 'de' ? 'Templates Ansehen' : 'View Templates' }}</span>
                    </a>
                </div>

                <!-- Trust Indicators -->
                <div class="flex flex-wrap items-center justify-center gap-8 text-sm">
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">{{ app()->getLocale() === 'de' ? 'Keine Kreditkarte nötig' : 'No Credit Card' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">{{ app()->getLocale() === 'de' ? 'Sofortiger Zugang' : 'Instant Access' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-gray-700">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-semibold">{{ app()->getLocale() === 'de' ? 'Premium Support' : 'Premium Support' }}</span>
                    </div>
                </div>

                <!-- Social Proof -->
                <div class="mt-12 pt-8 border-t border-gray-300/50">
                    <p class="text-sm text-gray-600 mb-4 font-medium">
                        {{ app()->getLocale() === 'de' ? 'Vertraut von über 500+ Unternehmen' : 'Trusted by 500+ businesses worldwide' }}
                    </p>
                    <div class="flex items-center justify-center gap-1">
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="ml-2 text-gray-700 font-semibold">4.9/5</span>
                        <span class="ml-1 text-gray-600 text-sm">({{ app()->getLocale() === 'de' ? '200+ Bewertungen' : '200+ reviews' }})</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Clean Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-6">

                <!-- Copyright Section -->
                <div class="text-center md:text-left">
                    <div class="flex items-center justify-center md:justify-start gap-2 mb-2">
                        <img src="{{ asset('storage/img/logo/Metanow.webp') }}" width="90" alt="Metanow Logo">
                    </div>
                    <p class="text-sm text-gray-500">
                        &copy; {{ date('Y') }} Metanow | {{ app()->getLocale() === 'de' ? 'Alle Rechte vorbehalten.' : 'All rights reserved.' }}
                    </p>
                </div>

                <!-- Navigation & Language -->
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <!-- Language Selector -->
                    <div class="flex items-center gap-2 bg-gray-50 rounded-lg px-2 py-1.5">
                        <a href="{{ route('templates.index') }}"
                            class="flex items-center justify-center px-2.5 py-1.5 rounded-md transition-colors @if(app()->getLocale() === 'en') bg-brand text-white @else text-gray-600 hover:bg-white @endif">
                            <span class="fi fi-gb" style="width: 20px; height: 15px;"></span>
                        </a>
                        <a href="{{ route('templates.index.de') }}"
                            class="flex items-center justify-center px-2.5 py-1.5 rounded-md transition-colors @if(app()->getLocale() === 'de') bg-brand text-white @else text-gray-600 hover:bg-white @endif">
                            <span class="fi fi-de" style="width: 20px; height: 15px;"></span>
                        </a>
                    </div>

                    <!-- Legal Links -->
                    <div class="flex items-center gap-4 text-sm">
                        <a href="{{ app()->getLocale() === 'de' ? 'https://www.metanow.dev/de/imprint' : 'https://www.metanow.dev/en/imprint' }}" target="_blank" class="text-gray-600 hover:text-brand transition-colors">
                            {{ app()->getLocale() === 'de' ? 'Impressum' : 'Imprint' }}
                        </a>
                        <span class="text-gray-300">•</span>
                        <a href="{{ app()->getLocale() === 'de' ? 'https://www.metanow.dev/de/privacy-policy' : 'https://www.metanow.dev/en/privacy-policy' }}" target="_blank" class="text-gray-600 hover:text-brand transition-colors">
                            {{ app()->getLocale() === 'de' ? 'Datenschutz' : 'Privacy' }}
                        </a>
                        <span class="text-gray-300">•</span>
                        <a href="{{ app()->getLocale() === 'de' ? 'https://www.metanow.dev/de/terms-of-service' : 'https://www.metanow.dev/en/terms-of-service' }}" target="_blank" class="text-gray-600 hover:text-brand transition-colors">
                            {{ app()->getLocale() === 'de' ? 'AGB' : 'Terms' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</div>