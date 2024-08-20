<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class UserConnectionInfo
 *
 * This model represents the user connection information and utilizes 
 * Laravel Eloquent ORM for database interactions. It also logs activities 
 * using the Spatie Activitylog package.
 *
 * @package App\Models
 */
class UserConnectionInfo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    use LogsActivity;

    /**
     * Configure the options for activity logging.
     *
     * @return \Spatie\Activitylog\LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->setDescriptionForEvent(fn (string $eventName) => class_basename(get_called_class()) . " has been {$eventName} id is  $this->id")
            ->logOnlyDirty();
    }
    /**
     * Define a relationship to the Mikrotik model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mikrotik()
    {
        return $this->belongsTo(Mikrotik::class, 'mikrotik_id', 'id')->withDefault();
    }
    /**
     * Define a relationship to the Package model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }
}
