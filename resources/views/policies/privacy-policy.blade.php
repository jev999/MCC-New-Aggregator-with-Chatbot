@extends('layouts.app')

@section('title', 'Privacy Policy - MCC News Aggregator')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0"><i class="fas fa-shield-alt"></i> Privacy Policy</h2>
                    <p class="mb-0">Data Privacy Act of 2012 (Republic Act No. 10173) Compliance</p>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle"></i> Effective Date:</strong> {{ date('F d, Y') }}
                        <br>
                        <strong><i class="fas fa-user-shield"></i> Last Updated:</strong> {{ date('F d, Y') }}
                    </div>

                    <h3>1. Introduction</h3>
                    <p>
                        Madridejos Community College (MCC) News Aggregator with Chatbot ("we", "our", or "us") is committed to protecting your privacy and personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information in accordance with the Data Privacy Act of 2012 (Republic Act No. 10173) of the Philippines.
                    </p>

                    <h3>2. Data Collection</h3>
                    <p>We collect the following personal information:</p>
                    <ul>
                        <li><strong>Account Information:</strong> Email address, password (encrypted), full name</li>
                        <li><strong>Role Information:</strong> Student/Faculty designation, department, year level (for students)</li>
                        <li><strong>Activity Data:</strong> Login logs, access timestamps, geolocation data (for security purposes)</li>
                        <li><strong>Usage Data:</strong> News views, comments, chatbot interactions</li>
                    </ul>

                    <h3>3. How We Use Your Information</h3>
                    <p>We use your personal information to:</p>
                    <ul>
                        <li>Provide news and information services tailored to your department and year level</li>
                        <li>Authenticate your identity and authorize access</li>
                        <li>Send important announcements and notifications</li>
                        <li>Analyze usage patterns to improve our services</li>
                        <li>Ensure security and prevent unauthorized access</li>
                        <li>Comply with legal and regulatory requirements</li>
                    </ul>

                    <h3>4. Data Security</h3>
                    <p>We implement appropriate technical and organizational security measures to protect your personal information:</p>
                    <ul>
                        <li><strong>Encryption:</strong> All sensitive data including passwords are encrypted using industry-standard algorithms</li>
                        <li><strong>HTTPS/TLS:</strong> All communication is secured using TLS 1.2+ encryption</li>
                        <li><strong>Access Controls:</strong> Limited access based on role-based permissions</li>
                        <li><strong>Secure Storage:</strong> Data stored in secure databases with regular backups</li>
                        <li><strong>Session Security:</strong> Secure session management with timeout mechanisms</li>
                    </ul>

                    <h3>5. Data Sharing and Disclosure</h3>
                    <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only:</p>
                    <ul>
                        <li>With authorized MCC personnel for legitimate academic and administrative purposes</li>
                        <li>As required by law or legal process</li>
                        <li>To protect our rights, privacy, safety, or property</li>
                    </ul>

                    <h3>6. Your Rights (Data Privacy Act of 2012)</h3>
                    <p>As a data subject, you have the right to:</p>
                    <ul>
                        <li><strong>Right to be Informed:</strong> Access this privacy policy and know what data we collect</li>
                        <li><strong>Right to Access:</strong> Request and obtain copies of your personal data</li>
                        <li><strong>Right to Object:</strong> Object to processing of your personal data</li>
                        <li><strong>Right to Erasure:</strong> Request deletion of your personal data</li>
                        <li><strong>Right to Data Portability:</strong> Request your data in a portable format</li>
                        <li><strong>Right to Complaint:</strong> File complaints with the National Privacy Commission (NPC)</li>
                        <li><strong>Right to Damages:</strong> Seek indemnification for violations</li>
                    </ul>

                    <h3>7. Data Retention</h3>
                    <p>
                        We retain your personal information for as long as necessary to fulfill the purposes outlined in this policy, or as required by law. Unused accounts and inactive records may be deleted after reasonable retention periods.
                    </p>

                    <h3>8. Children's Privacy</h3>
                    <p>
                        Our services are intended for use by MCC students and faculty members only. We do not knowingly collect information from children under 13 years of age without parental consent.
                    </p>

                    <h3>9. Cookies and Tracking Technologies</h3>
                    <p>We use essential cookies for:</p>
                    <ul>
                        <li>Session management and authentication</li>
                        <li>Security (CSRF protection, login attempts)</li>
                        <li>User preferences and settings</li>
                    </ul>
                    <p>You may configure your browser to refuse cookies, but this may limit functionality.</p>

                    <h3>10. Third-Party Services</h3>
                    <p>This service may integrate with third-party services:</p>
                    <ul>
                        <li><strong>Microsoft 365:</strong> For email authentication and notifications</li>
                        <li><strong>Google reCAPTCHA:</strong> For bot protection</li>
                    </ul>
                    <p>These services have their own privacy policies.</p>

                    <h3>11. Changes to This Privacy Policy</h3>
                    <p>
                        We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last Updated" date.
                    </p>

                    <h3>12. Contact Information</h3>
                    <p>
                        For questions, concerns, or to exercise your rights under the Data Privacy Act of 2012, please contact:
                    </p>
                    <div class="alert alert-primary">
                        <strong>Data Protection Officer</strong><br>
                        Madridejos Community College<br>
                        Email: mcc-nac@mcclawis.edu.ph<br>
                        Phone: [Contact Number]<br>
                        Address: [College Address]
                    </div>

                    <h3>13. Consent</h3>
                    <p class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> By using our service and checking the privacy policy checkbox during registration, you acknowledge that you have read, understood, and agree to this Privacy Policy and consent to the processing of your personal data in accordance with the Data Privacy Act of 2012.
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

