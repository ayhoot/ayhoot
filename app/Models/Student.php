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

class Student extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_verified_at',
        'student_id',
        'username',
        'password',
        'website',
        'wallet',
        'card',
        'description',
        'verified_at',
        'code',
        'avatar',
        'cover',
        'city_id'
    ];

    protected $appends = ['name'];

    protected $hidden = [
        'password',
        'remember_token',
        'code',
    ];



    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }


    public function institutes()
    {
        return $this->belongsToMany(Institute::class, 'institute_student');
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_student');
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
        return $this->hasMany(Activity::class, 'student_id');
    }

    public function categories(): MorphMany
    {
        return $this->morphMany(Category::class, 'user');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_student');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_student')->withTimestamps();
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_student');
    }


    public function hasPurchasedExam($examId)
    {
        // Check both old pivot table and new purchase table
        return $this->exams()->where('exam_id', $examId)->exists() ||
               \App\Models\ExamPurchase::where('exam_id', $examId)
                   ->where('purchaser_id', $this->id)
                   ->where('purchaser_type', 'student')
                   ->where('payment_status', 'completed')
                   ->exists();
    }

    public function hasPurchasedCourse($courseId)
    {
        // Check both old pivot table and new purchase table
        return $this->courses()->where('course_id', $courseId)->exists() ||
               \App\Models\CoursePurchase::where('course_id', $courseId)
                   ->where('purchaser_id', $this->id)
                   ->where('purchaser_type', 'student')
                   ->where('payment_status', 'completed')
                   ->exists();
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    
    public function answers()
    {
        return $this->hasMany(Answer::class);
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


    /**
     * Get the avatar image URL with default fallback
     */
    public function getAvatarAttribute($value)
    {
        if (!$value) {
            return '/imgHolders/profile.svg';
        }
        
        if (str_starts_with($value, 'http')) {
            return $value;
        }
        
        return '/uploads/' . ltrim($value, '/');
    }

    /**
     * Get the cover image URL
     */
    public function getCoverAttribute($value)
    {
        if (!$value) {
            return null;
        }
        
        if (str_starts_with($value, 'http')) {
            return $value;
        }
        
        return '/uploads/' . ltrim($value, '/');
    }
}
    