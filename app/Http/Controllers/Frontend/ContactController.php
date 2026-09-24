<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\FooterDetails;
use App\Models\ContactEnquiry;

class ContactController extends Controller
{
    public function index()
    {
        $contact = FooterDetails::latest()->first();
        return view('frontend.contactus', compact('contact'));
    }

    public function send(Request $request)
    {
        Log::info('=== send() started ===', ['ip' => $request->ip()]);

        $request->validate([
            'name'                 => 'required|string|max:255',
            'email'                => 'required|email|max:255',
            'phone'                => 'nullable|string|max:30',
            'phone_full'           => 'nullable|string|max:30',
            'subject'              => 'required|string|max:255',
            'message'              => 'nullable|string',
            'g-recaptcha-response' => 'required',
        ], [
            'g-recaptcha-response.required' => 'Please confirm you are not a robot.',
        ]);

        // Verify reCAPTCHA with Google (outbound HTTPS, port 443 — not blocked)
        $captcha = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => '6LeYGlgtAAAAAGONYEX_WXARMp12OGJ1YGbFzi5_',
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!($captcha->json('success') ?? false)) {
            Log::warning('reCAPTCHA failed', $captcha->json() ?? []);
            return back()
                ->withInput()
                ->withErrors(['g-recaptcha-response' => 'reCAPTCHA verification failed. Please try again.']);
        }

        Log::info('reCAPTCHA passed');
        Log::info('Validation passed', ['email' => $request->email]);

        // Use the full international number if available, else the raw input
        $phone = $request->phone_full ?: $request->phone;

        // Save to database
        try {
            $enquiry = ContactEnquiry::create([
                'name'    => $request->name,
                'email'   => $request->email,
                'phone'   => $phone,
                'subject' => $request->subject,
                'message' => $request->message,
            ]);
            Log::info('Enquiry saved to DB', ['id' => $enquiry->id]);
        } catch (\Exception $e) {
            Log::error('Failed to save enquiry to DB: ' . $e->getMessage());
        }

        $details = [
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $phone,
            'subject' => $request->subject,
            'message' => $request->message,
        ];
        Log::info('Details prepared', $details);

        // Admin mail
        Log::info('Attempting admin mail...');
        try {
            Mail::send('emails.contact_admin', ['details' => $details], function ($message) {
                $message->to('info@ddpu.co.uk')
                        ->cc([
                            'admin@ddpu.co.uk',
                            // 'shweta@matrixbricks.com',
                            // 'smrita@matrixbricks.com'
                        ])
                        ->subject('Contact Us Enquiry');
            });
            Log::info('Admin mail sent successfully');
        } catch (\Exception $e) {
            Log::error('Failed to send contact mail to admin: ' . $e->getMessage());
        }

        // User mail
        Log::info('Attempting user mail...', ['to' => $details['email']]);
        try {
            Mail::send('emails.contact_user', ['details' => $details], function ($message) use ($details) {
                $message->to($details['email'])
                        ->cc('admin@ddpu.co.uk')
                        ->subject('Thank You for Your Enquiry');
            });
            Log::info('User mail sent to: ' . $details['email']);
        } catch (\Exception $e) {
            Log::error('Failed to send thank-you mail to user: ' . $e->getMessage());
        }

        Log::info('=== send() finished, redirecting ===');

        return redirect()->route('thankyou')
                         ->with('success', 'Your enquiry has been sent successfully.');
    }
}