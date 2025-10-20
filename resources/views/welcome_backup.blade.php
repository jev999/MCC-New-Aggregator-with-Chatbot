<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MCC-NAC Portal System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="welcome-page">
    <!-- Header Navigation -->
    <header class="main-header">
        <div class="header-container">
            <div class="logo-section">
                <div class="logo-circle">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <span class="logo-text">MCC-NAC</span>
            </div>
            
            <nav class="main-nav">
                <a href="{{ route('login') }}" class="nav-link">Login</a>
                <a href="{{ route('ms365.signup') }}" class="nav-link signup-btn">Signup</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-background">
            <img src="{{ asset('images/mccfront.jpg') }}" alt="Madridejos Community College" class="hero-image">
            <div class="hero-overlay"></div>
        </div>
        
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="typewriter-text" data-text="Welcome to MCC-NAC Portal">Welcome to MCC-NAC Portal</h1>
                <p class="hero-subtitle-main fade-in-up-delay-1">Madridejos Community College News Aggregator with Chatbot</p>
                <p class="hero-subtitle fade-in-up-delay-2">Access your academic resources and stay connected with campus life</p>
                <div class="hero-cta fade-in-up-delay-3">
                    <a href="{{ route('login') }}" class="btn btn-hero-primary">
                        <i class="fas fa-rocket"></i>
                        <span>Get Started</span>
                    </a>
                    <a href="#curriculum-section" class="btn btn-hero-outline">
                        <i class="fas fa-graduation-cap"></i>
                        <span>Explore Programs</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Learning Begins Section -->
    <section class="learning-section">
        <div class="container">
            <div class="learning-content">
                <div class="learning-text-left reveal-animation" data-animation="slide-in-left">
                    <h2 class="gradient-text">Learning Begins With Us</h2>
                    <div class="text-highlight-bar"></div>
                    <p class="animated-paragraph">We, at Madridejos Community College offer supportive and inspirational environments for young enquiring minds to learn and grow with us. Our passion for learning means we achieve more than outstanding results. We strive to build confident and creative thinkers and aim at delivering an education that is truly relevant to their future.</p>
                </div>
                <div class="learning-text-right reveal-animation" data-animation="slide-in-right">
                    <p class="animated-paragraph">We are an early learning academy focused on social-emotional development and early literacy and numeracy. Our students walk out with the character and confidence to make their mark in the world, equipped with the knowledge and real-world skills that take them way ahead in the industry they may serve.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Curriculum Overview Section -->
    <section class="curriculum-section" id="curriculum-section">
        <div class="container">
            <div class="curriculum-header">
                <div class="curriculum-title reveal-animation" data-animation="slide-in-left">
                    <h2 class="gradient-text">Curriculum Overview</h2>
                    <div class="text-highlight-bar"></div>
                </div>
                <div class="curriculum-description reveal-animation" data-animation="slide-in-right">
                    <p class="animated-paragraph">The Madridejos Community College aims at offering all our students a broad and balanced curriculum that provides rewarding and stimulating activities to prepare them for the best social and cultural life.</p>
                </div>
            </div>
            
            <div class="programs-grid">
                <div class="program-card reveal-animation" data-animation="fade-in-up" data-delay="0.2">
                    <div class="program-image">
                        <img src="{{ asset('images/BSIT.jpg') }}" alt="BSIT Program" class="program-img">
                        <div class="program-overlay">
                            <div class="overlay-content">
                                <div class="overlay-icon">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                                <h4>Explore Program</h4>
                                <p>Discover our comprehensive IT curriculum</p>
                                <div class="overlay-features">
                                    <span class="feature-tag">Programming</span>
                                    <span class="feature-tag">Web Development</span>
                                    <span class="feature-tag">Database</span>
                                </div>
                            </div>
                        </div>
                        <div class="program-glow"></div>
                    </div>
                    <div class="program-department bsit-dept">
                        <i class="fas fa-code"></i>
                        <span>Department: BSIT</span>
                    </div>
                    <div class="program-info">
                        <h3>Bachelor of Science in Information Technology</h3>
                        <p>Comprehensive IT education covering programming, systems analysis, and emerging technologies.</p>
                        <div class="program-stats">
                            <div class="stat-item">
                                <i class="fas fa-clock"></i>
                                <span>4 Years</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-users"></i>
                                <span>Active Students</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="program-card reveal-animation" data-animation="fade-in-up" data-delay="0.4">
                    <div class="program-image">
                        <img src="{{ asset('images/BSBA.jpg') }}" alt="BSBA Program" class="program-img">
                        <div class="program-overlay">
                            <div class="overlay-content">
                                <div class="overlay-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h4>Explore Program</h4>
                                <p>Explore business administration opportunities</p>
                                <div class="overlay-features">
                                    <span class="feature-tag">Management</span>
                                    <span class="feature-tag">Marketing</span>
                                    <span class="feature-tag">Finance</span>
                                </div>
                            </div>
                        </div>
                        <div class="program-glow"></div>
                    </div>
                    <div class="program-department bsba-dept">
                        <i class="fas fa-briefcase"></i>
                        <span>Department: BSBA</span>
                    </div>
                    <div class="program-info">
                        <h3>Bachelor of Science in Business Administration</h3>
                        <p>Strategic business education focusing on management, marketing, and entrepreneurship.</p>
                        <div class="program-stats">
                            <div class="stat-item">
                                <i class="fas fa-clock"></i>
                                <span>4 Years</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-users"></i>
                                <span>Active Students</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="program-card reveal-animation" data-animation="fade-in-up" data-delay="0.6">
                    <div class="program-image">
                        <img src="{{ asset('images/BEED.jpg') }}" alt="BEED Program" class="program-img">
                        <div class="program-overlay">
                            <div class="overlay-content">
                                <div class="overlay-icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <h4>Explore Program</h4>
                                <p>Shape young minds through education</p>
                                <div class="overlay-features">
                                    <span class="feature-tag">Teaching</span>
                                    <span class="feature-tag">Child Development</span>
                                    <span class="feature-tag">Curriculum</span>
                                </div>
                            </div>
                        </div>
                        <div class="program-glow"></div>
                    </div>
                    <div class="program-department beed-dept">
                        <i class="fas fa-apple-alt"></i>
                        <span>Department: BEED</span>
                    </div>
                    <div class="program-info">
                        <h3>Bachelor of Elementary Education</h3>
                        <p>Comprehensive teacher training program for elementary education professionals.</p>
                        <div class="program-stats">
                            <div class="stat-item">
                                <i class="fas fa-clock"></i>
                                <span>4 Years</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-users"></i>
                                <span>Active Students</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="program-card reveal-animation" data-animation="fade-in-up" data-delay="0.8">
                    <div class="program-image">
                        <img src="{{ asset('images/BSED.jpg') }}" alt="BSED Program" class="program-img">
                        <div class="program-overlay">
                            <div class="overlay-content">
                                <div class="overlay-icon">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h4>Explore Program</h4>
                                <p>Advanced secondary education training</p>
                                <div class="overlay-features">
                                    <span class="feature-tag">Secondary Ed</span>
                                    <span class="feature-tag">Subject Mastery</span>
                                    <span class="feature-tag">Pedagogy</span>
                                </div>
                            </div>
                        </div>
                        <div class="program-glow"></div>
                    </div>
                    <div class="program-department bsed-dept">
                        <i class="fas fa-book-open"></i>
                        <span>Department: BSED</span>
                    </div>
                    <div class="program-info">
                        <h3>Bachelor of Secondary Education</h3>
                        <p>Advanced teacher preparation for secondary school educators across various subjects.</p>
                        <div class="program-stats">
                            <div class="stat-item">
                                <i class="fas fa-clock"></i>
                                <span>4 Years</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-users"></i>
                                <span>Active Students</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="program-card reveal-animation" data-animation="fade-in-up" data-delay="1.0">
                    <div class="program-image">
                        <img src="{{ asset('images/BSHM.jpg') }}" alt="BSHM Program" class="program-img">
                        <div class="program-overlay">
                            <div class="overlay-content">
                                <div class="overlay-icon">
                                    <i class="fas fa-concierge-bell"></i>
                                </div>
                                <h4>Explore Program</h4>
                                <p>Excellence in hospitality management</p>
                                <div class="overlay-features">
                                    <span class="feature-tag">Hotel Management</span>
                                    <span class="feature-tag">Tourism</span>
                                    <span class="feature-tag">Service</span>
                                </div>
                            </div>
                        </div>
                        <div class="program-glow"></div>
                    </div>
                    <div class="program-department bshm-dept">
                        <i class="fas fa-utensils"></i>
                        <span>Department: BSHM</span>
                    </div>
                    <div class="program-info">
                        <h3>Bachelor of Science in Hospitality Management</h3>
                        <p>Professional training in hotel management, tourism, and hospitality services.</p>
                        <div class="program-stats">
                            <div class="stat-item">
                                <i class="fas fa-clock"></i>
                                <span>4 Years</span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-users"></i>
                                <span>Active Students</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portal Selection Section -->
    <section class="portal-section">
        <div class="container">
            <div class="section-header reveal-animation" data-animation="fade-in-up">
                <h2 class="gradient-text">Choose Your Portal</h2>
                <div class="text-highlight-bar" style="margin: 0 auto 2rem;"></div>
                <p class="animated-paragraph">Select the appropriate portal to access your account and resources</p>
            </div>
            
            <div class="portal-cards">
                <div class="portal-card admin-portal reveal-animation" data-animation="slide-in-left" data-delay="0.2">
                    <div class="portal-glow"></div>
                    <div class="portal-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="portal-info">
                        <h3>Admin Portal</h3>
                        <p>Manage system content, users, and administrative functions</p>
                        <ul class="portal-features">
                            <li><i class="fas fa-check"></i> Content Management</li>
                            <li><i class="fas fa-check"></i> User Administration</li>
                            <li><i class="fas fa-check"></i> System Analytics</li>
                        </ul>
                    </div>
                    <div class="portal-actions">
                        <a href="{{ route('admin.login') }}" class="btn btn-primary portal-btn">
                            <i class="fas fa-sign-in-alt"></i> 
                            <span>Admin Login</span>
                            <div class="btn-shine"></div>
                        </a>
                    </div>
                </div>
                
                <div class="portal-card user-portal reveal-animation" data-animation="slide-in-right" data-delay="0.4">
                    <div class="portal-glow"></div>
                    <div class="portal-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="portal-info">
                        <h3>Student/Faculty Portal</h3>
                        <p>Access announcements, events, news, and academic resources</p>
                        <ul class="portal-features">
                            <li><i class="fas fa-check"></i> View Announcements</li>
                            <li><i class="fas fa-check"></i> Campus Events</li>
                            <li><i class="fas fa-check"></i> Latest News</li>
                        </ul>
                    </div>
                    <div class="portal-actions">
                        <a href="{{ route('user.login') }}" class="btn btn-primary portal-btn">
                            <i class="fas fa-sign-in-alt"></i> 
                            <span>Student Login</span>
                            <div class="btn-shine"></div>
                        </a>
                        <a href="{{ route('user.register') }}" class="btn btn-outline portal-btn">
                            <i class="fas fa-user-plus"></i> 
                            <span>Register Account</span>
                            <div class="btn-shine"></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-header reveal-animation" data-animation="fade-in-up">
                <h2 class="gradient-text">Portal Features</h2>
                <div class="text-highlight-bar" style="margin: 0 auto 2rem;"></div>
                <p class="animated-paragraph">Discover what our portal system offers</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-item reveal-animation" data-animation="fade-in-up" data-delay="0.2">
                    <div class="feature-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h4>Announcements</h4>
                    <p>Stay updated with the latest campus announcements and important notices</p>
                    <div class="feature-shine"></div>
                </div>
                
                <div class="feature-item reveal-animation" data-animation="fade-in-up" data-delay="0.4">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h4>Events</h4>
                    <p>Never miss important campus events, seminars, and academic activities</p>
                    <div class="feature-shine"></div>
                </div>
                
                <div class="feature-item reveal-animation" data-animation="fade-in-up" data-delay="0.6">
                    <div class="feature-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h4>News</h4>
                    <p>Read the latest news and updates from the college community</p>
                    <div class="feature-shine"></div>
                </div>
                
                <div class="feature-item reveal-animation" data-animation="fade-in-up" data-delay="0.8">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Community</h4>
                    <p>Connect with fellow students, faculty, and staff members</p>
                    <div class="feature-shine"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <div class="logo-circle">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <span class="logo-text">MCC-NAC</span>
                    </div>
                    <p>Madridejos Community College<br>News Aggregator with Chatbot</p>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="{{ route('login') }}">Login</a></li>
                        <li><a href="{{ route('user.register') }}">Register</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p><i class="fas fa-map-marker-alt"></i> Bunakan, Madridejos, Cebu</p>
                    <p><i class="fas fa-envelope"></i> info@mcc-nac.edu.ph</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} Madridejos Community College. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- AI Chatbot Widget -->
    <div id="chatbot-widget" class="chatbot-widget">
        <button id="chatbot-toggle" class="chatbot-toggle" title="Chat with MCC AI Assistant">
            <i class="fas fa-comments"></i>
            <span class="chatbot-badge">Need Help?</span>
        </button>
        
        <div id="chatbot-container" class="chatbot-container">
            <div class="chatbot-header">
                <div class="chatbot-title">
                    <i class="fas fa-robot"></i>
                    <span>MCC AI Assistant</span>
                    <div class="status-indicator"></div>
                </div>
                <button class="chatbot-minimize" onclick="toggleChatbot()">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
            
            <div class="chatbot-messages" id="chatbot-messages">
                <div class="message bot-message">
                    <div class="message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <p>Hello! Welcome to MCC Portal! 👋<br><br>I'm your AI assistant. I can help you with:</p>
                        <p>• Information about our academic programs<br>• Admission and enrollment guidance<br>• Campus events and announcements<br>• Contact information<br>• General questions about MCC</p>
                        <p>How can I assist you today?</p>
                        <span class="message-time">Now</span>
                    </div>
                </div>
            </div>
            
            <div class="chatbot-input">
                <div class="input-container">
                    <input type="text" id="chatbot-input" placeholder="Ask me about MCC programs, events, or anything else..." maxlength="500">
                    <button id="chatbot-send" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Welcome Page Styles */
