<?php

namespace App\Http\Controllers\Shops\Rooms;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Room;

class RoomsController extends BaseController {

    public $successMsg = [
        
        'rooms.create' => 'Room created successfully',
        'rooms.data.found' => 'Rooms found successfully',
    ];
    
    public function createRoom(Request $request) {
        
        $data = $request->all();
        $model = new Room();
        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        unset($data['id']);
        $room = Room::create($data);
        return $this->returnSuccess(__($this->successMsg['rooms.create']),$room);
    }
    
    public function getRooms(Request $request) {
        
        $rooms = Room::where('shop_id', $request->shop_id)->pluck('name','id');
        return $this->returnSuccess(__($this->successMsg['rooms.data.found']),$rooms);
    }

}
