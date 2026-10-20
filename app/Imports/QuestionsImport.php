<?php

namespace App\Imports;

use App\Models\Question;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;

class QuestionsImport implements ToCollection
{
    protected $exam;

    public function __construct($exam)
    {
        $this->exam = $exam;
    }

    public function collection(Collection $rows)
    {
        $teacherId = Auth::id();
        $instituteId = Auth::user()->selected_institute;

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Skip header

            Question::create([
                'exam_id' => $this->exam->id,
                'teacher_id' => $teacherId,
                'institute_id' => $instituteId,
                'title' => $row[0],
                'type' => $row[1] ?? 'ordinary',
                'duration' => (int)($row[2] ?? 60),
                'points' => (int)($row[3] ?? 1),
                'correct_answer' => $row[4]+1 ?? null,
                'options' => json_encode(explode('|', $row[5] ?? '')),
                'alternative_answers' => json_encode(explode('|', $row[6] ?? '')),
                'sequence_options' => json_encode(explode('|', $row[7] ?? '')),
                'allow_partial_credit' => $row[8] ?? false,
                'long_answer' => $row[9] ?? null,
                'explanation' => $row[10] ?? null,
                'difficulty_level' => $row[11] ?? 'medium',
                'files' => json_encode([]),
                'q_options' => json_encode([]),
            ]);
        }
    }
}
