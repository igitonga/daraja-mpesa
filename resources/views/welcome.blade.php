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
            .wrapper {
                font-family: 'Nunito', sans-serif;
                display: grid;
                grid-template-columns: auto auto;
                column-gap: 2em;
                row-gap: 2em;
                padding: 2em;
            }
            .each-cont{
                border: 2px solid #39b54a;
                border-radius: 10px;
                padding: 1em;
            }
            h5{
                color: #39b54a;
                font-weight: 900;
            }
            .btn{
                background: #39b54a;
                color: #fff;
            }
            .alert{
                text-align: center;
            }
        </style>
    </head>
    <body>
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
        <div class="wrapper">
            {{-- dynamic qr code --}}
            <div class="each-cont">
                <form action="{{ url('qrcode') }}" method="get">
                    @csrf
                    <h5>Dynamic QR code</h5>       
                    @if (isset($code))
                    {!! QrCode::size(200)->generate('{{ $code }}') !!}
                    @else
                    <label for="amount">Amount</label><br>
                    <input id="amount" type="number" name="amount"><br>
        
                    <button class="btn mt-3">Generate</button>
                    @endif
        
                </form>
            </div>

            {{-- c2b express --}}
            <div class="each-cont">
                <form action="{{ url('stkpush') }}" method="POST" class="mt-3">
                    @csrf
                    <h5>Customer to Business (STKpush)</h5><br>
                    <label for="phone">Phone number</label><br>
                    <input id="phone" type="phone" name="phone" placeholder="254713000000"><br>
        
                    <label for="amount">Amount</label><br>
                    <input id="amount" type="number" name="amount" placeholder="Minimum 1"><br>
        
                    <button class="btn mt-3">Send</button>
                </form>
            </div>

            {{-- b2c express --}}
            <div class="each-cont">
                <form action="{{ url('b2c') }}" method="POST" class="mt-3">
                    @csrf
                    <h5>Business to Customer (STKpush)</h5><br>
        
                    <label for="phone">Phone number</label><br>
                    <input id="phone" type="phone" name="phone" placeholder="254713000000"><br>
        
                    <label for="amount">Amount</label><br>
                    <input id="amount" type="number" name="amount" placeholder="Minimum 10"><br>
        
                    <button class="btn mt-3">Send</button>
                </form>
            </div>

            {{-- Buy goods and services --}}
            <div class="each-cont">
                <form action="{{ url('buy-goods-services') }}" method="POST" class="mt-3">
                    @csrf
                    <h5>Buy goods and services</h5><br>

                    <label for="amount">Amount</label><br>
                    <input id="amount" type="number" name="amount" placeholder="Minimum 10"><br>
        
                    <button class="btn mt-3">Send</button>
                </form>
            </div>

            {{-- Business paybill --}}
            <div class="each-cont">
                <form action="{{ url('buy-goods-services') }}" method="POST" class="mt-3">
                    @csrf
                    <h5>Business paybill</h5><br>

                    <label for="amount">Amount</label><br>
                    <input id="amount" type="number" name="amount" placeholder="Minimum 10"><br>
        
                    <button class="btn mt-3">Send</button>
                </form>
            </div>

            {{-- b2b --}}
            <div class="each-cont">
                <form action="{{ url('b2b') }}" method="POST" class="mt-3">
                    @csrf
                    <h5>Business to Business (USSD push)</h5><br>

                    <label for="amount">Amount</label><br>
                    <input id="amount" type="number" name="amount" placeholder="Minimum 10"><br>

                    <button class="btn mt-3">Send</button>
                </form>
            </div>
        </div>     
    </body>
    
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</html>
