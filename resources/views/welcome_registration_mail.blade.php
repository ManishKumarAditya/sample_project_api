<h3>Welcome {{ strtoupper($name) }},</h3> 

<div>
    set your password mail.
    <br/>
    <a href="{{ env('FRONTEND_URL') }}/create_password/{{ $token }}">Create Password</a>
</div>

Thanks,<br>
{{ env('MAIL_FROM_NAME') }}