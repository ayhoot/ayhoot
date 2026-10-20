<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'cover',
        'monetize',
        'price',
        'status',
        'discounts',
        'institute_id',
        'teacher_id',
        'category_id',
        'folder_id',
        'locale',
        'play_music'
    ];

    protected $with = ['category', 'teacher', 'institute'];
    protected $casts = [
        'discounts' => 'array',
        'teacher_id' => 'integer',
        'play_music' => 'boolean'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function games()
    {
        return $this->hasMany(Game::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }

    public function getStudentsCountAttribute()
    {
        if (array_key_exists('students_count', $this->attributes) && $this->attributes['students_count'] !== null) {
            return (int) $this->attributes['students_count'];
        }

        $answersCount = $this->answers()->count();
        $gamesCount = $this->games()->count();
        return max($answersCount, $gamesCount);
    }

    public function getPlaysCountAttribute()
    {
        if (array_key_exists('plays_count', $this->attributes) && $this->attributes['plays_count'] !== null) {
            return (int) $this->attributes['plays_count'];
        }

        return $this->getStudentsCountAttribute();
    }


    public function students()
    {
        return $this->belongsToMany(Student::class, 'exam_student')
            ->withPivot(['is_paid', 'access_at'])
            ->withTimestamps();
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'exam_label');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_exam')->withTimestamps();
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function purchases()
    {
        return $this->hasMany(ExamPurchase::class);
    }

    public function teacherPurchases()
    {
        return $this->hasMany(ExamPurchase::class)->where('purchaser_type', 'teacher');
    }

    public function institutePurchases()
    {
        return $this->hasMany(ExamPurchase::class)->where('purchaser_type', 'institute');
    }

    public function studentPurchases()
    {
        return $this->hasMany(ExamPurchase::class)->where('purchaser_type', 'student');
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

    public function hasAccessFor($user, $gameId = null)
    {
        // Owner has access
        if ($user instanceof Teacher && $this->teacher_id == $user->id) {
            return true;
        }
        
        if ($user instanceof Institute && $this->institute_id == $user->id) {
            return true;
        }

        // Free exams are accessible to all
        if ($this->monetize == 'free') {
            return true;
        }

        // For students, check multiple access methods
        if ($user instanceof Student) {
            // Check direct assignment via game ID if provided (assignment link/QR code)
            if ($gameId) {
                $resolvedGameId = is_object($gameId) ? $gameId->id : $gameId;
                if (Game::where('id', $resolvedGameId)->where('exam_id', $this->id)->exists()) {
                    return true;
                }
            }

            // Check if student has access via any Game assigned to one of their groups
            if (Game::where('exam_id', $this->id)
                ->whereHas('groups.students', function ($q) use ($user) {
                    $q->where('students.id', $user->id);
                })->exists()) {
                return true;
            }

            // Check old pivot table first (legacy support)
            if ($user->exams()->where('exam_id', $this->id)->exists()) {
                return true;
            }
            
            // Check direct exam purchase
            if ($this->isPurchasedBy($user->id, 'student')) {
                return true;
            }
            
            // Check if student has access through purchased courses
            $coursesWithThisExam = Course::whereHas('exams', function($query) {
                $query->where('exam_id', $this->id);
            })->get();
            
            foreach ($coursesWithThisExam as $course) {
                if ($course->hasAccessFor($user)) {
                    return true;
                }
            }
            
            return false;
        }

        // For teachers and institutes, check purchase table and course access
        if ($user instanceof Teacher || $user instanceof Institute) {
            $userType = strtolower(class_basename($user));
            
            // Check direct exam purchase
            if ($this->isPurchasedBy($user->id, $userType)) {
                return true;
            }
            
            // Check if user has access through purchased courses
            $coursesWithThisExam = Course::whereHas('exams', function($query) {
                $query->where('exam_id', $this->id);
            })->get();
            
            foreach ($coursesWithThisExam as $course) {
                if ($course->hasAccessFor($user)) {
                    return true;
                }
            }
            
            return false;
        }

        return false;
    }

    /**
     * Check if user can purchase this exam (not owner)
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

        // Free exams don't need to be purchased
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
     * Scope to only published exams (for public/discovery listings)
     */
    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    /**
     * Scope to filter exams by locale
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

        static::creating(function ($exam) {
            if (empty($exam->locale)) {
                $exam->locale = \App\Services\LocaleService::getStoreLocale();
            }
        });
    }


    // with count students
}