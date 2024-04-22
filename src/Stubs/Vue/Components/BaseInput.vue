<template>
    <div>
        <!-- Text, Textarea, Date Inputs -->
        <div v-if="['text', 'textarea', 'date'].includes(type)">
            <label class="block text-sm font-medium leading-6 text-gray-900">{{ label }}</label>
            <input
                v-if="type !== 'textarea'"
                :name="name"
                :type="type"
                :value="modelValue"
                @input="updateValue($event.target.value)"
                :placeholder="placeholder ?? ''"
                :disabled="disabled"
                :readonly="readonly"
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                :class="{'ring-red-500': errors && errors[name], 'ring-gray-300': !errors || !errors[name]}"
            />
            <textarea
                v-else
                :name="name"
                :value="modelValue"
                @input="updateValue($event.target.value)"
                :placeholder="placeholder ?? ''"
                :disabled="disabled"
                :readonly="readonly"
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                :class="{'ring-red-500': errors && errors[name], 'ring-gray-300': !errors || !errors[name]}"
            ></textarea>
            <p v-if="errors && errors[name]" class="text-sm text-red-600 mt-1">{{ errors[name] }}</p>
        </div>

        <!-- Select Input -->
        <div v-else-if="type === 'select'">
            <label class="block text-sm font-medium leading-6 text-gray-900">{{ label }}</label>
            <select
                :name="name"
                :value="modelValue"
                @change="updateValue($event.target.value)"
                :disabled="disabled"
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6"
            >
                <option v-for="option in options" :key="option.value" :value="option.value">{{ option.text }}</option>
            </select>
            <p v-if="errors && errors[name]" class="text-sm text-red-600 mt-1">{{ errors[name] }}</p>
        </div>

        <!-- Radio Buttons -->
        <div v-else-if="type === 'radio'" class="radio-group">
            <label v-for="(option, index) in options" :key="index"
                   class="block text-sm font-medium leading-6 text-gray-900">
                <input
                    type="radio"
                    :name="name"
                    :value="option.value"
                    @change="updateValue(option.value)"
                    :checked="modelValue === option.value"
                    :disabled="disabled"
                >{{ option.text }}
            </label>
            <p v-if="errors && errors[name]" class="text-sm text-red-600 mt-1">{{ errors[name] }}</p>
        </div>

        <!-- Checkbox -->
        <div v-else-if="type === 'checkbox'">
            <div class="flex gap-2 items-center">
                <input
                    type="checkbox"
                    :name="name"
                    :checked="modelValue"
                    @change="updateValue($event.target.checked)"
                    :disabled="disabled"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm"
                />
                <label class="block text-sm font-medium leading-6 text-gray-900">{{ label }}</label>
            </div>
            <p v-if="errors && errors[name]" class="text-sm text-red-600 mt-1">{{ errors[name] }}</p>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    modelValue: [String, Number, Boolean, Array],
    type: {
        type: String,
        default: 'text',
    },
    name: String,
    label: String,
    placeholder: String,
    options: Array,
    errors: Object,
    disabled: Boolean,
    readonly: Boolean,
});

const emit = defineEmits(['update:modelValue'])

function updateValue(value) {
    emit('update:modelValue', value);
}
</script>
