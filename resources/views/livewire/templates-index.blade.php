<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 overflow-x-hidden">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40"style="margin-bottom:80px">
        <div class="container mx-auto px-2 sm:px-4 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div class="text-2xl font-bold text-[#D53741]">
                    <a href="https://metanow.dev">
                    <img src="{{ asset('storage/img/logo/Metanow.webp') }}" width="120" class="sm:w-[180px]" alt="Metanow Logo">
                    </a>
                </div>
            </div>
            
            <div class="flex items-center space-x-1 sm:space-x-4">
                <div class="flex space-x-3">
                            <a href="{{ route('templates.index') }}"
                               class="flex items-center space-x-2 px-3 py-1 rounded-md transition-all duration-200 @if(app()->getLocale() === 'en') bg-red-50 text-[#B12A31] @else text-gray-400 hover:text-gray-900 hover:bg-gray-200 @endif">
                                <span class="fi fi-gb" style="width: 20px; height: 15px;"></span>                                
                            </a>
                            <a href="{{ route('templates.index.de') }}"
                               class="flex items-center space-x-2 px-3 py-1 rounded-md transition-all duration-200 @if(app()->getLocale() === 'de') bg-red-50 text-[#B12A31] @else text-gray-400 hover:text-gray-900 hover:bg-gray-200 @endif">
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

    <!-- Page Title Section -->
    <div style="margin-bottom: 50px;">
        <div class="container mx-auto px-2 sm:px-4 text-center ">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-[#D53741] mb-1">
                Handcrafted WordPress Projects
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                {{ app()->getLocale() === 'de' ? 'Entdecken Sie unsere Sammlung professionell gestalteter WordPress-Vorlagen' : 'Discover our collection of professionally designed WordPress templates' }}
            </p>
        </div>
    </div>

    <div class="container mx-auto px-2 sm:px-4">
        
        <!-- Enhanced Search & Controls Bar -->
        <div class="mb-6">
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
                            class="flex items-center space-x-2 px-4 py-2 font-medium transition-all duration-200 {{ $showFilters ? 'bg-red-50 text-[#D53741] border border-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
                            </svg>
                            <span>{{ $this->filterToggleText }}</span>
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
                                        @if(in_array($key, $selectedCategories))
                                            <svg class="w-4 h-4 ml-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        @else
                                            
                                        @endif
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
                                    <img src="{{ $template->screenshot_url }}" alt="{{ $template->name ?? $template->slug }}"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                        loading="lazy" style="image-resolution: 350px">
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
                                <div class="sm:w-80 aspect-video sm:aspect-square bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                                    @if($template->screenshot_url)
                                        <img src="{{ $template->screenshot_url }}" alt="{{ $template->name ?? $template->slug }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                            loading="lazy">
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
                                        <h3 class="font-bold text-gray-900 text-xl group-hover:text-[#D53741] transition-colors duration-200">
                                            {{ $template->name ?? ucfirst(str_replace('-', ' ', $template->slug)) }}
                                        </h3>
                                        
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
                .pagination-wrapper .hidden.sm\\:flex-1.sm\\:flex.sm\\:items-center.sm\\:justify-between {
                    flex-direction: column !important;
                    gap: 20px !important;
                    align-items: center !important;
                }
                @media (min-width: 640px) {
                    .pagination-wrapper .hidden.sm\\:flex-1.sm\\:flex.sm\\:items-center.sm\\:justify-between {
                        flex-direction: column !important;
                        justify-content: center !important;
                    }
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
        <section class="bg-gradient-to-r from-[#D53741] to-[#B12A31] text-white mb-8 mt-16 py-16">
            <div class="container mx-auto px-2 sm:px-4 text-center">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">
                    {{ app()->getLocale() === 'de' ? 'Bereit für Ihr nächstes Projekt?' : 'Ready for Your Next Project?' }}
                </h2>
                <p class="text-xl mb-8 opacity-90 max-w-2xl mx-auto">
                    {{ app()->getLocale() === 'de' ? 'Lassen Sie uns Ihre Vision in eine professionelle WordPress-Website verwandeln. Kontaktieren Sie unser Expertenteam noch heute.' : 'Let us turn your vision into a professional WordPress website. Contact our expert team today.' }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="https://api.leadconnectorhq.com/widget/bookings/gleni" 
                       class="bg-white px-4 text-[#D53741] hover:bg-gray-100 px-8 py-4 font-semibold text-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        {{ app()->getLocale() === 'de' ? 'Termin buchen' : 'Book a meeting' }}
                    </a>
                    <a href="https://www.metanow.dev/get-a-quote" 
                       class="border-2 px-3 border-white text-white px-8 py-4 font-semibold text-lg transition-all duration-200 hover:bg-white hover:text-[#D53741]">
                        {{ app()->getLocale() === 'de' ? 'Kontakt aufnehmen' : 'Contact us' }}
                    </a>
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
                               class="flex items-center space-x-1 px-2 py-1 rounded-md transition-all duration-200 @if(app()->getLocale() === 'en') bg-red-50 text-[#B12A31] @else text-gray-400 hover:text-gray-900 hover:bg-gray-200 @endif">
                                <span class="fi fi-us" style="width: 16px; height: 12px;"></span>
                                <span class="text-xs">EN</span>                             
                            </a>
                            <a href="{{ route('templates.index.de') }}"
                               class="flex items-center space-x-1 px-2 py-1 rounded-md transition-all duration-200 @if(app()->getLocale() === 'de') bg-red-50 text-[#B12A31] @else text-gray-400 hover:text-gray-900 hover:bg-gray-200 @endif">
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
                                <img src="{{ asset('storage/img/logo/Metanow.webp') }}" width="80" alt="Metanow Logo" class="filter brightness-0 invert">
                            </a>
                        </div>
                    </div>
                    
                    <!-- Desktop: Horizontal Layout -->
                    <div class="hidden md:flex md:items-center md:space-x-6">
                        <!-- Language Flags -->
                        <div class="flex space-x-3">
                            <a href="{{ route('templates.index') }}"
                               class="flex items-center space-x-2 px-3 py-1 rounded-md transition-all duration-200 @if(app()->getLocale() === 'en') bg-red-50 text-[#B12A31] @else text-gray-400 hover:text-gray-900 hover:bg-gray-200 @endif">
                                <span class="fi fi-gb" style="width: 20px; height: 15px;"></span>                                
                            </a>
                            <a href="{{ route('templates.index.de') }}"
                               class="flex items-center space-x-2 px-3 py-1 rounded-md transition-all duration-200 @if(app()->getLocale() === 'de') bg-red-50 text-[#B12A31] @else text-gray-400 hover:text-gray-900 hover:bg-gray-200 @endif">
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
                                <img src="{{ asset('storage/img/logo/Metanow.webp') }}" width="120" alt="Metanow Logo" class="filter brightness-0 invert">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    
</div>
</div>
