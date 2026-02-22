<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'company', 'province', 'description', 'email_or_link', 'photo', 'country_id'
    ];


    protected static function boot()
    {
        parent::boot();

        static::created(function ($job) {
            $job->slug = $job->generateSlug($job->title, $job->id);
            $job->save();
            
            SocialMediaJob::create([
                'job_id' => $job->id,
                'post_status' => 0,
            ]);
        });
    }

    private function generateSlug($title, $id)
    {
        if (static::whereSlug($slug = Str::slug($title))->exists()) {
            $max = static::whereTitle($title)->latest('id');
            $slug = $slug . '-' . $id;
        }
        return $slug;
    }
}
