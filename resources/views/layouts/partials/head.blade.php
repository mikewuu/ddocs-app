<title>ddocs - for your docs</title>

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="stripe-key" content="{{ env('STRIPE_KEY') }}"/>

<!-- Favicon -->
<link rel="shortcut icon" href="{{ asset('/images/icons/favicon.png') }}">

<!-- Scripts -->
<script>
    window.awsURL = "{{awsURL()}}";
</script>


<!-- External Scripts -->
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
@include('vendor.trackers.fb-pixel')
@include('vendor.trackers.ga')
@include('vendor.fonts.type-kit')