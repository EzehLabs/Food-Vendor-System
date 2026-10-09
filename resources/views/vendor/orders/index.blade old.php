@extends('layouts.app')

@section('content')

    <div class="container">

        <h2>Customer Orders</h2>

        

        @foreach($orders as $order)

            <div class="card collapsed-card">
                <div class="card-header">
                    <h5 class="card-title">
                        <strong> {{ $order->order_number }} </strong>
                    </h5>

                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse"> <i class="fas fa-plus"></i> </button>
                    </div>
                    
                </div>
                <!-- /.card-header -->

                <div class="card-body">
                
                </div>
                <!-- ./card-body -->
                
            </div>

            <div class="card mb-3">

                <div class="card-header">

                    <strong> {{ $order->order_number }} </strong>

                    <span class="float-end"> {{ $order->user->name }} </span>

                </div>

                <div class="card-body">

                    @foreach($order->items as $item)

                        @if($item->food->vendor_id == auth()->user()->vendor->id)
                            <div class="d-flex justify-content-between">
                                <div> 
                                    <strong> {{ $item->food->name }} </strong> × {{ $item->quantity }} 
                                </div>

                                <div> 
                                    ₦{{ number_format($item->price * $item->quantity,2) }} 
                                </div>
                            </div>

                            <hr>

                        @endif

                    @endforeach

                    <p>
                        <h5>Total: ₦{{ number_format($order->total_amount,2) }}</h5>
                        
                    </p>

                </div>

            </div>

        @endforeach

    </div>

@endsection