<?php

namespace App\Http\Controllers\WaitingList;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Massage;
use DB;

class WaitingListController extends BaseController {

    public function ongoingMassage(Request $request) {
        
        $massage = Massage::where('shop_id' , $request->shop_id)->select('id')->get()->toArray();
                
        $ongoingMassages = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('massage_timings', 'massage_timings.id', '=', 'booking_massages.massage_timing_id')
                ->join('massages', 'massages.id', '=', 'massage_timings.massage_id')
                ->leftJoin('rooms', 'rooms.id', '=', 'booking_massages.room_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->join('users', 'users.id', '=', 'bookings.user_id')
                ->join('therapists', 'therapists.id', '=', 'booking_infos.therapist_id')
                ->join('session_types', 'session_types.id', '=', 'bookings.session_id')
                ->leftJoin('user_gender_preferences', 'user_gender_preferences.id', '=', 'booking_massages.gender_preference')
                ->where(['booking_massages.is_confirm' => '1', 'bookings.booking_type' => $request->type])
                ->whereIn('massage_timings.massage_id',$massage)
                ->select('booking_massages.id AS bookingMassageId','bookings.session_id AS sessionId','session_types.type AS sessionType',
                        DB::raw('CONCAT(COALESCE(users.name,"")," ",COALESCE(users.surname,"")) AS clientName'),'massages.name AS massageName',
                        'massage_timings.time AS massageDuration','booking_infos.massage_time as massageStartTime',
                        DB::raw('CONCAT(COALESCE(therapists.name,"")," ",COALESCE(therapists.surname,"")) AS therapistName'),'rooms.name AS roomName',
                        'booking_massages.notes_of_injuries AS note','user_gender_preferences.name AS genderPreference')->get();                        

       return ['ongoingMassages' => $ongoingMassages];
    }
    public function waitingMassage(Request $request) {
        
        $massage = Massage::where('shop_id' , $request->shop_id)->select('id')->get()->toArray();                
        
        $waitingMassages = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('massage_timings', 'massage_timings.id', '=', 'booking_massages.massage_timing_id')
                ->join('massages', 'massages.id', '=', 'massage_timings.massage_id')
                ->leftJoin('rooms', 'rooms.id', '=', 'booking_massages.room_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->join('users', 'users.id', '=', 'bookings.user_id')
                ->join('therapists', 'therapists.id', '=', 'booking_infos.therapist_id')
                ->join('session_types', 'session_types.id', '=', 'bookings.session_id')
                ->leftJoin('user_gender_preferences', 'user_gender_preferences.id', '=', 'booking_massages.gender_preference')
                ->where(['booking_massages.is_confirm' => '0', 'bookings.booking_type' => $request->type])
                ->whereIn('massage_timings.massage_id',$massage)
                ->select('booking_massages.id AS bookingMassageId','bookings.session_id AS sessionId','session_types.type AS sessionType',
                        DB::raw('CONCAT(COALESCE(users.name,"")," ",COALESCE(users.surname,"")) AS clientName'),'massages.name AS massageName',
                        'massage_timings.time AS massageDuration','booking_infos.massage_time as massageStartTime',
                        DB::raw('CONCAT(COALESCE(therapists.name,"")," ",COALESCE(therapists.surname,"")) AS therapistName'),'rooms.name AS roomName',
                        'booking_massages.notes_of_injuries AS note','user_gender_preferences.name AS genderPreference')->get();

       return ['waitingMssages' => $waitingMassages];
    }
}
