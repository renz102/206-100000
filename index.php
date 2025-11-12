<?php
session_start();

// Reset failed attempts if showing forgot-password suggestion
if (isset($_SESSION['show_forgot_suggestion'])) {
    $_SESSION['failed_attempts'] = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BLOOMS | Natural Florist</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    font-family: 'Poppins', sans-serif;
    background: #faf6ef;
    color: #3e3b32;
    overflow-x: hidden;
  }

  /* HEADER */
  header {
    background: #e4d9c5;
    padding: 25px 35px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #c8bfa9;
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
  }

  /* BURGER */
  .burger {
    width: 30px;
    height: 22px;
    cursor: pointer;
  }

  .burger span {
    position: absolute;
    width: 30px;
    height: 4px;
    background: #4f5a3d;
    left: 25px;
    transition: all 0.4s ease;
    margin-top: 10px;
  }

  .burger span:nth-child(1) { top: 22px; }
  .burger span:nth-child(2) { top: 30px; }
  .burger span:nth-child(3) { top: 38px; }

  .burger.active span:nth-child(1) {
    transform: rotate(45deg);
    top: 30px;
  }

  .burger.active span:nth-child(2) {
    opacity: 0;
  }

  .burger.active span:nth-child(3) {
    transform: rotate(-45deg);
    top: 30px;
  }

  /* LOGO (centered title) */
  .logo {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    font-family: 'Playfair Display', serif;
    color: #4f5a3d;
    font-size: 50px;
    letter-spacing: 1px;
    cursor: pointer;
    transition: color 0.3s;
  }

  .logo:hover {
    color: #7b8e5c;
  }

  /* AUTH BUTTONS */
  .auth-buttons {
    display: flex;
    gap: 10px;
  }

  .auth-buttons button {
    background: #4f5a3d;
    color: #fff;
    border: none;
    padding: 7px 14px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s;
  }

  .auth-buttons button:hover {
    background: #7b8e5c;
  }

  /* HERO SECTION */
  .hero {
    background: url('https://www.gardenia.net/wp-content/uploads/2023/05/dicentra-spectabilis-bleeding-heart.webp') center/cover no-repeat;
    height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #fff;
    position: relative;
  }

  .hero::after {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(50, 40, 25, 0.45);
  }

  .hero h2 {
    position: relative;
    font-size: 80px;
    z-index: 2;
    text-shadow: 0 2px 6px rgba(0,0,0,0.4);
  }

  /* SLIM NAV SIDEBAR */
  .nav-menu {
    position: fixed;
    top: 0; left: -15%;
    width: 15%;
    height: 100%;
    background: #e4d9c5;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding-top: 100px;
    padding-left: 25px;
    transition: left 0.4s ease;
    z-index: 900;
    border-right: 1px solid #c8bfa9;
  }

  .nav-menu.active {
    left: 0;
  }

  .nav-menu a {
    color: #4f5a3d;
    text-decoration: none;
    font-size: 20px;
    margin: 15px 0;
    transition: color 0.3s;
  }

  .nav-menu a:hover {
    color: #7b8e5c;
  }

  /* SECTION SCREENS */
  .screen {
    position: fixed;
    top: 0; left: 0;
    width: 100vw;
    height: 100vh;
    background: #faf6ef;
    color: #3e3b32;
    display: none;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
    z-index: 800;
    padding: 40px;
  }

  .screen.active {
    display: flex;
    animation: fadeIn 0.4s ease forwards;
  }

  @keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  .screen h2 {
    color: #4f5a3d;
    margin-bottom: 20px;
    font-size: 32px;
  }

  .screen p {
    max-width: 600px;
    color: #4a443b;
    line-height: 1.6;
  }

  footer {
    background: #e4d9c5;
    text-align: center;
    padding: 15px;
    font-size: 14px;
    color: #4a443b;
    border-top: 1px solid #c8bfa9;
  }

  /* RESPONSIVE */
  @media (max-width: 768px) {
    .nav-menu {
      width: 60%;
      left: -60%;
    }

    .nav-menu.active {
      left: 0;
    }
  }

