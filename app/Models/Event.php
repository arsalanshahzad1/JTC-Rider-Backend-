<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Event extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'events';

    public const PATH = 'event';

    public const PATH_GALLERY = 'event/gallery';

    protected $appends = ['main_image', 'gallery_images'];

    protected $hidden = ['media'];

    protected $fillable = [
        'title',
        'event_type_id',
        'description',
        'capacity',
        'status',
        'price'
    ];

    public function event_type()
    {
        return $this->belongsTo(EventType::class);
    }
    public function event_booking()
    {
        return $this->hasMany(EventBooking::class);
    }

    public function getFirstImageUrl()
    {
        $imageUrl = $this->getFirstMediaUrl('event');

        return $imageUrl ?: asset('no-image/no-image.png');
    }

    public function getMainImageAttribute()
    {
        return $this->getFirstImageUrl();
    }

    public function getGalleryImageUrl()
    {
        $imageUrl = $this->getMedia('event/gallery');
        if(!empty($imageUrl)){
            $gallery_arr = [];
            foreach($imageUrl as $gallery){
                array_push($gallery_arr, $gallery->original_url);
            }
        }

        return $gallery_arr;
    }

    public function getGalleryImagesAttribute()
    {
        return $this->getGalleryImageUrl();
    }
}
