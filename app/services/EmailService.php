<?php

/**
 * Email Service
 * Handles sending emails for various system notifications
 */
class EmailService {
    private string $senderEmail;
    private string $senderName;
    
    public function __construct() {
        // Get sender details from config or set defaults
        $this->senderEmail = defined('EMAIL_SENDER') ? EMAIL_SENDER : 'no-reply@barangay-business.com';
        $this->senderName = defined('EMAIL_SENDER_NAME') ? EMAIL_SENDER_NAME : 'Barangay Business Registration';
    }
    
    /**
     * Send an email
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $message Email body (HTML content)
     * @param array $attachments Optional attachments
     * @return bool Whether the email was sent successfully
     */
    public function sendEmail(string $to, string $subject, string $message, array $attachments = []): bool {
        // Set headers
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/html; charset=UTF-8";
        $headers[] = "From: {$this->senderName} <{$this->senderEmail}>";
        $headers[] = "Reply-To: {$this->senderEmail}";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        
        // Send email
        $sent = mail($to, $subject, $message, implode("\r\n", $headers));
        
        // Log email sending
        $logMessage = date('Y-m-d H:i:s') . " - Email to: $to, Subject: $subject, Status: " . ($sent ? 'Success' : 'Failed');
        error_log($logMessage, 3, APPROOT . '/logs/email.log');
        
        return $sent;
    }
    
    /**
     * Generate email template with standard header and footer
     * 
     * @param string $content The main content for the email
     * @param string $buttonText Optional button text
     * @param string $buttonUrl Optional button URL
     * @return string Complete HTML email
     */
    private function generateEmailTemplate(string $content, string $buttonText = '', string $buttonUrl = ''): string {
        $buttonHtml = '';
        if (!empty($buttonText) && !empty($buttonUrl)) {
            $buttonHtml = <<<HTML
            <div style="text-align: center; margin: 30px 0;">
                <a href="$buttonUrl" style="background-color: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">$buttonText</a>
            </div>
HTML;
        }
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>$buttonText</title>
        </head>
        <body style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background-color: #f8f9fa; padding: 20px; text-align: center; border-bottom: 3px solid #0d6efd;">
                <h1 style="color: #0d6efd; margin: 0;">Barangay Business Registration</h1>
            </div>
            
            <div style="background-color: white; padding: 20px; border: 1px solid #ddd; border-top: none;">
                $content
                
                $buttonHtml
                
                <p style="margin-top: 30px;">Thank you,<br>The Barangay Business Registration Team</p>
            </div>
            
            <div style="background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #6c757d; border: 1px solid #ddd; border-top: none;">
                <p>This is an automated message, please do not reply to this email.</p>
                <p>© 2025 Barangay Business Registration System. All rights reserved.</p>
            </div>
        </body>
        </html>
HTML;
    }
    
    /**
     * Send application status change email
     * 
     * @param string $to Recipient email
     * @param string $businessName Business name
     * @param string $newStatus New application status
     * @param string $message Additional message or explanation
     * @param string $actionUrl URL for action button (view application details)
     * @return bool Whether the email was sent successfully
     */
    public function sendStatusChangeEmail(string $to, string $businessName, string $newStatus, string $message, string $actionUrl): bool {
        // Set email subject based on status
        $subject = "Business Application Status Update: $businessName";
        
        // Format status for display
        $statusDisplay = ucfirst(str_replace('_', ' ', $newStatus));
        
        // Create email content
        $content = <<<HTML
        <h2>Application Status Update</h2>
        <p>Dear Business Owner,</p>
        <p>The status of your business application for <strong>$businessName</strong> has been updated to <strong>$statusDisplay</strong>.</p>
        <p>$message</p>
HTML;
        
        // Generate complete email with template
        $emailBody = $this->generateEmailTemplate($content, "View Application Details", $actionUrl);
        
        // Send the email
        return $this->sendEmail($to, $subject, $emailBody);
    }
    
