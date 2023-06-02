@extends('layout.default')
@section('title', 'Dashboard')
@section('content')
    <div class="container mt-4" x-data="dashboard">
        <h3 role="button" @click="goto('/')">Dashboard</h3>
        <div class="mt-4 d-flex justify-content-end gap-2" @click="syncToday">
            <button type="button" class="btn btn-primary">
                <i class="bi bi-cloud-haze2"></i>
                <span x-show="! isSyncing">Perbarui dari Telegram</span>
                <span x-show="isSyncing">Loading ...</span>
            </button>
            <button type="button"
                data-bs-toggle="modal" data-bs-target="#editModal"
                class="btn btn-success" @click="newTrx(0)">+ Pemasukan
            </button>
            <button type="button"
                data-bs-toggle="modal" data-bs-target="#editModal"
                class="btn btn-danger" @click="newTrx(1)">+ Pengeluaran
            </button>
        </div>
        <template x-if="errMsg">
            <div class="mt-4 alert alert-danger">
                <span x-text="errMsg"></span>
            </div>
        </template>
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
                @click="goto('/invalidchat')"
                class="col border border-danger rounded rounded-1 py-2 px-3">
                <small class="fw-bold">Invalid Chat</small>
                <div class="fs-5 text-danger">{{ $failed_parsed }} item</div>
            </div>
        </div>

        <form class="mt-4 row">
            <div class="col-8">
                <div class="d-flex gap-2">
                    <input type="month" class="form-control"
                        name="bulan"
                        value="{{ $req_month }}"
                        lang="id-ID"
                        placeholder="Pilih Bulan" role="button" />
                    <select x-ref="fromFilter" class="form-control" name="from" placeholder="Pengirim">
                        <option value="">Semua Pengirim</option>
                        @foreach ($froms as $fr)
                            <option value="{{ $fr->id }}">{{ $fr->username }}</option>
                        @endforeach
                    </select>
                    <select x-ref="chatroomFilter" class="form-control" name="chatroom" placeholder="Chatroom">
                        <option value="">Semua Chatroom</option>
                        @foreach ($chatrooms as $cr)
                            <option value="{{ $cr->id }}">{{ $cr->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-4 d-flex gap-2 justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel-fill"></i>
                    <span>Filter</span>
                </button>
                <button type="button" class="btn btn-secondary" @click="sendReport">
                    <i class="bi bi-chat-right-dots-fill"></i>
                    <span x-show="! isSendingReport">Kirim Laporan</span>
                    <span x-show="isSendingReport">Loading ...</span>
                </button>
            </div>
        </form>
        <table class="table table-hover table-responsive mt-4">
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
                <template x-for="trx in transactions">
                    <tr>
                        <td x-text="trx.trx_date_format"></td>
                        <td x-text="trx.amount_format" :class="trx.is_expense ? 'text-danger' : 'text-success'"></td>
                        <td x-text="trx.description"></td>
                        <td x-text="trx.is_expense ? 'Pengeluaran' : 'Pemasukan'"
                            :class="trx.is_expense ? 'text-danger' : 'text-success'"
                        ></td>
                        <td x-text="trx.from.username"></td>
                        <td>
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
                </template>
            </tbody>
        </table>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Hapus Item?</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form x-ref="deleteform" method="post" action="{{ url('/delete') }}">
                        @csrf
                        <input type="hidden" name="id" :value="modalTrx?.id">
                    </form>
                    <div>
                        Data ini akan dihapus, apakah anda yakin?
                        <div>Waktu: <span x-text="modalTrx?.trx_date_format"></span></div>
                        <div>Nominal: <span x-text="modalTrx?.amount_format"
                            :class="getTextColor(modalTrx)"></span></div>
                        <div>Tipe: <span x-text="getTextKind(modalTrx)"
                            :class="getTextColor(modalTrx)"></span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-danger" @click="$refs.deleteform.submit()">Ya, Hapus</button>
                </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel"
                        x-text="modalKind == 'add' ? 'Tambah Baru' : 'Edit Item'">Edit Item</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form x-ref="editform" method="post" :action="modalKind == 'add' ? '/add' : '/edit'">
                        @csrf
                        <input type="hidden" name="id" :value="modalTrx?.id">
                        <input type="hidden" name="is_expense" :value="modalTrx?.is_expense ? 1 : 0">
                        <div class="row mb-3 align-items-center">
                            <label class="col-sm-2 col-form-label">Tipe</label>
                            <div class="col-sm-10">
                                <span x-text="getTextKind(modalTrx)" :class="getTextColor(modalTrx)"></span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Waktu</label>
                            <div class="col-sm-10">
                                <input role="button" name="trx_date"
                                    type="date" class="form-control" :value="modalTrx?.trx_date" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Nominal</label>
                            <div class="col-sm-10">
                                <input type="number" name="amount" min="1"
                                    class="form-control" :value="modalTrx?.amount" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label">Deskripsi</label>
                            <div class="col-sm-10">
                                <input type="text" name="description"
                                    class="form-control" :value="modalTrx?.description" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary"
                        {{-- @click="$refs.editform.submit()" --}}
                        @click="submitForm($refs.editform)"
                        >Simpan</button>
                </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/axios@1.4.0/dist/axios.min.js" integrity="sha256-/UzhKodZQoGvzunHOkD+eswoK8yedk+7OvoUgalqCR4=" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        const transactions = {!! $list_json !!}
        const yearMonth = {!! json_encode(request()->bulan) !!}
        const fromParam = {!! json_encode(request()->from) !!}
        const chatroomParam = {!! json_encode(request()->chatroom) !!}
    </script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
