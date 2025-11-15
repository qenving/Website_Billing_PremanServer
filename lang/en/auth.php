<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    */

    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    // Login
    'login' => [
        'title' => 'Login to Your Account',
        'email' => 'Email Address',
        'password' => 'Password',
        'remember_me' => 'Remember Me',
        'forgot_password' => 'Forgot Your Password?',
        'login_button' => 'Login',
        'no_account' => 'Don\'t have an account?',
        'register_now' => 'Register Now',
        'welcome_back' => 'Welcome Back',
        'admin_login' => 'Admin Login',
        'client_login' => 'Client Login',
    ],

    // Register
    'register' => [
        'title' => 'Create New Account',
        'name' => 'Full Name',
        'email' => 'Email Address',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'company' => 'Company Name (Optional)',
        'phone' => 'Phone Number',
        'address' => 'Address',
        'city' => 'City',
        'country' => 'Country',
        'register_button' => 'Register',
        'already_registered' => 'Already have an account?',
        'login_now' => 'Login Now',
        'agree_terms' => 'I agree to the',
        'terms_of_service' => 'Terms of Service',
        'and' => 'and',
        'privacy_policy' => 'Privacy Policy',
        'registration_success' => 'Registration successful! Please login.',
    ],

    // Forgot Password
    'forgot_password' => [
        'title' => 'Reset Your Password',
        'description' => 'Enter your email address and we\'ll send you a link to reset your password.',
        'email' => 'Email Address',
        'send_link' => 'Send Reset Link',
        'back_to_login' => 'Back to Login',
        'link_sent' => 'Password reset link has been sent to your email.',
    ],

    // Reset Password
    'reset_password' => [
        'title' => 'Reset Password',
        'email' => 'Email Address',
        'password' => 'New Password',
        'confirm_password' => 'Confirm New Password',
        'reset_button' => 'Reset Password',
        'password_reset' => 'Your password has been reset successfully.',
    ],

    // Email Verification
    'verify_email' => [
        'title' => 'Verify Your Email',
        'description' => 'Please verify your email address by clicking the link we sent to',
        'resend_link' => 'Resend Verification Link',
        'link_sent' => 'Verification link has been sent.',
        'verified' => 'Email verified successfully.',
    ],

    // Two Factor Authentication
    'two_factor' => [
        'title' => 'Two Factor Authentication',
        'code' => 'Authentication Code',
        'recovery_code' => 'Recovery Code',
        'use_recovery_code' => 'Use a recovery code',
        'use_auth_code' => 'Use an authentication code',
        'verify_button' => 'Verify',
    ],

    // Logout
    'logout' => [
        'success' => 'You have been logged out successfully.',
    ],
];
