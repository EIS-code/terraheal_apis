<?php

namespace App\Http\Controllers\News;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\News;

class NewsController extends BaseController
{
    public $errorMsg = [
        
    ];

    public $successMsg = [
        'found.successfully'    => 'News found successfully.',
        'read.successfully'     => 'Read successfully.'
    ];

    public function get()
    {
        $model  = new News();

        $data   = $model::where('is_read', $model::NOT_READ)->get();

        return $this->returns('found.successfully', $data);
    }

    public function setRead(Request $request)
    {
        $model  = new News();
        $id     = (int)$request->get('id', false);

        if (!empty($id)) {
            $find = $model::find($id);

            if (!empty($find)) {
                $find->is_read = $model::READ;

                if ($find->save()) {
                    return $this->returns('read.successfully');
                }
            }
        }

        return $this->returns();
    }

    public function returns($message = NULL, $with = NULL)
    {
        $message = !empty($this->successMsg[$message]) ? __($this->successMsg[$message]) : __($this->returnNullMsg);

        if (!empty($with) && !$with->isEmpty()) {
            return $this->returnSuccess($message, array_values($with->toArray()));
        } elseif (!empty($message)) {
            return $this->returnSuccess($message);
        }

        return $this->returnNull();
    }
}
