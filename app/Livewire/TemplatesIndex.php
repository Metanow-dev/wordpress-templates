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

    protected $queryString = ['search', 'sort', 'page'];

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSort() { $this->resetPage(); }

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

        if ($this->sort === 'az') {
            $q->orderBy('name')->orderBy('slug');
        } else {
            $q->orderByDesc('last_scanned_at')->orderBy('slug');
        }

        $templates = $q->paginate(12);

        return view('livewire.templates-index', compact('templates'));
    }
}
