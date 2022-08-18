<template>
  <div class="app-container flex justify-center min-h-screen">
    <div
      id="chat-parent-container"
      class="container relative my-10 2xl:my-52 shadow-xl rounded-lg p-5 pr-0"
    >
      <div
        ref="chatContainerRef"
        class="chat-list overflow-auto"
      >
        <p
          v-if="!chatMessages.length"
          class="no-data"
        >
          It's empty here. Start asking the bot something...
        </p>
        <div
          v-else
          class="flex flex-col"
        >
          <p
            v-for="chatMessage in chatMessages"
            :key="chatMessage.date"
            class="chat-box break-all w-fit p-2"
            :class="[chatMessage.user, { 'self-end': chatMessage.user === 'sender' }]"
          >
            <span
              v-if="chatMessage.message === 'forbidden'"
              class="text-red-400 italic"
            >
              Message not sent. Message contains invalid characters
            </span>
            <span v-else>{{ chatMessage.message }}</span>
          </p>
          <p
            v-if="isLoadingInsertingConversation"
            class="text-gray-400 text-sm mt-2"
          >
            Inserting your conversation to database...
          </p>
          <button
            v-if="isFulfilled"
            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 mt-5 self-center rounded w-fit"
            @click="startOver"
          >
            Done. Click here to start over
          </button>
        </div>
      </div>

      <div
        ref="inputChatContainerRef"
        class="input-chat p-5"
      >
        <div class="input-field relative">
          <input
            ref="chatInputRef"
            v-model="senderInput"
            type="text"
            :disabled="disableInput"
            class="h-full w-full p-3 pl-4 pr-4"
            @keydown.enter="onSenderInputEntered"
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
    import { onMounted, ref, watch } from "vue";
    import axios from "axios";
    import { isUrl } from "./utils";

    const chatMessages = ref([])
    const senderInput = ref('')

    const chatContainerRef = ref(null)
    const inputChatContainerRef = ref(null)
    const chatInputRef = ref(null)

    const loadingInput = ref(false)
    const disableInput = ref(false)
    const isFulfilled = ref(false)
    const isLoadingInsertingConversation = ref(false)

    watch(chatMessages.value, (value) => {
        value.length && scrollToBottom()
    })

    watch(isFulfilled, (value) => {
        value && scrollToBottom()
    })

    onMounted(() => {
        startOver()

        calculateChatListContainerHeight()
        window.addEventListener('resize', calculateChatListContainerHeight)
    })

    const startOver = () => {
        chatMessages.value = []
        disableInput.value = false
        isFulfilled.value = false
        localStorage.removeItem('conversation')
        chatInputFocus()
    }

    const onSenderInputEntered = async () => {
        if (senderInput.value !== '') {

            const senderInputValue = senderInput.value
            loadingInput.value = true
            senderInput.value = ''

            if (runValidation(senderInputValue)) {
                loadingInput.value = false

                chatMessages.value.push({
                    user: 'sender',
                    message: 'forbidden',
                    date: new Date().getTime()
                })

                chatInputFocus()
                scrollToBottom()
                return
            }

            chatMessages.value.push({
                user: 'sender',
                message: senderInputValue,
                date: new Date().getTime()
            })

            storeMessageInLocalStorage(senderInputValue)

            chatInputFocus()

            try {
                const { data } = await axios.post('/api/conversation/send-message', {
                    message: senderInputValue
                })

                loadingInput.value = false

                if (data.status === 'Fulfilled') {
                    disableInput.value = true

                    chatMessages.value.push({
                        user: 'recipient-bot',
                        message: data.message,
                        date: new Date().getTime()
                    })
                    storeMessageInLocalStorage(data.message)

                    isLoadingInsertingConversation.value = true

                    const response = await axios.post('/api/conversation', {
                        messages: localStorage
                            .getItem('conversation')
                            .split('|')
                            .filter(item => item !== '')
                    })

                    isLoadingInsertingConversation.value = false
                    isFulfilled.value = true
                    localStorage.removeItem('conversation')
                } else {
                    chatMessages.value.push({
                        user: 'recipient-bot',
                        message: data.message,
                        date: new Date().getTime()
                    })

                    if (data.status !== 'Failed') {
                        storeMessageInLocalStorage(data.message)
                    }
                }

                chatInputFocus()
            } catch (e) {
                loadingInput.value = false

                if (e.response.status === 422) {
                    alert('Failed to send the message. The message contains invalid characters')
                    return
                }

                alert('Server error')
            }
        }
    }

    const storeMessageInLocalStorage = (message) => {
        const getLocalStorageData = localStorage.getItem('conversation')

        if (getLocalStorageData) {
            localStorage.setItem('conversation', `${getLocalStorageData}${message}|`)
            return
        }

        localStorage.setItem('conversation', `${message}|`)
    }

    const runValidation = (senderInput) => {
        // contains link
        if (isUrl(senderInput)) {
            return true
        }

        // contains html
        if (/<[a-z/][\s\S]*>/i.test(senderInput)) {
            return true
        }

        // contains spaces
        return !(/^\S*$/u.test(senderInput));
    }

    const calculateChatListContainerHeight = () => {
        const getContainerHeightString = window
            .getComputedStyle(
                document.getElementById('chat-parent-container'),
                null
            )
            .getPropertyValue('height')

        const getContainerHeight = Number(
            getContainerHeightString.substring(0, getContainerHeightString.length - 2)
        )
        const inputChatContainerHeight = inputChatContainerRef.value.clientHeight

        chatContainerRef.value.style.height = getContainerHeight - inputChatContainerHeight - 35 + 'px'
    }

    const chatInputFocus = () => {
        setTimeout(() => {
            chatInputRef.value.focus()
        },10)
    }

    const scrollToBottom = () => {
        setTimeout(() => {
            chatContainerRef.value.scrollTop = chatContainerRef.value.scrollHeight + 2000
        },10)
    }
</script>

<style lang="scss">
.app-container {
    .container {
        width: 500px;
        border: 1px solid rgb(203 213 225);

        .chat-list {
            p.no-data {
                opacity: .3;
            }

            .chat-box {
                &.sender {
                    background-color: #DFF0FD;
                    margin-top: 15px;
                    margin-right: 20px;
                    margin-left: 160px;
                    color: black;
                    border-top-right-radius: 1rem;
                    border-bottom-left-radius: 1rem;
                }

                &.recipient-bot {
                    background-color: #F4F6F9;
                    color: black;
                    margin-top: 15px;
                    margin-right: 160px;
                    border-top-left-radius: 1rem;
                    border-bottom-right-radius: 1rem;
                }
            }
        }

        .input-chat {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid rgb(203 213 225);

            .input-field {
                border: 1px solid rgb(203 213 225);
                border-radius: 25px;

                input {
                    transition: all .25s;
                    border-radius: inherit;

                    &:focus {
                        outline: none;
                    }

                    &:disabled {
                        background: rgba(0, 0, 0, 0.2);
                    }
                }

                .circle-spinner {
                    position: absolute;
                    top: 7px;
                    right: 15px;
                }
            }
        }
    }
}
</style>
