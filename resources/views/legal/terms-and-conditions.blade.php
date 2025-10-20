@extends('layouts.app')

@section('title', 'Terms and Conditions')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">
                        <i class="fas fa-file-contract me-2"></i>
                        Terms and Conditions
                    </h2>
                    <p class="mb-0 mt-2">Last updated: {{ date('F d, Y') }}</p>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important:</strong> By using this system, you agree to be bound by these Terms and Conditions.
                    </div>

                    <h3>1. Acceptance of Terms</h3>
                    <p>By accessing and using the MCC-NAC (Mabini Colleges of the Philippines - Nueva Ecija Academic Center) online platform, you accept and agree to be bound by the terms and provision of this agreement.</p>

                    <h3>2. Use License</h3>
                    <p>Permission is granted to temporarily use the MCC-NAC platform for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                    <ul>
                        <li>Modify or copy the materials</li>
                        <li>Use the materials for any commercial purpose or for any public display</li>
                        <li>Attempt to reverse engineer any software contained on the platform</li>
                        <li>Remove any copyright or other proprietary notations from the materials</li>
                    </ul>

                    <h3>3. User Accounts and Responsibilities</h3>
                    <p>When creating an account, you agree to:</p>
                    <ul>
                        <li>Provide accurate, current, and complete information</li>
                        <li>Maintain and update your account information</li>
                        <li>Maintain the security of your password and account</li>
                        <li>Accept responsibility for all activities under your account</li>
                        <li>Notify us immediately of any unauthorized use of your account</li>
                    </ul>

                    <h3>4. Prohibited Uses</h3>
                    <p>You may not use our platform:</p>
                    <ul>
                        <li>For any unlawful purpose or to solicit others to perform unlawful acts</li>
                        <li>To violate any international, federal, provincial, or state regulations, rules, laws, or local ordinances</li>
                        <li>To infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
                        <li>To harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
                        <li>To submit false or misleading information</li>
                        <li>To upload or transmit viruses or any other type of malicious code</li>
                    </ul>

                    <h3>5. Content and Intellectual Property</h3>
                    <p>All content, including but not limited to text, graphics, logos, images, and software, is the property of MCC-NAC and is protected by copyright and other intellectual property laws.</p>

                    <h3>6. Privacy and Data Protection</h3>
                    <p>Your privacy is important to us. Our Privacy Policy explains how we collect, use, and protect your information when you use our platform. By using our platform, you agree to the collection and use of information in accordance with our Privacy Policy.</p>

                    <h3>7. Data Privacy Act Compliance</h3>
                    <p>This platform complies with the Data Privacy Act of 2012 (Republic Act No. 10173) of the Philippines. We:</p>
                    <ul>
                        <li>Collect only necessary personal information</li>
                        <li>Use personal data only for legitimate educational purposes</li>
                        <li>Implement appropriate security measures to protect personal data</li>
                        <li>Provide data subjects with rights to access, correct, and delete their personal data</li>
                        <li>Obtain consent before processing personal data</li>
                    </ul>

                    <h3>8. Limitation of Liability</h3>
                    <p>In no event shall MCC-NAC or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the platform.</p>

                    <h3>9. Accuracy of Materials</h3>
                    <p>The materials appearing on the MCC-NAC platform could include technical, typographical, or photographic errors. MCC-NAC does not warrant that any of the materials on its platform are accurate, complete, or current.</p>

                    <h3>10. Links</h3>
                    <p>MCC-NAC has not reviewed all of the sites linked to our platform and is not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by MCC-NAC of the site.</p>

                    <h3>11. Modifications</h3>
                    <p>MCC-NAC may revise these terms of service for its platform at any time without notice. By using this platform, you are agreeing to be bound by the then current version of these terms of service.</p>

                    <h3>12. Governing Law</h3>
                    <p>These terms and conditions are governed by and construed in accordance with the laws of the Philippines and you irrevocably submit to the exclusive jurisdiction of the courts in that state or location.</p>

                    <h3>13. Contact Information</h3>
                    <p>If you have any questions about these Terms and Conditions, please contact us:</p>
                    <ul>
                        <li><strong>Email:</strong> privacy@mcc-nac.edu.ph</li>
                        <li><strong>Address:</strong> Mabini Colleges of the Philippines - Nueva Ecija Academic Center</li>
                        <li><strong>Phone:</strong> [Contact Number]</li>
                    </ul>

                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Note:</strong> These terms and conditions may be updated periodically. Users will be notified of significant changes through the platform or email.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
