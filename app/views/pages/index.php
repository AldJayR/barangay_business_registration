<?php
// Ensure constants are available
require_once dirname(dirname(dirname(__FILE__))) . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Barangay Business Registration System - Streamline your business registration process">
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <title><?= isset($title) ? sanitize($title) . ' | ' . SITENAME : SITENAME ?></title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #495057;
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
        }
        
        .navbar {
            background-color: rgba(255, 255, 255, 0.95) !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.4rem;
        }
        
        .nav-link {
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            color: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .hero-section {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.9) 0%, rgba(41, 128, 185, 0.8) 100%), url('https://images.unsplash.com/photo-1664575599736-c5197c684172');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0;
            margin-bottom: 80px;
            position: relative;
        }
        
        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 80px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='%23f8f9fa' fill-opacity='1' d='M0,128L48,117.3C96,107,192,85,288,80C384,75,480,85,576,117.3C672,149,768,203,864,208C960,213,1056,171,1152,154.7C1248,139,1344,149,1392,154.7L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E");
            background-size: cover;
            background-position: center;
        }
        
        .card {
            border-radius: 15px;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: rgba(52, 152, 219, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        
        .feature-icon i {
            font-size: 40px;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?= URLROOT ?>">
                <?= SITENAME ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="<?= URLROOT ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#how-it-works">How It Works</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq">FAQ</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-primary" href="<?= URLROOT ?>/login">Log In</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary" href="<?= URLROOT ?>/register">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6" data-aos="fade-right" data-aos-duration="1000">
                    <h1 class="display-4 fw-bold mb-4">Streamline Your Business Registration</h1>
                    <p class="lead mb-4">Register your business with ease and manage all your permits and compliance requirements in one secure platform.</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= URLROOT ?>/register" class="btn btn-light btn-lg">Get Started</a>
                        <a href="#features" class="btn btn-outline-light btn-lg">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                    <img src="https://cdn.pixabay.com/photo/2018/03/12/12/32/woman-3219507_1280.png" alt="Business Registration Illustration" class="img-fluid mt-5 mt-lg-0">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">Why Choose Our Platform?</h2>
                <p class="lead text-muted mx-auto" style="max-width: 700px;">Our system simplifies the entire business registration process, saving you time and ensuring compliance with local regulations.</p>
            </div>
            
            <div class="row g-4">
                <!-- Feature 1 -->
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card h-100 p-4">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="card-title h5 mb-3">Save Time</h3>
                            <p class="card-text text-muted">Complete your business registration online without visiting multiple government offices.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 2 -->
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="card h-100 p-4">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="card-title h5 mb-3">Ensure Compliance</h3>
                            <p class="card-text text-muted">Our system guides you through all required permits and approvals for legal operation.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Feature 3 -->
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="card h-100 p-4">
                        <div class="feature-icon mx-auto">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <div class="card-body text-center">
                            <h3 class="card-title h5 mb-3">Track Progress</h3>
                            <p class="card-text text-muted">Monitor your application status in real-time and receive updates at every step.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="400">
                <a href="<?= URLROOT ?>/register" class="btn btn-primary btn-lg">Register Your Business Today</a>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="bg-light py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">How It Works</h2>
                <p class="lead text-muted mx-auto" style="max-width: 700px;">Our simple 4-step process makes business registration easier than ever before.</p>
            </div>
            
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="row g-4">
                        <!-- Step 1 -->
                        <div class="col-md-6 d-flex" data-aos="fade-up" data-aos-delay="100">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary rounded-circle text-white fw-bold d-flex align-items-center justify-content-center me-3" style="min-width: 40px; height: 40px;">1</div>
                                <div>
                                    <h3 class="h5 mb-3">Create an Account</h3>
                                    <p class="text-muted">Register as a business owner with your basic information and create a secure login.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 2 -->
                        <div class="col-md-6 d-flex" data-aos="fade-up" data-aos-delay="200">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary rounded-circle text-white fw-bold d-flex align-items-center justify-content-center me-3" style="min-width: 40px; height: 40px;">2</div>
                                <div>
                                    <h3 class="h5 mb-3">Submit Business Details</h3>
                                    <p class="text-muted">Provide your business information, location, and upload any required supporting documents.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 3 -->
                        <div class="col-md-6 d-flex" data-aos="fade-up" data-aos-delay="300">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary rounded-circle text-white fw-bold d-flex align-items-center justify-content-center me-3" style="min-width: 40px; height: 40px;">3</div>
                                <div>
                                    <h3 class="h5 mb-3">Pay Registration Fees</h3>
                                    <p class="text-muted">Complete payment using our secure online payment system with various options.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Step 4 -->
                        <div class="col-md-6 d-flex" data-aos="fade-up" data-aos-delay="400">
                            <div class="d-flex align-items-start">
                                <div class="bg-primary rounded-circle text-white fw-bold d-flex align-items-center justify-content-center me-3" style="min-width: 40px; height: 40px;">4</div>
                                <div>
                                    <h3 class="h5 mb-3">Receive Your Permit</h3>
                                    <p class="text-muted">Once approved, download your digital business permit and start operating legally.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-5">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h2 class="display-5 fw-bold mb-3">Frequently Asked Questions</h2>
                <p class="lead text-muted mx-auto" style="max-width: 700px;">Find answers to common questions about business registration in our community.</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion" data-aos="fade-up" data-aos-delay="100">
                        <!-- FAQ Item 1 -->
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    What businesses need to register with the barangay?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    All businesses operating within the barangay's jurisdiction must register, regardless of size. This includes retail stores, service providers, food establishments, and home-based businesses.
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ Item 2 -->
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    What documents do I need to prepare?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Typically, you'll need proof of identity (valid ID), proof of residence or business location, business name registration (if applicable), and other supporting documents depending on your business type. Our system will guide you through the specific requirements.
                                </div>
                            </div>
                        </div>
                        
                        <!-- FAQ Item 3 -->
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed rounded" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    How long does the registration process take?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    With our online system, most applications are processed within 3-5 business days once all required documents are submitted and verified, and payment is confirmed.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 text-center text-lg-start mb-4 mb-lg-0">
                    <h2 class="fw-bold">Ready to register your business?</h2>
                    <p class="lead mb-0">Join thousands of business owners who have simplified their registration process.</p>
                </div>
                <div class="col-lg-4 text-center text-lg-end">
                    <a href="<?= URLROOT ?>/register" class="btn btn-light btn-lg">Get Started Today</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-5 bg-dark text-white">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="mb-3"><?= SITENAME ?></h5>
                    <p class="text-muted">Streamlining business registration and compliance for our local community.</p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-light rounded-circle"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-light rounded-circle"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-light rounded-circle"><i class="bi bi-instagram"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="mb-3">Quick Links</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="#features" class="text-muted text-decoration-none">Features</a></li>
                        <li class="mb-2"><a href="#how-it-works" class="text-muted text-decoration-none">How It Works</a></li>
                        <li class="mb-2"><a href="#faq" class="text-muted text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-3">
                    <h6 class="mb-3">Contact</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>Barangay Hall, Main Street</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i>(02) 8123-4567</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i>info@barangaybusiness.gov.ph</li>
                    </ul>
                </div>
                <div class="col-lg-3">
                    <h6 class="mb-3">Office Hours</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">Monday - Friday: 8:00 AM - 5:00 PM</li>
                        <li class="mb-2">Saturday: 8:00 AM - 12:00 PM</li>
                        <li class="mb-2">Sunday: Closed</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4 bg-secondary">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="small text-muted mb-0">© <?= date('Y') ?> <?= SITENAME ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="small text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="small text-muted text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <!-- AOS Animation Library JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS animation library
        AOS.init({
            once: true, // Whether animation should happen only once
            duration: 800, // Animation duration
        });
        
        // Make sure hero section has enough height on small screens
        const adjustHeroHeight = () => {
            const navbar = document.querySelector('.navbar');
            const hero = document.querySelector('.hero-section');
            if (navbar && hero) {
                const navbarHeight = navbar.offsetHeight;
                const windowHeight = window.innerHeight;
                hero.style.minHeight = `${windowHeight - navbarHeight}px`;
            }
        };
        
        // Adjust hero height on load and resize
        window.addEventListener('load', adjustHeroHeight);
        window.addEventListener('resize', adjustHeroHeight);
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>