<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory,SoftDeletes;
    protected $fillable = [
        'title',
        'description',
        'cover',
        'monetize',
        'price',
        'status',
        'archive',
        'institute_id',
        'options',
        'teacher_id',
        'category_id',
        'folder_id',
        'locale'
    ];

    protected $with = ['exams'];

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'course_exam')->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'course_student')->withTimestamps();
    }

    public function folders()
    {
        return $this->belongsToMany(Folder::class, 'course_folder')->withTimestamps();
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'course_label');
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function purchases()
    {
        return $this->hasMany(CoursePurchase::class);
    }

    public function teacherPurchases()
    {
        return $this->hasMany(CoursePurchase::class)->where('purchaser_type', 'teacher');
    }

    public function institutePurchases()
    {
        return $this->hasMany(CoursePurchase::class)->where('purchaser_type', 'institute');
    }

    public function studentPurchases()
    {
        return $this->hasMany(CoursePurchase::class)->where('purchaser_type', 'student');
    }

    public function isPurchasedBy($userId, $userType)
    {
        // Handle both formats: 'student' and 'App\Models\Student'
        $userTypeClass = 'App\\Models\\' . ucfirst($userType);
        $userTypeSimple = strtolower($userType);
        
        return $this->purchases()
            ->where('purchaser_id', $userId)
            ->where(function($query) use ($userTypeClass, $userTypeSimple) {
                $query->where('purchaser_type', $userTypeClass)
                      ->orWhere('purchaser_type', $userTypeSimple);
            })
            ->where('payment_status', 'completed')
            ->exists();
    }

    public function hasAccessFor($user)
    {
        // Owner has access
        if ($user instanceof Teacher && $this->teacher_id == $user->id) {
            return true;
        }
        
        if ($user instanceof Institute && $this->institute_id == $user->id) {
            return true;
        }

        // Free courses are accessible to all
        if ($this->monetize == 'free') {
            return true;
        }

        // For students, check both old pivot table and new purchase table
        if ($user instanceof Student) {
            // Check old pivot table first (legacy support)
            if ($user->courses()->where('course_id', $this->id)->exists()) {
                return true;
            }
            
            // Check new purchase table
            return $this->isPurchasedBy($user->id, 'student');
        }

        // For teachers and institutes, check purchase table
        $userType = strtolower(class_basename($user));
        return $this->isPurchasedBy($user->id, $userType);
    }

    /**
     * Check if user can purchase this course (not owner)
     */
    public function canBePurchasedBy($user)
    {
        // Owners cannot purchase their own content
        if ($user instanceof Teacher && $this->teacher_id == $user->id) {
            return false;
        }
        
        if ($user instanceof Institute && $this->institute_id == $user->id) {
            return false;
        }

        // Free courses don't need to be purchased
        if ($this->monetize == 'free') {
            return false;
        }

        // Already has access
        if ($this->hasAccessFor($user)) {
            return false;
        }

        return true;
    }

    /**
     * Scope to filter courses by locale
     */
    public function scopeByLocale($query, $locale = null)
    {
        if (!$locale) {
            $locale = \App\Services\LocaleService::getCurrentLocale();
        }
        return $query->where('locale', $locale);
    }

    /**
     * Boot method to set locale when creating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->locale)) {
                $course->locale = \App\Services\LocaleService::getStoreLocale();
            }
        });
    }


    
}
    