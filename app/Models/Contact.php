<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'phone_numbers',
        'emails',
        'gender',
        'profile_picture',
        'additional_files',
        'is_merged',
        'merged_into_contact_id',
    ];

    protected $casts = [
        'phone_numbers' => 'array',
        'emails' => 'array',
        'additional_files' => 'array',
    ];

    public function customFieldValues()
    {
        return $this->hasMany(ContactCustomFields::class);
    }

    public function mergedContacts()
    {
        return $this->hasMany(Contact::class, 'merged_into_contact_id');
    }

    public function masterContact()
    {
        return $this->belongsTo(Contact::class, 'merged_into_contact_id');
    }
}
