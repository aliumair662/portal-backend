<?php

namespace App\Models;

use App\Enums\RequestType;
use App\Enums\Roles;
use App\Notifications\UserActivated;
use App\Notifications\UserRegistered;
use App\Notifications\UserResetPassword;
use App\Services\CompanyService;
use Carbon\Carbon;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use App\Models\Request as VanWijkRequest;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasPermissions, HasRoles, CanResetPassword;

    public static $createRules = [
        'first_name' => 'required|string|min:2|max:100',
        'last_name' => 'required|string|min:2|max:100',
        'email' => 'required|string|email|max:100|unique:users,email',
        'company_name' => 'required|string|min:2|max:100',
        'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
        'password' => 'required|string|confirmed|min:6'
    ];

    public static $updateRules = [
        'first_name' => 'required|string|min:2|max:100',
        'last_name' => 'required|string|min:2|max:100',
        // 'email' => 'required|string|email|max:100|unique:users',
        /*'company_name' => 'required|string|min:2|max:100',*/
        'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
        'date_of_birth' => 'nullable|date',
    ];

    protected $guarded = ['id'];
    protected $appends = ['all_permissions', 'is_account', 'is_client', 'last_login_date', 'last_login_time', 'is_admin', 'is_super_admin', 'is_portal', 'odoo'];
    protected $with = ['accounts', 'roles'];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getPortalUserId()
    {
        if (isset($this->attributes['parent_user_id'])) {
            return $this->attributes['parent_user_id'];
        }
        ;

        return $this->attributes['id'];
    }

    /**
     *  Check if user is admin
     */
    public function isAdmin()
    {
        return $this->hasRole([Roles::VAN_WIJK, Roles::SUPER_ADMIN]);
    }

    /**
     * Register a new User from a request
     *
     */
    public function register(Request $request)
    {
        /*$validator = Validator::make($request->all(),
            self::$createRules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }*/

        $request->validate(self::$createRules);
        // $request->validate(['email' => 'unique:users,email']);
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'company_name' => $request->company_name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);
        VanWijkRequest::create([
            'status' => RequestType::OPEN,
            'requested_by' => $user->id,
            'requestable_id' => $user->id,
            'requestable_type' => "App\Models\User"
        ]);

        $user->syncPermissions([
            "view stock",
            "view order",
            "view history",
            "view setting"
        ]);

        $user->save();

        // Create a Request for a new user

        // Send registered email
        $user->notify((new UserRegistered()));

        return $user;
    }

    public function activate()
    {
        $this->active = true;
        $this->save();

        // Send mail to user that account is active
        $this->notify((new UserActivated()));
    }

    public function getAllPermissionsAttribute()
    {
        return $this->getPermissionNames();
    }

    public function getLastLoginDateAttribute()
    {
        if (!isset($this->attributes['last_login_at'])) {
            return '-'; //Carbon::now()->format('d-m-Y');
        }
        return Carbon::parse($this->attributes['last_login_at'])->format('d-m-Y');
    }

    public function getLastLoginTimeAttribute()
    {
        if (!isset($this->attributes['last_login_at'])) {
            return ''; //Carbon::now()->format('H:i');
        }
        return Carbon::parse($this->attributes['last_login_at'])->format('H:i');
    }

    public function getIsAccountAttribute()
    {
        foreach ($this->roles as $role) {
            if ($role['name'] == Roles::PORTAL_ACCOUNT) {
                return true;
            }
        }

        return false;
    }

    public function getIsClientAttribute()
    {
        foreach ($this->roles as $role) {
            if ($role['name'] == Roles::PORTAL_CLIENT) {
                return true;
            }
        }

        return false;
    }

    public function getIsPortalAttribute()
    {
        foreach ($this->roles as $role) {
            if ($role['name'] == Roles::PORTAL) {
                return true;
            }
        }

        return false;
    }

    public function getIsSuperAdminAttribute()
    {
        foreach ($this->roles as $role) {
            if ($role['name'] == Roles::SUPER_ADMIN) {
                return true;
            }
        }

        return false;
    }

    public function getIsAdminAttribute()
    {
        foreach ($this->roles as $role) {
            if ($role['name'] == Roles::VAN_WIJK) {
                return true;
            }
        }

        return false;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPassword($token));
    }

    public function request()
    {
        // return $this->morphOne(VanWijkRequest::class, 'requestable');
    }

    public function locations()
    {
        return $this->hasMany(UserLocation::class);
    }

    /** Delivery */
    public function addressRequests()
    {
        return $this->deliveryAddresses();
    }

    public function deliveryAddresses()
    {
        return $this->hasMany(UserDeliveryAddress::class);
    }
    /************/

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getOdooAttribute()
    {
        if (isset($this->attributes['odoo_user_id'])) {
            return (new CompanyService())->find($this->attributes['odoo_user_id']);
        }
        return false;
    }

    public function getPricelist()
    {
        $odoo = $this->getOdooAttribute();
        if ($odoo !== false) {
            return $odoo[0]['property_product_pricelist'];
        }
    }

    public function accounts()
    {
        return $this->hasMany(User::class, 'parent_user_id');
    }

    public function getAccountsWithUserAttribute()
    {
        $accounts = $this->accounts()->get();

        $self = User::find($this->attributes['id']);
        $self = collect([$self]);

        if ($accounts->isEmpty()) {
            return $self;
        }

        return $self->merge($accounts)->toArray();
    }
    public function getAppAccessAttribute()
    {
        if(in_array('app access',json_decode($this->getAllPermissionsAttribute()))){
            return true;
        }else{
            return false;
        }
    }
}
