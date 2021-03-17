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
        
        $model = new Room();
        $checks = $model->validator($request->all());
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $room = Room::create($request->all());
        return $this->returnSuccess(__($this->successMsg['rooms.create']),$room);
    }
    
    public function getRooms(Request $request) {
        
        $rooms = Room::where('shop_id', $request->shop_id)->pluck('name','id');
        return $this->returnSuccess(__($this->successMsg['rooms.data.found']),$rooms);
    }

}
