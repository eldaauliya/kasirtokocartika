<div class="card card-cyan card-outline">
    <div class="card-body">
        <h3 class="m-0 text-right">Rp: <span id="totalJumlah">0</span> ,-</h3>
    </div>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<form action="{{ route('transaksi.store') }}" method="POST" class="card card-cyan card-outline">
    @csrf
    <div class="card-body">
        <p class="text-right">
            Tanggal: {{ $tanggal }}
        </p>
        <div class="row">
            <div class="col">
                <label>Nama Pelanggan</label>
                <select id="pelangganId" name="pelanggan_id" class="form-control @error('pelanggan_id') is-invalid @enderror">
                <option value="">Pilih Pelanggan</option>
                @foreach($pelanggans as $pelanggan)
                    <option value="{{ $pelanggan->id }}">{{ $pelanggan->nama }}</option>
                @endforeach
                </select>
                @error('pelanggan_id')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>
            <div class="col">
                <input type="hidden" id="namaPelanggan" name="nama_pelanggan">
            </div>
            <div class="col">
                <label>Nama Kasir</label>
                <input type="text" class="form-control" value="{{ $nama_kasir }}" disabled>
            </div>
        </div>
        <table class="table table-striped table-hover table-bordered st-3">
            <thead>
                <tr>
                    <th>Nama Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Sub Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="resultCart">
                <tr>
                    <td colspan="5" class="text-center"> Tidak ada data.</td>
                </tr>
            </tbody>
        </table>
        <div class="row at-3">
            <div class="col-2 offset-6">
                <p>Total</p>
                <p>Pajak 10% </p>
                <!-- Tambahkan view diskon di bawah pajak -->
                <p>Diskon</p>
                <p>Total Bayar</p>
            </div>
            <div class="col-4 text-right">
                <p id="subtotal">0</p>
                <p id="taxAmount">0</p>
                <!-- Tambahkan view diskon di bawah pajak -->
                <input type="text" name="diskon" class="form-control" placeholder="Jumlah Diskon" value="{{ old('diskon') }}">
                <p id="total">0</p>
            </div>
        </div>
        <div class="col-6 offset-6">
            <hr class="mt-0">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">Cash</span>
                </div>
                <input type="text" name="cash" class="form-control @error('cash') is-invalid @enderror" placeholder="Jumlah Cash" value="{{ old('cash') }}">
            </div>
            <input type="hidden" name="total_bayar" id="totalBayar" />
            @error('cash')
                <div class="invalid-feedback d-block">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div class="col-12 form-inline mt-3">
            <a href="{{ route('transaksi.index') }}" class="btn btn-secondary mr-2">Ke Transaksi</a>
            <a href="{{ route('cart.clear') }}" class="btn btn-danger">Kosongkan</a>
            <button type="submit" class="btn btn-success ml-auto">
                <i class="fas fa-money-bill-wave mr-2"></i> Bayar Transaksi
            </button>
        </div>
    </div>
</form>

@push('scripts')
<script>
    $(function() {
        fetchCart();
    });

    function fetchCart() {
        $.getJSON("/cart", function(response) {
            $('#resultCart').empty();

            const {
                items,
                subtotal,
                tax_amount,
                total,
                extra_info
            } = response;

            // Hitung diskon jika subtotal melebihi 45000
            let diskon = subtotal > 45000 ? 5000 : 0;

            // Hitung total bayar setelah diskon
            const totalBayarSetelahDiskon = total - diskon;

            $('#subtotal').html(rupiah(subtotal));
            $('#taxAmount').html(rupiah(tax_amount));
            $('#total').html(rupiah(totalBayarSetelahDiskon));
            $('#totalBayar').val(totalBayarSetelahDiskon);
            $('#totalJumlah').html(rupiah(totalBayarSetelahDiskon));

            for (const property in items) {
                addRow(items[property]);
            }

            if (Array.isArray(items) && items.length === 0) {
                $('#resultCart').html('<tr><td colspan="5" class="text-center">Tidak ada data.</td></tr>');
            }

            if (extra_info && extra_info.pelanggan) {
                const {
                    id,
                    nama
                } = extra_info.pelanggan;
                $('#namaPelanggan').val(nama);
                $('#pelangganId').val(id);
            }

        });
    }

    function addRow(item) {
        const {
            hash,
            price,
            title,
            quantity,
            total_price
        } = item;

        let btn = `<button type="button" class="btn btn-xs btn-success mr-2" onclick="ePut('${hash}',${quantity + 1})">
            <i class="fas fa-plus"></i>
            </button>`;

        btn += `<button type="button" class="btn btn-xs btn-primary mr-2" onclick="ePut('${hash}',${quantity - 1})">
            <i class="fas fa-minus"></i>
            </button>`;

        btn += `<button type="button" class="btn btn-xs btn-danger" onclick="eDel('${hash}')">
            <i class="fas fa-times"></i>
            </button>`;

        const row = `<tr>
            <td>${title}</td>
            <td><input type="number" class="form-control form-control-sm qty-input" data-hash="${hash}" value="${quantity}" min="1"></td>
            <td>${rupiah(price)}</td>
            <td>${rupiah(total_price)}</td>
            <td>${ btn }</td>
            </tr>`;

        $('#resultCart').append(row);

        $('.qty-input').on('input change', function() {
            const hash = $(this).data('hash');
            const newQty = $(this).val();
            // Kirim perubahan ke server menggunakan AJAX
            ePut(hash, newQty);
        });
    }

    function rupiah(number) {
        return new Intl.NumberFormat("id-ID").format(number);
    }

    function ePut(hash, qty) {
        $.ajax({
            type: "PUT",
            url: '/cart/' + hash,
            data: {
                qty: qty
            },
            dataType: "json",
            success: function(response) {
                fetchCart();
                // Perbarui nilai qty-input sesuai dengan nilai yang dikembalikan oleh server
                $('.qty-input[data-hash="' + hash + '"]').val(response.items[hash].quantity);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    }

    function eDel(hash) {
        $.ajax({
            type: "DELETE",
            url: '/cart/' + hash,
            dataType: "json",
            success: function(response) {
                fetchCart();
            }
        });
    }

    // Penanganan perubahan pada input nama pelanggan
    $('#pelangganId').change(function(event) {
        event.preventDefault();
        const pelangganId = $(this).val();
        $.ajax({
            type: "POST",
            url: '/transaksi/add-pelanggan',
            data: {
                id: pelangganId,
                _token: '{{ csrf_token() }}'
            },
            dataType: "json",
            success: function(response) {
                // Tambahkan logika jika diperlukan
                $('#namaPelanggan').val(response.nama);
            },
            error: function(xhr, status, error) {
                console.error(error);
            }
        });
    });
</script>
@endpush