<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { XMarkIcon } from '@heroicons/vue/20/solid';

const props = defineProps({
    externalMessages: {
        type: Array,
        default: () => []
    }
});

const messages = ref([]);
const flash = computed(() => usePage().props.flash);

const dismissMessage = (index) => {
    messages.value.splice(index, 1);
};

const addMessageWithTimeout = (newMessage) => {
    messages.value.push(newMessage);
    setTimeout(() => {
        dismissMessage(0);
    }, 3000);
};

watch(flash, (newValue) => {
    let delay = 0;
    if (newValue.message) {
        setTimeout(() => addMessageWithTimeout({
            message: newValue.message,
            bgColor: 'rgb(229 231 235)', // bg-gray-200
            textColor: 'rgb(17 24 39)' // text-gray-900
        }), delay);
        delay += 1000;
    }
    if (newValue.success) {
        setTimeout(() => addMessageWithTimeout({
            message: newValue.success,
            bgColor: 'rgb(134 239 172)', // bg-green-300
            textColor: 'rgb(17 24 39)' // text-gray-900
        }), delay);
        delay += 1000;
    }
    if (newValue.error) {
        setTimeout(() => addMessageWithTimeout({
            message: newValue.error,
            bgColor: 'rgb(252 165 165)', // bg-red-300
            textColor: 'rgb(17 24 39)' // text-gray-900
        }), delay);
        delay += 1000;
    }
});

onMounted(() => {
    // Process flash messages first
    ['message', 'success', 'error'].forEach((type) => {
        if (flash.value[type]) {
            addMessageWithTimeout({
                message: flash.value[type],
                bgColor: type === 'error' ? 'rgb(252 165 165)' : (type === 'success' ? 'rgb(134 239 172)' : 'rgb(229 231 235)'),
                textColor: 'rgb(17 24 39)'
            });
        }
    });

    // Then, handle external messages
    props.externalMessages.forEach((externalMessage, index) => {
        setTimeout(() => {
            addMessageWithTimeout(externalMessage);
        }, index * 1000); // Ensures a delay between consecutive external messages
    });
});
</script>

<template>
    <div aria-live="assertive" class="pointer-events-none fixed inset-0 flex items-end px-4 py-6 sm:items-start sm:p-6">
        <div class="flex w-full flex-col items-center space-y-4 sm:items-end">
            <transition-group
                name="notification"
                enter-active-class="transform ease-out duration-300 transition"
                enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                leave-active-class="transition ease-in duration-100"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-for="(message, index) in messages"
                    :key="index"
                    :style="{ backgroundColor: message.bgColor, color: message.textColor }"
                    class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5"
                >
                    <div class="p-4">
                        <div class="flex items-center">
                            <div class="flex w-0 flex-1 justify-between">
                                <p class="w-0 flex-1 text-sm font-medium">{{ message.message }}</p>
                            </div>
                            <div class="ml-4 flex flex-shrink-0">
                                <button
                                    type="button"
                                    @click="dismissMessage(index)"
                                    :style="{ backgroundColor: message.bgColor, color: message.textColor }"
                                    class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <span class="sr-only">Close</span>
                                    <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition-group>
        </div>
    </div>
</template>