.welcome-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    background-attachment: fixed;
    position: relative;
    overflow-x: hidden;
}

.welcome-page::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(120, 119, 198, 0.2) 0%, transparent 50%);
    pointer-events: none;
    z-index: 1;
}

.welcome-page > * {
    position: relative;
    z-index: 2;
}

/* Header Styles */
.main-header {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    padding: 1rem 0;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo-section {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-circle {
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1e40af;
    font-size: 1.25rem;
}

.logo-text {
    color: white;
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: 0.05em;
}

.main-nav {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.nav-link {
    color: white;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.signup-btn {
    background: #10b981;
    color: white !important;
    font-weight: 600;
}

.signup-btn:hover {
    background: #059669;
    transform: translateY(-1px);
}

/* Hero Section */
.hero-section {
    position: relative;
    height: 70vh;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
}

.hero-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
}

.hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: white;
    max-width: 800px;
    padding: 0 2rem;
}

/* Typewriter Effect */
.typewriter-text {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 50%, #ffffff 100%);
    background-size: 200% 200%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: shimmerText 3s ease-in-out infinite, typewriter 4s steps(26) 1s both;
    border-right: 3px solid rgba(255, 255, 255, 0.8);
    white-space: nowrap;
    overflow: hidden;
    width: 0;
}

@keyframes typewriter {
    from { width: 0; }
    to { width: 100%; }
}

@keyframes shimmerText {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.hero-subtitle-main {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    opacity: 0;
    transform: translateY(30px);
}

.hero-subtitle {
    font-size: 1.125rem !important;
    opacity: 0;
    margin-top: 1rem !important;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    transform: translateY(30px);
}

.hero-cta {
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    opacity: 0;
    transform: translateY(30px);
}

/* Hero Button Styles */
.btn-hero-primary {
    background: linear-gradient(135deg, #10b981 0%, #047857 100%);
    color: white;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-hero-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-hero-primary:hover::before {
    left: 100%;
}

.btn-hero-primary:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 15px 40px rgba(16, 185, 129, 0.4);
}

.btn-hero-outline {
    background: transparent;
    color: white;
    padding: 1rem 2rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
}

.btn-hero-outline::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 0;
    height: 100%;
    background: rgba(255, 255, 255, 0.1);
    transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-hero-outline:hover::before {
    width: 100%;
}

.btn-hero-outline:hover {
    transform: translateY(-3px);
    border-color: rgba(255, 255, 255, 0.6);
    box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1);
}

/* Fade in up delays */
.fade-in-up-delay-1 {
    animation: fadeInUp 1s ease-out 1.5s both;
}

.fade-in-up-delay-2 {
    animation: fadeInUp 1s ease-out 2s both;
}

.fade-in-up-delay-3 {
    animation: fadeInUp 1s ease-out 2.5s both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Common Styles */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
}

.portal-btn {
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s ease;
}

.portal-btn:hover .btn-shine {
    left: 100%;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

.btn-outline {
    background: transparent;
    color: #667eea;
    border: 2px solid #667eea;
    backdrop-filter: blur(10px);
}

.btn-outline:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

/* Portal Section */
.portal-section {
    padding: 5rem 0;
    background: white;
}

.section-header {
    text-align: center;
    margin-bottom: 4rem;
}

.section-header h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
}

.section-header p {
    font-size: 1.125rem;
    color: #6b7280;
    max-width: 600px;
    margin: 0 auto;
}

.portal-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 3rem;
    max-width: 1000px;
    margin: 0 auto;
}

.portal-card {
    background: white;
    border-radius: 1.5rem;
    padding: 2.5rem;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
}

.portal-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.portal-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    border-color: rgba(102, 126, 234, 0.3);
}

.portal-glow {
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(135deg, #667eea, #764ba2, #10b981);
    background-size: 400% 400%;
    border-radius: 1.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: -1;
    animation: gradientRotate 6s ease infinite;
}

.portal-card:hover .portal-glow {
    opacity: 0.3;
}

.portal-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2.25rem;
    margin-bottom: 2rem;
    position: relative;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.portal-icon::before {
    content: '';
    position: absolute;
    top: -3px;
    left: -3px;
    right: -3px;
    bottom: -3px;
    background: linear-gradient(135deg, #667eea, #764ba2, #10b981, #f59e0b);
    background-size: 400% 400%;
    border-radius: 1.25rem;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: -1;
    animation: gradientRotate 4s ease infinite;
}

.portal-card:hover .portal-icon {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
}

.portal-card:hover .portal-icon::before {
    opacity: 1;
}

.portal-info h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 1rem;
}

.portal-info p {
    color: #6b7280;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.portal-features {
    list-style: none;
    padding: 0;
    margin-bottom: 2rem;
}

.portal-features li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
    color: #374151;
}

.portal-features i {
    color: #10b981;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.portal-card:hover .portal-features i {
    color: #047857;
    transform: scale(1.2);
}

.portal-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Features Section */
.features-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
    position: relative;
    overflow: hidden;
}

