<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Ticket extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $guarded = ['id'];
    protected $with = ['messages', 'user', 'media'];
    protected $appends = ['date', 'time', 'last_updated', 're_open'];

    public function getDateAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('d-m-Y');
    }

    public function getTimeAttribute()
    {
        return Carbon::parse($this->attributes['created_at'])->format('H:i');
    }

    public function getLastUpdatedAttribute()
    {
        return Carbon::parse($this->attributes['updated_at'])->diffForHumans();
    }

    public function getReOpenAttribute()
    {
        $createdAt = Carbon::parse($this->created_at);
        $updatedAt = Carbon::parse($this->updated_at);

        return ($createdAt->format('H:i:s') === $updatedAt->format('H:i:s')) ? false : true;
        // public function getReOpenAttribute()
        // {
        //     return $this->created_at !== $this->updated_at ? true : false;
    }
    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}