/* ===== MODAL WRAPPER ===== */
.modal {
  display: none;
  position: fixed;
  inset: 0;
  z-index: 2000;
  background: rgba(0, 0, 0, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 30px;
  overflow-y: auto;
  animation: fadeModal 0.25s ease;
}

@keyframes fadeModal {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}

/* ===== MODAL CONTENT ===== */
.modal-content {
  background: #faf6ef;
  width: 420px;
  max-width: 95%;
  padding: 40px 35px;
  border-radius: 14px;
  box-shadow: 0 10px 35px rgba(0, 0, 0, 0.25);
  border: 1px solid #d8ccb7;
  position: relative;
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from { transform: translateY(10px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

.modal-content h2 {
  text-align: center;
  color: #4f5a3d;
  font-size: 28px;
  margin-bottom: 25px;
  letter-spacing: 0.5px;
}

/* ===== FORM FIELDS ===== */
.modal-content form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.modal-content label {
  font-size: 14px;
  color: #3e3b32;
  margin-bottom: 5px;
  font-weight: 500;
}

.modal-content input,
.modal-content select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #c8bfa9;
  border-radius: 6px;
  background: #fff;
  font-size: 15px;
  transition: all 0.2s ease;
}

.modal-content input:focus,
.modal-content select:focus {
  border-color: #7b8e5c;
  outline: none;
  box-shadow: 0 0 0 2px rgba(123, 142, 92, 0.15);
}

/* ===== BUTTON ===== */
.modal-content button {
  background: #4f5a3d;
  color: #fff;
  border: none;
  padding: 12px;
  border-radius: 6px;
  font-size: 17px;
  cursor: pointer;
  margin-top: 10px;
  transition: all 0.3s ease;
}

.modal-content button:hover {
  background: #7b8e5c;
  transform: translateY(-1px);
}

/* ===== CLOSE BUTTON ===== */
.close {
  color: #4f5a3d;
  position: absolute;
  right: 18px;
  top: 12px;
  font-size: 28px;
  font-weight: bold;
  cursor: pointer;
  transition: color 0.2s;
}

.close:hover {
  color: #7b8e5c;
}

/* ===== ALERT BOXES ===== */
.alert-box {
  background-color: #fdecea;
  color: #a94442;
  border: 1px solid #f5c6cb;
  border-radius: 6px;
  padding: 10px 15px;
  font-size: 14px;
  animation: fadeIn 0.5s ease;
}

.alert-box.success {
  background-color: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #c8e6c9;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 500px) {
  .modal-content {
    width: 90%;
    padding: 30px 25px;
  }
}

/* Explore button in hero */
.explore-btn {
    margin-top:25px;
    padding: 15px 40px;
    font-size: 40px;
    background-color: #4f5a3d;
    color: #fff;
    border: none;
    border-radius:18px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    z-index: 2;
}

.explore-btn:hover {
    background-color: #7b8e5c;
    transform: scale(1.05);
}

/* Make any .screen scrollable */
.screen.scrollable {
  overflow-y: auto; /* enable vertical scrolling */
  -webkit-overflow-scrolling: touch; /* smooth scroll on mobile */
}

/* ABOUT SECTION */
#about {
  position: relative;       /* stays consistent with your screen layout */
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  overflow-y: auto;      /* allows scrolling */
  padding: 100px 40px 60px;
  align-items: flex-start;
  justify-content: flex-start;
  background: #faf6ef;   /* clean background */
}

/* Optional: additional scroll styling for modular approach */
#about.scrollable {
  overflow-y: auto;
  -webkit-overflow-scrolling: touch; /* smooth scroll for mobile */
  padding: 80px 40px 40px 40px; /* slightly different padding if needed */
}

#about p {
  text-align: center;
  font-family: 'Arial', sans-serif;
  max-width: 1200px;
  line-height: 2;
  font-size: 30px;
  margin: 0 auto 20px auto;     /* centers the block itself horizontally */
  color: #3e3b32;
}

#about h2 {
  font-size: 80px;
  margin-bottom: 50px;
  font-family: 'Arial', sans-serif;
  color: #2b2820;
  text-align: left;             /* stays left-aligned */
}

/* TEAM SECTION UNDER ABOUT */
#team {
  max-width: 1200px; /* increased from 900px */
  margin: 0 auto;
  padding: 50px 20px;
}

.team-member {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 30px; /* slightly bigger gap */
  padding: 30px 0; /* more padding for breathing room */
  flex-wrap: wrap; /* allows stacking on smaller screens */
}

.team-member img {
  width: 200px;   /* increased from 150px */
  height: 200px;  /* increased from 150px */
  object-fit: cover;
  border-radius: 50%;
  border: 2px solid #333;
  order: 2; /* image appears on the right on large screens */
}

.member-info {
  flex: 1;
  order: 1; /* text appears on the left on large screens */
}

.member-info h1 {
  margin: 0;
  font-size: 28px; /* slightly bigger */
}

.member-info h3 {
  margin: 5px 0 0;
  font-size: 20px; /* slightly bigger */
  font-weight: normal;
  color: #555;
}

hr {
  border: 0;
  height: 4px;               /* thicker line */
  width: 80%;                /* longer, almost full width */
  background-color: #d8c4a2; /* beige/earthy tone */
  margin: 40px auto;         /* spacing above & below, centered */
  border-radius: 2px;        /* slight rounding for elegance */
}

/* MOBILE: stack image above text */
@media screen and (max-width: 600px) {
  .team-member {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .team-member img {
    order: 0; /* image appears first */
    margin-bottom: 15px; /* more spacing */
    width: 180px;  /* slightly smaller for mobile */
    height: 180px;
  }

  .member-info {
    order: 1;
  }
}

/* CONTACT SCREEN */
#contact {
  display: center;
  background: #f3ede0ff; /* soft, warm background */

}

#contact h2 {
  font-family: 'Arial', sans-serif;
  font-size: 70px;
  color: #605947ff;
  margin-bottom: 40px;
}

