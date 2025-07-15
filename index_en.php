<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="UTF-8">
  <title>Ababel Development & Investment Company</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #711739;
      --secondary-color: #99004d;
      --accent-color: #ffc107;
      --light-color: #f8f9fa;
      --dark-color: #212529;
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Montserrat', sans-serif;
      background-color: #f8f9fa;
      color: #333;
      overflow-x: hidden;
    }
    
    /* Header */
    .hero-section {
      height: 100vh;
      background: linear-gradient(rgba(113, 23, 57, 0.85), rgba(153, 0, 77, 0.8)), 
                  url('https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80') center center/cover;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: white;
      text-align: center;
      padding: 0 20px;
    }
    
    .logo-container {
      margin-bottom: 30px;
      animation: fadeInDown 1s ease-out;
    }
    
    .logo {
      height: 90px;
      filter: drop-shadow(0 0 10px rgba(0,0,0,0.4));
    }
    
    .hero-content {
      max-width: 800px;
      animation: fadeInUp 1.5s ease-in-out;
    }
    
    .hero-title {
      font-size: 3.2rem;
      font-weight: 800;
      margin-bottom: 20px;
      text-shadow: 0 4px 8px rgba(0,0,0,0.3);
      line-height: 1.2;
    }
    
    .hero-subtitle {
      font-size: 1.4rem;
      font-weight: 300;
      margin-bottom: 40px;
      opacity: 0.9;
      line-height: 1.6;
    }
    
    .highlight {
      color: var(--accent-color);
      font-weight: 700;
    }
    
    /* Navigation */
    .nav-links {
      display: flex;
      gap: 15px;
      margin-top: 30px;
      animation: fadeInUp 2s ease-in-out;
    }
    
    .nav-link {
      padding: 12px 30px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 18px;
      text-decoration: none;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .nav-link-primary {
      background: white;
      color: var(--primary-color);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .nav-link-primary:hover {
      background: var(--accent-color);
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    }
    
    .nav-link-secondary {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      backdrop-filter: blur(5px);
    }
    
    .nav-link-secondary:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-3px);
    }
    
    /* Sections */
    .section {
      padding: 80px 0;
    }
    
    .section-title {
      text-align: center;
      font-size: 2.2rem;
      font-weight: 700;
      margin-bottom: 50px;
      position: relative;
      padding-bottom: 15px;
      color: var(--primary-color);
    }
    
    .section-title::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: var(--accent-color);
      border-radius: 2px;
    }
    
    /* About */
    .about-content {
      display: flex;
      align-items: center;
      gap: 50px;
    }
    
    .about-text {
      flex: 1;
    }
    
    .about-image {
      flex: 1;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    .about-image img {
      width: 100%;
      height: auto;
      transition: transform 0.5s ease;
    }
    
    .about-image:hover img {
      transform: scale(1.05);
    }
    
    /* Services */
    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
    }
    
    .service-card {
      background: white;
      border-radius: 15px;
      padding: 35px 30px;
      text-align: center;
      transition: all 0.4s ease;
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
      border-bottom: 4px solid var(--accent-color);
      position: relative;
      overflow: hidden;
      z-index: 1;
    }
    
    .service-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 0;
      background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
      transition: height 0.4s ease;
      z-index: -1;
    }
    
    .service-card:hover::before {
      height: 100%;
    }
    
    .service-card:hover {
      transform: translateY(-10px);
      color: white;
    }
    
    .service-icon {
      font-size: 48px;
      color: var(--primary-color);
      margin-bottom: 25px;
      transition: all 0.4s ease;
    }
    
    .service-card:hover .service-icon {
      color: var(--accent-color);
      transform: scale(1.2);
    }
    
    .service-title {
      font-size: 22px;
      font-weight: 700;
      margin-bottom: 15px;
      transition: color 0.4s ease;
    }
    
    .service-card:hover .service-title {
      color: white;
    }
    
    .service-description {
      font-size: 16px;
      line-height: 1.7;
      transition: color 0.4s ease;
    }
    
    .service-card:hover .service-description {
      color: rgba(255, 255, 255, 0.9);
    }
    
    /* Why Us */
    .features-section {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
    }
    
    .features-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
    }
    
    .feature-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border-radius: 12px;
      padding: 30px;
      text-align: center;
      transition: all 0.4s ease;
      border: 1px solid rgba(255, 255, 255, 0.15);
    }
    
    .feature-card:hover {
      transform: translateY(-8px);
      background: rgba(255, 255, 255, 0.15);
    }
    
    .feature-icon {
      font-size: 40px;
      color: var(--accent-color);
      margin-bottom: 20px;
    }
    
    .feature-title {
      font-size: 20px;
      font-weight: 700;
      margin-bottom: 15px;
    }
    
    /* Contact */
    .contact-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 40px;
    }
    
    .contact-info {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }
    
    .contact-item {
      display: flex;
      align-items: flex-start;
      gap: 15px;
      margin-bottom: 25px;
      padding-bottom: 25px;
      border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .contact-item:last-child {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom: none;
    }
    
    .contact-icon {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: rgba(113, 23, 57, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      color: var(--primary-color);
      flex-shrink: 0;
    }
    
    .contact-text {
      flex: 1;
    }
    
    .contact-title {
      font-weight: 700;
      margin-bottom: 5px;
      color: var(--primary-color);
    }
    
    .contact-form {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.08);
    }
    
    .form-control {
      padding: 15px;
      border: 1px solid rgba(0,0,0,0.1);
      border-radius: 8px;
      margin-bottom: 20px;
      transition: all 0.3s ease;
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(113, 23, 57, 0.2);
    }
    
    .btn-submit {
      background: var(--primary-color);
      color: white;
      border: none;
      padding: 15px;
      font-size: 18px;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      margin-top: 10px;
    }
    
    .btn-submit:hover {
      background: var(--secondary-color);
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    /* Footer */
    footer {
      background: var(--primary-color);
      color: white;
      padding: 40px 0 20px;
      text-align: center;
    }
    
    .footer-content {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    .footer-logo {
      height: 70px;
      margin-bottom: 20px;
      filter: brightness(0) invert(1);
    }
    
    .footer-text {
      max-width: 600px;
      margin: 0 auto 25px;
      line-height: 1.7;
      opacity: 0.9;
    }
    
    .social-icons {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .social-icon {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      color: white;
      transition: all 0.3s ease;
    }
    
    .social-icon:hover {
      background: var(--accent-color);
      transform: translateY(-5px);
      color: var(--dark-color);
    }
    
    .copyright {
      padding-top: 25px;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      font-size: 14px;
      opacity: 0.7;
    }
    
    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-50px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(50px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    /* Responsive */
    @media (max-width: 992px) {
      .hero-title {
        font-size: 2.5rem;
      }
      
      .hero-subtitle {
        font-size: 1.2rem;
      }
      
      .about-content {
        flex-direction: column;
      }
      
      .section {
        padding: 60px 0;
      }
    }
    
    @media (max-width: 768px) {
      .hero-title {
        font-size: 2rem;
      }
      
      .section-title {
        font-size: 1.8rem;
      }
      
      .nav-links {
        flex-direction: column;
        width: 100%;
        max-width: 350px;
      }
    }
    
    @media (max-width: 576px) {
      .hero-title {
        font-size: 1.8rem;
      }
      
      .section-title {
        font-size: 1.6rem;
      }
      
      .service-card {
        padding: 25px 20px;
      }
    }
    
    /* Language Toggle Button */
    .language-toggle {
      position: absolute;
      top: 30px;
      right: 30px;
      z-index: 1000;
      animation: fadeIn 1s ease-in-out;
    }
    
    .lang-btn {
      padding: 10px 20px;
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: none;
      border-radius: 30px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
      backdrop-filter: blur(10px);
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    
    .lang-btn:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: translateY(-3px);
      box-shadow: 0 6px 15px rgba(0,0,0,0.2);
    }
    
    .lang-btn:active {
      transform: translateY(1px);
    }
    
    .lang-btn i {
      font-size: 1.2rem;
    }
  </style>
</head>
<body>

  <!-- Language Toggle Button -->
  <div class="language-toggle">
    <button class="lang-btn" onclick="window.location.href='index.php'">
      <i class="fas fa-globe"></i> العربية
    </button>
  </div>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="logo-container">
      <img src="logo.png" alt="Ababel Company Logo" class="logo">
    </div>
    
    <div class="hero-content">
      <h1 class="hero-title">Welcome to <span class="highlight">Ababel</span> Development & Investment</h1>
      <p class="hero-subtitle">Experts in customs clearance, import from China, and smart logistics solutions</p>
      
      <div class="nav-links">
        <a href="app/client_login.php" class="nav-link nav-link-primary">
          <i class="fas fa-users"></i> Client Login
        </a>
        <a href="app/login.php" class="nav-link nav-link-primary">
          <i class="fas fa-user-tie"></i> Staff Login
        </a>
        <a href="#contact" class="nav-link nav-link-secondary">
          <i class="fas fa-headset"></i> Contact Us
        </a>
      </div>
    </div>
    
    <a href="#about" class="scroll-down" style="color: white; font-size: 2rem; position: absolute; bottom: 20px;">
      <i class="fas fa-chevron-down"></i>
    </a>
  </section>

  <!-- About Section -->
  <section id="about" class="section">
    <div class="container">
      <h2 class="section-title">About Us</h2>
      
      <div class="about-content">
        <div class="about-text">
          <p class="lead" style="font-size: 1.3rem; line-height: 1.8; margin-bottom: 25px;">
            <strong>Ababel Development & Investment Limited</strong> is a leading company in the field of logistics and customs clearance services, established to provide integrated solutions for our clients in import and export.
          </p>
          
          <p style="line-height: 1.8; margin-bottom: 20px;">
            We have extensive experience in dealing with global markets, especially the Chinese market, where we provide comprehensive services including purchasing, consolidation, customs clearance, and door-to-door delivery.
          </p>
          
          <p style="line-height: 1.8;">
            We strive to provide high-quality services at competitive prices, while adhering to deadlines and ensuring the safety of goods from source to final recipient.
          </p>
          
          <div class="mt-4" style="display: flex; gap: 15px;">
            <div style="background: rgba(113, 23, 57, 0.1); padding: 15px; border-radius: 10px; flex: 1; text-align: center;">
              <h3 style="color: var(--primary-color); margin-bottom: 10px;">+5 Years</h3>
              <p>Industry Experience</p>
            </div>
            <div style="background: rgba(113, 23, 57, 0.1); padding: 15px; border-radius: 10px; flex: 1; text-align: center;">
              <h3 style="color: var(--primary-color); margin-bottom: 10px;">+100 Clients</h3>
              <p>Satisfied with our services</p>
            </div>
          </div>
        </div>
        
        <div class="about-image">
          <img src="banner.jpg" alt="About Ababel Company">
        </div>
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section id="services" class="section" style="background: #f9f9f9;">
    <div class="container">
      <h2 class="section-title">Our Services</h2>
      
      <div class="services-grid">
        <div class="service-card">
          <div class="service-icon">
            <i class="fas fa-ship"></i>
          </div>
          <h3 class="service-title">Logistics Services</h3>
          <p class="service-description">
            Door-to-door transportation and tracking services with integrated logistics solutions to ensure your goods arrive on time at the best cost.
          </p>
        </div>
        
        <div class="service-card">
          <div class="service-icon">
            <i class="fas fa-boxes-packing"></i>
          </div>
          <h3 class="service-title">Consolidation & Shipping</h3>
          <p class="service-description">
            Consolidation of goods in China and comprehensive packaging for containers with the best sea and air transportation options.
          </p>
        </div>
        
        <div class="service-card">
          <div class="service-icon">
            <i class="fas fa-shopping-cart"></i>
          </div>
          <h3 class="service-title">Import from China</h3>
          <p class="service-description">
            Fast and secure purchasing, clearance, and delivery from Chinese factories with quality assurance and best prices through our extensive network.
          </p>
        </div>
        
        <div class="service-card">
          <div class="service-icon">
            <i class="fas fa-file-contract"></i>
          </div>
          <h3 class="service-title">Customs Clearance</h3>
          <p class="service-description">
            Efficient completion of all customs procedures with a specialized team that ensures all required documents are completed according to laws and regulations.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Why Us Section -->
  <section class="section features-section">
    <div class="container">
      <h2 class="section-title" style="color: white;">Why Choose Ababel?</h2>
      
      <div class="features-container">
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-clock"></i>
          </div>
          <h3 class="feature-title">Fast Execution</h3>
          <p>We adhere to agreed delivery times with clients without delays</p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h3 class="feature-title">Quality Assurance</h3>
          <p>We guarantee service quality with continuous monitoring of operations</p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-hand-holding-usd"></i>
          </div>
          <h3 class="feature-title">Competitive Prices</h3>
          <p>We offer the best market prices while maintaining service quality</p>
        </div>
        
        <div class="feature-card">
          <div class="feature-icon">
            <i class="fas fa-headset"></i>
          </div>
          <h3 class="feature-title">Full Technical Support</h3>
          <p>24/7 support team to serve our clients and answer inquiries</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="section">
    <div class="container">
      <h2 class="section-title">Contact Us</h2>
      
      <div class="contact-grid">
        <div class="contact-info">
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-phone"></i>
            </div>
            <div class="contact-text">
              <h4 class="contact-title">Company Phone</h4>
              <p>+249910564187</p>
              <p>+8618520989970</p>
            </div>
          </div>
          
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-envelope"></i>
            </div>
            <div class="contact-text">
              <h4 class="contact-title">Email</h4>
              <p>info@ababel.net</p>
            </div>
          </div>
          
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-map-marker-alt"></i>
            </div>
            <div class="contact-text">
              <h4 class="contact-title">Address</h4>
              <p>Port Sudan, Sudan</p>
            </div>
          </div>
          
          <div class="contact-item">
            <div class="contact-icon">
              <i class="fas fa-clock"></i>
            </div>
            <div class="contact-text">
              <h4 class="contact-title">Working Hours</h4>
              <p>Sunday - Thursday: 8 AM - 4 PM</p>
              <p>Friday & Saturday: Closed</p>
            </div>
          </div>
        </div>
        
        <div class="contact-form">
          <h3 style="color: var(--primary-color); margin-bottom: 25px; text-align: center;">Send Your Inquiry</h3>
          
          <form method="POST">
            <div class="mb-3">
              <input type="text" name="name" class="form-control" placeholder="Full Name" required>
            </div>
            <div class="mb-3">
              <input type="email" name="email" class="form-control" placeholder="Email Address" required>
            </div>
            <div class="mb-3">
              <input type="tel" name="phone" class="form-control" placeholder="Phone Number">
            </div>
            <div class="mb-3">
              <textarea name="message" class="form-control" rows="5" placeholder="Your Message" required></textarea>
            </div>
            <button type="submit" class="btn-submit">
              <i class="fas fa-paper-plane"></i> Send Message
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <img src="logo.png" alt="Ababel Logo" class="footer-logo">
      
      <p class="footer-text">
        Ababel Development & Investment Limited provides integrated solutions for import, customs clearance, and smart logistics services with international standards.
      </p>
      <!--
      <div class="social-icons">
        <a href="#" class="social-icon">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="#" class="social-icon">
          <i class="fab fa-twitter"></i>
        </a>
        <a href="#" class="social-icon">
          <i class="fab fa-linkedin-in"></i>
        </a>
        <a href="#" class="social-icon">
          <i class="fab fa-instagram"></i>
        </a>
        <a href="#" class="social-icon">
          <i class="fab fa-youtube"></i>
        </a>
      </div>
      -->
      <div class="copyright">
        All Rights Reserved &copy; Ababel Development & Investment Limited 2025
      </div>
    </div>
  </footer>

  <script>
    // Add scroll animations
    document.addEventListener('DOMContentLoaded', function() {
      const sections = document.querySelectorAll('.section');
      
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.animation = 'fadeInUp 0.8s ease forwards';
            observer.unobserve(entry.target);
          }
        });
      }, {
        threshold: 0.1
      });
      
      sections.forEach(section => {
        observer.observe(section);
        section.style.opacity = '0';
      });
    });
  </script>
</body>
</html>