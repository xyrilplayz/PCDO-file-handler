<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CooperativeUploads extends Model
{
    protected $fillable = [
        'cooperative_id',
        'checklist_item_id',
        'file_name',
        'mime_type',
        'file_content'
    ];

    // Relationship to checklist item
    public function checklistItem()
    {
        return $this->belongsTo(ChecklistItem::class, 'checklist_item_id');
    }

    // Relationship to cooperative
    public function cooperative()
    {
        return $this->belongsTo(Cooperative::class, 'cooperative_id');
    }
}
