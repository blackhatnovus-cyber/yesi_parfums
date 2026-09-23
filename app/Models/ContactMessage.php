<?php

namespace App\Models;

use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'message', 'status'])]
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    /** @var array<int, string> */
    public const STATUSES = ['unread', 'read', 'replied'];

    /** @var array<string, string> */
    protected $attributes = [
        'status' => 'unread',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['status' => 'string'];
    }
}
