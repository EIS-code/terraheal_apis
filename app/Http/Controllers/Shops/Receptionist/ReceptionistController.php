<?php

namespace App\Http\Controllers\Shops\Receptionist;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Receptionist;
use App\ReceptionistDocuments;

class ReceptionistController extends BaseController {

    public $successMsg = [
        
        'receptionist.create' => 'Receptionist created successfully',
        'receptionist.document' => 'Receptionist document uploaded successfully',
        'receptionist.data' => 'Receptionist found successfully',
    ];
    
    public function createReceptionist(Request $request) {
        
        $model = new Receptionist();
        $data = $request->all();
        
        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        /* For profile Image */
        if ($request->hasFile('photo')) {
            $checkImage = $model->validatePhoto($data);
            if ($checkImage->fails()) {
                unset($data['photo']);

                return $this->returnError($checkImage->errors()->first(), NULL, true);
            }
            $fileName = time().'.' . $data['photo']->getClientOriginalExtension();
            $storeFile = $data['photo']->storeAs($model->profilePhotoPath, $fileName, $model->fileSystem);

            if ($storeFile) {
                $data['photo'] = $fileName;
            }
        }        
        $receptionist = $model->create($data);
        
        return $this->returnSuccess(__($this->successMsg['receptionist.create']),$receptionist);
    }

    public function addDocument(Request $request) {
        
        $model = new ReceptionistDocuments();
        $data = $request->all();
        
        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        if ($request->hasFile('file_name')) {
            $fileName = time().'.' . $data['file_name']->getClientOriginalExtension();
            $storeFile = $data['file_name']->storeAs($model->directory, $fileName, $model->fileSystem);

            if ($storeFile) {
                $data['file_name'] = $fileName;
            }
        }        
        $document = $model->create($data);
        
        return $this->returnSuccess(__($this->successMsg['receptionist.document']),$document);
    }
    
    public function getReceptionist(Request $request) {
        
        $receptionist = Receptionist::with('country','city','shop:id,name','documents')->where('id',$request->receptionist_id)->get();
        
        return $this->returnSuccess(__($this->successMsg['receptionist.data']),$receptionist);
    }
}
