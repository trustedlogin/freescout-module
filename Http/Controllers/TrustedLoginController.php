<?php

namespace Modules\TrustedLogin\Http\Controllers;

use App\Mailbox;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class TrustedLoginController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('trustedlogin::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        return view('trustedlogin::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('trustedlogin::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        return view('trustedlogin::edit');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function mailboxSettings()
    {
        // Get settings for the current mailbox
        $mailbox_id = \Route::current()->parameter('mailbox_id');
        $mailbox = Mailbox::find($mailbox_id);
        $trustedloginMailboxEnabled = $mailbox->getMeta('trustedlogin_enabled', false);

        return view('trustedlogin::mailboxSettings', compact('mailbox', 'trustedloginMailboxEnabled'));
    }

    /**
     * Save Mailbox Settings
     */
    public function saveMailboxSettings(Request $request)
    {
        // Get settings for the current mailbox
        $mailbox_id = \Route::current()->parameter('mailbox_id');
        $mailbox = Mailbox::find($mailbox_id);

        // Save the settings
        $value = $request->input('trustedlogin_enabled');
        $mailbox->setMetaParam('trustedlogin_enabled', $value, true);


        \Session::flash('flash_success_floating', __('TrustedLogin Mailbox Settings saved'));

        // Redirect back to the mailbox settings page
        return redirect()->route('mailboxes.trustedlogin', ['mailbox_id' => $mailbox_id]);
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy()
    {
    }
}
