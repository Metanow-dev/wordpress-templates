<?php

namespace App\Livewire;

use App\Models\Template;
use Livewire\Component;
use Livewire\WithPagination;

class TemplatesIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sort = 'recent'; // or 'az'
    public array $selectedCategories = [];
    public array $selectedTags = [];

    protected $queryString = ['search', 'sort', 'selectedCategories', 'selectedTags', 'page'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSort() { $this->resetPage(); }
    public function updatingSelectedCategories() { $this->resetPage(); }
    public function updatingSelectedTags() { $this->resetPage(); }

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
        $this->resetPage();
    }

    public function render()
    {
        $q = Template::query();

        if ($this->search !== '') {
            $q->where(function ($qq) {
                $qq->where('name', 'like', "%{$this->search}%")
                   ->orWhere('slug', 'like', "%{$this->search}%")
                   ->orWhere('description_en', 'like', "%{$this->search}%")
                   ->orWhere('description_de', 'like', "%{$this->search}%");
            });
        }

        if (!empty($this->selectedCategories)) {
            $q->whereIn('primary_category', $this->selectedCategories);
        }

        if (!empty($this->selectedTags)) {
            foreach ($this->selectedTags as $tag) {
                $q->whereJsonContains('tags', $tag);
            }
        }

        if ($this->sort === 'az') {
            $q->orderBy('name')->orderBy('slug');
        } else {
            $q->orderByDesc('last_scanned_at')->orderBy('slug');
        }

        $templates = $q->paginate(12);

        $categories = config('catalog.categories');
        $tags = config('catalog.tags');

        return view('livewire.templates-index', compact('templates', 'categories', 'tags'));
    }
}
