<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Example Component</div>

                    <div id="chat-box" class="card-body bg-secondary">
                        <div 
                            class="media m-2 bg-light rounded"
                            v-for="(chat, index) in messages" :key="index"
                        >
                            <div class="media-body m-2">
                                <h5 class="mt-0">{{ chat.user.name }}</h5>
                                {{ chat.message }}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="form-group">
                            <input type="text"
                            class="form-control"
                            v-model="message"
                            @keyup.enter="sendMessage()"
                            @keydown="typingEvent()"
                            >
                        </div>
                        <p class="text-muted" v-if="userTyping">{{ userTyping.name }} typing...</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Users Online</div>
                    <div id="user-online" class="card-body bg-secondary">
                        <ul class="list-group">
                            <li 
                                class="list-group-item"
                                v-for="(user, index) in users" :key="index"
                            >{{ user.name }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                message: '',
                messages: [],
                users: [],
                userTyping: false,
                typingTimer: false
            }
        },

        props: {
            user: Object
        },

        methods: {
            fetchMessage() {
                axios.get('/fetch')
                .then((result) => {
                    this.messages = result.data
                    console.log(result.data);
                }).catch((err) => {
                    console.log(err);
                });

            },

            typingEvent() {
                Echo.join('chat')
                    .whisper('typing', this.user);
            },

            sendMessage() {
                this.messages.push({
                    user: this.user,
                    message: this.message
                })

                axios.post('/send', {
                    message: this.message
                })
                .then((result) => {
                    console.log(result);
                }).catch((err) => {
                    console.log(err);
                });

                this.message = '';
                // console.log(this.message);
            },
            
            scrollDown() {
                let container = document.getElementById('chat-box');
                let scrollHeight = container.scrollHeight;
                container.scrollTop = scrollHeight;
            }

        },

        mounted() {
            this.fetchMessage();

                Echo.join('chat')
                .here((users) => {
                    this.users = users;
                })
                .joining((user) => {
                    this.users.push(user);
                    console.log(user.name);
                })
                .leaving((user) => {
                    this.users = this.users.filter(u => u.id != user.id);
                    console.log(user.name);
                })
                .listen('ChatSent', (e) => {
                    this.messages.push(e.message);
                    console.log(e);
                })
                .listenForWhisper('typing', (user) => {
                    this.userTyping = user;

                    if (this.typingTimer) {
                        clearTimeout(this.typingTimer);
                    }

                    this.typingTimer =  setTimeout(() => {
                        this.userTyping = false;
                    }, 2000)
                })

            this.scrollDown();
        },

        updated() {
            this.scrollDown();
        }
    }
</script>

<style lang="css" scoped>
    #chat-box {
        overflow: auto;
        height: 300px;
    }
</style>