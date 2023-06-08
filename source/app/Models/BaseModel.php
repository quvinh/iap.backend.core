<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Common\MetaInfo as CommonMetaInfo;

abstract class BaseModel extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function setMetaInfo(CommonMetaInfo $meta = null, bool $isCreate = true): void
    {
        if (is_null($meta))
            $meta = new CommonMetaInfo();
        if ($isCreate) {
            $this->created_at = $meta->time;
            $this->created_by = $meta->name;
            // $this->created_signature = $meta->signature;
        } else {
            $this->updated_at = $meta->time;
            $this->updated_by = $meta->name;
            // $this->updated_signature = $meta->signature;
        }
    }

    public function clearMetaInfo(): void
    {
        $this->created_at = null;
        $this->created_by = null;
        $this->updated_at = null;
        $this->updated_by = null;
    }
}