.features-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 30% 20%, rgba(16, 185, 129, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 70% 80%, rgba(102, 126, 234, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    max-width: 1000px;
    margin: 0 auto;
}

.feature-item {
    text-align: center;
    padding: 2rem;
    background: white;
    border-radius: 1.5rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(102, 126, 234, 0.1);
}

.feature-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
}

.feature-item:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12);
    border-color: rgba(102, 126, 234, 0.3);
}

.feature-item:hover::before {
    opacity: 1;
}

.feature-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    transition: left 0.6s ease;
}

.feature-item:hover .feature-shine {
    left: 100%;
}

/* Floating animation */
.float-animation {
    animation: floatUp 6s ease-in-out infinite;
}

@keyframes floatUp {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

.feature-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.75rem;
    margin: 0 auto 1.5rem;
    position: relative;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}

.feature-icon::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(135deg, #667eea, #764ba2, #10b981);
    border-radius: 50%;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: -1;
    animation: iconRotate 4s linear infinite;
}

.feature-item:hover .feature-icon {
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
}

.feature-item:hover .feature-icon::before {
    opacity: 1;
}

@keyframes iconRotate {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.feature-item h4 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.feature-item:hover h4 {
    color: #667eea;
    transform: translateY(-2px);
}

.feature-item p {
    color: #6b7280;
    line-height: 1.6;
    transition: all 0.3s ease;
}

.feature-item:hover p {
    color: #4b5563;
    transform: translateY(-1px);
}

/* Footer */
.main-footer {
    background: #1f2937;
    color: white;
    padding: 3rem 0 1rem;
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.footer-section h4 {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: white;
}

.footer-section ul {
    list-style: none;
    padding: 0;
}

.footer-section ul li {
    margin-bottom: 0.5rem;
}

.footer-section ul li a {
    color: #d1d5db;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-section ul li a:hover {
    color: white;
}

.footer-section p {
    color: #d1d5db;
    line-height: 1.6;
    margin-bottom: 0.5rem;
}

.footer-section i {
    margin-right: 0.5rem;
    color: #3b82f6;
}

.footer-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.footer-bottom {
    border-top: 1px solid #374151;
    padding-top: 2rem;
    text-align: center;
}

.footer-bottom p {
    color: #9ca3af;
    margin: 0;
}

/* Learning Begins Section */
.learning-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
    position: relative;
    overflow: hidden;
}

.learning-section::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.05) 0%, transparent 70%);
    animation: float 20s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

