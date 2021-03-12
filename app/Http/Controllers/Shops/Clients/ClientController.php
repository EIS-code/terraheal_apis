<?php

namespace App\Http\Controllers\Shops\Clients;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\User;


class ClientController extends BaseController {

    public $successMsg = [
        'client.data.found' => 'Client data found successfully'
    ];
    
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
}
