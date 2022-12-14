<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <title>Mpesa</title>

        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
            form{
                border: 1px solid grey;
                border-radius: 10px;
                padding: 1em;
            }
        </style>
    </head>
    <body class="p-5">
        <form action="{{ url('registerurl') }}" method="get">
            @csrf
            <h5>Register URL (Done once)</h5>

            <button class="btn btn-primary mt-3">Register</button>
        </form>
        <form action="{{ url('stkpush') }}" method="POST" class="mt-3">
            @csrf
            <h5>Customer to Business (STKpush)</h5><br>
            @if (\Session::has('Success'))
                <div class="alert alert-success">
                    {!! \Session::get('Success') !!}
                </div>
            @endif                                
            @if (\Session::has('error'))
                <div class="alert alert-danger">
                    {!! \Session::get('error') !!}
                </div>
            @endif 
            <label for="phone">Phone number</label><br>
            <input id="phone" type="phone" name="phone" placeholder="254713000000"><br>

            <label for="amount">Amount</label><br>
            <input id="amount" type="number" name="amount"><br>

            <button class="btn btn-primary mt-3">Send</button>
        </form>
        <form action="{{ url('stkpush') }}" method="POST" class="mt-3">
            @csrf
            <h5>Business to Customer (STKpush)</h5><br>

            <label for="phone">Phone number</label><br>
            <input id="phone" type="phone" name="phone" placeholder="254713000000"><br>

            <label for="amount">Amount</label><br>
            <input id="amount" type="number" name="amount"><br>

            <button class="btn btn-primary mt-3">Send</button>
        </form>
    </body>
    
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</html>
