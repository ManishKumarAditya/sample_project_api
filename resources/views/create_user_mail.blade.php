<h3>Hi {{ strtoupper($name) }},</h3> 

<div>
    <p> Your login credentials are:</p>
    <p>E-Mail ID: <strong>{{ $email }}</strong></p>
    <p>Password: <strong>{{ $password }}</strong></p>
</div>

Thanks,<br>
{{ env('MAIL_FROM_NAME') }}