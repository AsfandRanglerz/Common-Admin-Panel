@extends('admin.layout.app')

@section('title', 'AI Chat')

@section('content')
<div class="main-content">
    <!-- User Info Display -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="alert alert-info">
                <strong>Current User:</strong> 
                {{ Auth::check() ? Auth::user()->name : 'Not logged in' }} (ID: {{ Auth::id() }})
                
                @if(Auth::check())
                    <a href="{{ url('/logout') }}" class="btn btn-sm btn-warning float-end">Logout</a>
                @else
                    <a href="{{ url('/login/1') }}" class="btn btn-sm btn-success float-end">Login as User 1</a>
                    <a href="{{ url('/login/2') }}" class="btn btn-sm btn-primary float-end me-2">Login as User 2</a>
                @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="alert alert-secondary">
                <strong>Chatting with:</strong> {{ $receiver->name }} (ID: {{ $receiver->id }})
            </div>
        </div>
    </div>

    <h4 class="mb-3 text-center">AI Translation Chat 💬</h4>

    <div class="card shadow-sm">
        <div class="card-body" id="chat-box" style="height:400px; overflow-y:auto; background:#f8f9fa;">
            <!-- Messages load dynamically -->
            @if(!Auth::check())
                <div class="alert alert-warning text-center">
                    Please login first to send messages!
                </div>
            @endif
        </div>

        <div class="card-footer bg-white">
            <form id="chatForm">
                @csrf
                <div class="input-group">
                    <input type="hidden" id="receiver_id" value="{{ $receiver_id }}">
                    <input type="text" id="message" class="form-control" placeholder="Type your message..." 
                           autocomplete="off" {{ !Auth::check() ? 'disabled' : '' }}>
                    <button class="btn btn-primary" type="button" id="sendBtn" 
                            {{ !Auth::check() ? 'disabled' : '' }}>
                        Send
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Debug Info -->
    <div class="mt-3 p-3 bg-light rounded">
        <h6>Debug Information:</h6>
        <p>Current User ID: <span id="debug-user-id">{{ Auth::id() ?? 'Not logged in' }}</span></p>
        <p>Receiver ID: <span id="debug-receiver-id">{{ $receiver_id }}</span></p>
        <button onclick="loadMessages()" class="btn btn-sm btn-info">Reload Messages</button>
        <button onclick="testAjax()" class="btn btn-sm btn-warning">Test AJAX</button>
    </div>
</div>
@endsection

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function(){
    console.log('✅ Chat JS Loaded!');
    console.log('Current User ID:', {{ Auth::id() ?? 'null' }});

    const receiver_id = $('#receiver_id').val();
    let isSending = false;

    // Load messages function
    function loadMessages() {
        console.log('🔄 Loading messages...');
        
        $.get("{{ url('/chat') }}/" + receiver_id, function(res){
            console.log('📥 Messages loaded:', res);
            let html = '';
            
            if(res && res.length > 0) {
                res.forEach(msg => {
                    let align = msg.sender_id == {{ Auth::id() ?? 0 }} ? 'text-end' : 'text-start';
                    let bgColor = msg.sender_id == {{ Auth::id() ?? 0 }} ? 'primary' : 'secondary';
                    
                    html += `<div class="message-item ${align} mb-2">
                                <span class="badge bg-${bgColor} p-2">
                                    ${msg.message}
                                    <small class="d-block text-white-50">
                                        ${new Date(msg.created_at).toLocaleTimeString()}
                                    </small>
                                </span>
                            </div>`;
                });
            } else {
                html = '<p class="text-center text-muted">No messages yet. Start a conversation!</p>';
            }
            
            $('#chat-box').html(html);
            $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
        }).fail(function(xhr) {
            console.error('❌ Error loading messages:', xhr.responseText);
            $('#chat-box').html('<div class="alert alert-danger">Error loading messages</div>');
        });
    }

    // Test AJAX function
    window.testAjax = function() {
        console.log('🧪 Testing AJAX...');
        $.get("{{ url('/current-user') }}", function(res) {
            console.log('AJAX Test Response:', res);
            alert('AJAX Working! Current User: ' + JSON.stringify(res));
        });
    }

    // Initial load if user is logged in
    @if(Auth::check())
    loadMessages();
    setInterval(loadMessages, 3000);
    @endif

    // Send message function
    $('#sendBtn').on('click', function(){
        @if(!Auth::check())
            alert('Please login first!');
            return;
        @endif

        if (isSending) return;
        
        let text = $('#message').val().trim();
        if (!text) {
            alert('Please enter a message');
            return;
        }

        isSending = true;
        $('#sendBtn').html('Sending...').prop('disabled', true);

        const lang = 'ur'; // Default

        console.log('📤 Sending message:', { receiver_id, message: text });

        $.ajax({
            url: "{{ route('chat.send') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                receiver_id: receiver_id,
                message: text,
                language: lang
            },
            success: function(res) {
                console.log('✅ Message sent successfully:', res);
                $('#message').val('');
                loadMessages();
            },
            error: function(xhr) {
                console.error('❌ Send failed:', xhr.responseText);
                alert('Error: ' + xhr.responseText);
            },
            complete: function() {
                isSending = false;
                $('#sendBtn').html('Send').prop('disabled', false);
            }
        });
    });

    // Enter key support
    $('#message').on('keypress', function(e){
        if(e.which == 13) {
            e.preventDefault();
            $('#sendBtn').click();
        }
    });
});
</script>
@endsection