<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Show Terms and Conditions page
     */
    public function termsAndConditions()
    {
        return view('legal.terms-and-conditions');
    }

    /**
     * Show Privacy Policy page
     */
    public function privacyPolicy()
    {
        return view('legal.privacy-policy');
    }

    /**
     * Show Data Protection Notice
     */
    public function dataProtectionNotice()
    {
        return view('legal.data-protection-notice');
    }

    /**
     * Show Cookie Policy
     */
    public function cookiePolicy()
    {
        return view('legal.cookie-policy');
    }
}
