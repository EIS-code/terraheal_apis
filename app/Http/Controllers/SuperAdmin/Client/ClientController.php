<?php

namespace App\Http\Controllers\SuperAdmin\Client;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\User;
use App\TherapistUserRating;

class ClientController extends BaseController
{   

    public $successMsg = [
        'no.data.found' => "Data not found !",
        'clients.get' => "Clients found successfully !",
        'client.get' => "Client found successfully !",
    ];

    public function getAllClients() {

        $clients = User::all();
        if (!empty($clients)) {
            return $this->returnSuccess(__($this->successMsg['clients.get']), $clients);
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']), null);
        }
    }
    
    public function getInfo(Request $request) {
        
        $client = User::find($request->client_id);
        $ratings = TherapistUserRating::where('user_id', $request->client_id)->get()->groupBy('type');
        
        $cnt = $rates = $avg = 0;
        $ratingData = [];
        if ($ratings->count() > 0) {
            foreach ($ratings as $i => $rating) {
                $first = $rating->first();
                foreach ($rating as $key => $rate) {
                    $rates += $rate->rating;
                    $cnt++;
                }
                $ratingData[] = [
                    'type' => $first->type,
                    'avg' => number_format($rates / $cnt, 2)
                ];
            }
        }
        $client['ratingData'] = $ratingData;
        return $this->returnSuccess(__($this->successMsg['client.get']), $client);
    }
}
