<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeasurementProfile extends Model
{
    protected $fillable = [
        'user_id', 'label', 'chest', 'waist', 'hips', 'shoulder', 'sleeve',
        'shirt_length', 'neck', 'armhole', 'wrist', 'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function fields(): array
    {
        return [
            'chest' => 'Chest',
            'waist' => 'Waist',
            'hips' => 'Hips',
            'shoulder' => 'Shoulder Width',
            'sleeve' => 'Sleeve Length',
            'shirt_length' => 'Shirt / Kameez Length',
            'neck' => 'Neck',
            'armhole' => 'Armhole',
            'wrist' => 'Wrist',
        ];
    }
}
