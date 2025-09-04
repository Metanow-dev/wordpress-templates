<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'slug','name','demo_url','screenshot_url','language','active_theme','plugins',
        'description_en','description_de','auto_translated','primary_category','tags',
        'classification_confidence','classification_source','classification_rationale',
        'locked_by_human','needs_review','snippet_payload','text_snippet_hash','last_classified_hash',
        'last_scanned_at','last_classified_at',
    ];

    protected $casts = [
        'plugins' => 'array',
        'tags' => 'array',
        'snippet_payload' => 'array',
        'auto_translated' => 'boolean',
        'locked_by_human' => 'boolean',
        'needs_review' => 'boolean',
        'last_scanned_at' => 'datetime',
        'last_classified_at' => 'datetime',
        'classification_confidence' => 'decimal:2',
    ];
}
