<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FileRequest extends Model
{

    /**
     * Assignable fields.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'due',
        'version',
        'required',
        'status',
        'checklist_id'
    ];

    /**
     * Date fields to be converted automatically via Carbon.
     *
     * @var array
     */
    protected $dates = [
        'due'
    ];

    /**
     * Format as Carbon Date only if value given to prevent '0000-00-00 00:00:00'
     *
     * @param $value
     */
    public function setDueAttribute($value)
    {
        if($value) $this->attributes['due'] = Carbon::createFromFormat('d/m/Y', $value);
    }

    /**
     * All File(s) belong to a single Checklist that has required them.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function checklist()
    {
        return $this->belongsTo(Checklist::class);
    }

    /**
     * A File Request could potentially have many physical files uploaded to it.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany(File::class);
    }

    /**
     * Helper func to see if File's status attribute matches given
     * param.
     *
     * @param $status
     * @return bool
     */
    public function hasStatus($status)
    {
        $allowedStatuses = ['waiting', 'received', 'rejected'];
        if(! in_array($status, $allowedStatuses)) abort(500, "Can't whether a File has an invalid status: " . $status);
        return $this->status === $status;
    }
}
