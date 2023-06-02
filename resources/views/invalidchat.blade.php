@extends('layout.default')
@section('title', 'Invalid Chats')
@section('content')
    <div class="container mt-4" x-data="dashboard">
        <h3 role="button" @click="goto('/')">< Invalid Chats</h3>
        <template x-if="errMsg">
            <div class="mt-4 alert alert-danger">
                <span x-text="errMsg"></span>
            </div>
        </template>

        <table class="table table-hover table-responsive mt-4">
            <caption>Invalid Chats</caption>
            <thead>
                <th>Update ID</th>
                <th>Pesan</th>
                <th>Last Proceeed</th>
            </thead>
            <tbody>
                <template x-for="chat in chats">
                    <tr>
                        <td x-text="chat.update_id"></td>
                        <td>
                            <a href="#" @click="setChatDetail(chat)"
                                data-bs-toggle="modal" data-bs-target="#chatDetail">
                                Click to Check
                            </a>
                        </td>
                        <td x-text="chat.parsed_at"></td>
                    </tr>
                </template>
            </tbody>
        </table>

        <!-- Detail Modal -->
        <div class="modal modal-xl fade" id="chatDetail" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Chat Details</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form x-ref="updateform" method="post" action="{{ url('/invalidchat/solved') }}">
                        @csrf
                        <input type="hidden" name="update_id" :value="chatDetail?.update_id">
                        <input type="hidden" name="error_solved" value="1">
                    </form>

                    <pre class="h-32" x-text="beautifyJson(chatDetail?.message)"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" @click="$refs.updateform.submit()">Tandai Selesai</button>
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
        const chats = {!! $chats !!}
    </script>
    <script src="{{ asset('js/chat.js') }}"></script>
@endpush
