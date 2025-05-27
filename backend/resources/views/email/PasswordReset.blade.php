<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Password Reset</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      padding: 30px;
    }
    .container {
      max-width: 600px;
      background-color: #fff;
      padding: 20px;
      margin: auto;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .button {
      display: flex;
      justify-content: center;
      background-color: #1b4c8b;
      color: #fff;
      padding: 10px 20px;
      margin-top: 20px;width:300px;
      text-decoration: none;
      border-radius: 4px;
    }
    .footer {
      margin-top: 30px;
      font-size: 12px;
      color: #777;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Password Reset Request</h2>
    <p>Hello,</p>
    <p>You recently requested to reset your password. Click the button below to reset it:</p>
    <a href="{{$url}}" class="button" >Reset Password</a>
    <p>If you did not request a password reset, you can safely ignore this email.</p>
    <p>Thanks,<br>The Team</p>
    <div class="footer">
      © 2025 PayCaddy. All rights reserved.
    </div>
  </div>
</body>
</html>
