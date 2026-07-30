<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Contact Enquiry</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f7f7f7; margin: 0; padding: 0; }
        .email-wrapper { max-width: 600px; margin: 20px auto; background: #fff; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .email-header { background: #0a3d62; padding: 20px; text-align: center; }
        .email-header img { max-width: 150px; }
        .email-body { padding: 20px; color: #333; line-height: 1.6; }
        .email-footer { background: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #777; }
        h2 { color: #0a3d62; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table td { padding: 8px; border: 1px solid #ddd; }
    </style>
</head>
<body>
<div class="email-wrapper">

    <!-- Header -->
    <div class="email-header">
        <img src="https://anvayafoundation.com/DDPU/frontend/assets/img/logo/ddpu-logo.jpg" alt="DDPU Logo">
    </div>

    <!-- Body -->
    <div class="email-body">
        <h2>New Contact Enquiry Received</h2>
        <p>You have received a new enquiry through the contact form. Details are below:</p>

        <table>
    <tr>
        <td><strong>Name:</strong></td>
        <td>{{ $details['name'] }}</td>
    </tr>
    <tr>
        <td><strong>Email:</strong></td>
        <td>{{ $details['email'] }}</td>
    </tr>
    @if(!empty($details['phone']))
    <tr>
        <td><strong>Phone:</strong></td>
        <td>{{ $details['phone'] }}</td>
    </tr>
    @endif
    @if(!empty($details['subject']))
    <tr>
        <td><strong>Subject:</strong></td>
        <td>{{ $details['subject'] }}</td>
    </tr>
    @endif
    <tr>
        <td><strong>Message:</strong></td>
        <td>{!! nl2br(e($details['message'])) !!}</td>
    </tr>
</table>
    </div>

    <!-- Footer -->
    <div class="email-footer">
        &copy; {{ date('Y') }} Doctors and Dentists Protection Union (DDPU). All rights reserved.
    </div>
</div>
</body>
</html>
