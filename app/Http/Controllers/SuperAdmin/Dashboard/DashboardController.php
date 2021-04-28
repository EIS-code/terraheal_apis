<?php

namespace App\Http\Controllers\SuperAdmin\Dashboard;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapy;
use App\Massage;
use App\Therapist;
use App\Shop;
use App\User;

class DashboardController extends BaseController {

    public $successMsg = [
        'data.found' => 'Dashboard details found successfully.',
    ];

    public function getDetails(Request $request) {
        
        $massages = Massage::all()->count(); 
        $therapies = Therapy::all()->count();
        $shops = Shop::where('is_admin', Shop::IS_ADMIN)->get()->count();
        $therapists = Therapist::all()->count();
        $clients = User::all()->count();
        
        return $this->returnSuccess(__($this->successMsg['data.found']), ['massages' => $massages, 'therapies' => $therapies, 'shops' => $shops,
            'therapists' => $therapists, 'clients' => $clients]);
    }
    
    
}