.learning-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: start;
}

/* Gradient Text Effect */
.gradient-text {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    line-height: 1.2;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #667eea 100%);
    background-size: 200% 200%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: gradientShift 4s ease-in-out infinite;
    position: relative;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.text-highlight-bar {
    width: 60px;
    height: 4px;
    background: linear-gradient(135deg, #10b981 0%, #047857 100%);
    border-radius: 2px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}

.text-highlight-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
    animation: barShine 2s ease-in-out infinite;
}

@keyframes barShine {
    0% { left: -100%; }
    100% { left: 100%; }
}

.animated-paragraph {
    font-size: 1rem;
    line-height: 1.8;
    color: #4b5563;
    text-align: justify;
    position: relative;
}

.animated-paragraph::before {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: width 1s ease-out 0.5s;
}

.reveal-animation.animate .animated-paragraph::before {
    width: 100%;
}

/* Curriculum Section */
.curriculum-section {
    padding: 5rem 0;
    background: linear-gradient(135deg, #ffffff 0%, #f0f9ff 100%);
    position: relative;
    overflow: hidden;
}

.curriculum-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 10% 20%, rgba(120, 119, 198, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.1) 0%, transparent 50%);
    pointer-events: none;
}

.curriculum-header {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    margin-bottom: 4rem;
    align-items: start;
}

