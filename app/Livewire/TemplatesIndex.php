<?php

namespace App\Livewire;

use App\Models\Template;
use App\Helpers\CategoryHelper;
use Livewire\Component;
use Livewire\WithPagination;

class TemplatesIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sort = 'recent'; // recent, az, za, confidence, category
    public string $view = 'grid'; // grid, list
    public string $perPage = '12'; // 12, 24, 48
    public array $selectedCategories = [];
    public array $selectedTags = [];
    public bool $showFilters = true;
    public bool $onlyWithScreenshots = false;
    public bool $onlyClassified = false;
    
    protected $queryString = [
        'search', 'sort', 'view', 'perPage', 'selectedCategories', 'selectedTags', 
        'showFilters', 'onlyWithScreenshots', 'onlyClassified', 'page'
    ];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSort() { $this->resetPage(); }
    public function updatingView() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }
    public function updatingSelectedCategories() { $this->resetPage(); }
    public function updatingSelectedTags() { $this->resetPage(); }
    public function updatingOnlyWithScreenshots() { $this->resetPage(); }
    public function updatingOnlyClassified() { $this->resetPage(); }

    public function toggleCategory($category)
    {
        if (in_array($category, $this->selectedCategories)) {
            $this->selectedCategories = array_values(array_diff($this->selectedCategories, [$category]));
        } else {
            $this->selectedCategories[] = $category;
        }
        $this->resetPage();
    }

    public function toggleTag($tag)
    {
        if (in_array($tag, $this->selectedTags)) {
            $this->selectedTags = array_values(array_diff($this->selectedTags, [$tag]));
        } else {
            $this->selectedTags[] = $tag;
        }
        $this->resetPage();
    }

    public function removeTag($tag)
    {
        $this->selectedTags = array_values(array_diff($this->selectedTags, [$tag]));
        $this->resetPage();
    }

    public function removeCategory($category)
    {
        $this->selectedCategories = array_values(array_diff($this->selectedCategories, [$category]));
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->selectedCategories = [];
        $this->selectedTags = [];
        $this->search = '';
        $this->onlyWithScreenshots = false;
        $this->onlyClassified = false;
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function setView($view)
    {
        $this->view = $view;
        $this->resetPage();
    }

    public function getFilterStats()
    {
        $baseQuery = Template::query();
        
        if ($this->search !== '') {
            $baseQuery->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('slug', 'like', "%{$this->search}%")
                  ->orWhere('description_en', 'like', "%{$this->search}%")
                  ->orWhere('description_de', 'like', "%{$this->search}%");
            });
        }

        return [
            'total' => $baseQuery->count(),
            'with_screenshots' => $baseQuery->clone()->whereNotNull('screenshot_url')->count(),
            'classified' => $baseQuery->clone()->whereNotNull('primary_category')->count(),
            'high_confidence' => $baseQuery->clone()->where('classification_confidence', '>=', 0.8)->count(),
        ];
    }

    public function render()
    {
        $q = Template::query();

        // Search filter
        if ($this->search !== '') {
            $q->where(function ($qq) {
                $qq->where('name', 'like', "%{$this->search}%")
                   ->orWhere('slug', 'like', "%{$this->search}%")
                   ->orWhere('description_en', 'like', "%{$this->search}%")
                   ->orWhere('description_de', 'like', "%{$this->search}%")
                   ->orWhere('primary_category', 'like', "%{$this->search}%");
            });
        }

        // Category filter
        if (!empty($this->selectedCategories)) {
            $q->whereIn('primary_category', $this->selectedCategories);
        }

        // Tags filter
        if (!empty($this->selectedTags)) {
            foreach ($this->selectedTags as $tag) {
                $q->whereJsonContains('tags', $tag);
            }
        }

        // Advanced filters
        if ($this->onlyWithScreenshots) {
            $q->whereNotNull('screenshot_url');
        }

        if ($this->onlyClassified) {
            $q->whereNotNull('primary_category');
        }


        // Sorting
        match ($this->sort) {
            'az' => $q->orderBy('name')->orderBy('slug'),
            'za' => $q->orderByDesc('name')->orderBy('slug'),
            'confidence' => $q->orderByDesc('classification_confidence')->orderByDesc('last_scanned_at'),
            'category' => $q->orderBy('primary_category')->orderBy('name'),
            default => $q->orderByDesc('last_scanned_at')->orderBy('slug'),
        };

        $templates = $q->paginate(intval($this->perPage));

        $locale = app()->getLocale();
        $categories = CategoryHelper::getCategoriesForLocale($locale);
        $tags = config('catalog.tags');
        $stats = $this->getFilterStats();

        return view('livewire.templates-index', compact('templates', 'categories', 'tags', 'stats'));
    }
}
