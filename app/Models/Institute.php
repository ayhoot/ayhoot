<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Institute extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'phone',
        'website',
        'description',
        'phone_verified_at',
        'verified_at',
        'code',
        'wallet',
        'card',
        'location',
        'avatar',
        'cover',
        'city_id',
        'invite_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'code',
    ];

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'institute_teacher');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'institute_student');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'institute_id');
    }

    
    public function courses()
    {
        return $this->hasMany(Course::class, 'institute_id');
    }

    public function exams()
    {
        return $this->hasMany(Exam::class, 'institute_id');
    }


    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function tickets()
    {
        return $this->morphMany(Ticket::class, 'sender');
    }

    public static function findByUsername($username)
    {
        return self::where('username', $username)->first();
    }

    public function categories(): MorphMany
    {
        return $this->morphMany(Category::class, 'user');
    }


    public function getCoverAttribute($value)
    {
        return $value ? url('uploads/institute_covers/' . $value) : null;
    }

    public function getAvatarAttribute($value)
    {
        if (!$value) return null;
        if (str_starts_with($value, 'http')) return $value;
        $value = preg_replace('#^/?uploads/#', '', ltrim($value, '/'));
        if (!str_contains($value, '/')) $value = 'institute_avatars/' . $value;
        return url('uploads/' . $value);
    }


    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function instituteRequests()
    {
        return $this->hasMany(TeacherRequest::class);
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
        return $this->belongsToMany(Label::class, 'institute_label');
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
        )->where('exam_purchases.purchaser_type', 'institute');
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
        )->where('course_purchases.purchaser_type', 'institute');
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

        static::creating(function ($institute) {
            if (empty($institute->invite_id)) {
                $institute->invite_id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }


   
}    