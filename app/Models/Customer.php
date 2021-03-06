<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'state_id',
        'customer_id',
        'license',
        'state_licence',
        'first_name',
        'last_name',
        'initial',
        'address_p',
        'address_s',
        'city',
        'zip',
        'telephone_res',
        'telephone_bus',
        'cellphone',
        'email',
        'birthday',
        'gender',
        'ssn'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = strtoupper($value);
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = strtoupper($value);
    }

    public function setLicenseAttribute($value)
    {
        if(is_null($value) || $value == '')
        {
            $this->attributes['license'] = NULL;
        }
        else
        {
            $this->attributes['license'] = $value;

        }
    }

    public function setEmailAttribute($value)
    {
        if(is_null($value) || $value == '')
        {
            $this->attributes['email'] = NULL;
        }
        else
        {
            $this->attributes['email'] = $value;
        }
    }

    public function setSsnAttribute($value)
    {
        if(is_null($value) || $value == '')
        {
            $this->attributes['ssn'] = NULL;
        }
        else
        {
            $this->attributes['ssn'] = $value;

        }
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function loan()
    {
        return $this->hasMany(Loan::class);
    }
}
