<div class="container mt-4" x-data="dashboard">
    <h3 role="button" wire:click="goto('/v2')">Dashboard</h3>
    {{-- <div class="mt-4 d-flex justify-content-end gap-2">
        <button type="button"
            data-bs-toggle="modal" data-bs-target="#editModal"
            class="btn btn-success" @click="newTrx(0)">+ Pemasukan
        </button>
        <button type="button"
            data-bs-toggle="modal" data-bs-target="#editModal"
            class="btn btn-danger" @click="newTrx(1)">+ Pengeluaran
        </button>
    </div> --}}
    @if(! empty($errMsg))
        <div class="mt-4 alert alert-danger">
            {{ $errMsg }}
        </div>
    @endif
    <div>
        {{ $reqMonth }}
    </div>
    <div class="row gap-3 mt-4 mx-auto">
        <div class="col border border-danger rounded rounded-1 py-2 px-3">
            <small class="fw-bold">Pengeluaran Bulan Ini</small>
            <div class="fs-5 text-danger">{{ $expense }}</div>
        </div>
        <div class="col border border-success rounded rounded-1 py-2 px-3">
            <small class="fw-bold">Pemasukan Bulan Ini</small>
            <div class="fs-5 text-success">{{ $income }}</div>
        </div>
        <div class="col border border-primary rounded rounded-1 py-2 px-3">
            <small class="fw-bold">Saldo Bulan Ini</small>
            <div class="fs-5 text-primary">{{ $balance }}</div>
        </div>
        <div role="button"
            wire:click="goto('/invalidchat')"
            class="col border border-danger rounded rounded-1 py-2 px-3">
            <small class="fw-bold">Invalid Chat</small>
            <div class="fs-5 text-danger">{{ $failedParsed }} item</div>
        </div>
    </div>

    <form class="mt-4 row g-2 align-items-end" wire:submit.prevent="generateList">
        <div class="col-sm-12 col-xl-8">
            <div class="row gap-0">
                <div class="col-4">
                    <label>Bulan</label>
                    <input type="month" class="form-control"
                        name="bulan"
                        wire:model.lazy="reqMonth"
                        lang="id-ID"
                        placeholder="Pilih Bulan" role="button" />
                </div>
                <div class="col-4">
                    <label>Pengirim</label>
                    <select class="form-control" name="from" placeholder="Pengirim"
                        wire:model.lazy="fromID">
                        <option value="">Semua Pengirim</option>
                        @foreach ($froms as $fr)
                            <option value="{{ $fr->id }}">{{ $fr->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4">
                    <label>Grup</label>
                    <select class="form-control" name="chatroom" placeholder="Chatroom"
                        wire:model.lazy="chatRoomID">
                        <option value="">Semua Chatroom</option>
                        @foreach ($chatrooms as $cr)
                            <option value="{{ $cr->id }}">{{ $cr->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-12 col-xl-4 d-flex gap-2 justify-content-between">
            <div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel-fill"></i>
                    <span>Filter</span>
                </button>
            </div>
            <div>
                <button type="button" class="btn btn-secondary"
                    wire:loading.attr="disabled"
                    wire:target="sendReport"
                    wire:click="sendReport">
                    <i class="bi bi-chat-right-dots-fill"></i>
                    <span wire:loading>Loading ... </span>
                    <span wire:loading.remove>Kirim Laporan</span>
                </button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover table-striped table-responsive mt-4" style="min-width: 800px; width: 100%">
            <caption>History Keuangan</caption>
            <thead>
                <th>Waktu</th>
                <th>Nominal</th>
                <th>Deskripsi</th>
                <th>Tipe</th>
                <th>Sender</th>
                <th>Aksi</th>
            </thead>
            <tbody>
                @foreach($list as $trx)
                <tr>
                    <td>{{ $trx->amount_rp }}</td>
                    <td>{{ $trx->trx_date_str }}</td>
                    <td>{{ $trx->description }}</td>
                    <td class="{{ $trx->trx_color }}">{{ $trx->trx_str }}</td>
                    <td>{{ $trx->from->username ?? '-' }}</td>
                    <td style="width: 100px">
                        <button type="button" @click="setModalTrx(trx, 'edit')"
                            data-bs-toggle="modal" data-bs-target="#editModal"
                            class="btn btn-sm btn-primary" title="Ubah">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button type="button"  @click="setModalTrx(trx, 'delete')"
                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                            class="btn btn-sm btn-danger" title="Hapus">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
