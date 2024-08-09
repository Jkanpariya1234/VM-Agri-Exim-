<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\contact_us;
use Illuminate\Support\Facades\Mail;
    
class contact extends Controller
{
    function index()
    {
        return view('contact');
    }

    public function submit_contact(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'country' => 'required',
            'message' => 'required',
        ]);

        $con = new contact_us();
        $con->name = $request->input('name');
        $con->email = $request->input('email');
        $con->subject = $request->input('subject');
        $con->country = $request->input('country');
        $con->message = $request->input('message');

        $con->save();

        return response()->json(['success' => 'Your message has been sent successfully!']);
    }
}
