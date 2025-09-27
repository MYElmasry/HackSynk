// Chat functionality for participant dashboard
class ChatManager {
    constructor() {
        this.currentUser = null;
        this.currentConversation = null;
        this.participants = [];
        this.conversations = [];
        this.messages = [];
        this.refreshInterval = null;
        this.messageRefreshInterval = null;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadUserData();
    }
    
    bindEvents() {
        // Refresh participants button
        document.getElementById('refresh-participants')?.addEventListener('click', () => {
            this.loadParticipants();
        });
        
        // Participant search
        document.getElementById('participant-search')?.addEventListener('input', (e) => {
            this.filterParticipants(e.target.value);
        });
        
        // Send message button
        document.getElementById('send-message-btn')?.addEventListener('click', () => {
            this.sendMessage();
        });
        
        // Message input enter key
        document.getElementById('message-input')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });
        
        // Mark read button
        document.getElementById('mark-read-btn')?.addEventListener('click', () => {
            this.markMessagesAsRead();
        });
    }
    
    loadUserData() {
        // Get current user data from the page
        const userDataElement = document.getElementById('user-data');
        if (userDataElement) {
            this.currentUser = JSON.parse(userDataElement.textContent);
        }
        
        if (this.currentUser) {
            this.loadParticipants();
            this.loadConversations();
            this.startRefreshIntervals();
        }
    }
    
    async loadParticipants() {
        try {
            const response = await fetch(`../api/chat.php?action=get_participants&current_user_id=${this.currentUser.id}`);
            const data = await response.json();
            
            if (data.participants) {
                this.participants = data.participants;
                this.renderParticipants();
            }
        } catch (error) {
            console.error('Error loading participants:', error);
            this.showError('Failed to load participants');
        }
    }
    
    async loadConversations() {
        try {
            const response = await fetch(`../api/chat.php?action=get_conversations&user_id=${this.currentUser.id}`);
            const data = await response.json();
            
            if (data.conversations) {
                this.conversations = data.conversations;
                this.updateParticipantsWithConversations();
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
        }
    }
    
    async loadMessages(conversationId) {
        try {
            const response = await fetch(`../api/chat.php?action=get_messages&conversation_id=${conversationId}&user_id=${this.currentUser.id}`);
            const data = await response.json();
            
            if (data.messages) {
                this.messages = data.messages;
                this.renderMessages();
                this.scrollToBottom();
            }
        } catch (error) {
            console.error('Error loading messages:', error);
            this.showError('Failed to load messages');
        }
    }
    
    async sendMessage() {
        const messageInput = document.getElementById('message-input');
        const message = messageInput.value.trim();
        
        if (!message || !this.currentConversation) {
            return;
        }
        
        try {
            const response = await fetch('../api/chat.php?action=send_message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    conversation_id: this.currentConversation.id || this.currentConversation.conversation_id,
                    sender_id: this.currentUser.id,
                    message: message,
                    message_type: 'text'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                messageInput.value = '';
                this.loadMessages(this.currentConversation.id || this.currentConversation.conversation_id);
                this.loadConversations(); // Refresh conversations to update last message
            } else {
                this.showError('Failed to send message');
            }
        } catch (error) {
            console.error('Error sending message:', error);
            this.showError('Failed to send message');
        }
    }
    
    async createConversation(participantId) {
        try {
            const response = await fetch('../api/chat.php?action=create_conversation', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    participant1_id: this.currentUser.id,
                    participant2_id: participantId
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                return data.conversation_id;
            } else {
                throw new Error(data.error || 'Failed to create conversation');
            }
        } catch (error) {
            console.error('Error creating conversation:', error);
            this.showError('Failed to start conversation');
            return null;
        }
    }
    
    async markMessagesAsRead() {
        if (!this.currentConversation) return;
        
        try {
            await fetch('../api/chat.php?action=mark_messages_read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    conversation_id: this.currentConversation.id || this.currentConversation.conversation_id,
                    user_id: this.currentUser.id
                })
            });
            
            this.loadConversations(); // Refresh to update unread counts
        } catch (error) {
            console.error('Error marking messages as read:', error);
        }
    }
    
    
    renderParticipants() {
        const participantsList = document.getElementById('participants-list');
        if (!participantsList) return;
        
        participantsList.innerHTML = '';
        
        this.participants.forEach(participant => {
            const participantElement = this.createParticipantElement(participant);
            participantsList.appendChild(participantElement);
        });
    }
    
    createParticipantElement(participant) {
        const div = document.createElement('div');
        div.className = 'participant-item';
        div.dataset.participantId = participant.id;
        
        const unreadCount = participant.unread_count || 0;
        
        div.innerHTML = `
            <div class="participant-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="participant-info">
                <div class="participant-name">${this.escapeHtml(participant.full_name)}</div>
                <div class="participant-username">@${this.escapeHtml(participant.username)}</div>
            </div>
            ${unreadCount > 0 ? `<div class="unread-badge">${unreadCount}</div>` : ''}
        `;
        
        div.addEventListener('click', () => {
            this.selectParticipant(participant);
        });
        
        return div;
    }
    
    selectParticipant(participant) {
        // Remove active class from all participants
        document.querySelectorAll('.participant-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Add active class to selected participant
        const participantElement = document.querySelector(`[data-participant-id="${participant.id}"]`);
        if (participantElement) {
            participantElement.classList.add('active');
        }
        
        // Find or create conversation
        let conversation = this.conversations.find(conv => 
            conv.other_participant_id === participant.id
        );
        if (!conversation) {
            // Create new conversation
            this.createConversation(participant.id).then(conversationId => {
                if (conversationId) {
                    conversation = {
                        id: conversationId,
                        other_participant_id: participant.id,
                        other_participant_name: participant.full_name,
                        other_participant_username: participant.username
                    };
                    this.currentConversation = conversation;
                    this.showChatInterface(participant);
                    this.loadMessages(conversationId);
                }
            });
        } else {
            this.currentConversation = conversation;
            this.showChatInterface(participant);
            this.loadMessages(conversation.id || conversation.conversation_id);
        }
    }
    
    showChatInterface(participant) {
        // Show chat header
        const chatHeader = document.getElementById('chat-header');
        const chatUserInfo = document.getElementById('chat-user-info');
        const chatUserName = document.getElementById('chat-user-name');
        const chatInputContainer = document.getElementById('chat-input-container');
        
        if (chatHeader) chatHeader.style.display = 'flex';
        if (chatUserName) chatUserName.textContent = participant.full_name;
        if (chatInputContainer) chatInputContainer.style.display = 'block';
        
        // Clear messages area
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            chatMessages.innerHTML = '';
        }
    }
    
    renderMessages() {
        const chatMessages = document.getElementById('chat-messages');
        if (!chatMessages) return;
        
        chatMessages.innerHTML = '';
        
        if (this.messages.length === 0) {
            chatMessages.innerHTML = `
                <div class="no-conversation">
                    <i class="fas fa-comments"></i>
                    <h3>No messages yet</h3>
                    <p>Start the conversation by sending a message</p>
                </div>
            `;
            return;
        }
        
        this.messages.forEach(message => {
            const messageElement = this.createMessageElement(message);
            chatMessages.appendChild(messageElement);
        });
    }
    
    createMessageElement(message) {
        const div = document.createElement('div');
        const isSent = parseInt(message.sender_id) === parseInt(this.currentUser.id);
        div.className = `message ${isSent ? 'sent' : ''}`;
        
        const time = new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        div.innerHTML = `
            <div class="message-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="message-content">
                <div class="message-text">${this.escapeHtml(message.message)}</div>
                <div class="message-time">${time}</div>
                ${isSent ? `<div class="message-status ${message.is_read ? 'read' : 'unread'}">
                    <i class="fas fa-check${message.is_read ? '-double' : ''}"></i>
                </div>` : ''}
            </div>
        `;
        
        return div;
    }
    
    updateParticipantsWithConversations() {
        // Update participants with conversation data
        this.participants.forEach(participant => {
            const conversation = this.conversations.find(conv => 
                conv.other_participant_id === participant.id
            );
            
            if (conversation) {
                participant.conversation_id = conversation.id;
                participant.unread_count = conversation.unread_count || 0;
                participant.last_message = conversation.last_message;
                participant.last_message_time = conversation.last_message_time;
            }
        });
        
        this.renderParticipants();
    }
    
    filterParticipants(searchTerm) {
        const participants = document.querySelectorAll('.participant-item');
        const term = searchTerm.toLowerCase();
        
        participants.forEach(participant => {
            const name = participant.querySelector('.participant-name').textContent.toLowerCase();
            const username = participant.querySelector('.participant-username').textContent.toLowerCase();
            
            if (name.includes(term) || username.includes(term)) {
                participant.style.display = 'flex';
            } else {
                participant.style.display = 'none';
            }
        });
    }
    
    
    scrollToBottom() {
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }
    
    startRefreshIntervals() {
        // Refresh messages every 5 seconds if in a conversation
        this.messageRefreshInterval = setInterval(() => {
            if (this.currentConversation) {
                this.loadMessages(this.currentConversation.id || this.currentConversation.conversation_id);
            }
        }, 5000);
    }
    
    stopRefreshIntervals() {
        if (this.messageRefreshInterval) {
            clearInterval(this.messageRefreshInterval);
        }
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    showError(message) {
        // You can implement a toast notification or alert here
        console.error(message);
        alert(message);
    }
    
    destroy() {
        this.stopRefreshIntervals();
    }
}

// Initialize chat when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize chat if we're on the participant dashboard
    if (document.getElementById('chat-messages-section')) {
        window.chatManager = new ChatManager();
        
    }
});
