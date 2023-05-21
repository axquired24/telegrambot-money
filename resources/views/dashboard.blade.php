@extends('layout.default')
@section('title', 'Dashboard')
@section('content')
    <div class="container mt-4" x-data="dashboard">
        <h3>Dashboard</h3>
        <div class="row gap-3 mt-4 mx-auto">
            <div class="col border border-danger rounded rounded-1 p-2">
                <small class="fw-bold">Total Pengeluaran</small>
                <div class="text-danger">{{ $expense }}</div>
            </div>
            <div class="col border border-success rounded rounded-1 p-2">
                <small class="fw-bold">Total Pemasukan</small>
                <div class="text-success">{{ $income }}</div>
            </div>
            <div class="col border border-primary rounded rounded-1 p-2">
                <small class="fw-bold">Sisa Saldo</small>
                <div class="text-primary">{{ $balance }}</div>
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-end" @click="syncToday">
            <button type="button" class="btn btn-primary">
                <span x-show="! isSyncing">Sync Hari Ini</span>
                <span x-show="isSyncing">Loading ...</span>
            </button>
        </div>
        <div class="mt-4 alert alert-danger" x-show="errMsg">
            <span x-text="errMsg"></span>
        </div>
        <form class="mt-1 row">
            <div class="col-4">
                <div class="input-group">
                    <input type="month" class="form-control"
                        name="bulan"
                        value="{{ $req_month }}"
                        lang="id-ID"
                        placeholder="Pilih Bulan" role="button" />
                    <button type="submit" class="btn btn-secondary">Pilih Bulan</button>
                </div>
            </div>
            <div class="col-4">

            </div>
        </form>
        <table class="table table-hover table-responsive mt-4">
            <caption>History Keuangan</caption>
            <thead>
                <th>Waktu</th>
                <th>Nominal</th>
                <th>Deskripsi</th>
                <th>Tipe</th>
            </thead>
            <tbody>
                @foreach($list as $item)
                    <tr>
                        <td>{{ $item->trx_date_format }}</td>
                        <td>
                            @if($item->is_expense)
                                <span class="text-danger">{{ $item->amount_format }}</span>
                            @else
                                <span class="text-success">{{ $item->amount_format }}</span>
                            @endif
                        </td>
                        <td>{{ $item->description }}</td>
                        <td>
                            @if($item->is_expense)
                                <span class="text-danger">Pengeluaran</span>
                            @else
                                <span class="text-success">Pengeluaran</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/axios@1.4.0/dist/axios.min.js" integrity="sha256-/UzhKodZQoGvzunHOkD+eswoK8yedk+7OvoUgalqCR4=" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
