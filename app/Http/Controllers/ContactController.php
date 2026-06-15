<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Show the contact page.
     */
    public function index()
    {
        return view('contact');
    }

    /**
     * Handle contact form submission.
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'company'    => 'nullable|string|max:255',
            'subject'    => 'required|string|in:general,sales,support,partnership,bug,feature',
            'message'    => 'required|string|min:10|max:5000',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Store in log for now (can be upgraded to database + email later)
        \Log::info('Contact form submission', [
            'name'    => $request->first_name . ' ' . $request->last_name,
            'email'   => $request->email,
            'company' => $request->company,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        return back()->with('success', 'Thank you! Your message has been sent. We\'ll get back to you within 24 hours.');
    }
}
