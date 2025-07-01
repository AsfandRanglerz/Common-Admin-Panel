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

                                <table class="table responsive" id="table_id_events">
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

@section('js')

    <!-- DataTables -->
    <script>
        $(document).ready(function() {
            $('#table_id_events').DataTable({
                paging: false,
                info: false
            });

            // SweetAlert Delete
            $('.show_confirm').on('click', function(e) {
                e.preventDefault();
                var formId = $(this).data('form');
                var form = $('#' + formId);

                swal({
                    title: "Are you sure you want to delete this record?",
                    text: "If you delete this FAQ's record, it will be gone forever.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then(function(willDelete) {
                    if (willDelete) {
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: {
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                swal({
                                    title: "Success!",
                                    text: "Record deleted successfully",
                                    icon: "success",
                                    button: false,
                                    timer: 3000
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function() {
                                swal("Error!", "Failed to delete record.", "error");
                            }
                        });
                    }
                });
            });

            // Toastr for reorder success
            const message = localStorage.getItem('toastMessage');
            if (message) {
                toastr.success(message);
                localStorage.removeItem('toastMessage');
            }

            // jQuery-based sortable using jQuery UI
            $('#sortable-faqs').sortable({
                handle: '.sort-handler',
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
                        success: function() {
                            localStorage.setItem('toastMessage',
                                'Alignment has been updated successfully');
                            location.reload();
                        }
                    });
                }
            });
        });
    </script>

@endsection
