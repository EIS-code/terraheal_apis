<?php

namespace App\Http\Controllers\SuperAdmin\Client;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\User;

class ClientController extends BaseController
{   

    public $successMsg = [
        'no.data.found' => "Data not found !",
        'clients.get' => "Clients found successfully !",
    ];

    public function getAllClients() {

        $clients = User::all();
        if (!empty($clients)) {
            return $this->returnSuccess(__($this->successMsg['clients.get']), $clients);
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']), null);
        }
    }
}