#contact p {
  font-family: 'Arial', sans-serif;
  font-size: 28px;
  line-height: 2;
  color: #3e3b32;
  max-width: 800px;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: -3px;
}

.profile-circle {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 2px solid #4f5a3d;
  object-fit: cover;
}

.alert-box {
  background-color: #fdecea;
  color: #a94442;
  border: 1px solid #f5c6cb;
  border-radius: 6px;
  padding: 10px 15px;
  margin: 10px 0 20px 0;
  font-size: 14px;
  animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

.alert-box.success {
  background-color: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #c8e6c9;
}

@media (max-width: 500px) {
  .modal-content {
    width: 90%;
    padding: 25px;
  }
  .modal-content form {
    flex-direction: column;
  }
  .modal-content .half {
    width: 100%;
  }
}

/* PROFILE SCREEN */
#profile {
  display: flex;              /* enables flex layout for the profile screen */
  flex-direction: column;     /* stack content vertically */
  align-items: center;        /* center horizontally */
  padding-top: 120px;         /* pushes content below fixed header/logo */
  gap: 30px;                  /* spacing between photo section and info sections */
}

/* PROFILE PHOTO */
.profile-photo-section {
  display: flex;
  flex-direction: column;     /* stack photo + upload form vertically */
  align-items: center;        /* center horizontally */
  margin-bottom: 20px;
  z-index: 1;
}

.profile-circle-large {
  width: 90px;
  height: 90px;
  border: 3px solid #4f5a3d;
  object-fit: cover;
  border-radius: 50%;
  background: #faf6ef;
  margin-bottom: 10px;       /* space between photo and upload button */
}

/* CONTAINER FOR BASIC + CONTACT INFO */
.profile-sections-container {
  display: flex;             /* enables side-by-side layout */
  gap: 40px;                 /* spacing between sections */
  flex-wrap: wrap;           /* allows stacking on smaller screens */
  justify-content: center;   /* center horizontally */
  width: 100%;
  max-width: 900px;          /* optional: limit width */
}

/* INDIVIDUAL PROFILE SECTIONS */
.profile-section {
  flex: 1;                   /* sections grow evenly */
  min-width: 250px;          /* prevents shrinking too much */
  padding: 18px;
  border-radius: 10px;
  background: #fff;           /* optional: give sections contrast */
  box-shadow: 0 2px 6px rgba(0,0,0,0.1); /* subtle shadow */
}

.profile-section h3 {
  font-size: 18px;
  margin-bottom: 12px;
}

/* FORM ELEMENTS */
.profile-section form input,
.profile-section form select,
.profile-section form button {
  width: 100%;
  padding: 8px 10px;
  font-size: 14px;
  margin-bottom: 10px;
  border: 1px solid #c8bfa9;
  border-radius: 6px;
}

.profile-section form button {
  background-color: #4f5a3d;
  color: #fff;
  cursor: pointer;
  transition: all 0.3s;
}

.profile-section form button:hover {
  background-color: #7b8e5c;
}

.profile-section.contact-info {
  flex: unset;      /* remove flex-grow/shrink */
  height: auto;     /* auto height to fit content */
  padding: 12px 15px; /* reduce vertical padding */
}

/* ========== PROFILE SECTION ========== */
.profile-container {
  display: flex;
  justify-content: flex-start;
  align-items: flex-start;
  gap: 20px;
  padding: 20px;
  margin-top: 70px; /* so it won't overlap with logo/navbar */
}

.profile-info {
  margin-top: -45px; /* moves it slightly upward */
}

.profile-photo-section,
.profile-info,
.contact-info {
  background: #fff;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  width: 300px;
}

.profile-photo {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  display: block;
  margin: 10px auto;
}

.profile-container h2 {
  text-align: center;
  color: #333;
  margin-bottom: 10px;
}

.profile-container form {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.profile-container input,
.profile-container select,
.profile-container button {
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
}

.profile-container button {
  background-color: #4caf50;
  color: white;
  cursor: pointer;
  transition: background 0.2s ease;
}

.profile-container button:hover {
  background-color: #3e8e41;
}

#profile {
  display: none; /* this will be overridden by .screen.active */
}

#profile.active {
  display: flex;
}

.profile-sections-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 25px;
  width: 100%;
  max-width: 950px;
}

