<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ZohoController extends Controller
{
    public function index(Request $request)
    {
        $uri = route('zohodeal');
        $scope = 'ZohoCRM.modules.deals.CREATE';
        $client_id = "1000.JO1132HURAL7UM3P8EKTNOBX3VXNLY";
        $accestype = 'offline';
        // making the authorization request for pathing throught Authentification Server
        $redirectTo = 'https://accounts.zoho.com/oauth/v2/auth' . '?' . http_build_query(
            [
            'client_id' => $client_id,
            'redirect_uri' => $uri,
            'scope' => 'ZohoCRM.modules.deals.CREATE',
            'response_type' => 'code',
            ]);
        return redirect($redirectTo);
    }

    public function create(Request $request)
    {
        $input = $request->all();
        // dump($input);
        $client_id = '1000.JO1132HURAL7UM3P8EKTNOBX3VXNLY';
        $client_secret = '91940660047d5effab1fe8a5d78a847ef24b216845';
        $request->session()->forget('zoho_deal_id');

        //request for  generating Zoho CRM token
        // $input['code'] - this is authentification code
        $tokenUrl = 'https://accounts.zoho.com/oauth/v2/token?code='.$input["code"].'&client_id='.$client_id.'&client_secret='.$client_secret.'&redirect_uri='.route('zohodeal').'&grant_type=authorization_code';

        // post request to Resource server
        $curl = curl_init();     
        curl_setopt($curl, CURLOPT_VERBOSE, 0);     
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);     
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);     
        curl_setopt($curl, CURLOPT_TIMEOUT, 300);   
        curl_setopt($curl, CURLOPT_POST, TRUE); // post request  
        curl_setopt($curl, CURLOPT_URL, $tokenUrl);     
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);     

        $tResult = curl_exec($curl);
        curl_close($curl);
        // get json string result with access_token
       $tokenResult = json_decode($tResult);
       dump($tokenResult);
        if(isset($tokenResult->access_token) && $tokenResult->access_token != '') {
            // Forming deal sample for requesting:
            // I passed only mandatory fields for current type of resource
            $jsonData = '{
                "data": [
                    {
                        "Deal_Name": "Deal bohdan09012000",
                        "Stage": "Qualification"
                    }
                ]
            }';

            // forming request for creating deal.
            $curl = curl_init('https://www.zohoapis.com/crm/v2/Deals');
            curl_setopt($curl, CURLOPT_VERBOSE, 0);     
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);     
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);     
            curl_setopt($curl, CURLOPT_TIMEOUT, 300);   
            curl_setopt($curl, CURLOPT_POST, TRUE); // post request  
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);     
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                "Authorization: Zoho-oauthtoken ".$tokenResult->access_token
            ) );
            curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
            
            //Making request for adding deal
            $cResponse = curl_exec($curl);
            curl_close($curl);

            $dealResponse = json_decode($cResponse);  //getting json response.
            dump($dealResponse);
        } else {
            return redirect()->route('home');
        }        
    }
}

