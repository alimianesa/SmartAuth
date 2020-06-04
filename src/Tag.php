<?php

namespace Alimianesa\SmartAuth;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'active'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function files()
    {
        return $this->belongsToMany(
            AliveFile::class,
            'file_tag',
            'tag_id',
            'file_id'
        );
    }
}
