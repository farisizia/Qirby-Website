@extends('layouts.master')

@section('judul')
Schedule
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('template/plugins/datatables/jquery.dataTables.js') }}"></script>
    <script src="{{ asset('template/plugins/datatables-bs4/js/dataTables.bootstrap4.js') }}"></script>
    <script>
        $(function () {
            $("#example1").DataTable();
        });
    </script>

    <script>
        function confirmDelete(scheduleId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + scheduleId).submit();
                }
            })
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
@section('content')
<link rel="stylesheet" href="{{ asset('assets/css/schedule.css') }}" />
<!-- Alert -->
@if (session()->has('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<div class="card-body">
    <div class="table-responsive">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Name Property</th>
                    <th>Name User</th>
                    <th>Phone User</th>
                    <th>Date Schedule</th>
                    <th>Time Schedule</th>
                    <th>PIC</th>
                    <th>Note</th>
                    <th>Status Schedule</th>
                    <th>Action User</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($jadwal as $j)
                    <tr>
                        <td>{{ $j->properti->name }}</td>
                        <td>{{ $j->pengguna->name_user }}</td>
                        <td>{{ $j->pengguna->phone_user }}</td>
                        <td>{{ $j->tanggal }}</td>
                        <td>{{ $j->pukul }}</td>
                        <td>{{ $j->pic }}</td>
                        <td>{{ $j->catatan }}</td>
                        <td>{{ ucfirst($j->jadwal_diterima) }}</td>
                        <td>
                            <button type="button" class="btn btn-detail"
                                data-bs-target="#modal-lihat-detail-jadwal-{{ $loop->iteration }}"
                                data-bs-toggle="modal">Detail</button>
                            <button type="button" class="btn btn-danger"
                                onclick="confirmDelete('{{ $j->id_jadwal }}')">Delete</button>
                            <form id="delete-form-{{ $j->id_jadwal }}" method="post"
                                action="{{ route('schedule.destroy', $j->id_jadwal) }}" style="display: none;">
                                @method('delete')
                                @csrf
                            </form>
                        </td>
                    </tr>

                    <!-- Modal Detail Jadwal -->
                    <div class="modal fade" id="modal-lihat-detail-jadwal-{{ $loop->iteration }}" tabindex="-1"
                        role="dialog" aria-labelledby="modal-lihat-detail-jadwal-{{ $loop->iteration }}-label"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modal-lihat-detail-jadwal-{{ $loop->iteration }}-label">
                                        Schedule Details</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span
                                            aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Name:</strong> {{ $j->pengguna->name_user }}</p>
                                    <p><strong>Phone:</strong> {{ $j->pengguna->phone_user }}</p>
                                    <p><strong>Schedule Date:</strong> {{ $j->tanggal }}</p>
                                    <p><strong>Schedule Time:</strong> {{ $j->pukul }}</p>
                                    <p><strong>PIC:</strong> {{ $j->pic }}</p>
                                    <p><strong>Note:</strong> {{ $j->catatan }}</p>
                                    <p><strong>Status:</strong> {{ ucfirst($j->jadwal_diterima) }}</p>

                                    @if ($j->jadwal_diterima == 'accept')
                                        <!-- Form for 'Done' action -->
                                        <form action="{{ route('schedule.update', $j->id_jadwal) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="done">
                                            <input type="hidden" name="pic" value="{{ $j->pic }}">
                                            <input type="hidden" name="catatan" value="{{ $j->catatan }}">
                                            <button type="submit" class="btn btn-success">Done</button>
                                        </form>
                                    @elseif ($j->jadwal_diterima == 'pending')
                                        <!-- Form for 'Accept' and 'Reject' actions -->
                                        <form action="{{ route('schedule.update', $j->id_jadwal) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-3">
                                                <label for="edit-pic-{{ $loop->iteration }}">Edit PIC</label>
                                                <input class="form-control" id="edit-pic-{{ $loop->iteration }}" name="pic"
                                                    type="text" value="{{ $j->pic }}">
                                            </div>
                                            <div class="mb-3">
                                                <label for="edit-catatan-{{ $loop->iteration }}">Edit Note</label>
                                                <textarea class="form-control" id="edit-catatan-{{ $loop->iteration }}"
                                                    name="catatan" rows="3">{{ $j->catatan }}</textarea>
                                            </div>
                                            <input type="hidden" name="status" id="status-{{ $loop->iteration }}" value="">
                                            <button type="submit" class="btn btn-success"
                                                onclick="document.getElementById('status-{{ $loop->iteration }}').value='accept';">Accept</button>
                                            <button type="submit" class="btn btn-danger"
                                                onclick="document.getElementById('status-{{ $loop->iteration }}').value='reject';">Reject</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>

        <!-- Modal for Adding New Schedule -->
        <div class="modal fade" id="modal-tambah-jadwal" tabindex="-1" role="dialog"
            aria-labelledby="modal-tambah-jadwal-label" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modal-tambah-jadwal-label">Add New Schedule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('schedule.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="pemilih-pengguna">Select User</label>
                                <select class="form-control" id="pemilih-pengguna" name="pengguna">
                                    <option selected disabled>Choose user</option>
                                    @foreach ($pengguna as $p)
                                        <option value="{{ $p->id }}">{{ $p->name_user }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="pemilih-properti">Select Property</label>
                                <select class="form-control" id="pemilih-properti" name="properti">
                                    <option selected disabled>Choose property</option>
                                    @foreach ($properti as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="masukan-tanggal">Date</label>
                                <input class="form-control" id="masukan-tanggal" name="tanggal" type="date">
                            </div>
                            <div class="mb-3">
                                <label for="masukan-pukul">Time</label>
                                <input class="form-control" id="masukan-pukul" name="pukul" type="time">
                            </div>
                            <div class="mb-3">
                                <label for="masukan-pic">PIC</label>
                                <input class="form-control" id="masukan-pic" name="pic" type="text" required>
                            </div>
                            <div class="mb-3">
                                <label for="masukan-catatan">Note</label>
                                <input class="form-control" id="masukan-catatan" name="catatan" type="text" required>
                            </div>
                            <button class="btn btn-primary" type="submit">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endsection