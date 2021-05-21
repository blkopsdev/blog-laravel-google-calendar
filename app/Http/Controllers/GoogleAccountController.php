<?php

namespace App\Http\Controllers;

use App\GoogleAccount;
use App\Services\Google;
use Illuminate\Http\Request;

class GoogleAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the google accounts.
     */
    public function index()
    {
        return view('accounts', [
            'accounts' => auth()->user()->googleAccounts,
        ]);
    }

    /**
     * Handle the OAuth connection which leads to 
     * the creating of a new Google Account.
     */
    public function store(Request $request, Google $google)
    {
        if (! $request->has('code')) {
            return redirect($google->createAuthUrl());
        }

        $google->authenticate($request->get('code'));

        $service = $google->service('People');
        $optParams = [
            'personFields' => 'names,emailAddresses,metadata'
        ];
        
        $results = $service->people->get('people/me', $optParams);
        
        auth()->user()->googleAccounts()->updateOrCreate(
            [
                'user_id' => auth()->user()->id,
            ],
            [
                'google_id'=> $results->metadata->sources[0]->id,
                'name' => $results->names[0]->displayName,
                'email' => $results->emailAddresses[0]->value,
                'token' => $google->getAccessToken(),
            ]
        );

        return redirect()->route('google.index');
    }

    /**
     * Revoke the account's token and delete the it locally.
     */
    public function destroy(GoogleAccount $googleAccount, Google $google)
    {
        $googleAccount->delete();

        $google->revokeToken($googleAccount->token);

        return redirect()->back();
    }
}
