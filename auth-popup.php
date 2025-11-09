<!-- Auth Popup -->
<div id="auth-popup" class="popup" style="display:none;">
    <div class="popup-content">
        <span class="close-btn">&times;</span>
        <div class="tabs">
            <button id="login-tab" class="tab-btn active">Login</button>
            <button id="signup-tab" class="tab-btn">Signup</button>
        </div>

        <!-- Login Form -->
        <form id="login-form" class="form active">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>

        <!-- Signup Form -->
        <form id="signup-form" class="form">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Signup</button>
        </form>
    </div>
</div>

<style>
/* Basic Popup Styling */
.popup {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.popup-content {
    background: #fff;
    padding: 20px;
    width: 350px;
    border-radius: 8px;
    position: relative;
}

.close-btn {
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
    font-size: 22px;
}

.tabs {
    display: flex;
    justify-content: space-around;
    margin-bottom: 15px;
}

.tab-btn {
    padding: 10px 20px;
    cursor: pointer;
    background: #eee;
    border: none;
    border-radius: 4px;
}

.tab-btn.active {
    background: #333;
    color: #fff;
}

.form {
    display: none;
    flex-direction: column;
}

.form.active {
    display: flex;
}

.form input {
    margin-bottom: 10px;
    padding: 8px;
}

.form button {
    padding: 10px;
    cursor: pointer;
}
</style>

<script>
// Show/Hide Popup
function openAuthPopup() {
    document.getElementById('auth-popup').style.display = 'flex';
}
function closeAuthPopup() {
    document.getElementById('auth-popup').style.display = 'none';
}

// Close on X click
document.querySelector('#auth-popup .close-btn').addEventListener('click', closeAuthPopup);

// Tab Switching
const loginTab = document.getElementById('login-tab');
const signupTab = document.getElementById('signup-tab');
const loginForm = document.getElementById('login-form');
const signupForm = document.getElementById('signup-form');

loginTab.addEventListener('click', () => {
    loginTab.classList.add('active');
    signupTab.classList.remove('active');
    loginForm.classList.add('active');
    signupForm.classList.remove('active');
});

signupTab.addEventListener('click', () => {
    signupTab.classList.add('active');
    loginTab.classList.remove('active');
    signupForm.classList.add('active');
    loginForm.classList.remove('active');
});
</script>
