
@component("mail::panel")
<p>
	<strong class="text-success">
		{{ $subject }} at {{ config('app.name') }}
	</strong>
</p>

<p>Hi  <strong>{{ $name }}</strong> <br>
{{ $details }}
</p>

@if($activity == 'registration')
<p>You will be able to login into the system using the following details<br>
<strong>Username</strong>: <span>{{ $username }}</span><br>
<strong>Password</strong>: <span>{{ $password }}</span><br>
</p>
@endif

<br>
<a href="{{ config('app.domain') }}">Click this link to login and start using the system</a>
<br>


Thanks & Regards,<br>
{{ $registra }}<br>
{{ $registraPosition }}, 
 @if(isset($companyData))
     {{ $companyData['company_name'] }}
     @else
     {{ env('APP_NAME') }}
     @endif
<br>
{{ $registraEmail }}

@endcomponent