/* Updated in gradient-text section above */
.curriculum-description .animated-paragraph {
    font-size: 1rem;
    line-height: 1.8;
    color: #4b5563;
    text-align: justify;
}

.programs-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 2rem;
    margin-bottom: 3rem;
}

.programs-grid .program-card:nth-child(4),
.programs-grid .program-card:nth-child(5) {
    grid-column: span 1;
}

.programs-grid .program-card:nth-child(4) {
    margin-left: 25%;
}

.programs-grid .program-card:nth-child(5) {
    margin-right: 25%;
}

.program-card {
    background: white;
    border-radius: 1.5rem;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #e5e7eb;
    opacity: 0;
    transform: translateY(50px) scale(0.9);
    position: relative;
    backdrop-filter: blur(10px);
}

.program-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
    z-index: 1;
}

.program-card.animate {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.program-card:hover {
    transform: translateY(-20px) scale(1.03);
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
}

.program-card:hover::before {
    opacity: 1;
}

/* Program Glow Effect */
.program-glow {
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(135deg, #667eea, #764ba2, #10b981, #f59e0b);
    background-size: 400% 400%;
    border-radius: 1.5rem;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: -1;
    animation: gradientRotate 4s ease infinite;
}

.program-card:hover .program-glow {
    opacity: 0.7;
}

@keyframes gradientRotate {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.program-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
    position: relative;
}

.program-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.program-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.95), rgba(118, 75, 162, 0.9));
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateY(100%) scale(0.8);
    backdrop-filter: blur(10px);
}

