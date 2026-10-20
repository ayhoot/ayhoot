<?php

namespace App\Models;

use App\Http\Middleware\Authenticate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Teacher extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_verified_at',
        'teacher_id',
        'username',
        'password',
        'verified_at',
        'wallet',
        'card',
        'code',
        'website',
        'description',
        'plan_id',
        'name',
        'address',
        'status',
        'avatar',
        'cover',
        'city_id',
        'invite_id'
    ];

    protected $appends = ['name', 'selected_institute'];
    protected $with = ['institutes', 'plan'];

    protected $hidden = [
        'password',
        'remember_token',
        'code',
    ];
    protected $casts = [
        'plan_id' => 'integer',
    ];


    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }


    public function institutes()
    {
        return $this->belongsToMany(Institute::class, 'institute_teacher');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'teacher_student');
    }


    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }


    public function getSelectedInstituteAttribute()
    {
        $token = $this->currentAccessToken();
        return $token ? $token->institute_id : null;
    }


    public static function findByUsername($username)
    {
        return self::where('username', $username)->first();
    }

    public function tickets()
    {
        return $this->morphMany(Ticket::class, 'sender');
    }
    public function activities()
    {
        return $this->hasMany(Activity::class, 'teacher_id');
    }

    public function categories(): MorphMany
    {
        return $this->morphMany(Category::class, 'user');
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function teacherRequests()
    {
        return $this->hasMany(TeacherRequest::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'teacher_id');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function getCoverAttribute($value)
    {
        return $value ? url('uploads/teacher_covers/' . $value) : null;
    }

    public function getAvatarAttribute($value)
    {
        if (!$value) return null;
        if (str_starts_with($value, 'http')) return $value;
        $value = preg_replace('#^/?uploads/#', '', ltrim($value, '/'));
        if (!str_contains($value, '/')) $value = 'teacher_avatars/' . $value;
        return url('uploads/' . $value);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function getFeatureValue(string $role)
    {
        return optional(
            $this->plan?->features->firstWhere('role', $role)
        )?->value;
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'teacher_label');
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function examPurchases()
    {
        return $this->morphMany(ExamPurchase::class, 'purchaser');
    }

    public function coursePurchases()
    {
        return $this->morphMany(CoursePurchase::class, 'purchaser');
    }

    public function purchasedExams()
    {
        return $this->hasManyThrough(
            Exam::class,
            ExamPurchase::class,
            'purchaser_id',
            'id',
            'id',
            'exam_id'
        )->where('exam_purchases.purchaser_type', 'teacher');
    }

    public function purchasedCourses()
    {
        return $this->hasManyThrough(
            Course::class,
            CoursePurchase::class,
            'purchaser_id',
            'id',
            'id',
            'course_id'
        )->where('course_purchases.purchaser_type', 'teacher');
    }

    public function groupAssignments()
    {
        return $this->belongsToMany(Group::class, 'group_assignments');
    }




    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }

    public function readNotifications()
    {
        return $this->notifications()->read();
    }

    public function markAllNotificationsAsRead()
    {

        return $this->unreadNotifications()->update(['read_at' => now()]);
    }

    protected static function boot()
        {
            parent::boot();

            static::creating(function ($teacher) {
                if (empty($teacher->invite_id)) {
                    $teacher->invite_id = (string) \Illuminate\Support\Str::uuid();
                }
            });
        }




    

}
    