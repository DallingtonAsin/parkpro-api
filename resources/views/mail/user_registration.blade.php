
@component("mail::panel")
<p>
	<strong class="text-success">
		{{ $subject }} at {{ config('app.name') }}
	</strong>
</p>

<p>Hello  <strong>{{ $first_name }},</strong> <br/>
{{ $details }}
</p>

@if($activity == 'registration')
<p>You will be able to login into the system using the following details<br/>
<strong>Email</strong>: <span>{{ $email }}</span><br/>
<strong>Password</strong>: <span>{{ $password }}</span><br/>
</p>
@endif

<p><a href="{{ config('app.domain') }}">Click this link to login and start using the system</a></p>

Thanks & Regards,<br/>
{{ $registra }}<br/>
{{ $company }}
<br/>
{{ $registraEmail }}

@endcomponent