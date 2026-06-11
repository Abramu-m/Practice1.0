@extends('layouts.app_main_layout')

@section('page_title')
    {{ 'My Signature' }}
@endsection

@section('main_content')
    <div class="row">
        <div class="col-md-8">

            @if (session('status') === 'signature-updated')
                <div class="alert alert-success">Signature saved successfully!</div>
            @elseif (session('status') === 'signature-removed')
                <div class="alert alert-success">Signature removed.</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">How to prepare your signature</h3>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Sign your name in dark ink (black or blue) on a plain white sheet of paper.</li>
                        <li>Take a photo straight-on, in good lighting, avoiding shadows and creases.</li>
                        <li>Crop the photo tightly around just the signature.</li>
                        <li>Save the image as a JPG or PNG file (max 2MB).</li>
                    </ol>
                    <p class="text-muted mt-2 mb-0">Alternatively, draw your signature directly below using your mouse, finger, or stylus.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Current Signature</h3>
                </div>
                <div class="card-body text-center">
                    @if($user->signature)
                        <img src="{{ asset('storage/' . $user->signature) }}" alt="Current Signature" class="img-thumbnail mb-3" style="max-height: 120px; background: #fff;">
                        <div>
                            <form method="POST" action="{{ route('profile.signature.destroy') }}" onsubmit="return confirm('Remove your signature?')">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash me-1"></i>Remove Signature
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-muted mb-0">No signature on file yet.</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="signature-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="draw-tab" data-bs-toggle="tab" data-bs-target="#draw-pane" type="button" role="tab">
                                Draw Signature
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-pane" type="button" role="tab">
                                Upload Photo
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="signature-tabs-content">

                        {{-- Draw tab --}}
                        <div class="tab-pane fade show active" id="draw-pane" role="tabpanel">
                            <form id="draw-signature-form" method="POST" action="{{ route('profile.signature.update') }}">
                                @csrf
                                <input type="hidden" name="signature_data" id="signature_data">
                                <div class="border rounded mb-3" style="background:#fff;">
                                    <canvas id="signature-pad" width="600" height="200" style="width:100%; max-width:600px; height:200px; touch-action:none;"></canvas>
                                </div>
                                <button type="button" id="clear-signature" class="btn btn-outline-secondary">
                                    <i class="bi bi-eraser me-1"></i>Clear
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check2 me-1"></i>Save Drawn Signature
                                </button>
                            </form>
                        </div>

                        {{-- Upload tab --}}
                        <div class="tab-pane fade" id="upload-pane" role="tabpanel">
                            <form method="POST" action="{{ route('profile.signature.update') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="signature_file" class="form-label">Signature photo</label>
                                    <input type="file" name="signature_file" id="signature_file" class="form-control" accept="image/*" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-upload me-1"></i>Upload Signature
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        $(document).ready(function () {
            const canvas = document.getElementById('signature-pad');
            const signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });

            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                signaturePad.clear();
            }
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            $('#clear-signature').on('click', function () {
                signaturePad.clear();
            });

            $('#draw-signature-form').on('submit', function (e) {
                if (signaturePad.isEmpty()) {
                    e.preventDefault();
                    alert('Please draw your signature first.');
                    return;
                }
                $('#signature_data').val(signaturePad.toDataURL('image/png'));
            });
        });
    </script>
@endsection
