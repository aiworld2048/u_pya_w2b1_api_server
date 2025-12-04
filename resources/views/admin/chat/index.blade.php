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
        <div class="container-fluid" id="chat-page" data-chat-base-url="{{ url('admin/chat') }}">
            <div class="row">
                <div class="col-12">
                    @if ($players->isEmpty())
                        <div class="card">
                            <div class="card-body text-center text-muted">
                                You don't have any players yet.
                            </div>
                        </div>
                    @else
                        <div class="player-chat-accordion">
                            @foreach ($players as $player)
                                <div class="card player-card mb-3" data-player-id="{{ $player->id }}" data-player-name="{{ $player->user_name }}">
                                    <div class="card-header d-flex justify-content-between align-items-center player-toggle" role="button" tabindex="0">
                                        <div>
                                            <div class="font-weight-bold">{{ $player->user_name }}</div>
                                            <small class="text-muted">{{ $player->name ?? $player->user_name }}</small>
                                        </div>
                                        <i class="fas fa-chevron-down toggle-icon"></i>
                                    </div>
                                    <div class="card-body player-chat-panel d-none">
                                        <div class="chat-messages border rounded bg-light mb-3 p-3" data-player-id="{{ $player->id }}">
                                            <p class="text-center text-muted mb-0">Press refresh to load messages.</p>
                                        </div>
                                        <form class="chat-form" data-player-id="{{ $player->id }}">
                                            <div class="form-group mb-2">
                                                <textarea class="form-control" rows="3" placeholder="Type your message..."></textarea>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">Messages are private between you and {{ $player->user_name }}.</small>
                                                <div>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm chat-refresh-btn" data-player-id="{{ $player->id }}">
                                                        <i class="fas fa-sync-alt"></i>
                                                    </button>
                                                    <button type="submit" class="btn btn-primary btn-sm chat-send-btn" data-player-id="{{ $player->id }}">
                                                        <i class="fas fa-paper-plane mr-1"></i>Send
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="mt-2 text-danger small chat-error d-none"></div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
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
            const chatPage = document.getElementById('chat-page');
            if (!chatPage) {
                return;
            }

            const baseUrl = chatPage.dataset.chatBaseUrl;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

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

            const renderMessageBubble = (message) => {
                const isAgent = message.sender_type === 'agent';
                const bubbleClass = isAgent ? 'chat-message chat-message-agent' : 'chat-message chat-message-player';
                const senderLabel = escapeHtml(message.sender?.user_name ?? (isAgent ? 'You' : 'Player'));

                return `
                    <div class="${bubbleClass}">
                        <div class="chat-meta">
                            <strong>${senderLabel}</strong>
                            <small>${formatDate(message.created_at)}</small>
                        </div>
                        <div class="chat-text">${formatMessage(message.message)}</div>
                    </div>
                `;
            };

            const renderMessages = (payload, container) => {
                const items = payload?.data ?? [];

                if (!items.length) {
                    container.innerHTML = '<p class="text-center text-muted my-3">No messages yet. Start the conversation!</p>';

                    return;
                }

                items.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

                container.innerHTML = items.map(renderMessageBubble).join('');
                container.scrollTop = container.scrollHeight;
            };

            const setError = (box, message = '') => {
                if (!box) {
                    return;
                }

                if (!message) {
                    box.classList.add('d-none');
                    box.textContent = '';

                    return;
                }

                box.classList.remove('d-none');
                box.textContent = message;
            };

            const loadMessages = (card, force = false) => {
                if (!card) {
                    return;
                }

                const playerId = card.dataset.playerId;
                const messagesContainer = card.querySelector('.chat-messages');
                const errorBox = card.querySelector('.chat-error');

                if (!playerId || !messagesContainer) {
                    return;
                }

                if (card.dataset.loading === 'true' && !force) {
                    return;
                }

                card.dataset.loading = 'true';
                setError(errorBox);
                messagesContainer.innerHTML = '<p class="text-center text-muted my-3">Loading messages...</p>';

                fetch(`${baseUrl}/${playerId}/messages?per_page=50`, {
                    headers: {
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Unable to load messages.');
                        }

                        return response.json();
                    })
                    .then((payload) => {
                        renderMessages(payload, messagesContainer);
                        card.dataset.loaded = 'true';
                    })
                    .catch((error) => {
                        setError(errorBox, error.message ?? 'Failed to load messages.');
                        messagesContainer.innerHTML = '<p class="text-center text-danger my-3">Failed to load messages.</p>';
                    })
                    .finally(() => {
                        card.dataset.loading = 'false';
                    });
            };

            const cards = Array.from(document.querySelectorAll('.player-card'));

            cards.forEach((card) => {
                const playerId = card.dataset.playerId;
                const panel = card.querySelector('.player-chat-panel');
                const toggle = card.querySelector('.player-toggle');
                const icon = card.querySelector('.toggle-icon');
                const form = card.querySelector('.chat-form');
                const textarea = form?.querySelector('textarea');
                const sendBtn = card.querySelector('.chat-send-btn');
                const refreshBtn = card.querySelector('.chat-refresh-btn');
                const messagesContainer = card.querySelector('.chat-messages');
                const errorBox = card.querySelector('.chat-error');

                if (!playerId || !panel || !toggle || !form || !textarea || !sendBtn || !refreshBtn || !messagesContainer) {
                    return;
                }

                const togglePanel = () => {
                    const isHidden = panel.classList.contains('d-none');

                    if (isHidden) {
                        panel.classList.remove('d-none');
                        icon.classList.replace('fa-chevron-down', 'fa-chevron-up');

                        if (card.dataset.loaded !== 'true') {
                            loadMessages(card);
                        }

                        textarea.focus();
                    } else {
                        panel.classList.add('d-none');
                        icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
                    }
                };

                toggle.addEventListener('click', togglePanel);
                toggle.addEventListener('keypress', (event) => {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        togglePanel();
                    }
                });

                refreshBtn.addEventListener('click', () => loadMessages(card, true));

                form.addEventListener('submit', (event) => {
                    event.preventDefault();

                    const message = textarea.value.trim();

                    if (!message) {
                        setError(errorBox, 'Message cannot be empty.');
                        return;
                    }

                    setError(errorBox);
                    sendBtn.disabled = true;
                    textarea.disabled = true;

                    fetch(`${baseUrl}/${playerId}/messages`, {
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
                                const errorMessage = data?.message ?? 'Failed to send message.';

                                throw new Error(errorMessage);
                            }

                            return response.json();
                        })
                        .then((payload) => {
                            textarea.value = '';
                            const bubble = renderMessageBubble(payload.data);
                            messagesContainer.insertAdjacentHTML('beforeend', bubble);
                            messagesContainer.scrollTop = messagesContainer.scrollHeight;
                            card.dataset.loaded = 'true';
                        })
                        .catch((error) => {
                            setError(errorBox, error.message ?? 'Failed to send message.');
                        })
                        .finally(() => {
                            sendBtn.disabled = false;
                            textarea.disabled = false;
                            textarea.focus();
                        });
                });
            });
        });
    </script>
@endpush