/* INDIVIDUAL PROFILE SECTIONS */
.profile-info, 
.security-info, 
.profile-photo-section {
  background: #fff;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
  min-width: 280px;
  flex: 1 1 300px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.profile-info h2,
.security-info h2,
.profile-photo-section h2 {
  margin-bottom: 15px;
  font-size: 22px;
  color: #4f5a3d;
  border-bottom: 1px solid #e4d9c5;
  padding-bottom: 8px;
}

/* FORM ELEMENTS */
.profile-info form input,
.profile-info form select,
.profile-info form button,
.security-info form input,
.security-info form select,
.security-info form button {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #c8bfa9;
  border-radius: 6px;
  font-size: 15px;
}

.profile-info form button,
.security-info form button {
  background: #4f5a3d;
  color: #fff;
  cursor: pointer;
  margin-top: 6px;
  transition: all 0.3s;
}

.profile-info form button:hover,
.security-info form button:hover {
  background: #7b8e5c;
}

/* SEPARATOR */
.security-info hr {
  border: none;
  height: 1px;
  background: #e4d9c5;
  margin: 20px 0;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .profile-sections-container {
    flex-direction: column;
    align-items: center;
  }

  .profile-info,
  .security-info,
  .profile-photo-section {
    max-width: 90%;
  }
}

/* Flex container for email/phone and password */
.security-flex-container {
  display: flex;
  gap: 30px; /* space between left and right */
  flex-wrap: wrap; /* stack on small screens */
}

/* Left and right sides */
.contact-side, 
.password-side {
  flex: 1 1 280px; /* grow/shrink with min width */
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Vertical divider between the two sides */
.password-side {
  border-left: 1px solid #e4d9c5;
  padding-left: 20px;
}

/* Responsive: stack on small screens */
@media (max-width: 768px) {
  .security-flex-container {
    flex-direction: column;
  }
  .password-side {
    border-left: none;
    padding-left: 0;
  }
}

/* Widen the security container */
.security-info {
  background: #fff;
  max-width: 1200px;  /* increase from 900 */
  width: 95%;
  margin: 0 auto;
  padding: 30px 25px;  /* give more breathing room */
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.security-info {
margin-top: -55px;
}

/* Table styling */
.security-table {
  width: 100%;
  border-collapse: collapse;
}

.security-table td {
  vertical-align: top;
  padding: 12px 15px;
}

.security-table label {
  display: block;
  font-weight: 500;
  color: #3e3b32;
  margin-bottom: 6px;
}

.security-table input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #c8bfa9;
  border-radius: 6px;
  font-size: 15px;
  transition: all 0.2s ease;
}

.security-table input:focus {
  border-color: #7b8e5c;
  outline: none;
  box-shadow: 0 0 0 2px rgba(123, 142, 92, 0.15);
}

.security-buttons {
  display: flex;
  justify-content: flex-start; /* aligns to left */
  gap: 12px; /* space between buttons */
  margin-top: 10px;
  flex-wrap: wrap; /* optional: allow wrap on very small screens */
}

.security-buttons button {
  flex: 1; /* make them equal width */
  min-width: 140px; /* ensure they don’t get too small */
}

.security-buttons button {
  background: #4f5a3d;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 10px 16px;
  cursor: pointer;
  margin-left: 10px;
  transition: all 0.3s;
}

.security-buttons button:hover {
  background: #7b8e5c;
}

/* Responsive: stack table cells vertically on small screens */
@media (max-width: 768px) {
  .security-table td {
    display: block;
    width: 100%;
    padding: 8px 0;
  }

  .security-buttons {
    text-align: center;
  }

  .security-buttons button {
    margin: 8px 5px 0 5px;
    width: 48%;
  }
  
  .shop-table img {
  width: 100%;
  height: 220px;
  object-fit: cover;
  border-radius: 10px;
  }

  .shop-table {
    width: 80%;
    margin: 0 auto;
  }

  .shop-table td {
    width: 50%;
    vertical-align: top;
    text-align: center;
    padding: 15px;
  }

  .shop-table p {
    margin-top: 10px;
    color: #3b3b2f;
  }
}

</style>
</head>
<body>

<header>
  <div class="burger" id="burger">
    <span></span><span></span><span></span>
  </div>
  <h1 class="logo" id="logo">BLOOMS</h1>
  <div class="auth-buttons">
  <?php if(isset($_SESSION['username'])): ?>
    <div class="user-info">
      <?php
        // Safely get gender and profile picture
        $gender = $_SESSION['gender'] ?? 'Male';
        $img = $_SESSION['profile_pic'] ?? (
          ($gender === 'Female') ? 'female1.png' : 'male1.png'
        );
      ?>
      <img src="<?= htmlspecialchars($img); ?>" alt="Profile" class="profile-circle">
      <span>Welcome, <?= htmlspecialchars($_SESSION['full_name'] ?: $_SESSION['username']); ?>!</span>
      <a href="logout.php"><button>Logout</button></a>
    </div>
  <?php else: ?>
    <button id="loginBtn">Log In</button>
    <button id="signupBtn">Sign Up</button>
  <?php endif; ?>
</div>
</header>
<section class="hero">
  <h2>Bringing Nature’s Finest to You</h2>
  <h3><p style="color: red;">or can we?</p></h3>
  <!-- Explore Button -->
  <button class="explore-btn" onclick="window.location.href='shop.php'">EXPLORE!</button>
</section>

<!-- SLIM SIDEBAR MENU -->
  <div class="nav-menu" id="navMenu">

    <?php if(isset($_SESSION['username'])): ?>
    <a href="#" id="profileLink" data-screen="profile">Profile</a>
    <?php endif; ?>

    <a href="#" data-screen="home">Home</a>
    <a href="#" data-screen="about">About Us</a>
    <a href="shop.php">Shop</a>
    <a href="#" data-screen="contact">Contacts</a>
  </div>

  <!-- ABOUT SCREEN -->
  <div class="screen scrollable" id="about">
    <br><br><h2>About Us</h2><br>
      <p>
        Welcome to Bloom Exotica, your premier destination for discovering rare and exquisite flowers from around the world. Our website was crafted with passion to bring the beauty of nature closer to every enthusiast, collector, and admirer of the extraordinary. Here, each bloom tells a story — of vibrant origins, delicate growth, and the artistry of nature itself.
        Our mission is to make exotic flowers accessible, allowing visitors to explore, learn, and purchase with ease through a seamless and elegant online experience.
      </p><br><br><br><br><br>

      <h2>Acknowledgement</h2><br>
      <p>
        The IT students would like to express their heartfelt gratitude to everyone who contributed to the successful development of the BLOOMS | Naural Florist. This project was made possible through the dedication, teamwork, and shared effort of each member of the development team.
        The group extends their sincere appreciation to Mr. Rickman Malubag, the User Experience (UX) Designer, for his innovative design work in Figma, ensuring that users experience smooth and intuitive navigation throughout the system.
        Special thanks are also given to Mr. Diether Lingon, who handled the User Interface (UI) Design), for his creativity and skill in crafting a visually appealing and user-friendly interface.
        The team likewise acknowledges Mr. Mark Joseph Soriano, the Database Administrator, for his careful and organized management of the project’s database, which serves as the foundation of the system’s reliability.
        Sincere appreciation is also extended to Mr. Renz Pio Valenzuela, who served as the Backend Developer, for his commitment to ensuring the system’s stability, functionality, and overall performance.
      </p> <br><br>
      <p>
        Above all, the students express their deepest gratitude to their great professor, Mr. Richard De Guzman, for his guidance, patience, and valuable insights that greatly contributed to the success of this project. His mentorship has inspired the team to improve both their technical and collaborative skills.
        Finally, the team would like to thank their families and peers for their continuous encouragement, understanding, and support throughout the development process. Their presence and motivation have been instrumental in the completion of this project.
      </p><br><br><br><br><br>

      <!-- TEAM SECTION -->
      <div id="team">
        <!-- Member 1 -->
        <hr>
        <div class="team-member">
          <img src="images/rickman.jpg" alt="Rickman Malubag">
          <div class="member-info">
            <h1>Rickman Malubag</h1>
            <h3><i>UX Designer</i></h3>
          </div>
        </div>
        <hr>

        <!-- Member 2 -->
        <hr>
        <div class="team-member">
          <img src="images/diether.jpg" alt="Diether Lingon">
          <div class="member-info">
            <h1>Diether Lingon</h1>
            <h3><i>UI Designer</i></h3>
          </div>
        </div>
        <hr>

        <!-- Member 3 -->
        <hr>
        <div class="team-member">
          <img src="images/mark.jpg" alt="Mark Joseph Soriano">
          <div class="member-info">
            <h1>Mark Joseph Soriano</h1>
            <h3><i>Database Administrator</i></h3>
          </div>
        </div>
        <hr>

        <!-- Member 4 -->
        <hr>
        <div class="team-member">
          <img src="images/renz.jpg" alt="Renz Pio Valenzuela">
          <div class="member-info">
            <h1>Renz Pio Valenzuela</h1>
            <h3><i>Backend Developer</i></h3>
          </div>
        </div>
        <hr>
        <hr>
        <hr>
      </div> 
  </div>

  <!-- CONTACT SCREEN -->
  <div class="screen" id="contact">
    <h2>Contact Us</h2>
    <p>
      Questions? Orders? We’d love to hear from you.<br><br>
      <em><b>Email:</b></em> exoticsblooms@gmail.com<br>
      <em><b>Phone:</b></em> (+63)962-510-0933
    </p>
  </div>

   <!-- SHOP SCREEN (GONE HAHA) -->

  <!-- PROFILE SCREEN -->
<?php if(isset($_SESSION['username'])): ?>
<div class="screen" id="profile">
  <div class="profile-container">
    
    <!-- PROFILE PHOTO -->
    <div class="profile-photo-section">
      <h2>Profile Photo</h2>
      <form method="POST" action="update_profile.php" enctype="multipart/form-data">
        <img src="<?= htmlspecialchars($_SESSION['profile_pic'] ?? 'default.png'); ?>" 
             alt="Profile Photo" class="profile-photo">
        <input type="file" name="profile_pic" accept="image/*">
        <button type="submit">Update Photo</button>
      </form>
    </div>

    <!-- BASIC INFO -->
    <div class="profile-info">
      <h2>Basic Info</h2>
      <form method="POST" action="update_profile.php">
        <input type="hidden" name="update_basic" value="1">
        <label>Full Name:</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($_SESSION['full_name'] ?? ''); ?>" required>

        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($_SESSION['username'] ?? ''); ?>" required>

        <label>Birthday:</label>
        <input type="date" name="birthday" value="<?= htmlspecialchars($_SESSION['birthday'] ?? ''); ?>">

        <label>Gender:</label>
        <select name="gender">
          <option value="">Select Gender</option>
          <option value="Male" <?= ($_SESSION['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
          <option value="Female" <?= ($_SESSION['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
          <option value="Other" <?= ($_SESSION['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
        </select>

        <button type="submit">Save Changes</button>
      </form>
    </div>

<!-- SECURITY SECTION -->
<div class="security-info">
  <h2>Security</h2>
  <form id="securityForm" method="POST" action="update_profile.php">
    <table class="security-table">
      <tr>
        <td>
          <label for="email">Email:</label>
          <input 
            type="email" 
            id="email" 
            name="email" 
            value="<?= htmlspecialchars($_SESSION['email'] ?? ''); ?>" 
            placeholder="Enter your email">

        </td>
        <td>
          <label for="current_password">Current Password:</label>
          <input 
            type="password" 
            id="current_password" 
            name="current_password" 
            placeholder="********">
        </td>
      </tr>
      <tr>
        <td>
          <label for="phone_number">Phone Number:</label>
          <input 
            type="text" 
            id="phone_number" 
            name="phone_number" 
            value="<?= htmlspecialchars($_SESSION['phone_number'] ?? ''); ?>" 
            placeholder="Enter your phone number">

        </td>
        <td>
          <label for="new_password">New Password:</label>
          <input 
            type="password" 
            id="new_password" 
            name="new_password" 
            placeholder="Enter new password">
        </td>
      </tr>
      <tr>
        <td></td>
        <td>
          <label for="confirm_new_password">Confirm New Password:</label>
          <input 
            type="password" 
            id="confirm_new_password" 
            name="confirm_new_password" 
            placeholder="Confirm new password">
        </td>
      </tr>
    </table>

    <div class="security-buttons">
      <button type="submit" name="update_contact" id="updateContactBtn">Update Contact</button>
      <button type="submit" name="change_password" id="changePasswordBtn">Change Password</button>
    </div>

    <input type="hidden" name="change_password" id="change_password" value="">
  </form>
</div>

  </div>
</div>
<?php endif; ?>

<!-- LOGIN MODAL -->
<?php if (!isset($_SESSION['username'])): // Only show login modal if not logged in ?>
<div id="loginModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" id="closeLogin">&times;</span>
    <h2>Log In</h2>

    <?php if(isset($_SESSION['login_error'])): ?>
      <div class="alert-box">
        <?= htmlspecialchars($_SESSION['login_error']); ?>
      </div>
      <?php unset($_SESSION['login_error']); ?>
    <?php endif; ?>

    <form method="POST" action="login.php">
  <label>Username:</label>
  <input type="text" name="username" required><br>
  <label>Password:</label>
  <input type="password" name="password" required><br>

  <div style="text-align:right; margin-bottom:10px;">
    <a href="#" id="forgotPasswordLink" style="color:#4f5a3d; font-size:14px; text-decoration:underline;">
      Forgot Password?
    </a>
  </div>

  <button type="submit">Log In</button>
</form>
  </div>
</div>
<?php endif; ?>

<!-- SIGNUP MODAL -->
<?php if (!isset($_SESSION['username'])): // Only show signup modal if not logged in ?>
<div id="signupModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" id="closeSignup">&times;</span>
    <h2>Sign Up</h2>

    <?php if(isset($_SESSION['signup_error'])): ?>
      <div class="alert-box">
        <?= htmlspecialchars($_SESSION['signup_error']); ?>
      </div>
      <?php unset($_SESSION['signup_error']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['signup_success'])): ?>
      <div class="alert-box success">
        <?= htmlspecialchars($_SESSION['signup_success']); ?>
      </div>
      <?php unset($_SESSION['signup_success']); ?>
    <?php endif; ?>

    <form method="POST" action="signup.php">
      <div style="display:flex; gap:10px;">
        <div style="flex:1;">
          <label>First Name:</label>
          <input type="text" name="first_name" placeholder="First Name" required>
        </div>
        <div style="flex:1;">
          <label>Last Name:</label>
          <input type="text" name="last_name" placeholder="Last Name" required>
        </div>
      </div>

      <div class="half">
        <label>Gender:</label>
        <select name="gender" required>
          <option value="">Select Gender</option>
          <option value="Male">Male</option>
          <option value="Female">Female</option>
        </select>
      </div>

      <div class="half">
        <label>Username:</label>
        <input type="text" name="username" placeholder="Enter your Username" required>
      </div>

      <label>Email:</label>
      <input type="email" name="email" placeholder="Enter your Email" required>

      <label>Phone Number:</label>
      <input type="text" name="phone_number" placeholder="Enter your Phone Number" required>

      <label>Password:</label>
      <input type="password" name="password" placeholder="Enter your Password" required>

      <button type="submit">Sign Up</button>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- FORGOT PASSWORD MODAL -->
<div id="forgotPasswordModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" id="closeForgot">&times;</span>
    <h2>Reset Password</h2>
    <p style="font-size:14px; color:#4a443b; margin-bottom:15px;">
      Enter your email, and we’ll send you a link to reset your password.
    </p>

    <?php if(isset($_SESSION['forgot_error'])): ?>
      <div class="alert-box">
        <?= htmlspecialchars($_SESSION['forgot_error']); ?>
      </div>
      <?php unset($_SESSION['forgot_error']); ?>
    <?php endif; ?>

    <?php if(isset($_SESSION['forgot_success'])): ?>
      <div class="alert-box success">
        <?= htmlspecialchars($_SESSION['forgot_success']); ?>
      </div>
      <?php unset($_SESSION['forgot_success']); ?>
    <?php endif; ?>

    <form method="POST" action="forgot_password.php">
      <label>Email:</label>
      <input type="email" name="email" required>
      <button type="submit">Send Reset Link</button>
    </form>
  </div>
</div>

<!-- RESET PASSWORD MODAL -->
<div id="resetPasswordModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" id="closeReset">&times;</span>
    <h2>Set New Password</h2>
    <p style="font-size:14px; color:#4a443b; margin-bottom:15px;">
      Enter a new password to secure your account.
    </p>

    <?php if(isset($_SESSION['reset_error'])): ?>
      <div class="alert-box">
        <?= htmlspecialchars($_SESSION['reset_error']); ?>
      </div>
      <?php unset($_SESSION['reset_error']); ?>
    <?php endif; ?>

    <form method="POST" action="reset_password.php">
      <label>New Password:</label>
      <input type="password" name="new_password" required>
      <button type="submit">Reset Password</button>
    </form>
  </div>
</div>

<footer>
  &copy; 2025 Exotic Blooms. All Rights Reserved.
</footer>

<script>
// ---------- NAVIGATION ----------
const burger = document.getElementById('burger');
const navMenu = document.getElementById('navMenu');
const screens = document.querySelectorAll('.screen');
const hero = document.querySelector('.hero');
const logo = document.getElementById('logo');

// Safety: many elements may be null depending on logged-in state
const loginModal = document.getElementById("loginModal");
const signupModal = document.getElementById("signupModal");
const forgotPasswordModal = document.getElementById("forgotPasswordModal");
const resetPasswordModal = document.getElementById("resetPasswordModal");

const loginBtn = document.getElementById("loginBtn");
const signupBtn = document.getElementById("signupBtn");
const closeLogin = document.getElementById("closeLogin");
const closeSignup = document.getElementById("closeSignup");
const closeForgot = document.getElementById("closeForgot");
const closeReset = document.getElementById("closeReset");
const forgotPasswordLink = document.getElementById("forgotPasswordLink");

// --------- BURGER MENU TOGGLE ---------
if (burger && navMenu) {
  burger.addEventListener('click', () => {
    burger.classList.toggle('active');
    navMenu.classList.toggle('active');
  });
}

// --------- NAV LINK LOGIC ---------
const navLinks = document.querySelectorAll('.nav-menu a');
if (navLinks && navLinks.length) {
  navLinks.forEach(link => {
    link.addEventListener('click', e => {
      const href = link.getAttribute('href');
      const target = link.dataset.screen;

      // Allow normal navigation for external pages like shop.php
      if (href && href.endsWith('.php')) return;

      // Otherwise, handle internal section switching
      e.preventDefault();

      // Close nav menu
      if (navMenu) navMenu.classList.remove('active');
      if (burger) burger.classList.remove('active');

      // Hide hero for all except home
      if (hero) hero.style.display = (target === 'home') ? 'flex' : 'none';

      // Hide all screens
      screens.forEach(s => s.classList.remove('active'));

      // Hide profile when switching away
      const profileScreen = document.getElementById('profile');
      if (profileScreen) profileScreen.classList.remove('active');

      // Show target section
      if (target && document.getElementById(target)) {
        openScreen(target);
      } else {
        console.warn("No screen found for:", target);
      }
    });
  });
}

// --------- LOGO CLICK ---------
if (logo) logo.addEventListener('click', goHome);

// --------- SCREEN CONTROL FUNCTIONS ---------
function openScreen(id) {
  if (hero) hero.style.display = (id === 'home') ? 'flex' : 'none';
  screens.forEach(s => s.classList.remove('active'));
  const screen = document.getElementById(id);
  if (screen) screen.classList.add('active');
}

function goHome() {
  screens.forEach(s => s.classList.remove('active'));
  if (hero) hero.style.display = 'flex';
}

// --------- MODAL OPEN/CLOSE LOGIC ---------
if (loginBtn && loginModal) loginBtn.onclick = () => loginModal.style.display = "flex";
if (signupBtn && signupModal) signupBtn.onclick = () => signupModal.style.display = "flex";
if (forgotPasswordLink) {
  forgotPasswordLink.onclick = () => {
    if (loginModal) loginModal.style.display = "none";
    if (forgotPasswordModal) forgotPasswordModal.style.display = "flex";
  };
}

// Close modals
if (closeLogin && loginModal) closeLogin.onclick = () => loginModal.style.display = "none";
if (closeSignup && signupModal) closeSignup.onclick = () => signupModal.style.display = "none";
if (closeForgot && forgotPasswordModal) closeForgot.onclick = () => forgotPasswordModal.style.display = "none";
if (closeReset && resetPasswordModal) closeReset.onclick = () => resetPasswordModal.style.display = "none";

// Click outside to close modals or nav
window.addEventListener("click", (event) => {
  if (loginModal && event.target === loginModal) loginModal.style.display = "none";
  if (signupModal && event.target === signupModal) signupModal.style.display = "none";
  if (forgotPasswordModal && event.target === forgotPasswordModal) forgotPasswordModal.style.display = "none";
  if (resetPasswordModal && event.target === resetPasswordModal) resetPasswordModal.style.display = "none";

  if (navMenu && burger && !navMenu.contains(event.target) && !burger.contains(event.target)) {
    navMenu.classList.remove("active");
    burger.classList.remove("active");
  }
});

// --------- AUTO OPEN MODALS BASED ON SESSION OR HASH ---------
<?php if(isset($_SESSION['reset_modal_open'])): ?>
  if (typeof resetPasswordModal !== 'undefined' && resetPasswordModal) {
    resetPasswordModal.style.display = "flex";
  }
  <?php unset($_SESSION['reset_modal_open']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['show_forgot_suggestion'])): ?>
  if (typeof loginModal !== 'undefined' && loginModal) loginModal.style.display = "none";
  if (typeof forgotPasswordModal !== 'undefined' && forgotPasswordModal) forgotPasswordModal.style.display = "flex";
  <?php unset($_SESSION['show_forgot_suggestion']); ?>
<?php endif; ?>

// --------- AUTO HIDE SUCCESS ALERTS ---------
const successBox = document.querySelector(".alert-box.success");
if (successBox) {
  setTimeout(() => {
    successBox.style.transition = "opacity 0.5s";
    successBox.style.opacity = "0";
    setTimeout(() => successBox.remove(), 500);
  }, 3000);
}

// --------- UNIFIED PAGE LOAD HANDLER ---------
window.addEventListener("load", () => {
  const hash = window.location.hash.replace("#", "");
  const isLoggedIn = <?php echo isset($_SESSION['username']) ? 'true' : 'false'; ?>;
  const justLoggedIn = <?php echo isset($_SESSION['just_logged_in']) ? 'true' : 'false'; ?>;
  const stayOnProfile = <?php echo isset($_SESSION['stay_on_profile']) ? 'true' : 'false'; ?>;

  // --- Handle modals based on hash ---
  if (hash === "loginModal" && loginModal) {
    if (hero) hero.style.display = 'flex'; // ensure non-blank background
    loginModal.style.display = "flex";
  } else if (hash === "signupModal" && signupModal) {
    if (hero) hero.style.display = 'flex';
    signupModal.style.display = "flex";
  }

  // --- Screen logic ---
  if (stayOnProfile) {
    openScreen("profile");
  } else if (isLoggedIn && justLoggedIn) {
    goHome(); // open home instead of profile after successful login
  } else if (hash && document.getElementById(hash) && document.getElementById(hash).classList.contains('screen')) {
    openScreen(hash);
  } else {
    goHome();
  }

  if (hash) history.replaceState(null, null, window.location.pathname);
});
<?php unset($_SESSION['stay_on_profile']); ?>

// --------- SECURITY FORM HANDLING ---------
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('securityForm');
  const updateBtn = document.getElementById('updateContactBtn');
  const changeBtn = document.getElementById('changePasswordBtn');

  const email = document.getElementById('email');
  const phone = document.getElementById('phone_number');
  const currentPass = document.getElementById('current_password');
  const newPass = document.getElementById('new_password');
  const confirmPass = document.getElementById('confirm_new_password');

  updateBtn.addEventListener('click', function() {
    passwordFieldsDisabled(true);
    email.disabled = false;
    phone.disabled = false;
  });

  changeBtn.addEventListener('click', function(e) {
    e.preventDefault();
    passwordFieldsDisabled(false);
    email.disabled = true;
    phone.disabled = true;

    if (!currentPass.value.trim() || !newPass.value.trim() || !confirmPass.value.trim()) {
      alert('Please fill out all password fields before changing your password.');
      return;
    }

    if (newPass.value !== confirmPass.value) {
      alert('New password and confirmation do not match.');
      return;
    }

    document.getElementById('change_password').value = '1';
    
    form.submit();
  });
});

function passwordFieldsDisabled(state) {
  ['current_password', 'new_password', 'confirm_new_password'].forEach(id => {
    const field = document.getElementById(id);
    if (field) field.disabled = state;
  });
}
</script>

<?php
// Cleanup session flags after use
unset($_SESSION['just_logged_in']);
unset($_SESSION['stay_on_profile']);
?>

</body>
</html>
