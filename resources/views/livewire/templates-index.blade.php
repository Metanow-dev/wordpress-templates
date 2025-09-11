<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="text-2xl font-bold text-[#D53741]">
                    <img src="{{ asset('storage/img/logo/Metanow.webp') }}" width="180" alt="Metanow Logo">
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- Language Switcher -->
                <div class="flex items-center space-x-2">
                    <a href="/en/templates" class="px-3 py-1 text-sm rounded {{ app()->getLocale() === 'en' ? 'bg-red-50 text-[#B12A31] font-medium' : 'text-gray-600 hover:text-[#D53741]' }} transition-colors duration-200">
                        EN
                    </a>
                    <span class="text-gray-300">|</span>
                    <a href="/de/vorlagen" class="px-3 py-1 text-sm rounded {{ app()->getLocale() === 'de' ? 'bg-red-50 text-[#B12A31] font-medium' : 'text-gray-600 hover:text-[#D53741]' }} transition-colors duration-200">
                        DE
                    </a>
                </div>
                
                <a href="https://metanow.dev"
                    class="bg-[#D53741] hover:bg-[#B12A31] text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200">
                    Back to Metanow
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        
        <!-- Enhanced Search & Controls Bar -->
        <div class="mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200/50 p-6">
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
                                class="w-full pl-10 pr-4 py-3 text-base border-2 border-gray-300 bg-white rounded-xl focus:ring-2 focus:ring-[#D53741] focus:border-blue-500 transition-all duration-200 shadow-sm">
                            
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
                            class="flex items-center space-x-2 px-4 py-2 rounded-lg font-medium transition-all duration-200 {{ $showFilters ? 'bg-red-50 text-[#D53741] border border-red-200' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
                            </svg>
                            <span>{{ app()->getLocale() === 'de' ? 'Filter' : 'Filters' }}</span>
                        </button>

                        <!-- View Toggle -->
                        <div class="flex items-center bg-gray-100 rounded-lg p-1">
                            <button wire:click="setView('grid')" 
                                class="flex items-center space-x-1 px-3 py-1.5 rounded-md font-medium transition-all duration-200 {{ $view === 'grid' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                                <span class="hidden sm:inline">Grid</span>
                            </button>
                            <button wire:click="setView('list')" 
                                class="flex items-center space-x-1 px-3 py-1.5 rounded-md font-medium transition-all duration-200 {{ $view === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900' }}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span class="hidden sm:inline">List</span>
                            </button>
                        </div>

                        <!-- Sort Dropdown -->
                        <select wire:model.live="sort" 
                            class="px-4 py-2 border-0 bg-gray-50 rounded-lg focus:ring-2 focus:ring-[#D53741]/20 focus:bg-white transition-all duration-200 text-sm font-medium">
                            <option value="recent">{{ app()->getLocale() === 'de' ? 'Neueste' : 'Recent' }}</option>
                            <option value="az">A → Z</option>
                            <option value="za">Z → A</option>
                            <option value="category">{{ app()->getLocale() === 'de' ? 'Kategorie' : 'Category' }}</option>
                        </select>

                        <!-- Per Page -->
                        <select wire:model.live="perPage" 
                            class="px-3 py-2 border-0 bg-gray-50 rounded-lg focus:ring-2 focus:ring-[#D53741]/20 focus:bg-white transition-all duration-200 text-sm font-medium">
                            <option value="12">12</option>
                            <option value="24">24</option>
                            <option value="48">48</option>
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
                    
                    @if($search || !empty($selectedCategories) || !empty($selectedTags) || $onlyWithScreenshots || $onlyClassified)
                        <button wire:click="clearFilters"
                            class="px-3 py-1 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-all duration-200 text-sm font-medium">
                            {{ app()->getLocale() === 'de' ? 'Alle löschen' : 'Clear all' }}
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Advanced Filters Panel -->
        @if($showFilters)
            <div class="bg-white/80 backdrop-blur-sm border border-gray-200/50 rounded-2xl p-6 mb-8 shadow-sm">
                
                <!-- Advanced Options -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="flex items-center space-x-3 text-sm font-medium text-gray-700">
                            <input type="checkbox" wire:model.live="onlyWithScreenshots" 
                                class="rounded border-gray-300 text-[#D53741] focus:ring-[#D53741]">
                            <span>{{ app()->getLocale() === 'de' ? 'Nur mit Screenshots' : 'Only with screenshots' }}</span>
                            <span class="text-xs text-gray-500">({{ $stats['with_screenshots'] }})</span>
                        </label>
                    </div>
                    
                    <div>
                        <label class="flex items-center space-x-3 text-sm font-medium text-gray-700">
                            <input type="checkbox" wire:model.live="onlyClassified" 
                                class="rounded border-gray-300 text-[#D53741] focus:ring-[#D53741]">
                            <span>{{ app()->getLocale() === 'de' ? 'Nur klassifiziert' : 'Only classified' }}</span>
                            <span class="text-xs text-gray-500">({{ $stats['classified'] }})</span>
                        </label>
                    </div>
                </div>

                <!-- Categories -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-semibold text-gray-700">{{ app()->getLocale() === 'de' ? 'Kategorien:' : 'Categories:' }}</span>
                        @if(!empty($selectedCategories))
                            <button wire:click="$set('selectedCategories', [])" 
                                class="text-xs text-gray-500 hover:text-gray-700">
                                {{ app()->getLocale() === 'de' ? 'Kategorien löschen' : 'Clear categories' }}
                            </button>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($categories as $category)
                            <button wire:click="toggleCategory('{{ $category }}')" 
                                class="px-4 py-2 text-sm rounded-full transition-all duration-200 font-medium {{ in_array($category, $selectedCategories) ? 'bg-purple-500 text-white shadow-md transform scale-105' : 'bg-gray-100/80 text-gray-700 hover:bg-gray-200/80 hover:scale-105' }}">
                                {{ ucfirst(str_replace('_', ' ', $category)) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Tags -->
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="text-sm font-semibold text-gray-700">{{ app()->getLocale() === 'de' ? 'Tags:' : 'Tags:' }}</span>
                        @if(!empty($selectedTags))
                            <button wire:click="$set('selectedTags', [])" 
                                class="text-xs text-gray-500 hover:text-gray-700">
                                {{ app()->getLocale() === 'de' ? 'Tags löschen' : 'Clear tags' }}
                            </button>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($tags as $tag)
                            <button wire:click="toggleTag('{{ $tag }}')" 
                                class="px-3 py-1.5 text-sm rounded-full transition-all duration-200 font-medium {{ in_array($tag, $selectedTags) ? 'bg-blue-500 text-white shadow-sm transform scale-105' : 'bg-gray-100/80 text-gray-700 hover:bg-gray-200/80 hover:scale-105' }}">
                                {{ ucfirst(str_replace('_', ' ', $tag)) }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Active Filters Display -->
                    @if(!empty($selectedCategories) || !empty($selectedTags))
                        <div class="flex items-center gap-2 flex-wrap mt-4 pt-4 border-t border-gray-200/30">
                            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ app()->getLocale() === 'de' ? 'Aktiv:' : 'Active:' }}</span>

                            @foreach($selectedCategories as $category)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs bg-gradient-to-r from-[#D53741] to-[#B12A31] text-white font-medium">
                                    {{ ucfirst(str_replace('_', ' ', $category)) }}
                                    <button wire:click="removeCategory('{{ $category }}')" class="ml-1.5 text-purple-100 hover:text-white">×</button>
                                </span>
                            @endforeach

                            @foreach($selectedTags as $tag)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs bg-gradient-to-r from-[#D53741] to-[#B12A31] text-white font-medium">
                                    {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                    <button wire:click="removeTag('{{ $tag }}')" class="ml-1.5 text-blue-100 hover:text-white">×</button>
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Results -->
        @if($templates->count())
            @if($view === 'grid')
                <!-- Grid View -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 mb-12">
                    @foreach($templates as $template)
                        <div class="group bg-white rounded-2xl shadow-sm border border-gray-200/50 overflow-hidden hover:shadow-xl hover:shadow-gray-200/50 transition-all duration-300 hover:-translate-y-1">
                            <!-- Screenshot -->
                            <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                                @if($template->screenshot_url)
                                    <img src="{{ $template->screenshot_url }}" alt="{{ $template->name ?? $template->slug }}"
                                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
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
                                    <div class="absolute top-3 left-3">
                                        <span class="inline-block px-4 py-2 text-sm font-bold bg-gradient-to-r from-[#D53741] to-[#B12A31] text-white rounded-full shadow-lg border-2 border-white/20 backdrop-blur-sm">
                                            {{ \App\Helpers\CategoryHelper::getCategoryName($template->primary_category, app()->getLocale()) }}
                                        </span>
                                    </div>
                                @endif

                                <!-- Demo Button -->
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-300 flex items-center justify-center">
                                    <a href="{{ $template->demo_url }}" target="_blank"
                                        class="bg-[#D53741]/90 hover:bg-[#B12A31] text-white px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 shadow-lg opacity-0 group-hover:opacity-100 transform translate-y-4 group-hover:translate-y-0">
                                        {{ app()->getLocale() === 'de' ? 'Demo ansehen' : 'View Demo' }}
                                    </a>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-6">
                                <h3 class="font-bold text-gray-900 mb-2 text-lg line-clamp-1 group-hover:text-[#D53741] transition-colors duration-200">
                                    {{ $template->name ?? ucfirst(str_replace('-', ' ', $template->slug)) }}
                                </h3>

                                @if($template->description_en || $template->description_de)
                                    <p class="text-sm text-gray-600 line-clamp-2 mb-4 leading-relaxed">
                                        {{ app()->getLocale() === 'de' ? $template->description_de : $template->description_en }}
                                    </p>
                                @endif

                                <!-- Tags -->
                                @if($template->tags && count($template->tags) > 0)
                                    <div class="flex flex-wrap gap-1.5 mb-3">
                                        @foreach($template->tags as $tag)
                                            <button wire:click="toggleTag('{{ $tag }}')"
                                                class="inline-block px-2.5 py-1 text-xs rounded-md font-medium transition-all duration-200 cursor-pointer {{ in_array($tag, $selectedTags) ? 'bg-gradient-to-r from-[#D53741] to-[#B12A31] text-white shadow-sm' : 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700 hover:from-gray-200 hover:to-gray-100 border border-gray-200/50' }}">
                                                {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif

                                @if($template->active_theme)
                                    <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
                                        <span class="font-medium">{{ app()->getLocale() === 'de' ? 'Theme:' : 'Theme:' }}</span>
                                        {{ $template->active_theme }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- List View -->
                <div class="space-y-4 mb-12">
                    @foreach($templates as $template)
                        <div class="group bg-white rounded-xl shadow-sm border border-gray-200/50 overflow-hidden hover:shadow-lg transition-all duration-300">
                            <div class="flex flex-col sm:flex-row">
                                <!-- Screenshot -->
                                <div class="sm:w-80 aspect-video sm:aspect-square bg-gradient-to-br from-gray-100 to-gray-200 relative overflow-hidden">
                                    @if($template->screenshot_url)
                                        <img src="{{ $template->screenshot_url }}" alt="{{ $template->name ?? $template->slug }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
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
                                            <span class="inline-block px-3 py-1.5 text-xs font-bold bg-gradient-to-r from-[#D53741] to-[#B12A31] text-white rounded-full shadow-lg">
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
                                            class="bg-[#D53741] hover:bg-[#B12A31] text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
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
                                                    class="inline-block px-3 py-1 text-sm rounded-md font-medium transition-all duration-200 cursor-pointer {{ in_array($tag, $selectedTags) ? 'bg-gradient-to-r from-[#D53741] to-[#B12A31] text-white shadow-sm' : 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700 hover:from-gray-200 hover:to-gray-100 border border-gray-200/50' }}">
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
            <div class="flex justify-center">
                {{ $templates->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="bg-white/50 backdrop-blur-sm rounded-2xl p-12 max-w-md mx-auto">
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
                        class="bg-[#D53741] hover:bg-[#B12A31] text-white px-6 py-3 rounded-xl font-medium transition-colors duration-200">
                        {{ app()->getLocale() === 'de' ? 'Filter zurücksetzen' : 'Clear filters' }}
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="container mx-auto px-4 py-6">
            <div class="text-center text-gray-600">
                <p>&copy; {{ date('Y') }} Metanow. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>