.program-card:hover .program-overlay {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.program-card:hover .program-img {
    transform: scale(1.15) rotate(2deg);
    filter: brightness(1.1);
}

.overlay-content {
    text-align: center;
    color: white;
    padding: 1.5rem;
    transform: translateY(20px);
    transition: transform 0.4s ease 0.1s;
}

.program-card:hover .overlay-content {
    transform: translateY(0);
}

.overlay-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 1.5rem;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.program-card:hover .overlay-icon {
    transform: scale(1.1) rotate(360deg);
    background: rgba(255, 255, 255, 0.3);
}

.overlay-content h4 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.overlay-content p {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-bottom: 1rem;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.overlay-features {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
}

.feature-tag {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.feature-tag:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
}

/* Department Labels */
.program-department {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    position: relative;
    overflow: hidden;
}

.program-department::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s ease;
}

.program-card:hover .program-department::before {
    left: 100%;
}

.bsit-dept {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    color: white;
}

.bsba-dept {
    background: linear-gradient(135deg, #be185d 0%, #ec4899 100%);
    color: white;
}

.beed-dept {
    background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
    color: white;
}

.bsed-dept {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    color: white;
}

.bshm-dept {
    background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
    color: white;
}

.program-info {
    padding: 1.5rem;
    position: relative;
}

.program-info h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.75rem;
    line-height: 1.4;
    transition: all 0.3s ease;
}

.program-card:hover .program-info h3 {
    color: #667eea;
    transform: translateX(5px);
}

.program-info p {
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 1rem;
}

.program-stats {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #6b7280;
    background: #f8fafc;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    transition: all 0.3s ease;
}

.stat-item i {
    color: #667eea;
}

.program-card:hover .stat-item {
    background: #e0e7ff;
    transform: translateY(-2px);
}

/* Chatbot Widget Styles */
.chatbot-widget {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1500;
}

.chatbot-toggle {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    font-size: 1rem;
}

.chatbot-toggle:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
}

.chatbot-badge {
    font-size: 0.875rem;
}

.chatbot-container {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 400px;
    height: 500px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    border: 1px solid #f1f5f9;
    display: none;
    flex-direction: column;
    overflow: hidden;
}

.chatbot-container.active {
    display: flex;
    animation: chatbotSlideIn 0.3s ease;
}

@keyframes chatbotSlideIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.chatbot-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chatbot-title {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 600;
}

.status-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    margin-left: 8px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

