@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Privacy Policy
                    </h2>
                    <p class="mb-0 mt-2">Last updated: {{ date('F d, Y') }}</p>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Your Privacy Matters:</strong> This policy explains how we collect, use, and protect your personal information.
                    </div>

                    <h3>1. Information We Collect</h3>
                    
                    <h4>1.1 Personal Information</h4>
                    <p>We collect the following personal information:</p>
                    <ul>
                        <li><strong>Student Information:</strong> Name, student ID, department, year level, email addresses (MS365 and Gmail)</li>
                        <li><strong>Administrator Information:</strong> Username, role, department/office assignment, profile pictures</li>
                        <li><strong>Contact Information:</strong> Email addresses for communication and notifications</li>
                        <li><strong>Profile Data:</strong> Profile pictures and user preferences</li>
                    </ul>

                    <h4>1.2 Usage Information</h4>
                    <p>We automatically collect:</p>
                    <ul>
                        <li>Login and access logs</li>
                        <li>Content interaction data (comments, notifications)</li>
                        <li>Device and browser information</li>
                        <li>IP addresses and session data</li>
                    </ul>

                    <h3>2. How We Use Your Information</h3>
                    <p>We use your personal information for:</p>
                    <ul>
                        <li><strong>Educational Services:</strong> Providing access to announcements, events, news, and academic content</li>
                        <li><strong>Communication:</strong> Sending notifications about important updates and events</li>
                        <li><strong>Account Management:</strong> Managing user accounts and access permissions</li>
                        <li><strong>System Security:</strong> Monitoring for unauthorized access and security threats</li>
                        <li><strong>Analytics:</strong> Understanding usage patterns to improve our services</li>
                    </ul>

                    <h3>3. Data Collection Principles</h3>
                    <p>We follow the principle of data minimization:</p>
                    <ul>
                        <li>We collect only the information necessary for educational purposes</li>
                        <li>We do not collect unnecessary personal data</li>
                        <li>We regularly review and purge old, unnecessary data</li>
                        <li>We implement automated data retention policies</li>
                    </ul>

                    <h3>4. Data Security Measures</h3>
                    <p>We implement comprehensive security measures:</p>
                    <ul>
                        <li><strong>Encryption:</strong> All sensitive data is encrypted both in transit (HTTPS/TLS) and at rest</li>
                        <li><strong>Access Controls:</strong> Strict role-based access controls limit who can view personal data</li>
                        <li><strong>Security Headers:</strong> Implementation of security headers to prevent common attacks</li>
                        <li><strong>Regular Audits:</strong> Regular security audits and vulnerability assessments</li>
                        <li><strong>Secure Storage:</strong> Data stored in secure, encrypted databases</li>
                    </ul>

                    <h3>5. Data Sharing and Disclosure</h3>
                    <p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p>
                    <ul>
                        <li><strong>Legal Requirements:</strong> When required by law or legal process</li>
                        <li><strong>Educational Partners:</strong> With authorized educational institutions for legitimate academic purposes</li>
                        <li><strong>Service Providers:</strong> With trusted third-party service providers who assist in platform operations (under strict confidentiality agreements)</li>
                        <li><strong>Emergency Situations:</strong> When necessary to protect the safety and security of users</li>
                    </ul>

                    <h3>6. Data Retention</h3>
                    <p>We retain personal information only as long as necessary:</p>
                    <ul>
                        <li><strong>Active Users:</strong> Data retained while account is active</li>
                        <li><strong>Inactive Accounts:</strong> Data purged after 2 years of inactivity</li>
                        <li><strong>Notifications:</strong> Read notifications purged after 90 days</li>
                        <li><strong>Session Data:</strong> Session data purged after 30 days</li>
                        <li><strong>Logs:</strong> Security logs retained for 1 year for audit purposes</li>
                    </ul>

                    <h3>7. Your Rights Under the Data Privacy Act</h3>
                    <p>As a data subject, you have the following rights:</p>
                    <ul>
                        <li><strong>Right to Information:</strong> Know what personal data we collect and how we use it</li>
                        <li><strong>Right to Access:</strong> Request access to your personal data</li>
                        <li><strong>Right to Correction:</strong> Request correction of inaccurate or incomplete data</li>
                        <li><strong>Right to Erasure:</strong> Request deletion of your personal data (subject to legal requirements)</li>
                        <li><strong>Right to Object:</strong> Object to processing of your personal data</li>
                        <li><strong>Right to Data Portability:</strong> Request a copy of your data in a portable format</li>
                        <li><strong>Right to Withdraw Consent:</strong> Withdraw consent for data processing</li>
                    </ul>

                    <h3>8. Cookies and Tracking</h3>
                    <p>We use cookies and similar technologies to:</p>
                    <ul>
                        <li>Maintain your login session</li>
                        <li>Remember your preferences</li>
                        <li>Analyze platform usage</li>
                        <li>Improve security</li>
                    </ul>
                    <p>You can control cookie settings through your browser preferences.</p>

                    <h3>9. International Data Transfers</h3>
                    <p>Your personal data is primarily processed within the Philippines. If data is transferred internationally, we ensure adequate protection measures are in place.</p>

                    <h3>10. Children's Privacy</h3>
                    <p>Our platform is designed for educational use by students and staff. We do not knowingly collect personal information from children under 13 without parental consent.</p>

                    <h3>11. Changes to This Privacy Policy</h3>
                    <p>We may update this Privacy Policy periodically. We will notify users of significant changes through:</p>
                    <ul>
                        <li>Email notifications</li>
                        <li>Platform announcements</li>
                        <li>Updated policy posting with new effective date</li>
                    </ul>

                    <h3>12. Contact Information</h3>
                    <p>For privacy-related inquiries or to exercise your rights, contact our Data Protection Officer:</p>
                    <ul>
                        <li><strong>Email:</strong> privacy@mcc-nac.edu.ph</li>
                        <li><strong>Data Protection Officer:</strong> [DPO Name]</li>
                        <li><strong>Address:</strong> Mabini Colleges of the Philippines - Nueva Ecija Academic Center</li>
                        <li><strong>Phone:</strong> [Contact Number]</li>
                    </ul>

                    <h3>13. Complaints</h3>
                    <p>If you have concerns about how we handle your personal data, you may:</p>
                    <ul>
                        <li>Contact our Data Protection Officer</li>
                        <li>File a complaint with the National Privacy Commission (NPC)</li>
                        <li>Seek legal remedies as provided by law</li>
                    </ul>

                    <div class="alert alert-success mt-4">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Compliance:</strong> This privacy policy complies with the Data Privacy Act of 2012 (Republic Act No. 10173) and international best practices for data protection.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
