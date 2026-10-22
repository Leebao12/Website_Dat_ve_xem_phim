
@extends('layouts.site')

@section('title', 'Đơn ' . $booking->booking_code . ' | CINEMAX')

@section('content')

<style>
    /* Badge trạng thái đơn đặt vé */
    .booking-status-pill {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        align-self: flex-start;

        width: fit-content !important;
        min-width: 0 !important;
        height: auto !important;
        min-height: 0 !important;
        max-width: 100%;

        padding: 8px 14px !important;
        border-radius: 999px !important;

        font-size: 14px;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .booking-status-pill.status-da_thanh_toan {
        background: rgba(16, 185, 129, 0.14) !important;
        color: #6ee7b7 !important;
        border-color: rgba(16, 185, 129, 0.28);
    }

    .booking-status-pill.status-cho_thanh_toan {
        background: rgba(245, 158, 11, 0.14) !important;
        color: #fcd34d !important;
        border-color: rgba(245, 158, 11, 0.28);
    }

    .booking-status-pill.status-da_huy {
        background: rgba(239, 68, 68, 0.14) !important;
        color: #fca5a5 !important;
        border-color: rgba(239, 68, 68, 0.28);
    }

    .booking-code-value {
        overflow-wrap: anywhere;
        margin-bottom: 0;
    }
</style>

<div class="container section-block">

    <a href="{{ auth()->user()->role === 'quan_ly' ? route('manager.orders.index') : route('account.orders') }}"
       class="text-orange">
        ← Quay lại danh sách
    </a>

    <span class="overline d-block mt-3">BOOKING DETAILS</span>

    <h1>Chi tiết đơn đặt vé</h1>

    <div class="row g-4 mt-2">

        <!-- CỘT TRÁI: CHI TIẾT ĐƠN VÉ -->
        <div class="col-lg-8">

            <div class="dark-card">

                <!-- MÃ ĐẶT VÉ VÀ TRẠNG THÁI -->
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                    <div>
                        <span class="overline">MÃ ĐẶT VÉ</span>

                        <h2 class="text-orange booking-code-value">
                            {{ $booking->booking_code }}
                        </h2>
                    </div>

                    <span class="status status-{{ $booking->status }} booking-status-pill">
                        {{ [
                            'cho_thanh_toan' => 'Chờ thanh toán',
                            'da_thanh_toan' => 'Đã thanh toán',
                            'da_huy' => 'Đã hủy',
                        ][$booking->status] ?? 'Không xác định' }}
                    </span>

                </div>

                <hr>

                <!-- THÔNG TIN PHIM -->
                <h3>
                    {{ $booking->showtime->movie->title }}
                </h3>

                <div class="details-list">

                    <div>
                        <span>Phòng</span>
                        <b>{{ $booking->showtime->room->name }}</b>
                    </div>

                    <div>
                        <span>Ngày chiếu</span>
                        <b>
                            {{ $booking->showtime->show_date->format('d/m/Y') }}
                        </b>
                    </div>

                    <div>
                        <span>Giờ chiếu</span>
                        <b>
                            {{ $booking->showtime->starts_at->format('H:i') }}
                        </b>
                    </div>

                    <div>
                        <span>Ghế đã chọn</span>
                        <b>
                            {{ $booking->bookingSeats->pluck('seat.seat_code')->join(', ') }}
                        </b>
                    </div>

                    <div>
                        <span>Khách hàng</span>
                        <b>{{ $booking->user->name }}</b>
                    </div>

                </div>

                <hr>

                <!-- CHI TIẾT THANH TOÁN -->
                <h4>Chi tiết thanh toán</h4>

                @foreach ($booking->bookingSeats as $line)

                    <div class="order-line">
                        <span>
                            Ghế {{ $line->seat->seat_code }}
                        </span>

                        <strong>
                            {{ number_format($line->price, 0, ',', '.') }}đ
                        </strong>
                    </div>

                @endforeach

                @foreach ($booking->bookingProducts as $line)

                    <div class="order-line">
                        <span>
                            {{ $line->product->name }}
                            × {{ $line->quantity }}
                        </span>

                        <strong>
                            {{ number_format($line->subtotal, 0, ',', '.') }}đ
                        </strong>
                    </div>

                @endforeach

                <!-- TỔNG TIỀN -->
                <div class="order-total">
                    <span>Tổng thanh toán</span>

                    <strong>
                        {{ number_format($booking->total_amount, 0, ',', '.') }}đ
                    </strong>
                </div>

            </div>
        </div>

        <!-- CỘT PHẢI: TRẠNG THÁI GIAO DỊCH -->
        <div class="col-lg-4">

            <div class="dark-card">

                <h3>Trạng thái giao dịch</h3>

                @if ($booking->status === 'da_thanh_toan')

                    <p class="text-success">
                        ✓ Vé đã được thanh toán thành công.
                    </p>

                    <p class="quiet-text">
                        Xuất trình mã đặt vé khi đến rạp.
                        Bản đồ án chưa hỗ trợ quét cổng vé.
                    </p>

                @elseif ($booking->status === 'da_huy' || $booking->expired)

                    <p class="text-danger">
                        Đơn đã hủy hoặc hết hạn giữ ghế.
                    </p>

                    <a class="btn btn-hot"
                       href="{{ route('movies.show', $booking->showtime->movie) }}">
                        Đặt lại
                    </a>

                @else

                    <p>
                        Đơn đang được giữ ghế trong
                        {{ config('cinemax.hold_minutes') }}
                        phút kể từ khi tạo.
                    </p>

                    <p class="quiet-text">
                        Chế độ sandbox mô phỏng kết quả thanh toán
                        để phục vụ trình diễn.
                        Không chuyển tiền thật.
                    </p>

                    @if (auth()->id() === $booking->user_id)

                        <form method="post"
                              action="{{ route('booking.pay', $booking) }}">

                            @csrf

                            <button class="btn btn-hot w-100 mb-3"
                                    type="submit">
                                ✓ Thanh toán sandbox
                            </button>

                        </form>

                        <form method="post"
                              action="{{ route('booking.cancel', $booking) }}"
                              onsubmit="return confirm('Hủy đơn chưa thanh toán này?')">

                            @csrf

                            <button class="btn btn-outline-light w-100"
                                    type="submit">
                                Hủy đơn
                            </button>

                        </form>

                    @elseif (auth()->user()->role === 'quan_ly')

                        <form method="post"
                              action="{{ route('booking.cancel', $booking) }}">

                            @csrf

                            <button class="btn btn-outline-light w-100"
                                    type="submit">
                                Hủy đơn chưa thanh toán
                            </button>

                        </form>

                    @endif

                @endif

            </div>

            <!-- LỊCH SỬ GIAO DỊCH -->
            <div class="dark-card mt-4">

                <h4>Giao dịch</h4>

                @foreach ($booking->payments as $p)

                    <div class="order-line">
                        <span>{{ $p->payment_method }}</span>
                        <span>{{ $p->status }}</span>
                    </div>

                    @if ($p->transaction_code)

                        <p class="quiet-text">
                            {{ $p->transaction_code }}
                        </p>

                    @endif

                @endforeach

            </div>

        </div>

    </div>
</div>

@endsection
