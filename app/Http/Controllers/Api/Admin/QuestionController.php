<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\QuestionStoreRequest;
use App\Http\Requests\Admin\QuestionUpdateRequest;
use App\Models\Exam;
use App\Models\Question;

class QuestionController extends Controller
{
    public function index(Request $request,Exam $exam)
    {
        $questions = Question::where('exam_id', $exam->id)->get();
        return response([
            'exam' => Exam::find($exam->id),
            'questions' => $questions
        ]);
    }

    public function store(QuestionStoreRequest $request)
    {

        $data = $request->all();
        if($request->type=='sequence'){
            $data['options']=$request->sequence_options;
        }
        if ($request->hasFile('files')) {
            $filePaths = [];
            foreach ($request->file('files') as $file) {
                $filePaths[] = $file->store('questions');
            }
            $data['files'] = json_encode($filePaths);
        }


        $question = Question::create($data);
        return response($question, 200);
    }

    public function show($id)
    {
        $question = Question::findOrFail($id);
        return response($question);
    }

    public function update(QuestionUpdateRequest $request, $id)
    {

        $data = $request->validated();

        if ($request->hasFile('files')) {
            $filePaths = [];
            foreach ($request->file('files') as $file) {
                $filePaths[] = $file->store('questions'); // Store files in 'questions' directory
            }
            $data['files'] = json_encode($filePaths); // Save file paths as JSON
        }

        $question = Question::findOrFail($id);

        $question->update($data);
        return response($question);
    }

    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();
        return response(null, 204);
    }
}
