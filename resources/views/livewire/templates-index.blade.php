<div class="min-h-screen overflow-x-hidden">
    <!-- Background Image wrapper that extends into next section -->
    <div class="relative" style="padding-bottom: 200px;">
        <!-- Background Image with Gradient Fade -->
        <div class="absolute inset-0 z-0" style="height: 120%;">
            <img src="{{ asset('img/background.png') }}" alt="Hero Background" class="w-full h-full object-cover" >
            <!-- Gradient overlay that gradually fades from top to bottom -->
            <div 
                class="absolute inset-0 bg-gradient-to-b from-transparent from-0% via-white/40 via-40% via-white/70 via-70% to-white to-100%" 
                style="background-image:linear-gradient(180deg, #ffffff7a, #ffffff 85%);z-index:2">
            </div>
        </div>

        <!-- Hero Section with Background -->
        <div class="relative">
            <!-- Header with transparent background -->
            <header class="relative z-40 bg-transparent border-b border-white/30 backdrop-blur-sm" >
                <div class="container mx-auto px-2 sm:px-4 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <div class="text-2xl font-bold text-[#D53741]">
                            <a href="https://metanow.dev">
                            <img src="{{ asset('storage/img/logo/Metanow.webp') }}" width="120" class="sm:w-[180px]"  alt="Metanow Logo">
                            </a>
                        </div>
                    </div>

                    <div class="flex items-center space-x-1 sm:space-x-4">
                        <div class="flex space-x-3">
                                    <a href="{{ route('templates.index') }}"
                                       class="flex items-center space-x-2 px-3 py-1 transition-all duration-200 @if(app()->getLocale() === 'en') bg-red-50 text-[#B12A31] @else text-gray-400 hover:text-gray-900 hover:bg-gray-200/50 @endif">
                                        <span class="fi fi-gb" style="width: 20px; height: 15px;"></span>
                                    </a>
                                    <a href="{{ route('templates.index.de') }}"
                                       class="flex items-center space-x-2 px-3 py-1 transition-all duration-200 @if(app()->getLocale() === 'de') bg-red-50 text-[#B12A31] @else text-gray-400 hover:text-gray-900 hover:bg-gray-200/50 @endif">
                                        <span class="fi fi-de" style="width: 20px; height: 15px;"></span>
                                    </a>
                                </div>
                        <a href="https://metanow.dev"
                            class="bg-[#D53741] hover:bg-[#B12A31] text-white px-3 py-2 text-xs sm:text-sm sm:px-6 font-medium transition-colors duration-200 whitespace-nowrap">
                            <span class="hidden sm:inline">Back to Metanow</span>
                            <span class="sm:hidden">Back to Metanow</span>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Hero Content -->
            <div class="relative z-10" style="padding-top: 100px; padding-bottom: 10px;">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mx-auto" style="max-width: 1400px;">
                        <!-- Main Title -->
                        <h1 class="font-extrabold tracking-tight mb-10" style="font-size: clamp(2.5rem, 8vw, 4.2rem); line-height: 1.1;">
                            <span class="block text-[#D53741]">{{ app()->getLocale() === 'de' ? 'Handgefertigte WordPress Projekte' : 'Handcrafted WordPress Projects' }}</span>
                        </h1>

                        <!-- Description -->
                        <p class="text-gray-700 mx-auto leading-relaxed" style="font-size: clamp(1.125rem, 2.5vw, 1.5rem); max-width: 1100px;">
                            {{ app()->getLocale() === 'de' ? 'Entdecken Sie unsere Sammlung professionell gestalteter WordPress-Vorlagen für Ihr nächstes Projekt' : 'Discover our collection of professionally designed WordPress templates for your next project' }}
                        </p>

                        <!-- Stats Counter -->
                        <div class="mt-8 inline-flex items-center gap-3 bg-white/80 backdrop-blur-sm px-6 py-3  shadow-lg border border-gray-200">
                            <div class="flex items-center gap-2">
                                <span class="text-3xl font-bold text-[#D53741]">{{ $stats['total'] ?? count($templates) }}</span>
                                <span class="text-gray-600 font-medium">{{ app()->getLocale() === 'de' ? 'aus 1500+ Projekten' : 'from 1500+ projects' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div >
        <div class="container mx-auto px-2 sm:px-4 ">

            <!-- Enhanced Search & Controls Bar -->
        <div class="mb-6 relative z-10 bg-white w-full">
            <div class="bg-white shadow-sm border border-gray-200/50 p-6">
                <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
                    
                    <!-- Search Section -->
                    <div class="flex-1 max-w-2xl">
                        <div class="relative">
                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="search"
                                placeholder="{{ app()->getLocale() === 'de' ? 'Nach Vorlagen suchen...' : 'Search templates...' }}"
                                class="w-full pl-10 pr-4 py-3 text-base border-gray-300 bg-white focus:ring-2 focus:ring-[#D53741] focus:border-blue-500 transition-all duration-200 shadow-sm">
                            
                            @if($search)
                                <button wire:click="$set('search', '')" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Controls Section -->
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Filter Toggle -->
                        <button wire:click="toggleFilters"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="flex items-center space-x-2 px-4 py-2 font-medium transition-all duration-200 {{ $showFilters ? 'bg-red-50 text-[#D53741] border border-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
                            </svg>
                            <span>{{ $this->filterToggleText }}</span>
                            <span wire:loading wire:target="toggleFilters" class="ml-2">
                                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                        </button>

                        <!-- View Toggle -->
                        <div class="flex items-center bg-gray-100 p-1">
                            <button wire:click="setView('grid')" 
                                class="flex items-center space-x-1 px-3 py-1.5 font-medium transition-all duration-200 {{ $view === 'grid' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="hidden sm:inline">Grid</span>
                            </button>
                            <button wire:click="setView('list')" 
                                class="flex items-center space-x-1 px-3 py-1.5 font-medium transition-all duration-200 {{ $view === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span class="hidden sm:inline">List</span>
                            </button>
                        </div>

                        <!-- Sort Dropdown -->
                        <select wire:model.live="sort" 
                            class="px-4 py-2 border-0 bg-gray-50 focus:ring-2 focus:ring-[#D53741]/20 focus:bg-white transition-all duration-200 text-sm font-medium">
                            <option value="recent">{{ app()->getLocale() === 'de' ? 'Neueste' : 'Recent' }}</option>
                            <option value="az">A → Z</option>
                            <option value="za">Z → A</option>
                            <option value="category">{{ app()->getLocale() === 'de' ? 'Kategorie' : 'Category' }}</option>
                        </select>

                        <!-- Per Page -->
                        <select wire:model.live="perPage" 
                            class="px-3 py-2 border-0 bg-gray-50 focus:ring-2 focus:ring-[#D53741]/20 focus:bg-white transition-all duration-200 text-sm font-medium">
                            <option value="20">20</option>
                            <option value="40">40</option>
                            <option value="80">80</option>
                        </select>
                    </div>
                </div>

                <!-- Results Stats -->
                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                    <div class="text-sm text-gray-600">
                        {{ $templates->total() }} {{ app()->getLocale() === 'de' ? 'Vorlagen gefunden' : 'templates found' }}
                        @if($search || !empty($selectedCategories) || !empty($selectedTags) || $onlyWithScreenshots || $onlyClassified)
                            • {{ app()->getLocale() === 'de' ? 'Gefiltert von' : 'Filtered from' }} {{ $stats['total'] }} {{ app()->getLocale() === 'de' ? 'insgesamt' : 'total' }}
                        @endif
                    </div>
                    
                    <button wire:click="clearFilters"
                        class="px-3 py-1 {{ ($search || !empty($selectedCategories) || !empty($selectedTags) || $onlyWithScreenshots || $onlyClassified) ? 'bg-red-50 hover:bg-red-100 text-red-600' : 'bg-gray-50 hover:bg-gray-100 text-gray-400' }} transition-all duration-200 text-sm font-medium">
                        {{ app()->getLocale() === 'de' ? 'Alle löschen' : 'Clear all' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Advanced Filters Panel -->
        <div id="filters-panel" wire:key="filters-panel">
        @if($showFilters)
            <div class="bg-white/80 backdrop-blur-sm border border-gray-200/50 p-6 mb-8 shadow-sm">
                

                <!-- Professional Categories & Tags Layout -->
                <div class="space-y-8">
                    
                    <!-- Categories Section -->
                    <div class="bg-gradient-to-r from-gray-50 to-white border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                               
                                <h3 class="text-lg font-semibold text-gray-900">{{ app()->getLocale() === 'de' ? 'Kategorien' : 'Categories' }}</h3>
                                <span class="text-sm text-gray-500">({{ count($selectedCategories) }}/{{ count($categories) }})</span>
                            </div>
                            @if(!empty($selectedCategories))
                                <button wire:click="$set('selectedCategories', [])" 
                                    class="text-sm text-[#D53741] hover:text-[#B12A31] font-medium transition-colors">
                                    {{ app()->getLocale() === 'de' ? 'Alle löschen' : 'Clear all' }}
                                </button>
                            @endif
                        </div>

                        <!-- Categories Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                            @foreach($categories as $key => $category)
                                <button wire:click="toggleCategory('{{ $key }}')" 
                                    class="group relative px-4 py-3 text-sm font-medium  border-2 transition-all duration-200 text-left
                                        {{ in_array($key, $selectedCategories) 
                                            ? 'border-[#D53741] bg-[#D53741] text-white shadow-md' 
                                            : 'border-gray-200 bg-white text-gray-700 hover:border-[#D53741]/30 hover:bg-[#D53741]/5' }}">
                                    <div class="flex items-center justify-between">
                                        <span class="truncate">{{ $category }}</span>
                                        <div class="flex items-center ml-2 space-x-1">
                                            @if(($categoryCounts[$key] ?? 0) > 0)
                                                <span class="text-xs px-1.5 py-0.5 rounded {{ in_array($key, $selectedCategories) ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-600' }}">
                                                    {{ $categoryCounts[$key] }}
                                                </span>
                                            @endif
                                            @if(in_array($key, $selectedCategories))
                                                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tags Section -->
                    <div class="bg-gradient-to-r from-white to-gray-50  border border-gray-100 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                
                                <h3 class="text-lg font-semibold text-gray-900">{{ app()->getLocale() === 'de' ? 'Features & Technologien' : 'Features & Technologies' }}</h3>
                                <span class="text-sm text-gray-500">({{ count($selectedTags) }}/{{ count($tags) }})</span>
                            </div>
                            @if(!empty($selectedTags))
                                <button wire:click="$set('selectedTags', [])" 
                                    class="text-sm text-[#D53741] hover:text-[#B12A31] font-medium transition-colors">
                                    {{ app()->getLocale() === 'de' ? 'Alle löschen' : 'Clear all' }}
                                </button>
                            @endif
                        </div>

                        <!-- Popular Tags First -->
                        @php
                            $popularTags = ['woocommerce', 'responsive', 'elementor', 'contact_form', 'multilingual'];
                            $otherTags = array_diff($tags, $popularTags);
                        @endphp

                        <!-- Popular Tags -->
                        <div class="mb-6">
                            <div class="flex items-center mb-3">
                                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ app()->getLocale() === 'de' ? 'Beliebt' : 'Popular' }}</span>
                                <div class="flex-1 h-px bg-gray-200 ml-3"></div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($popularTags as $tag)
                                    @if(in_array($tag, $tags))
                                        <button wire:click="toggleTag('{{ $tag }}')" 
                                            class="inline-flex items-center px-3 py-2 text-sm font-medium border transition-all duration-200
                                                {{ in_array($tag, $selectedTags) 
                                                    ? 'border-[#D53741] bg-[#D53741] text-white shadow-sm' 
                                                    : 'border-gray-300 bg-white text-gray-700 hover:border-[#D53741] hover:bg-[#D53741]/5' }}">
                                            {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                            @if(in_array($tag, $selectedTags))
                                                <svg class="w-3 h-3 ml-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                            @endif
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- All Other Tags -->
                        <div>
                            <div class="flex items-center mb-3">
                                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ app()->getLocale() === 'de' ? 'Alle Features' : 'All Features' }}</span>
                                <div class="flex-1 h-px bg-gray-200 ml-3"></div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($otherTags as $tag)
                                    <button wire:click="toggleTag('{{ $tag }}')" 
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium border transition-all duration-200
                                            {{ in_array($tag, $selectedTags) 
                                                ? 'border-[#D53741] bg-[#D53741] text-white' 
                                                : 'border-gray-200 bg-gray-50 text-gray-600 hover:border-[#D53741] hover:bg-[#D53741]/10' }}">
                                        {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clean Active Filters Summary -->
                @if(!empty($selectedCategories) || !empty($selectedTags))
                    <div class="bg-gradient-to-r from-[#D53741]/5 to-[#B12A31]/5 border border-[#D53741]/20 p-4 mt-6">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-[#D53741]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                <span class="font-semibold text-[#D53741]">{{ app()->getLocale() === 'de' ? 'Aktive Filter' : 'Active Filters' }}</span>
                                <span class="text-sm text-gray-600">({{ count($selectedCategories) + count($selectedTags) }})</span>
                            </div>
                            <button wire:click="clearFilters" 
                                class="text-sm font-medium text-[#D53741] hover:text-[#B12A31] transition-colors">
                                {{ app()->getLocale() === 'de' ? 'Alle löschen' : 'Clear all' }}
                            </button>
                        </div>
                        
                        <div class="flex flex-wrap gap-2">
                            @foreach($selectedCategories as $category)
                                <span class="inline-flex items-center px-3 py-1.5 text-sm bg-[#D53741] text-white font-medium shadow-sm">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    {{ \App\Helpers\CategoryHelper::getCategoryName($category, app()->getLocale()) }}
                                    <button wire:click="removeCategory('{{ $category }}')" class="ml-2 hover:bg-white/20 p-0.5 transition-colors">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </span>
                            @endforeach

                            @foreach($selectedTags as $tag)
                                <span class="inline-flex items-center px-3 py-1.5 text-sm bg-gray-600 text-white font-medium">
                                    <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                    <button wire:click="removeTag('{{ $tag }}')" class="ml-2 hover:bg-white/20 p-0.5 transition-colors">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
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
            <div class="container mx-auto" style="margin-bottom:50px">
                
                <!-- Grid View -->
            @if($view === 'grid')
                <!-- Grid View -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 mb-12" style="gap: 20px;">
                    @foreach($templates as $template)
                        <div wire:key="grid-{{ $template->id ?? $template->slug }}" class="group bg-white shadow-sm border border-gray-200/50 overflow-hidden hover:shadow-xl hover:shadow-gray-200/50 px-2 py-2 transition-all duration-300 hover:-translate-y-1">
                            <!-- Screenshot -->
                            <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
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
                                            class="screenshot-img w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                            decoding="async"
                                            loading="{{ $isCritical ? 'eager' : 'lazy' }}"
                                            fetchpriority="{{ $isCritical ? 'high' : 'low' }}">
                                    @endif
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-400">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif

                                <!-- Category Badge -->
                                @if($template->primary_category)
                                    <div class="absolute top-2 left-2">
                                        <span class="inline-block px-1 py-0 text-sm font-bold bg-gradient-to-r from-[#D53741] to-[#B12A31] text-white shadow-lg border-2 border-white/20 backdrop-blur-sm">
                                            {{ \App\Helpers\CategoryHelper::getCategoryName($template->primary_category, app()->getLocale()) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Content with Uniform Height -->
                            <div class="px-6 py-4 flex flex-col h-56"> <!-- MODIFIED: Increased height for uniformity -->
                                <!-- Title (Fixed Height) -->
                                <a href="{{ $template->demo_url }}" target="_blank">
                                <h3 class="font-bold text-gray-900 mb-2 text-lg line-clamp-1 group-hover:text-[#D53741] transition-colors duration-200 min-h-[28px]">
                                    {{ $template->name ?? ucfirst(str_replace('-', ' ', $template->slug)) }}
                                </h3>
                                </a>

                                <!-- Description (Fixed Height) -->
                                <div class="mb-4 min-h-[40px]">
                                    @if($template->description_en || $template->description_de)
                                        <p class="text-sm text-gray-600 line-clamp-2 leading-relaxed">
                                            {{ app()->getLocale() === 'de' ? $template->description_de : $template->description_en }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Tags with Overflow Handling (Flexible Height) -->
                                <div class="flex-1 mb-3">
                                    @if($template->tags && count($template->tags) > 0)
                                        @php
                                            $visibleTags = array_slice($template->tags, 0, 4);
                                            $remainingCount = count($template->tags) - 4;
                                        @endphp
                                        
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($visibleTags as $tag)
                                                <button wire:click="toggleTag('{{ $tag }}')"
                                                    class="inline-block px-2.5 py-1 text-xs font-medium transition-all duration-200 cursor-pointer {{ in_array($tag, $selectedTags) ? 'bg-gradient-to-r from-[#D53741] to-[#B12A31] text-white shadow-sm' : 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700 hover:from-gray-200 hover:to-gray-100 border border-gray-200/50' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                                </button>
                                            @endforeach
                                            
                                            @if($remainingCount > 0)
                                                <div class="relative group/tags">
                                                    <button class="inline-block px-2.5 py-1 text-xs font-medium bg-gray-200 text-gray-600 hover:bg-gray-300 transition-all duration-200">
                                                        +{{ $remainingCount }}
                                                    </button>
                                                    
                                                    <!-- Tooltip with remaining tags -->
                                                    <div class="absolute bottom-full left-0 mb-2 hidden group-hover/tags:block z-50">
                                                        <div class="bg-black text-white text-xs py-2 px-3 whitespace-nowrap shadow-lg">
                                                            @foreach(array_slice($template->tags, 4) as $tag)
                                                                <span class="inline-block mr-2">{{ ucfirst(str_replace('_', ' ', $tag)) }}</span>
                                                            @endforeach
                                                            <div class="absolute top-full left-4 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-black"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <!-- View Demo Button (Fixed at Bottom) -->
                                <div class="mx-auto mt-auto pt-1 px-0 w-full">
                                    <a href="{{ $template->demo_url }}" target="_blank"
                                        class="block text-center text-sm font-medium text-gray-600 hover:text-white hover:bg-[#D53741] py-2 px-4 border border-transparent hover:border-[#D53741] transition-all duration-200 underline decoration-gray-400 hover:decoration-white decoration-2 underline-offset-4">
                                        {{ app()->getLocale() === 'de' ? 'Demo ansehen' : 'View Demo' }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- List View -->
                <div class="space-y-4 mb-12">
                    @foreach($templates as $template)
                        <div wire:key="list-{{ $template->id ?? $template->slug }}" class="group bg-white shadow-sm border border-gray-200/50 overflow-hidden hover:shadow-lg transition-all duration-300">
                            <div class="flex flex-col sm:flex-row">
                                <!-- Screenshot -->
                                <div class="sm:w-96 aspect-video bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
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
                                                class="screenshot-img w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
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
                                        <div class="absolute top-3 left-3">
                                            <span class="inline-block px-3 py-1.5 text-xs font-bold bg-gradient-to-r from-[#D53741] to-[#B12A31] text-white shadow-lg">
                                                {{ \App\Helpers\CategoryHelper::getCategoryName($template->primary_category, app()->getLocale()) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex-1 p-6">
                                    <div class="flex items-start justify-between mb-3">
                                        <a href="{{ $template->demo_url }}" target="_blank">
                                            <h3 class="font-bold text-gray-900 text-xl group-hover:text-[#D53741] transition-colors duration-200">
                                                {{ $template->name ?? ucfirst(str_replace('-', ' ', $template->slug)) }}
                                            </h3>
                                        </a>
                                        
                                        <a href="{{ $template->demo_url }}" target="_blank"
                                            class="bg-[#D53741] hover:bg-[#B12A31] text-white px-4 py-2 text-sm font-medium transition-colors duration-200 underline decoration-white decoration-2 underline-offset-4">
                                            {{ app()->getLocale() === 'de' ? 'Demo ansehen' : 'View Demo' }}
                                        </a>
                                    </div>

                                    @if($template->description_en || $template->description_de)
                                        <p class="text-gray-600 mb-4 leading-relaxed">
                                            {{ app()->getLocale() === 'de' ? $template->description_de : $template->description_en }}
                                        </p>
                                    @endif

                                    <!-- Tags -->
                                    @if($template->tags && count($template->tags) > 0)
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            @foreach($template->tags as $tag)
                                                <button wire:click="toggleTag('{{ $tag }}')"
                                                    class="inline-block px-3 py-1 text-sm font-medium transition-all duration-200 cursor-pointer {{ in_array($tag, $selectedTags) ? 'bg-gradient-to-r from-[#D53741] to-[#B12A31] text-white shadow-sm' : 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700 hover:from-gray-200 hover:to-gray-100 border border-gray-200/50' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($template->active_theme)
                                        <div class="text-sm text-gray-500">
                                            <span class="font-medium">{{ app()->getLocale() === 'de' ? 'Theme:' : 'Theme:' }}</span>
                                            {{ $template->active_theme }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Pagination -->
            <div class="flex justify-center" style="margin-top: 50px">
                <div class="pagination-wrapper">
                    {{ $templates->links() }}
                </div>
            </div>
            
            <style>
                /* Hint the browser about offscreen image size to reduce work */
                .screenshot-img[loading="lazy"] {
                    content-visibility: auto;
                    contain-intrinsic-size: 400px 225px; /* ~16:9 placeholder */
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
                    background-color: rgba(213, 55, 65, 0.06) !important; /* ~bg-[#D53741]/5 */
                    border-color: rgba(213, 55, 65, 0.3) !important;
                    color: #374151 !important; /* text-gray-700 */
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
                .pagination-wrapper [aria-current="page"] > span {
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
            
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="bg-white/50 backdrop-blur-sm p-12 max-w-md mx-auto">
                    <svg class="mx-auto h-16 w-16 text-gray-300 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">
                        {{ app()->getLocale() === 'de' ? 'Keine Vorlagen gefunden' : 'No templates found' }}
                    </h3>
                    <p class="text-gray-500 mb-6">
                        {{ app()->getLocale() === 'de' ? 'Versuchen Sie, Ihre Suchkriterien zu ändern.' : 'Try adjusting your search criteria.' }}
                    </p>
                    <button wire:click="clearFilters"
                        class="bg-[#D53741] hover:bg-[#B12A31] text-white px-6 py-3 font-medium transition-colors duration-200">
                        {{ app()->getLocale() === 'de' ? 'Filter zurücksetzen' : 'Clear filters' }}
                    </button>
                </div>
            </div>
        @endif
        </div>
        <!-- Call to Action Section -->
        <section class="relative overflow-hidden bg-gradient-to-br from-gray-900 via-[#B12A31] to-[#D53741] text-white mb-8 mt-16 py-20">
            <!-- Decorative elements -->
            <div class="absolute inset-0 overflow-hidden opacity-10">
                <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 w-80 h-80 bg-white rounded-full blur-3xl"></div>
            </div>

            <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Side - Visual Element -->
                    <div class="hidden lg:flex flex-col space-y-6">
                        <!-- Decorative cards showing benefits -->
                        <div class="bg-white/10 backdrop-blur-sm border border-white/20  p-6 transform hover:scale-105 transition-transform duration-300">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-white/20  flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1">{{ app()->getLocale() === 'de' ? 'Schnelle Lieferung' : 'Fast Delivery' }}</h3>
                                    <p class="text-white/80 text-sm">{{ app()->getLocale() === 'de' ? 'Ihr Projekt in Rekordzeit umgesetzt' : 'Your project delivered in record time' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm border border-white/20 p-6 transform hover:scale-105 transition-transform duration-300">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-white/20  flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1">{{ app()->getLocale() === 'de' ? 'Premium Qualität' : 'Premium Quality' }}</h3>
                                    <p class="text-white/80 text-sm">{{ app()->getLocale() === 'de' ? 'Professionelle Designs nach Maß' : 'Professional custom-made designs' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-6 transform hover:scale-105 transition-transform duration-300">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-white/20  flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-bold text-lg mb-1">{{ app()->getLocale() === 'de' ? 'Experten Support' : 'Expert Support' }}</h3>
                                    <p class="text-white/80 text-sm">{{ app()->getLocale() === 'de' ? 'Persönliche Betreuung vom Start bis zum Ende' : 'Personal support from start to finish' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Content -->
                    <div class="text-center lg:text-left">
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-6 leading-tight">
                            {{ app()->getLocale() === 'de' ? 'Bereit für Ihr nächstes Projekt?' : 'Ready for Your Next Project?' }}
                        </h2>
                        <p class="text-lg sm:text-xl text-white/90 mb-8 leading-relaxed max-w-xl">
                            {{ app()->getLocale() === 'de' ? 'Lassen Sie uns Ihre Vision in eine professionelle WordPress-Website verwandeln. Kontaktieren Sie unser Expertenteam noch heute.' : 'Let us turn your vision into a professional WordPress website. Contact our expert team today.' }}
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <a href="https://api.leadconnectorhq.com/widget/bookings/gleni"
                               class="group inline-flex items-center justify-center px-8 py-4 bg-white text-[#D53741] font-bold text-lg transition-all duration-300 hover:scale-105 hover:shadow-2xl">
                                <span>{{ app()->getLocale() === 'de' ? 'Termin buchen' : 'Book a Meeting' }}</span>
                                <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </a>
                            <a href="https://www.metanow.dev/get-a-quote"
                               class="group inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white font-bold text-lg backdrop-blur-sm transition-all duration-300 hover:bg-white hover:text-[#D53741] hover:scale-105">
                                {{ app()->getLocale() === 'de' ? 'Angebot erhalten' : 'Get a Quote' }}
                                <svg class="w-5 h-5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>

                        <!-- Trust badges mobile -->
                        <div class="lg:hidden mt-10 pt-8 border-t border-white/20">
                            <div class="flex flex-wrap justify-center gap-8 text-white/80">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span class="text-sm font-medium">{{ app()->getLocale() === 'de' ? 'Premium Qualität' : 'Premium Quality' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm font-medium">{{ app()->getLocale() === 'de' ? 'Schnelle Lieferung' : 'Fast Delivery' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Simple Footer -->
        <footer class="bg-white text-gray py-3">
            <div class="container mx-auto sm:px-4">
                <div class="flex flex-col space-y-3 md:flex-row md:justify-between md:items-center md:space-y-0">
                    
                    <!-- Copyright -->
                    <div class="text-center md:text-left">
                        <p class="text-xs sm:text-sm text-gray-400">
                            &copy; {{ date('Y') }} Metanow | {{ app()->getLocale() === 'de' ? 'Alle Rechte vorbehalten.' : 'All rights reserved.' }}
                        </p>
                    </div>
                    
                    <!-- Mobile: Stacked Layout -->
                    <div class="flex flex-col space-y-3 md:hidden">
                        <!-- Language Flags -->
                        <div class="flex justify-center space-x-3">
                            <a href="{{ route('templates.index') }}"
                               class="flex items-center space-x-1 px-2 py-1 transition-all duration-200 @if(app()->getLocale() === 'en') bg-red-50 text-[#B12A31] @else text-gray-400 hover:text-gray-900 hover:bg-gray-200 @endif">
                                <span class="fi fi-us" style="width: 16px; height: 12px;"></span>
                                <span class="text-xs">EN</span>                             
                            </a>
                            <a href="{{ route('templates.index.de') }}"
                               class="flex items-center space-x-1 px-2 py-1 transition-all duration-200 @if(app()->getLocale() === 'de') bg-red-50 text-[#B12A31] @else text-gray-400 hover:text-gray-900 hover:bg-gray-200 @endif">
                                <span class="fi fi-de" style="width: 16px; height: 12px;"></span>
                                <span class="text-xs">DE</span>
                            </a>
                        </div>
                        
                        <!-- Legal Links -->
                        <div class="flex justify-center space-x-4 text-xs">
                            <a href="{{ app()->getLocale() === 'de' ? 'https://www.metanow.dev/de/imprint' : 'https://www.metanow.dev/en/imprint' }}" target="_blank" class="text-gray-400 hover:text-gray-900 transition-colors">
                                {{ app()->getLocale() === 'de' ? 'Impressum' : 'Imprint' }}
                            </a>
                            <a href="{{ app()->getLocale() === 'de' ? 'https://www.metanow.dev/de/privacy-policy' : 'https://www.metanow.dev/en/privacy-policy' }}" target="_blank" class="text-gray-400 hover:text-gray-900 transition-colors">
                                {{ app()->getLocale() === 'de' ? 'Datenschutz' : 'Privacy' }}
                            </a>
                            <a href="{{ app()->getLocale() === 'de' ? 'https://www.metanow.dev/de/terms-of-service' : 'https://www.metanow.dev/en/terms-of-service' }}" target="_blank" class="text-gray-400 hover:text-gray-900 transition-colors">
                                {{ app()->getLocale() === 'de' ? 'AGB' : 'Terms' }}
                            </a>
                        </div>
                        
                        <!-- Mobile Powered by -->
                        <div class="flex justify-center items-center space-x-2">
                            <span class="text-xs text-gray-400">
                                {{ app()->getLocale() === 'de' ? 'Betrieben von' : 'Powered by' }}
                            </span>
                            <a href="https://metanow.dev" target="_blank" class="hover:opacity-80 transition-opacity duration-200">
                                <img src="{{ asset('storage/img/logo/Metanow.webp') }}" width="80" alt="Metanow Logo">
                            </a>
                        </div>
                    </div>
                    
                    <!-- Desktop: Horizontal Layout -->
                    <div class="hidden md:flex md:items-center md:space-x-6">
                        <!-- Language Flags -->
                        <div class="flex space-x-3">
                            <a href="{{ route('templates.index') }}"
                               class="flex items-center space-x-2 px-3 py-1 transition-all duration-200 @if(app()->getLocale() === 'en') bg-red-50 text-[#B12A31] @else text-gray-400 hover:text-gray-900 hover:bg-gray-200 @endif">
                                <span class="fi fi-gb" style="width: 20px; height: 15px;"></span>                                
                            </a>
                            <a href="{{ route('templates.index.de') }}"
                               class="flex items-center space-x-2 px-3 py-1 transition-all duration-200 @if(app()->getLocale() === 'de') bg-red-50 text-[#B12A31] @else text-gray-400 hover:text-gray-900 hover:bg-gray-200 @endif">
                                <span class="fi fi-de" style="width: 20px; height: 15px;"></span>
                            </a>
                        </div>
                        
                        <!-- Legal Links -->
                        <div class="flex space-x-4 text-sm">
                            <a href="{{ app()->getLocale() === 'de' ? 'https://www.metanow.dev/de/imprint' : 'https://www.metanow.dev/en/imprint' }}" target="_blank" class="text-gray-400 hover:text-gray-900 transition-colors">
                                {{ app()->getLocale() === 'de' ? 'Impressum' : 'Imprint' }}
                            </a>
                            <a href="{{ app()->getLocale() === 'de' ? 'https://www.metanow.dev/de/privacy-policy' : 'https://www.metanow.dev/en/privacy-policy' }}" target="_blank" class="text-gray-400 hover:text-gray-900 transition-colors">
                                {{ app()->getLocale() === 'de' ? 'Datenschutz' : 'Privacy Policy' }}
                            </a>
                            <a href="{{ app()->getLocale() === 'de' ? 'https://www.metanow.dev/de/terms-of-service' : 'https://www.metanow.dev/en/terms-of-service' }}" target="_blank" class="text-gray-400 hover:text-gray-900 transition-colors">
                                {{ app()->getLocale() === 'de' ? 'AGB' : 'Terms & Conditions' }}
                            </a>
                        </div>
                        
                        <!-- Powered by Metanow -->
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-400">
                                {{ app()->getLocale() === 'de' ? 'Betrieben von' : 'Powered by' }}
                            </span>
                            <a href="https://metanow.dev" target="_blank" class="hover:opacity-80 transition-opacity duration-200">
                                <img src="{{ asset('storage/img/logo/Metanow.webp') }}" width="120" alt="Metanow Logo">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    
</div>
</div>
