<!-- Cart Sidebar -->

<div class="offcanvas offcanvas-end" tabindex="-1" id="cartSidebar" style="width:400px;" aria-labelledby="cartSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">
            <i class="fas fa-shopping-cart"></i> My Cart
        </h5>

        <button type="button"
                class="btn-close"
                data-bs-dismiss="offcanvas">
        </button>
    </div>

    <div class="offcanvas-body">

        <div id="cart-items">

            @php
                $carts = \App\Models\Cart::where('user_id', auth()->id())
                            ->with('food')
                            ->get();
            @endphp

            @forelse($carts as $cart)

                <div class="card mb-3 border-0 shadow-sm">
                    <div class="card-body">

                        <div class="d-flex">

                            {{-- <img src="{{ asset('images/foods/'.$cart->food->image) }}"
                                 width="70"
                                 height="70"
                                 class="rounded me-3"
                                 style="object-fit:cover"> --}}

                            <div class="flex-grow-1">

                                <h6 class="mb-1">
                                    {{ $cart->food->name }}
                                </h6>

                                <small class="text-muted">
                                    ₦{{ number_format($cart->food->price,2) }}
                                </small>

                                <div class="mt-2">
                                    Qty:
                                    <strong>{{ $cart->quantity }}</strong>
                                </div>

                            </div>

                            <a url="{{ url('/cart/remove/'.$cart->id) }}" id="remove_item" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash"></i>
                            </a>

                        </div>

                    </div>
                </div>

            @empty

                <div class="text-center text-muted py-5">
                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                    <p>Your cart is empty.</p>
                </div>

            @endforelse

        </div>

    </div>

    <div class="offcanvas-footer p-3 border-top">

        <h5 class="mb-3" id="total_price">
            Total:
            ₦{{ number_format($carts->sum(fn($cart) => $cart->food->price * $cart->quantity),2) }}
        </h5>

        <form action="{{ route('checkout') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label>Delivery Address</label>
                <textarea
                    name="delivery_address"
                    class="form-control"
                    required></textarea>
            </div>

            <div class="mb-3">
                <label>Phone Number</label>
                <input
                    type="text"
                    name="phone"
                    class="form-control"
                    required>
            </div>

            <button type="submit" class="btn btn-success w-100">
                Proceed to Checkout
            </button>
        </form>

    </div>
</div>

<script>
    
</script>