.chatbot-minimize {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.chatbot-minimize:hover {
    background: rgba(255, 255, 255, 0.3);
}

.chatbot-messages {
    flex: 1;
    padding: 1.5rem;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    background: #f8fafc;
}

.chatbot-messages::-webkit-scrollbar {
    width: 6px;
}

.chatbot-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.chatbot-messages::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.message {
    display: flex;
    gap: 0.75rem;
    max-width: 85%;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.bot-message {
    align-self: flex-start;
}

.user-message {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.user-message .message-avatar {
    background: linear-gradient(135deg, #10b981, #047857);
}

.message-content {
    background: white;
    padding: 1rem;
    border-radius: 16px;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
}

.user-message .message-content {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-bottom-right-radius: 4px;
    border-bottom-left-radius: 16px;
    border: none;
}

.message-content p {
    margin: 0;
    line-height: 1.5;
}

.message-time {
    font-size: 0.75rem;
    opacity: 0.6;
    margin-top: 0.5rem;
    display: block;
}

.chatbot-input {
    padding: 1.5rem;
    border-top: 1px solid #f1f5f9;
    background: white;
}

.chatbot-input .input-container {
    display: flex;
    gap: 0.75rem;
}

.chatbot-input input {
    flex: 1;
    padding: 0.875rem 1rem;
    border: 2px solid #f1f5f9;
    border-radius: 12px;
    font-size: 0.875rem;
    outline: none;
    transition: all 0.3s ease;
}

.chatbot-input input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.chatbot-input button {
    padding: 0.875rem;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chatbot-input button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.chatbot-input button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

/* Reveal Animation System */
.reveal-animation {
    opacity: 0;
    transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.reveal-animation[data-animation="slide-in-left"] {
    transform: translateX(-100px) scale(0.9);
}

.reveal-animation[data-animation="slide-in-right"] {
    transform: translateX(100px) scale(0.9);
}

.reveal-animation[data-animation="fade-in-up"] {
    transform: translateY(50px) scale(0.9);
}

.reveal-animation.animate {
    opacity: 1;
    transform: translateX(0) translateY(0) scale(1);
}

/* Staggered Animation Delays */
.reveal-animation[data-delay="0.2"].animate {
    transition-delay: 0.2s;
}

.reveal-animation[data-delay="0.4"].animate {
    transition-delay: 0.4s;
}

.reveal-animation[data-delay="0.6"].animate {
    transition-delay: 0.6s;
}

.reveal-animation[data-delay="0.8"].animate {
    transition-delay: 0.8s;
}

.reveal-animation[data-delay="1.0"].animate {
    transition-delay: 1.0s;
}

/* Legacy support */
.slide-in-left {
    opacity: 0;
    transform: translateX(-50px);
    animation: slideInLeft 0.8s ease forwards;
}

.slide-in-right {
    opacity: 0;
    transform: translateX(50px);
    animation: slideInRight 0.8s ease forwards;
}

.fade-in-up {
    opacity: 0;
    transform: translateY(30px);
}

@keyframes slideInLeft {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Responsive Design */
@media (max-width: 1024px) {
    .programs-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .programs-grid .program-card:nth-child(4),
    .programs-grid .program-card:nth-child(5) {
        grid-column: span 1;
        margin: 0;
    }
}

@media (max-width: 768px) {
    .header-container {
        flex-direction: column;
        gap: 1rem;
        padding: 0 1rem;
    }

    .main-nav {
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .hero-text h1 {
        font-size: 2.5rem;
    }

    .hero-text p {
        font-size: 1.25rem;
    }

    .portal-cards {
        grid-template-columns: 1fr;
        padding: 0 1rem;
    }

    .portal-actions {
        flex-direction: column;
    }

    .portal-actions .btn {
        width: 100%;
        justify-content: center;
    }

    .features-grid {
        grid-template-columns: 1fr;
        padding: 0 1rem;
    }

    .section-header h2 {
        font-size: 2rem;
    }
    
    .learning-content,
    .curriculum-header {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .learning-text-left h2,
    .curriculum-title h2 {
        font-size: 2rem;
    }
    
    .programs-grid {
        grid-template-columns: 1fr;
    }
    
    .programs-grid .program-card:nth-child(4),
    .programs-grid .program-card:nth-child(5) {
        margin: 0;
    }

    .chatbot-container {
        width: 350px;
    }
}

@media (max-width: 480px) {
    .hero-text h1 {
        font-size: 2rem;
    }

    .portal-card {
        padding: 1.5rem;
    }

    .feature-item {
        padding: 1.5rem;
    }

    .chatbot-widget {
        bottom: 1rem;
        right: 1rem;
    }
    
    .chatbot-container {
        width: 320px;
        height: 450px;
    }
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    max-width: 500px;
    border-radius: 10px;
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>

<script>
    let conversationContext = {
    currentTopic: null,
    followUp: false,
    lastQuestion: null
};
// Enhanced Intersection Observer for reveal animations
document.addEventListener('DOMContentLoaded', function() {
    // Parallax effect for hero section
    const heroImage = document.querySelector('.hero-image');
    const heroContent = document.querySelector('.hero-content');
    
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const parallaxSpeed = 0.5;
        
        if (heroImage) {
            heroImage.style.transform = `translateY(${scrolled * parallaxSpeed}px)`;
        }
        if (heroContent) {
            heroContent.style.transform = `translateY(${scrolled * 0.3}px)`;
        }
    });
    
    // Reveal animation observer
    const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const delay = parseFloat(entry.target.dataset.delay) || 0;
                setTimeout(() => {
                    entry.target.classList.add('animate');
                    
                    // Add staggered animation for child elements
                    const children = entry.target.querySelectorAll('.stat-item, .feature-tag');
                    children.forEach((child, index) => {
                        setTimeout(() => {
                            child.style.transform = 'translateY(0)';
                            child.style.opacity = '1';
                        }, index * 100);
                    });
                }, delay * 1000);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all reveal animation elements
    document.querySelectorAll('.reveal-animation, .fade-in-up').forEach(element => {
        observer.observe(element);
    });
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Add floating animation to feature items
    const featureItems = document.querySelectorAll('.feature-item');
    featureItems.forEach((item, index) => {
        item.style.animationDelay = `${index * 0.2}s`;
        item.classList.add('float-animation');
    });
    
    // Initialize typewriter effect
    const typewriterElement = document.querySelector('.typewriter-text');
    if (typewriterElement) {
        // Remove the cursor after animation completes
        setTimeout(() => {
            typewriterElement.style.borderRight = 'none';
        }, 5500);
    }
});

// Chatbot functionality
function toggleChatbot() {
    const container = document.getElementById('chatbot-container');
    container.classList.toggle('active');
    
    if (container.classList.contains('active')) {
        document.getElementById('chatbot-input').focus();
    }
}

async function sendMessage() {
    const input = document.getElementById('chatbot-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    addMessage(message, 'user');
    input.value = '';
    input.disabled = true;
    document.getElementById('chatbot-send').disabled = true;
    
    showTypingIndicator();
    
    try {
        const response = await fetch('{{ route("api.chatbot") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message,
                context: conversationContext
            })
        });
        
        const data = await response.json();
        hideTypingIndicator();
        
        if (data.success) {
            addMessage(data.response.message, 'bot');
            if (data.response.context) {
                conversationContext = data.response.context;
            }
        } else {
            const response = getResponseForUserMessage(message);
            addMessage(response.message, 'bot');
            conversationContext = response.context || conversationContext;
        }
    } catch (error) {
        hideTypingIndicator();
        console.error('Chatbot error:', error);
        const response = getResponseForUserMessage(message);
        addMessage(response.message, 'bot');
        conversationContext = response.context || conversationContext;
    } finally {
        input.disabled = false;
        document.getElementById('chatbot-send').disabled = false;
        input.focus();
    }
}

async function sendMessage() {
    const input = document.getElementById('chatbot-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    addMessage(message, 'user');
    input.value = '';
    
    // Disable input while processing
    input.disabled = true;
    document.getElementById('chatbot-send').disabled = true;
    
    // Show typing indicator
    showTypingIndicator();
    
    try {
        const response = await fetch('{{ route("api.chatbot") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                message: message
            })
        });
        
        const data = await response.json();
        
        hideTypingIndicator();
        
        if (data.success) {
            addMessage(data.response, 'bot');
        } else {
            addMessage("Sorry, I encountered an issue. Please try again.", 'bot');
        }
        
    } catch (error) {
        hideTypingIndicator();
        console.error('Chatbot error:', error);
        addMessage("Sorry, I encountered an error. Please try again later.", 'bot');
    } finally {
        // Re-enable input
        input.disabled = false;
        document.getElementById('chatbot-send').disabled = false;
        input.focus();
    }
}


function getFallbackResponse(userMessage) {
    const message = userMessage.toLowerCase();
    
}

function addMessage(text, sender) {
    const messagesContainer = document.getElementById('chatbot-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}-message`;
    
    const now = new Date();
    const timeStr = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    messageDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-${sender === 'user' ? 'user' : 'robot'}"></i>
        </div>
        <div class="message-content">
            <p>${text}</p>
            <span class="message-time">${timeStr}</span>
        </div>
    `;
    
    messagesContainer.appendChild(messageDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function showTypingIndicator() {
    const messagesContainer = document.getElementById('chatbot-messages');
    const typingDiv = document.createElement('div');
    typingDiv.id = 'typing-indicator';
    typingDiv.className = 'message bot-message';
    typingDiv.innerHTML = `
        <div class="message-avatar">
            <i class="fas fa-robot"></i>
        </div>
        <div class="message-content">
            <p>Thinking...</p>
        </div>
    `;
    messagesContainer.appendChild(typingDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function hideTypingIndicator() {
    const indicator = document.getElementById('typing-indicator');
    if (indicator) {
        indicator.remove();
    }
}

// Enter key to send message
document.getElementById('chatbot-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

// Initialize chatbot toggle
document.getElementById('chatbot-toggle').addEventListener('click', toggleChatbot);

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Modal functionality
function openLoginModal() {
    document.getElementById('loginModal').style.display = 'block';
}

function closeLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
}

// Handle login type change
const loginTypeElement = document.getElementById('login_type');
if (loginTypeElement) {
    loginTypeElement.addEventListener('change', function() {
        const gmailField = document.getElementById('gmail-field');
        const ms365Field = document.getElementById('ms365-field');
        const usernameField = document.getElementById('username-field');

        if (gmailField) gmailField.style.display = 'none';
        if (ms365Field) ms365Field.style.display = 'none';
        if (usernameField) usernameField.style.display = 'none';

    switch(this.value) {
        case 'user':
            gmailField.style.display = 'block';
            break;
        case 'ms365':
            ms365Field.style.display = 'block';
            break;
        case 'superadmin':
        case 'department-admin':
        case 'office-admin':
            usernameField.style.display = 'block';
            break;
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('loginModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>
</body>
</html>
