<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;



    protected $fillable = [
        'exam_id',
        'teacher_id',
        'institute_id',
        'title',
        'slide',
        'type',
        'duration',
        'points',
        'correct_answer',
        'options',
        'short_answer',
        'alternative_answers',
        'sequence_options',
        'allow_partial_credit',
        'long_answer',
        'explanation',
        'difficulty_level',
        'category_id',
        'files',
        'q_options',
        'locale'
    ];
    protected $with = ['exam'];
    // protected $hidden = ['sequence_options'];

    protected $casts = [
        'files' => 'array',
        'alternative_answers' => 'array',
        'sequence_options' => 'array',
        // Temporarily remove these casts to debug
        // 'options' => 'array',
        // 'q_options' => 'array',
    ];


    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }


    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
    public function getFilesAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setFilesAttribute($value)
    {
        $this->attributes['files'] = json_encode($value);
    }

    public function getOptionsAttribute($value)
    {
        if (is_null($value) || $value == '') {
            return [];
        }

        // If already an array, return as is
        if (is_array($value)) {
            return $value;
        }

        $options = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        if (is_array($options)) {
            $options = array_map(function ($option) {
                if (is_array($option) && isset($option['correct'])) {
                    $option['correct'] = filter_var($option['correct'], FILTER_VALIDATE_BOOLEAN);
                }
                return $option;
            }, $options);
        }

        return $options ?? [];
    }
    public function getQOptionsAttribute($value)
    {
        return json_decode($value, true);
    }
    public function setQOptionsAttribute($value)
    {
        $this->attributes['q_options'] = json_encode($value);
    }


    public function setOptionsAttribute($value)
    {
        // If already a JSON string, keep it as is
        if (is_string($value) && (json_decode($value) !== null || $value == 'null' || $value == '')) {
            $this->attributes['options'] = $value;
            return;
        }

        // If null or empty, set as empty JSON array
        if (is_null($value) || (is_array($value) && empty($value))) {
            $this->attributes['options'] = '[]';
            return;
        }

        // Encode array to JSON
        $this->attributes['options'] = json_encode($value);
    }

    public function getAlternativeAnswersAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setAlternativeAnswersAttribute($value)
    {
        $this->attributes['alternative_answers'] = json_encode($value);
    }

    public function getSequenceOptionsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setSequenceOptionsAttribute($value)
    {
        $this->attributes['sequence_options'] = json_encode($value);
    }

    /**
     * Boot method to set locale when creating
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($question) {
            if (empty($question->locale)) {
                $question->locale = \App\Services\LocaleService::getStoreLocale();
            }
        });
    }
}
