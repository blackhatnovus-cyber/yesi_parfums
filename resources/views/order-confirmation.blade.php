@extends('layouts.app')

@section('title', $order->order_number.' — Issey Parfums')

@section('content')
    <section class="order-confirmation-page">
        <nav class="breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('account.orders') }}">Order history</a><span>/</span><span>Order {{ $order->order_number }}</span></nav>
        @if (session('status'))<div class="notice success" role="status">{{ session('status') }}</div>@endif
        <p class="eyebrow">{{ session('status') ? 'Order recorded' : 'Order details' }} · {{ ucfirst($order->status) }}</p>
        @if (session('status'))
            <h1>Thank you,<br><em>{{ $order->shipping_name }}.</em></h1>
        @else
            <h1>Order<br><em>details.</em></h1>
        @endif
        <p>Your order <strong>{{ $order->order_number }}</strong> has been recorded for the atelier. No payment was processed.</p>

        <div class="confirmation-ledger">
            @foreach ($order->items as $item)
                <article>
                    <img src="{{ $item->product_image ?: '/images/signature.jpg' }}" alt="">
                    <div><strong>{{ $item->product_name }}</strong><small>{{ $item->quantity }} × ₱{{ number_format((float) $item->unit_price, 2) }}</small></div>
                    <span>₱{{ number_format((float) $item->subtotal, 2) }}</span>
                </article>
            @endforeach
            <p><span>Total</span><strong>₱{{ number_format((float) $order->total, 2) }}</strong></p>
        </div>

        <dl class="confirmation-shipping">
            <div><dt>Email</dt><dd>{{ $order->shipping_email }}</dd></div>
            <div><dt>Ship to</dt><dd>{{ $order->shipping_address }}</dd></div>
        </dl>
        <div class="account-order-detail-actions">
            <a class="button-solid" href="{{ route('shop') }}">Continue Shopping</a>
            <a class="button-outline" href="{{ route('account.orders') }}">Back to Order History</a>
        </div>
    </section>
@endsection
