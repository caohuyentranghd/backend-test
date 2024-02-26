<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Verify Email</title>
</head>

<body>
    <p>Please click the following link to verify your email address:</p>
    <p>Code for coppy dev: <b>{{ $token }}</b></p>
    <a href="{{ $verificationUrl }}">Verify Email Address</a>
</body>

</html>
