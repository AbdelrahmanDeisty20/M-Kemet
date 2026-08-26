<?php

return [
    // General Response Messages
    'operationSuccessful' => 'Operation completed successfully',
    'errorOccurred'       => 'An error occurred, please try again later',
    'notFound'            => 'Requested item not found',
    'validationError'     => 'The given data was invalid',

    // Authentication Messages
    'accountCreatedSuccessfully'     => 'Account created successfully. Please enter the 6-digit OTP sent to your email.',
    'otpVerifiedSuccessfully'        => 'Account activated and logged in successfully',
    'otp_invalid'                    => 'The verification code is invalid or missing',
    'otp_expired'                    => 'The verification code has expired. Please request a new code.',
    'invalidPassword'                => 'Incorrect password',
    'invalidCredentials'             => 'Invalid email or password',
    'userSuspended'                  => 'This account is suspended. Please contact technical support.',
    'userPendingVerification'        => 'Account is not activated yet. Please verify your account using the OTP code first.',
    'otpResentSuccessfully'          => 'Verification code resent successfully',
    'otp_wait_resend'                => 'Please wait one minute before requesting a new code',
    'userAlreadyActive'              => 'This email address is already verified',
    'profileSuccessFully'            => 'Profile data retrieved successfully',
    'logoutSuccessFully'             => 'Logged out successfully',
    'logoutAllDevicesSuccessFully'   => 'Logged out from all devices successfully',
    'user_not_found'                 => 'User not found in the system',
    'resetOtpSentSuccessfully'       => 'Password reset OTP code has been sent to your email',
    'resetOtpValid'                  => 'OTP code is valid. You can now enter your new password.',
    'passwordResetSuccessfully'      => 'Password reset successfully. You can now log in.',
    'tokenRefreshedSuccessfully'     => 'Token refreshed successfully',
    'invalid_token'                  => 'Invalid or expired Refresh Token',

    // Mail Strings
    'otp_mail_subject' => 'Your Verification Code - M-Kemet Platform',
    'otp_mail_welcome' => 'Hello :name,',
    'otp_mail_intro'   => 'Thank you for registering on M-Kemet platform. Your verification code (OTP) is:',
    'otp_mail_expiry'  => 'This code is valid for 5 minutes only.',
    'otp_mail_ignore'  => 'If you did not request this code, please ignore this email.',
];
