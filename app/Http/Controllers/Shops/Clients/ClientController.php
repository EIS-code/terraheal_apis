<?php

namespace App\Http\Controllers\Shops\Clients;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\User;
use DB;
use Carbon\Carbon;

class ClientController extends BaseController {

    public $successMsg = [
        'client.data.found' => 'Client data found successfully',
        'client.future.booking' => 'Client future booking found successfully'
    ];
    
     public function __construct() {

        $query = DB::table('booking_massages')
                        ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                        ->join('massage_timings', 'massage_timings.id', '=', 'booking_massages.massage_timing_id')
                        ->join('massages', 'massages.id', '=', 'massage_timings.massage_id')
                        ->leftJoin('rooms', 'rooms.id', '=', 'booking_massages.room_id')
                        ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                        ->join('users', 'users.id', '=', 'bookings.user_id')
                        ->join('therapists', 'therapists.id', '=', 'booking_infos.therapist_id')
                        ->join('session_types', 'session_types.id', '=', 'bookings.session_id')
                        ->leftJoin('user_gender_preferences', 'user_gender_preferences.id', '=', 'booking_massages.gender_preference')
                        ->select('booking_massages.id AS bookingMassageId', 'bookings.session_id AS sessionId', 'session_types.type AS sessionType', 'bookings.shop_id AS shop_id',
                                DB::raw('CONCAT(COALESCE(users.name,"")," ",COALESCE(users.surname,"")) AS clientName'), 'massages.name AS massageName',
                                'massage_timings.time AS massageDuration', 'booking_infos.massage_time as massageStartTime','booking_infos.massage_date AS massageDate',
                                DB::raw('CONCAT(COALESCE(therapists.name,"")," ",COALESCE(therapists.surname,"")) AS therapistName'),'booking_infos.therapist_id AS therapist_id', 
                                'rooms.name AS roomName', 'booking_massages.notes_of_injuries AS note', 'user_gender_preferences.name AS genderPreference');
        $this->query = $query;
    }
    
    public function searchClients(Request $request) {
        
        $pageNumber = isset($request->page_number) ? $request->page_number : 1;
        $search_val = $request->search_val;
        
        $clients = User::where(['shop_id' => $request->shop_id, 'is_removed' => User::$notRemoved]);
        
        if(isset($request->name_filter))
        {
            $clients->where('name', 'like', $request->name_filter.'%');
        }
        if(isset($search_val))
        {
            if(is_numeric($search_val)) {
                $clients->where('id', $search_val);
            } else {
                $clients->orWhere('name', 'like', $search_val);
                $clients->orWhere('email', $search_val);
            }
        }
        $clientData = $clients->paginate(10, ['*'], 'page', $pageNumber);
        return $this->returnSuccess(__($this->successMsg['client.data.found']), $clientData);
    }
    
    public function clientDetails(Request $request) {
        
        $client = User::with('shop','city','country')
                ->where(['shop_id' => $request->shop_id,'id' => $request->client_id])
                ->get();
        return $this->returnSuccess(__($this->successMsg['client.data.found']), $client);
    }
    
    public function getFutureBookings(Request $request) {
        
        $futureBookings = $this->query->where(['bookings.shop_id' => $request->shop_id, 'booking_infos.therapist_id' => $request->client_id])
                            ->where('booking_infos.massage_date', '>=', Carbon::now()->format('Y-m-d'))                
                            ->get();
        return $this->returnSuccess(__($this->successMsg['client.future.booking']), $futureBookings);
    }
}
