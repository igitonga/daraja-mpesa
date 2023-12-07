<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @vite('resources/css/app.css')
        <link rel="stylesheet" href="{{ mix('css/app.css') }}">
        <title>Mpesa</title>
        <!-- Fonts -->
        <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            h5{
                color: #39b54a;
            }
        </style>
    </head>
    <body>
        <x-navbar/>

        @if (\Session::has('Success'))
            <div class="alert bg-green-600 mx-8">
                <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
                <strong>{!! \Session::get('Success') !!}</strong>
            </div>
        @endif                                
        @if (\Session::has('error'))
            <div class="alert bg-red-600 mx-8">
                <span class="closebtn" onclick="this.parentElement.style.display='none';">&times;</span> 
                <strong>{!! \Session::get('error') !!}</strong>
            </div>          
        @endif 
        <div class="grid grid-cols-2 gap-x-4 gap-y-4 px-8 py-4">
            {{-- dynamic qr code --}}
            <div class="each-cont p-6 rounded-lg">
                <form action="{{ url('qrcode') }}" method="get">
                    @csrf
                    <h5 class="text-xl font-bold">Dynamic QR code</h5>       
                    @if (isset($code))
                        {!! QrCode::size(200)->generate('{{ $code }}') !!}
                    @else
                        <label for="amount">Amount</label><br>
                        <input class="border-2 border-gray-400 p-1 rounded-md mb-3" id="amount" type="number" name="amount"><br>
            
                        <button class="btn py-2 px-6 rounded-lg">Generate</button>
                    @endif
        
                </form>
            </div>

            {{-- c2b express --}}
            <div class="each-cont p-6 rounded-lg">
                <form action="{{ url('stkpush') }}" method="POST">
                    @csrf
                    <h5 class="text-xl font-bold">Customer to Business (STKpush)</h5><br>
                    <label for="phone">Phone number</label><br>
                    <input class="border-2 border-gray-400 p-1 rounded-md mb-3" id="phone" type="phone" name="phone" placeholder="254713000000"><br>
        
                    <label for="amount">Amount</label><br>
                    <input class="border-2 border-gray-400 p-1 rounded-md mb-3" id="amount" type="number" name="amount" placeholder="Minimum 1"><br>
        
                    <button class="btn py-2 px-6 rounded-lg">Send</button>
                </form>
            </div>

            {{-- b2c express --}}
            <div class="each-cont p-6 rounded-lg">
                <form action="{{ url('b2c') }}" method="POST">
                    @csrf
                    <h5 class="text-xl font-bold">Business to Customer (STKpush)</h5><br>
        
                    <label for="phone">Phone number</label><br>
                    <input class="border-2 border-gray-400 p-1 rounded-md mb-3" id="phone" type="phone" name="phone" placeholder="254713000000"><br>
        
                    <label for="amount">Amount</label><br>
                    <input class="border-2 border-gray-400 p-1 rounded-md mb-3" id="amount" type="number" name="amount" placeholder="Minimum 10"><br>
        
                    <button class="btn py-2 px-6 rounded-lg">Send</button>
                </form>
            </div>

            {{-- Buy goods and services --}}
            <div class="each-cont p-6 rounded-lg">
                <form action="{{ url('buy-goods-services') }}" method="POST">
                    @csrf
                    <h5 class="text-xl font-bold">Buy goods and services</h5><br>

                    <label for="amount">Amount</label><br>
                    <input class="border-2 border-gray-400 p-1 rounded-md mb-3" id="amount" type="number" name="amount" placeholder="Minimum 10"><br>
        
                    <button class="btn py-2 px-6 rounded-lg">Send</button>
                </form>
            </div>

            {{-- Business paybill --}}
            <div class="each-cont p-6 rounded-lg">
                <form action="{{ url('paybill') }}" method="POST">
                    @csrf
                    <h5 class="text-xl font-bold">Business paybill</h5><br>

                    <label for="amount">Amount</label><br>
                    <input class="border-2 border-gray-400 p-1 rounded-md mb-3" id="amount" type="number" name="amount" placeholder="Minimum 10"><br>
        
                    <button class="btn py-2 px-6 rounded-lg">Send</button>
                </form>
            </div>

            {{-- b2b --}}
            <div class="each-cont p-6 rounded-lg">
                <form action="{{ url('b2b') }}" method="POST">
                    @csrf
                    <h5 class="text-xl font-bold">Business to Business (USSD push)</h5><br>

                    <label for="amount">Amount</label><br>
                    <input class="border-2 border-gray-400 p-1 rounded-md mb-3" id="amount" type="number" name="amount" placeholder="Minimum 10"><br>

                    <button class="btn py-2 px-6 rounded-lg">Send</button>
                </form>
            </div>
        </div>     
    </body>
    
</html>
