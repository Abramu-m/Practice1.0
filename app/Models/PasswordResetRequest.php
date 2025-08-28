<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordResetRequest extends Model
{
    use HasFactory;

    protected $table = 'password_reset_requests';

    protected $fillable = [
        'admin_id', 'user_id', 'user_email', 'status', 'sent_at', 'ip_address', 'user_agent', 'note'
    ];

    protected $dates = ['sent_at', 'created_at', 'updated_at'];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
