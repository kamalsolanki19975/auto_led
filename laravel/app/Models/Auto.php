<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Auto extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = ['registration_date' => 'date'];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function primaryDriver()
    {
        return $this->belongsTo(Driver::class, 'primary_driver_id');
    }

    public function secondaryDriver()
    {
        return $this->belongsTo(Driver::class, 'secondary_driver_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function group()
    {
        return $this->belongsTo(AutoGroup::class, 'auto_group_id');
    }

    public function groups()
    {
        return $this->belongsToMany(AutoGroup::class, 'auto_group_auto');
    }

    public function screen()
    {
        return $this->hasOne(Screen::class, 'current_auto_id');
    }

    public function device()
    {
        return $this->hasOne(Device::class, 'current_auto_id');
    }

    public function sim()
    {
        return $this->hasOne(Sim::class, 'current_auto_id');
    }

    public function campaignAssignments()
    {
        return $this->hasMany(CampaignAssignment::class);
    }

    public function campaigns()
    {
        return $this->belongsToMany(Campaign::class, 'campaign_assignments');
    }

    public function runtimeLogs()
    {
        return $this->hasMany(RuntimeLog::class);
    }

    public function maintenanceTickets()
    {
        return $this->hasMany(MaintenanceTicket::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
