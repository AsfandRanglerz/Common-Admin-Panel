@extends('admin.layout.app')
@section('title', 'FAQs')
@section('content')

    <div class="main-content" style="min-height: 562px;">
        <section class="section">
            <div class="section-body">
                <div class="row">
                    <div class="col-12 col-md-12 col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="col-12">
                                    <h4>FAQ's</h4>
                                </div>
                            </div>
                            <div class="card-body table-striped table-bordered table-responsive">
                                <div class="clearfix">
                                    <div class="create-btn">
                                        @if (Auth::guard('admin')->check() ||
                                                ($sideMenuPermissions->has('Faqs') && $sideMenuPermissions['Faqs']->contains('create')))
                                            <a class="btn btn-primary mb-3 text-white"
                                                href="{{ url('admin/faq-create') }}">Create</a>
                                        @endif
                                    </div>
                                </div>

                                <table class="table" id="table_id_events">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Sr.</th>
                                            <th>Question</th>
                                            <th>Description</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sortable-faqs">
                                        @foreach ($faqs as $farmer)
                                            <tr data-id="{{ $farmer->id }}">
                                                <td class="sort-handler" style="cursor: move; text-align: center;">
                                                    <i class="fas fa-th"></i>
                                                </td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $farmer->questions }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit(strip_tags($farmer->description), 150, '...') }}
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        @if (Auth::guard('admin')->check() ||
                                                                ($sideMenuPermissions->has('Faqs') && $sideMenuPermissions['Faqs']->contains('edit')))
                                                            <a href="{{ route('faq.edit', $farmer->id) }}"
                                                                class="btn btn-primary" style="margin-right: 10px">
                                                                <span><i class="fa fa-edit"></i></span>
                                                            </a>
                                                        @endif

                                                        @if (Auth::guard('admin')->check() ||
                                                                ($sideMenuPermissions->has('Faqs') && $sideMenuPermissions['Faqs']->contains('delete')))
                                                            <form id="delete-form-{{ $farmer->id }}"
                                                                action="{{ route('faq.destroy', $farmer->id) }}"
                                                                method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>

                                                            <button class="show_confirm btn d-flex gap-4"
                                                                data-form="delete-form-{{ $farmer->id }}" type="button"
                                                                style="background: #ff5608;">
                                                                <span><i class="fa fa-trash"></i></span>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection

@section('css')
    <!-- jQuery UI CSS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
@endsection

@section('js')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- jQuery UI -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#table_id_events').DataTable({
                paging: false,
                info: false,
                // Disable sorting for the first and last column
                columnDefs: [{
                    orderable: false,
                    targets: [0, -1]
                }]
            });

            // SweetAlert Delete Confirmation
            $('.show_confirm').on('click', function(e) {
                e.preventDefault();
                var formId = $(this).data('form');
                var form = $('#' + formId);

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                Swal.fire(
                                    'Deleted!',
                                    'Your FAQ has been deleted.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            },
                            error: function() {
                                Swal.fire(
                                    'Error!',
                                    'Failed to delete FAQ.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });

            // Toastr for reorder success
            @if (session('success'))
                toastr.success('{{ session('success') }}');
            @endif

            // Initialize sortable functionality
            $('#sortable-faqs').sortable({
                handle: '.sort-handler',
                axis: 'y',
                placeholder: "ui-state-highlight",
                update: function(event, ui) {
                    var order = [];
                    $('#sortable-faqs tr').each(function(index) {
                        order.push({
                            id: $(this).data('id'),
                            position: index + 1
                        });
                    });

                    $.ajax({
                        url: "{{ route('faq.reorder') }}",
                        method: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({
                            order: order,
                            _token: '{{ csrf_token() }}'
                        }),
                        success: function(response) {
                            toastr.success('Order updated successfully');
                            // Optional: Refresh the page to update serial numbers
                            // location.reload();
                        },
                        error: function(xhr) {
                            toastr.error('Error updating order');
                            console.error(xhr.responseText);
                        }
                    });
                }
            });
        });
    </script>
@endsection
