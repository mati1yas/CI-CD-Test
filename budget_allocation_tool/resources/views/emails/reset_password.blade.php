@component('mail::message')


Your six-digit One Time PIN for password reset is <br> 

<div style="text-align: center;">
    <h1 style="color: blue;">{{$pin}}</h1>
</div>. <br>

Expires in one  day .

If you've been recently registered by admins or requested a password reset, use this PIN to secure your account.
<br>
For security reasons, we recommend resetting your password promptly. Do not share this PIN with anyone.   <br>

<br>
<div style="text-align: center;">
    <a href="https://actionaccounting-439f0.web.app/#/" 
       style="display: inline-block; padding: 10px 20px; font-size: 16px; text-align: center; 
              text-decoration: none; color: #fff; background-color: #28a745; border-radius: 5px; 
              transition: background-color 0.3s;" 
       onmouseover="this.style.backgroundColor='#218838'" 
       onmouseout="this.style.backgroundColor='#28a745'">Reset Your Password</a>
</div>
<br>


If you did not make this request, please disregard this message.

Best,<br>
{{ config('app.name') }}



@endcomponent