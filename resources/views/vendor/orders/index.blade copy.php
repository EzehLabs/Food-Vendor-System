@extends('layouts.app')

@section('content')

    <div class="container">

        <h2>Customer Orders</h2>

        

        @foreach($orders as $order)

            <div class="card collapsed-card mb-3">
                <div class="card-header">
                    <h5 class="card-title">
                        <strong> {{ $order->order_number }} </strong>
                        <div class="card-tools float-end">
                        <span class=""> {{ $order->user->name }} </span>
                        <button type="button" class=" btn btn-tool" data-card-widget="collapse"> <i class="fas fa-plus"></i> </button> 
                        </div>
                        
                    </h5>

                    

                </div>
                <!-- /.card-header -->

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

                    <form action="{{ route('vendor.order.status', $order->id) }}" method="POST">
                        @csrf

                        <select name="status" class="form-select mb-2">

                            <option value="paid"
                                {{ $order->status=='paid'?'selected':'' }}>
                                Paid
                            </option>

                            <option value="preparing"
                                {{ $order->status=='preparing'?'selected':'' }}>
                                Preparing
                            </option>

                            <option value="ready"
                                {{ $order->status=='ready'?'selected':'' }}>
                                Ready
                            </option>

                            <option value="completed"
                                {{ $order->status=='completed'?'selected':'' }}>
                                Completed
                            </option>

                            <option value="cancelled"
                                {{ $order->status=='cancelled'?'selected':'' }}>
                                Cancelled
                            </option>

                        </select>

                        <button class="btn btn-success btn-sm">
                            Update
                        </button>
                    </form>

                    <p>
                        <h5>Total: ₦{{ number_format($order->total_amount,2) }}</h5>
                        
                    </p>

                </div>
                <!-- ./card-body -->
                
            </div>
        @endforeach

    </div>

@endsection