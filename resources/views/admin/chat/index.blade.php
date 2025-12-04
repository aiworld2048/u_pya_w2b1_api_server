@extends('layouts.master')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Player Chat</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Player Chat</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card" style="border-radius: 20px;">
                        <div class="card-header">
                            <h3 class="card-title">Your Players</h3>
                        </div>
                        <div class="card-body p-0">
                            @if ($players->isEmpty())
                                <div class="p-4 text-center text-muted">
                                    You don't have any players yet.
                                </div>
                            @else
                                <div class="list-group list-group-flush" id="chat-player-list">
                                    @foreach ($players as $player)
                                        <button type="button"
                                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center player-item"
                                            data-player-id="{{ $player->id }}"
                                            data-player-name="{{ $player->user_name }}">
                                            <div>
                                                <div class="font-weight-bold">{{ $player->user_name }}</div>
                                                <small class="text-muted">{{ $player->name }}</small>
                                            </div>
                                            <i class="fas fa-angle-right text-muted"></i>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card" id="chat-panel" data-chat-base-url="{{ url('admin/chat') }}">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0">
                                Conversation with <span id="chat-active-player" class="text-primary">—</span>
                            </h3>
                            <button class="btn btn-sm btn-outline-secondary" id="chat-refresh-btn" type="button">
                                <i class="fas fa-sync-alt mr-1"></i>Refresh
                            </button>
                        </div>
                        <div class="card-body chat-body" id="chat-messages">
                            <p class="text-center text-muted my-3">Select a player to load messages.</p>
                        </div>
                        <div class="card-footer">
                            <form id="chat-form">
                                <div class="form-group mb-2">
                                    <label for="chat-input" class="sr-only">Message</label>
                                    <textarea class="form-control" id="chat-input" rows="3" placeholder="Type your message..." disabled></textarea>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted" id="chat-form-hint">Messages are private between you and the player.</small>
                                    <button type="submit" class="btn btn-primary" id="chat-send-btn" disabled>
                                        <i class="fas fa-paper-plane mr-1"></i>Send
                                    </button>
                                </div>
                            </form>
                            <div class="mt-2 text-danger small" id="chat-error" style="display: none;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .chat-body {
            min-height: 400px;
            max-height: 600px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 1.25rem;
        }

        .chat-message {
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 12px;
            max-width: 80%;
            position: relative;
            word-break: break-word;
        }

        .chat-message-agent {
            background: #007bff;
            color: #fff;
            margin-left: auto;
        }

        .chat-message-player {
            background: #ffffff;
            border: 1px solid #dee2e6;
            margin-right: auto;
        }

        .chat-meta {
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
            display: flex;
            justify-content: space-between;
            opacity: 0.8;
        }

        .player-item.active {
            background-color: #f0f8ff;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const playerButtons = Array.from(document.querySelectorAll('.player-item'));
            const messagesContainer = document.getElementById('chat-messages');
            const form = document.getElementById('chat-form');
            const input = document.getElementById('chat-input');
            const sendBtn = document.getElementById('chat-send-btn');
            const refreshBtn = document.getElementById('chat-refresh-btn');
            const activePlayerLabel = document.getElementById('chat-active-player');
            const errorBox = document.getElementById('chat-error');
            const chatPanel = document.getElementById('chat-panel');
            const baseUrl = chatPanel.dataset.chatBaseUrl;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

            let activePlayerId = null;

            const escapeHtml = (value = '') => {
                value = `${value}`;

                return value.replace(/[&<>"']/g, (char) => ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                }[char]));
            };

            const formatMessage = (value) => {
                return escapeHtml(value).replace(/\n/g, '<br>');
            };

            const formatDate = (value) => {
                if (!value) {
                    return '';
                }

                const date = new Date(value);

                return date.toLocaleString();
            };

            const setError = (message = '') => {
                if (!message) {
                    errorBox.style.display = 'none';
                    errorBox.textContent = '';

                    return;
                }

                errorBox.style.display = 'block';
                errorBox.textContent = message;
            };

            const toggleComposer = (enabled) => {
                if (!input || !sendBtn) {
                    return;
                }

                input.disabled = !enabled;
                sendBtn.disabled = !enabled;

                if (!enabled) {
                    input.value = '';
                }
            };

            toggleComposer(false);

            const setLoadingState = () => {
                messagesContainer.innerHTML = '<p class="text-center text-muted my-3">Loading messages...</p>';
            };

            const renderMessages = (payload) => {
                const items = payload?.data ?? [];

                if (!items.length) {
                    messagesContainer.innerHTML = '<p class="text-center text-muted my-3">No messages yet. Start the conversation!</p>';

                    return;
                }

                    items.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

                messagesContainer.innerHTML = items.map((message) => {
                    const isAgent = message.sender_type === 'agent';
                    const bubblesClass = isAgent ? 'chat-message chat-message-agent' : 'chat-message chat-message-player';

                    return `
                        <div class="${bubblesClass}">
                            <div class="chat-meta">
                                <strong>${escapeHtml(message.sender?.user_name ?? (isAgent ? 'You' : 'Player'))}</strong>
                                <small>${formatDate(message.created_at)}</small>
                            </div>
                            <div class="chat-text">${formatMessage(message.message)}</div>
                        </div>
                    `;
                }).join('');

                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            };

            const fetchMessages = () => {
                if (!activePlayerId) {
                    return;
                }

                setError();
                setLoadingState();

                fetch(`${baseUrl}/${activePlayerId}/messages?per_page=50`, {
                    headers: {
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Unable to load messages');
                        }

                        return response.json();
                    })
                    .then(renderMessages)
                    .catch((error) => {
                        setError(error.message ?? 'Failed to load messages.');
                        messagesContainer.innerHTML = '<p class="text-center text-danger my-3">Failed to load messages.</p>';
                    });
            };

            const sendMessage = (event) => {
                event.preventDefault();

                if (!activePlayerId) {
                    setError('Please select a player first.');

                    return;
                }

                const message = input.value.trim();

                if (!message) {
                    setError('Message cannot be empty.');

                    return;
                }

                setError();
                sendBtn.disabled = true;

                fetch(`${baseUrl}/${activePlayerId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ message }),
                })
                    .then(async (response) => {
                        if (!response.ok) {
                            const data = await response.json().catch(() => ({}));
                            const message = data?.message ?? 'Failed to send message.';

                            throw new Error(message);
                        }

                        return response.json();
                    })
                    .then((payload) => {
                        input.value = '';

                        const existing = messagesContainer.innerHTML.includes('chat-message')
                            ? Array.from(messagesContainer.querySelectorAll('.chat-message')).length
                            : 0;

                        if (!existing) {
                            renderMessages({ data: [payload.data] });
                        } else {
                            const isAgent = payload.data.sender_type === 'agent';
                            const bubbleClass = isAgent ? 'chat-message chat-message-agent' : 'chat-message chat-message-player';
                            const html = `
                                <div class="${bubbleClass}">
                                    <div class="chat-meta">
                                        <strong>${escapeHtml(payload.data.sender?.user_name ?? 'You')}</strong>
                                        <small>${formatDate(payload.data.created_at)}</small>
                                    </div>
                                    <div class="chat-text">${formatMessage(payload.data.message)}</div>
                                </div>
                            `;

                            messagesContainer.insertAdjacentHTML('beforeend', html);
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                        }
                    })
                    .catch((error) => {
                        setError(error.message ?? 'Failed to send message.');
                    })
                    .finally(() => {
                        sendBtn.disabled = false;
                    });
            };

            const setActivePlayer = (button) => {
                playerButtons.forEach((btn) => btn.classList.remove('active'));
                button.classList.add('active');

                activePlayerId = button.dataset.playerId;
                activePlayerLabel.textContent = button.dataset.playerName;

                toggleComposer(true);
                input.focus();

                fetchMessages();
            };

            playerButtons.forEach((button) => {
                button.addEventListener('click', () => setActivePlayer(button));
            });

            refreshBtn.addEventListener('click', () => fetchMessages());
            form.addEventListener('submit', sendMessage);

            // Auto-select the first player on load
            if (playerButtons.length) {
                setActivePlayer(playerButtons[0]);
            } else {
                activePlayerLabel.textContent = '—';
                messagesContainer.innerHTML = '<p class="text-center text-muted my-3">No players available.</p>';
            }
        });
    </script>
@endpush

