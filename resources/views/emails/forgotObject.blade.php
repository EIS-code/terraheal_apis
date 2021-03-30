<html>
    <head>
        <title> {{ __('Forgot your object') }}</title>
    </head>
    <body>
        <div class="container">
            {{ __('Hello, ') }} {{ $body['user'] }} <br/>
            {{ __('you forgot your') }}  {{ $body['object'] }} {{ __('at') }} {{ $body['shop'] }} {{ __('in') }} {{ $body['room'] }} <br/>
            {{ __('So, kindly collect when you are available.') }} <br/>
            {{ __('Thank you for visiting.') }} <br/>
        </div>
    </body>
</html>