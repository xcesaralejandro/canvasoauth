<?php

namespace xcesaralejandro\canvasoauth\Models;

use App\Models\CanvasToken;
use Illuminate\Database\Eloquent\Model;

class CanvasUser extends Model
{

    protected $table = 'canvas_users';

    protected $fillable = ['canvas_client_id', 'canvas_id', 'canvas_global_id', 'name', 'effective_locale', 'fake_student'];

    public function client()
    {
        return $this->belongsTo(CanvasClient::class, 'canvas_client_id', 'id');
    }

    public function token()
    {
        return $this->hasOne(CanvasToken::class, 'canvas_user_id');
    }
}
