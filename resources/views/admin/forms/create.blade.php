@extends('admin.layout.app')
@section('title', 'Create ' . $form->name)
@section('content')

<div class="main-content">
    <section class="section">
        <div class="section-body">
            <a class="btn btn-primary mb-3" href="{{ url('admin/forms-index') }}">Back</a>

            <!-- Step Progress Preview -->
            <ul id="form-steps-preview" class="mb-3">
                @foreach($fields as $step => $stepFields)
                    <li data-step="{{ $step }}">Step {{ $step }}</li>
                @endforeach
            </ul>

            <form id="dynamic_form" action="{{ route('admin.forms.store', $form->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @foreach($fields as $step => $stepFields)
                    <div class="form-step" data-step="{{ $step }}" @if($step != 1) style="display:none;" @endif>
                        @foreach($stepFields as $field)
                            @php $f = $field->field; @endphp
                            <div class="form-group">
                                <label>{{ $field->label }}</label>

                                @if($f->field_type == 'text' || $f->field_type == 'email' || $f->field_type == 'password')
                                    <input type="{{ $f->field_type }}" class="form-control" name="{{ $field->parameter }}" placeholder="{{ $field->placeholder }}" required>
                                @elseif($f->field_type == 'textarea')
                                    <textarea class="form-control" name="{{ $field->parameter }}" placeholder="{{ $field->placeholder }}" required></textarea>
                                @elseif($f->field_type == 'file')
                                    <input type="file" class="form-control" name="{{ $field->parameter }}">
                                @elseif($f->field_type == 'radio' || $f->field_type == 'select')
                                    @php $options = json_decode($f->options, true) ?? []; @endphp
                                    @if($f->field_type == 'radio')
                                        @foreach($options as $option)
                                            <div class="form-check">
                                                <input type="radio" name="{{ $field->parameter }}" value="{{ $option }}" class="form-check-input" required>
                                                <label class="form-check-label">{{ $option }}</label>
                                            </div>
                                        @endforeach
                                    @else
                                        <select name="{{ $field->parameter }}" class="form-control" required>
                                            <option value="">Select {{ $field->label }}</option>
                                            @foreach($options as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                @endif
                            </div>
                        @endforeach

                        <div class="form-navigation">
                            @if($step != 1)
                                <button type="button" class="btn btn-secondary prev-step">Previous</button>
                            @endif
                            @if($step != count($fields))
                                <button type="button" class="btn btn-primary next-step">Next</button>
                            @else
                                <button type="submit" class="btn btn-success">Submit</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </form>
        </div>
    </section>
</div>

@endsection

@section('js')
<script>
$(document).ready(function() {
    let currentStep = 1;
    const totalSteps = $('.form-step').length;

    function updateStepPreview() {
        $('#form-steps-preview li').removeClass('active');
        $(`#form-steps-preview li[data-step=${currentStep}]`).addClass('active');
    }

    updateStepPreview();

    $('.next-step').click(function() {
        $(`.form-step[data-step=${currentStep}]`).hide();
        currentStep++;
        $(`.form-step[data-step=${currentStep}]`).show();
        updateStepPreview();
    });

    $('.prev-step').click(function() {
        $(`.form-step[data-step=${currentStep}]`).hide();
        currentStep--;
        $(`.form-step[data-step=${currentStep}]`).show();
        updateStepPreview();
    });
});
</script>
<style>
#form-steps-preview li {
    display:inline-block;
    margin-right:15px;
    padding:5px 10px;
    border:1px solid #ccc;
    border-radius:5px;
}
#form-steps-preview li.active {
    background-color:#007bff;
    color:#fff;
}
</style>
@endsection
