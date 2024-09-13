<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index()
    {
        return view('content.auth.login');
    }
    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        try {
            $user = Manager::select('email')->where(['email' => $request->email])->first();
            if (!$user) return back()->withErrors(['email' => 'The provided credentials do not match our records.',])->onlyInput('email');
            $remember = ($request->has('remember')) ? true : false;
            if (Auth::guard('web')->attempt($credentials, $remember)) {
                // $request->session()->regenerate();
                //$this->searchableData();
				if(auth()->user()->type == 'user'){
                    return redirect('/user-dashboard');
				}else{
					return redirect('/');
				}
            }
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        } catch (\Throwable $th) {
            notify()->error($th->getMessage());
        }
    }




    function searchableData()
    {
        $jsonFilePath = public_path('assets/json/search-vertical.json');
        // Path to the JSON file
        // $jsonFilePath = 'data.json';
        // Read JSON file
        $jsonData = file_get_contents($jsonFilePath);
        // New member object to be added
        // Decode JSON data into PHP array
        $dataArray = json_decode($jsonData, true);
        $customers = Customer::select(
            'id',
            'full_name',
            'username',
            'avater',
            'customer_for',
            'manager_id',
            'phone',
        )->get();

        $new_customers = [];
        foreach ($customers as $key => $cmr_item) {
            if ($cmr_item) {
                $new_customers[] = array(
                    "id" => $cmr_item->id,
                    "name" => "$cmr_item->username | $cmr_item->phone",
                    "subtitle" => $cmr_item->full_name,
                    "url" => route('customer-user.show', $cmr_item->id),
                    "manager_id" => $cmr_item->manager_id,
                    "customer_for" => $cmr_item->customer_for,
                );
            }
        }

        $dataArray['members'] = $new_customers;



        $managers = Manager::select('id', 'name', 'email', 'phone', 'profile_photo_url')->get();
        $new_manager = [];
        foreach ($managers as $key => $m_item) {
            if ($m_item) {
                $new_manager[] = array(
                    "id" => $m_item->id,
                    "name" => $m_item->name,
                    "subtitle" => $m_item->email,
                    "url" => "manager-profile/$m_item->id",
                );
            }
        }

        $dataArray['managers'] = $new_manager;
        $jsonData = json_encode($dataArray, JSON_PRETTY_PRINT);
        file_put_contents($jsonFilePath, $jsonData);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
