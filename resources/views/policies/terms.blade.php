@extends('layouts.app')

@section('title', 'Terms and Conditions - MCC News Aggregator')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h2 class="mb-0"><i class="fas fa-file-contract"></i> Terms and Conditions</h2>
                    <p class="mb-0">User Agreement for MCC News Aggregator</p>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle"></i> Effective Date:</strong> {{ date('F d, Y') }}
                        <br>
                        <strong><i class="fas fa-sync"></i> Last Updated:</strong> {{ date('F d, Y') }}
                    </div>

                    <h3>1. Acceptance of Terms</h3>
                    <p>
                        By accessing and using the Madridejos Community College (MCC) News Aggregator with Chatbot service (the "Service"), you accept and agree to be bound by these Terms and Conditions. If you do not agree to these terms, please do not use the Service.
                    </p>

                    <h3>2. Description of Service</h3>
                    <p>The Service provides:</p>
                    <ul>
                        <li>News and announcement aggregation for MCC students and faculty</li>
                        <li>Department-specific content filtering</li>
                        <li>Interactive chatbot assistance</li>
                        <li>Comment and interaction features</li>
                        <li>Profile management capabilities</li>
                    </ul>

                    <h3>3. User Eligibility</h3>
                    <p>To use this Service, you must:</p>
                    <ul>
                        <li>Be an active student or faculty member of MCC</li>
                        <li>Have a valid institutional email account (MS365 or Gmail)</li>
                        <li>Provide accurate registration information</li>
                        <li>Maintain the security of your account</li>
                    </ul>

                    <h3>4. User Account Responsibilities</h3>
                    <p>You are responsible for:</p>
                    <ul>
                        <li><strong>Account Security:</strong> Maintaining the confidentiality of your password and login credentials</li>
                        <li><strong>Account Activity:</strong> All activities conducted under your account</li>
                        <li><strong>Information Accuracy:</strong> Providing accurate and up-to-date information</li>
                        <li><strong>Immediate Notification:</strong> Reporting unauthorized access to your account immediately</li>
                    </ul>

                    <h3>5. Acceptable Use</h3>
                    <p>You agree NOT to:</p>
                    <ul>
                        <li>Use the Service for any illegal purpose</li>
                        <li>Post, transmit, or share harmful, offensive, or inappropriate content</li>
                        <li>Attempt to hack, breach, or compromise system security</li>
                        <li>Impersonate another user or entity</li>
                        <li>Upload malicious code, viruses, or malware</li>
                        <li>Use automated scripts or bots without authorization</li>
                        <li>Violate intellectual property rights of others</li>
                        <li>Harass, threaten, or abuse other users</li>
                    </ul>

                    <h3>6. Content and Intellectual Property</h3>
                    <p>
                        All content on this Service, including news articles, announcements, text, graphics, logos, and software, is the property of MCC or its content providers and is protected by copyright and trademark laws.
                    </p>

                    <h3>7. User-Generated Content</h3>
                    <p>
                        You retain ownership of content you post. However, by posting content, you grant MCC a non-exclusive, royalty-free license to use, modify, and display your content in connection with the Service.
                    </p>

                    <h3>8. Privacy</h3>
                    <p>
                        Your use of the Service is also governed by our Privacy Policy, which complies with the Data Privacy Act of 2012. Please review our <a href="{{ route('privacy') }}">Privacy Policy</a> to understand how we collect, use, and protect your information.
                    </p>

                    <h3>9. Service Availability</h3>
                    <p>We strive to provide uninterrupted service but make no guarantees regarding:</p>
                    <ul>
                        <li>Continuous availability without interruption or errors</li>
                        <li>Speed or performance of the Service</li>
                        <li>Accuracy or completeness of information</li>
                    </ul>

                    <h3>10. Account Termination</h3>
                    <p>We reserve the right to suspend or terminate your account if:</p>
                    <ul>
                        <li>You violate these Terms and Conditions</li>
                        <li>You engage in fraudulent or illegal activities</li>
                        <li>You are no longer affiliated with MCC</li>
                        <li>You fail to comply with institutional policies</li>
                    </ul>

                    <h3>11. Limitation of Liability</h3>
                    <p>
                        MCC and its service providers shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of the Service. The total liability shall not exceed the amount you paid for using the Service.
                    </p>

                    <h3>12. Indemnification</h3>
                    <p>
                        You agree to indemnify and hold harmless MCC, its officers, employees, and agents from any claims, losses, damages, or expenses arising from your use of the Service or violation of these terms.
                    </p>

                    <h3>13. Modifications to Terms</h3>
                    <p>
                        We reserve the right to modify these Terms and Conditions at any time. Material changes will be notified through the Service or via email. Continued use constitutes acceptance of modified terms.
                    </p>

                    <h3>14. Governing Law</h3>
                    <p>
                        These Terms and Conditions are governed by the laws of the Republic of the Philippines. Any disputes shall be resolved in the appropriate courts of the Philippines.
                    </p>

                    <h3>15. Contact Information</h3>
                    <p>For questions about these Terms, please contact:</p>
                    <div class="alert alert-secondary">
                        <strong>MCC News Aggregator Support</strong><br>
                        Madridejos Community College<br>
                        Email: mcc-nac@mcclawis.edu.ph<br>
                        Phone: [Contact Number]<br>
                        Address: [College Address]
                    </div>

                    <h3>16. Severability</h3>
                    <p>
                        If any provision of these Terms is found to be invalid or unenforceable, the remaining provisions shall remain in full effect.
                    </p>

                    <h3>17. Entire Agreement</h3>
                    <p class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> These Terms and Conditions, together with the Privacy Policy, constitute the entire agreement between you and MCC regarding the use of this Service.
                    </p>

                    <div class="mt-4 text-center">
                        <a href="{{ route('register') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Registration
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Go to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
    }
    
    .card-header {
        padding: 2rem 2rem 1rem;
    }
    
    .card-body {
        padding: 2rem;
        font-size: 1rem;
        line-height: 1.8;
    }
    
    .card-body h3 {
        color: #0066cc;
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 600;
    }
    
    .card-body h3:first-child {
        margin-top: 1rem;
    }
    
    .card-body ul {
        padding-left: 2rem;
        margin: 1rem 0;
    }
    
    .card-body ul li {
        margin-bottom: 0.5rem;
    }
    
    .card-body ul li strong {
        color: #0066cc;
    }
    
    .alert {
        margin: 1.5rem 0;
        padding: 1rem 1.5rem;
    }
    
    .btn {
        margin: 0 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
    }
</style>
@endsection