    /**
     * Send payment confirmation email
     * 
     * @param string $to Recipient email
     * @param string $businessName Business name
     * @param float $amount Payment amount
     * @param string $referenceNumber Payment reference number
     * @param string $receiptUrl URL to view/download receipt
     * @return bool Whether the email was sent successfully
     */
    public function sendPaymentConfirmationEmail(string $to, string $businessName, float $amount, string $referenceNumber, string $receiptUrl): bool {
        $subject = "Payment Confirmation: $businessName";
        
        $formattedAmount = number_format($amount, 2);
        $date = date('F j, Y');
        
        $content = <<<HTML
        <h2>Payment Confirmation</h2>
        <p>Dear Business Owner,</p>
        <p>We have received your payment for <strong>$businessName</strong>.</p>
        
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 5px 0;"><strong>Amount:</strong></td>
                    <td style="text-align: right;">₱$formattedAmount</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0;"><strong>Reference Number:</strong></td>
                    <td style="text-align: right;">$referenceNumber</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0;"><strong>Date:</strong></td>
                    <td style="text-align: right;">$date</td>
                </tr>
            </table>
        </div>
        
        <p>You can view or download your receipt by clicking the button below.</p>
HTML;
        
        $emailBody = $this->generateEmailTemplate($content, "View Receipt", $receiptUrl);
        
        return $this->sendEmail($to, $subject, $emailBody);
    }
    
    /**
     * Send permit approval email
     * 
     * @param string $to Recipient email
     * @param string $businessName Business name
     * @param string $permitNumber Permit number
     * @param string $expiryDate Permit expiry date
     * @param string $permitUrl URL to view/download permit
     * @return bool Whether the email was sent successfully
     */
    public function sendPermitApprovalEmail(string $to, string $businessName, string $permitNumber, string $expiryDate, string $permitUrl): bool {
        $subject = "Business Permit Approved: $businessName";
        
        $formattedDate = date('F j, Y', strtotime($expiryDate));
        
        $content = <<<HTML
        <h2>Business Permit Approved</h2>
        <p>Dear Business Owner,</p>
        <p>Congratulations! Your business permit for <strong>$businessName</strong> has been approved.</p>
        
        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 5px 0;"><strong>Permit Number:</strong></td>
                    <td style="text-align: right;">$permitNumber</td>
                </tr>
                <tr>
                    <td style="padding: 5px 0;"><strong>Expiry Date:</strong></td>
                    <td style="text-align: right;">$formattedDate</td>
                </tr>
            </table>
        </div>
        
        <p>You can view or download your business permit by clicking the button below.</p>
HTML;
        
        $emailBody = $this->generateEmailTemplate($content, "View Permit", $permitUrl);
        
        return $this->sendEmail($to, $subject, $emailBody);
    }
    
    /**
     * Send permit renewal reminder email
     * 
     * @param string $to Recipient email
     * @param string $businessName Business name
     * @param string $expiryDate Permit expiry date
     * @param int $daysRemaining Days remaining until expiry
     * @param string $renewalUrl URL to renewal page
     * @return bool Whether the email was sent successfully
     */
    public function sendRenewalReminderEmail(string $to, string $businessName, string $expiryDate, int $daysRemaining, string $renewalUrl): bool {
        $subject = "Permit Renewal Reminder: $businessName";
        
        $formattedDate = date('F j, Y', strtotime($expiryDate));
        
        $content = <<<HTML
        <h2>Permit Renewal Reminder</h2>
        <p>Dear Business Owner,</p>
        <p>This is a reminder that your business permit for <strong>$businessName</strong> will expire in <strong>$daysRemaining days</strong> on <strong>$formattedDate</strong>.</p>
        <p>To ensure continuous operation of your business, please renew your permit before the expiry date.</p>
HTML;
        
        $emailBody = $this->generateEmailTemplate($content, "Renew Permit", $renewalUrl);
        
        return $this->sendEmail($to, $subject, $emailBody);
    }
}