<?php

namespace App\Http\Controllers\Therapy;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapy;
use App\TherapyQuestionnaire;
use App\TherapyQuestionnaireAnswer;
use Carbon\Carbon;
use DB;

class TherapyController extends BaseController
{
    public $errorMsg = [
        'error.userid' => 'Please provide valid user_id.',
        'error.data' => 'Data should not be empty and it should be valid.'
    ];

    public $successMsg = [
        'success.therapy.questionnaire.found' => 'Therapy questionnaire found successfully !',
        'success.therapy.questionnaire.answers.created' => 'Therapy questionnaire answers created successfully !'
    ];

    public function returns($message = NULL, $with = NULL, $isError = false)
    {
        if ($isError && !empty($message)) {
            $message = !empty($this->errorMsg[$message]) ? __($this->errorMsg[$message]) : __($message);
        } else {
            $message = !empty($this->successMsg[$message]) ? __($this->successMsg[$message]) : __($this->returnNullMsg);
        }

        if (!$isError && !empty($with)) {
            if ($with instanceof Collection && !$with->isEmpty()) {
                return $this->returnSuccess($message, array_values($with->toArray()));
            } else {
                return $this->returnSuccess($message, $with->toArray());
            }
        } elseif ($isError) {
            return $this->returnError($message);
        }

        return $this->returnNull();
    }

    public function getQuestions(Request $request, int $limit = 10)
    {
        $model  = new TherapyQuestionnaire();
        $data   = $request->all();
        $query  = (!empty($data['q'])) ? $data['q'] : NULL;
        $limit  = (!is_numeric($limit)) ? 10 : $limit;

        $getQuestions = $model->where(function($sql) use($query) {
            $sql->where("title", "LIKE", "%{$query}%")
                ->orWhere("placeholder", "LIKE", "%{$query}%");
        })
        ->with('questionnaireAnswer')
        ->limit($limit)->get();

        if (!empty($getQuestions) && !$getQuestions->isEmpty()) {
            $getQuestions->map(function($question) {
                $question->value = NULL;

                if (!empty($question->questionnaireAnswer)) {
                    $question->value = $question->questionnaireAnswer->value;
                }

                unset($question->questionnaireAnswer);
            });
        }

        return $this->returns('success.therapy.questionnaire.found', $getQuestions);
    }

    public function createQuestions(Request $request)
    {
        $therapyQuestionnaireAnswer = [];

        DB::beginTransaction();

        try {
            $model  = new TherapyQuestionnaireAnswer();
            $data   = $request->all();
            $userId = (!empty($data['user_id'])) ? (int)$data['user_id'] : false;
            $data   = (!empty($data['data'])) ? (array)$data['data'] : [];
            $data   = (!isMultidimentional($data)) ? [$data] : $data;
            $now    = Carbon::now();

            if (!$userId) {
                return $this->returns('error.userid', NULL, true);
            }

            if (empty($data)) {
                return $this->returns('error.data', NULL, true);
            }

            $insertData = $matchIds = [];

            foreach ($data as $index => $answers) {
                if (!empty($answers['id'])) {
                    $questionId = (int)$answers['id'];
                    $value      = (!empty($answers['value'])) ? $answers['value'] : NULL;

                    $matchIds[$index] = [
                        'questionnaire_id' => $questionId,
                        'user_id'          => $userId
                    ];

                    $insertData[$index] = [
                        'value'            => $value,
                        'questionnaire_id' => $questionId,
                        'user_id'          => $userId,
                        'created_at'       => $now,
                        'updated_at'       => $now
                    ];

                    $validator = $model->validator($insertData[$index]);
                    if ($validator->fails()) {
                        return $this->returns($validator->errors()->first(), NULL, true);
                    }

                    $therapyQuestionnaireAnswer = $model->updateOrCreate($matchIds[$index], $insertData[$index]);
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.therapy.questionnaire.answers.created', collect([]));
    }
}
