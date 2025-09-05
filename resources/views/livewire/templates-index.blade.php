<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="text-2xl font-bold text-blue-600"><img src="{{ asset('storage/img/logo/Metanow.webp') }}" width="180" alt="Metanow Logo">



</div>
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- Language Switcher -->
                <div class="flex items-center space-x-2">
                    <a href="/en/templates" class="px-3 py-1 text-sm rounded {{ app()->getLocale() === 'en' ? 'bg-blue-100 text-blue-700 font-medium' : 'text-gray-600 hover:text-blue-600' }} transition-colors duration-200">
                        EN
                    </a>
                    <span class="text-gray-300">|</span>
                    <a href="/de/vorlagen" class="px-3 py-1 text-sm rounded {{ app()->getLocale() === 'de' ? 'bg-blue-100 text-blue-700 font-medium' : 'text-gray-600 hover:text-blue-600' }} transition-colors duration-200">
                        DE
                    </a>
                </div>
                
                <a href="https://metanow.dev"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200">
                    Back to Metanow
                </a>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        {{-- <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-3">
                {{ app()->getLocale()==='de' ? 'WordPress Vorlagen' : 'WordPress Templates' }}
            </h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                {{ app()->getLocale()==='de' ? 'Entdecken Sie professionelle WordPress-Themes für jedes Projekt' :
                'Discover professional WordPress themes for every project' }}
            </p>
        </div> --}}
        
        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative max-w-md mx-auto">
                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" wire:model.live="search"
                    placeholder="{{ app()->getLocale() === 'de' ? 'Nach Vorlagen suchen...' : 'Search templates...' }}"
                    class="w-full pl-10 pr-4 py-3 text-base border-2 border-gray-300 bg-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 shadow-sm">
            </div>
        </div>
        <!-- Compact Filters Bar -->
        <div class="bg-white/70 backdrop-blur-sm border border-gray-200/50 rounded-2xl p-6 mb-8 shadow-sm">


            <!-- Categories -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <span
                        class="text-sm font-semibold text-gray-700">{{ app()->getLocale() === 'de' ? 'Kategorien:' : 'Categories:' }}</span>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-500">
                            {{ $templates->total() }} {{ app()->getLocale() === 'de' ? 'Vorlagen' : 'templates' }}
                        </span>
                        <select wire:model.live="sort"
                            class="px-3 py-1.5 border-0 bg-gray-50/50 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all duration-200 text-sm">
                            <option value="recent">{{ app()->getLocale() === 'de' ? 'Neueste' : 'Recent' }}</option>
                            <option value="az">A–Z</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($categories as $category)
                                    <button wire:click="toggleCategory('{{ $category }}')" class="px-4 py-2 text-sm rounded-full transition-all duration-200 font-medium
                                                       {{ in_array($category, $selectedCategories)
                        ? 'bg-purple-500 text-white shadow-md transform scale-105'
                        : 'bg-gray-100/80 text-gray-700 hover:bg-gray-200/80 hover:scale-105' }}">
                                        {{ ucfirst(str_replace('_', ' ', $category)) }}
                                    </button>
                    @endforeach
                </div>
            </div>

            <!-- Tags -->
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span
                        class="text-sm font-semibold text-gray-700">{{ app()->getLocale() === 'de' ? 'Tags:' : 'Tags:' }}</span>
                    @if($search || !empty($selectedCategories) || !empty($selectedTags))
                        <button wire:click="clearFilters"
                            class="px-3 py-1 bg-gray-100/80 hover:bg-gray-200/80 text-gray-600 rounded-lg transition-all duration-200 text-xs font-medium">
                            {{ app()->getLocale() === 'de' ? 'Alle löschen' : 'Clear all' }}
                        </button>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $tag)
                                    <button wire:click="toggleTag('{{ $tag }}')" class="px-3 py-1.5 text-sm rounded-full transition-all duration-200 font-medium
                                                       {{ in_array($tag, $selectedTags)
                        ? 'bg-blue-500 text-white shadow-sm transform scale-105'
                        : 'bg-gray-100/80 text-gray-700 hover:bg-gray-200/80 hover:scale-105' }}">
                                        {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                    </button>
                    @endforeach
                </div>

                <!-- Active Filters Display -->
                @if(!empty($selectedCategories) || !empty($selectedTags))
                    <div class="flex items-center gap-2 flex-wrap mt-4 pt-4 border-t border-gray-200/30">
                        <span
                            class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ app()->getLocale() === 'de' ? 'Aktiv:' : 'Active:' }}</span>

                        @foreach($selectedCategories as $category)
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs bg-gradient-to-r from-purple-500 to-purple-600 text-white font-medium">
                                {{ ucfirst(str_replace('_', ' ', $category)) }}
                                <button wire:click="removeCategory('{{ $category }}')"
                                    class="ml-1.5 text-purple-100 hover:text-white">×</button>
                            </span>
                        @endforeach

                        @foreach($selectedTags as $tag)
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs bg-gradient-to-r from-blue-500 to-blue-600 text-white font-medium">
                                {{ ucfirst(str_replace('_', ' ', $tag)) }}
                                <button wire:click="removeTag('{{ $tag }}')"
                                    class="ml-1.5 text-blue-100 hover:text-white">×</button>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Results -->
        @if($templates->count())
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8 mb-12">
                @foreach($templates as $template)
                    <div
                        class="group bg-white rounded-2xl shadow-sm border border-gray-200/50 overflow-hidden hover:shadow-xl hover:shadow-gray-200/50 transition-all duration-300 hover:-translate-y-1">
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
                                    <span
                                        class="inline-block px-3 py-1.5 text-xs font-bold bg-white/90 backdrop-blur-sm text-gray-800 rounded-full shadow-sm">
                                        {{ ucfirst(str_replace('_', ' ', $template->primary_category)) }}
                                    </span>
                                </div>
                            @endif

                            <!-- Demo Button -->
                            <div
                                class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <a href="{{ $template->demo_url }}" target="_blank"
                                    class="bg-blue-600/90 backdrop-blur-sm hover:bg-blue-700 text-white px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 shadow-lg">
                                    {{ app()->getLocale() === 'de' ? 'Demo' : 'Demo' }}
                                </a>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6">
                            <h3
                                class="font-bold text-gray-900 mb-2 text-lg line-clamp-1 group-hover:text-blue-600 transition-colors duration-200">
                                {{ $template->name ?? ucfirst(str_replace('-', ' ', $template->slug)) }}
                            </h3>

                            @if($template->description_en || $template->description_de)
                                <p class="text-sm text-gray-600 line-clamp-2 mb-4 leading-relaxed">
                                    {{ app()->getLocale() === 'de' ? $template->description_de : $template->description_en }}
                                </p>
                            @endif

                            <!-- Tags Prominently Displayed -->
                            @if($template->tags && count($template->tags) > 0)
                                <div class="flex flex-wrap gap-1.5 mb-3">
                                    @foreach($template->tags as $tag)
                                            <button wire:click="toggleTag('{{ $tag }}')"
                                                class="inline-block px-2.5 py-1 text-xs rounded-md font-medium transition-all duration-200 cursor-pointer
                                                                           {{ in_array($tag, $selectedTags)
                                        ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-sm'
                                        : 'bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700 hover:from-gray-200 hover:to-gray-100 border border-gray-200/50' }}">
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
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium transition-colors duration-200">
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