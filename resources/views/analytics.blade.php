
@extends('layouts.layout')

@section('title')
    Analytics
@endsection

@section('content')
    <div class="grid grid-cols-3 gap-4 px-8 py-4">
        <span>
            <h1 class="text-center font-bold text-lg">Transaction Status</h1>
            <canvas id="transactionStatus"></canvas>
        </span>
    </div>
    
    <script>
        const ctx = document.getElementById('transactionStatus');
      
        var failed =  {{ Js::from($failed) }};
        var pending =  {{ Js::from($pending) }};
        var successful =  {{ Js::from($successful) }};
        
        new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: ['Failed','Pending','Successful'],
            datasets: [{
              data: [failed,pending,successful],
              backgroundColor: ['red','orange','green'],
              borderWidth: 1
            }]
          },
        });
      </script>
      
@endsection