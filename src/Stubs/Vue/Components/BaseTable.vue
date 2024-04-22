<script setup lang="ts">
import axios from 'axios'
import { router } from '@inertiajs/vue3'

interface TableColumn {
    name: string;
    key: string;
    type: 'string' | 'date'; // Extend this union type as needed
    defaultValue: string;
}

// // Column definitions
// const columns = ref([
//     { name: 'Name', key: 'name', type: 'string', defaultValue: 'N/A' },
//     { name: 'Email', key: 'email', type: 'string', defaultValue: 'N/A' },
//     { name: 'Registered On', key: 'registered', type: 'date', defaultValue: 'N/A' },
// ]);
//
// // Sample data items
// const items = ref([
//     { id: 1, name: 'John Doe', email: 'john@example.com', registered: '2020-01-01' },
//     { id: 2, name: 'Jane Doe', email: 'jane@example.com', registered: '2020-02-15' },
//     { id: 3, name: 'Jim Beam', email: 'jim@example.com', registered: '2020-03-20' },
// ]);

const props = withDefaults(defineProps<{
    columns: TableColumn[];
    items: Array<any>;
    createRoute: String;
    editRoute: String;
    deleteRoute: String;
    showRoute: String;
}>(), {
    columns: () => [], // Default to an empty array if not provided
    items: () => [], // Default to an empty array if not provided
    createRoute: '',
    editRoute: '',
    deleteRoute: '',
    showRoute: '',
});

const getDisplayValue = (type, value) => {
    if (type === 'date') {
        return new Date(value).toLocaleDateString();
    }
    // Extend this function based on your needs
    return value;
};

const handleDelete = async (id) => {
    try {
        await axios.post(route(props.deleteRoute, id))
    } catch (error) {
        console.error('An error occurred while trying to delete the item.', error)
    }
}

const handleView = (id) => {
    router.visit(route(props.showRoute, id))
}

const handleEdit = (id) => {
    router.visit(route(props.editRoute, id))
}

const handleCreate = () => {
    router.visit(route(props.createRoute))
}
</script>

<template>
    <table class="min-w-full leading-normal">
        <thead>
        <tr>
            <!-- Use TailwindCSS for styling -->
            <th
                v-for="column in columns"
                :key="column.key"
                class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                {{ column.name }}
            </th>
            <th scope="col" class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                <span class="sr-only">Actions</span>
            </th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="item in items" :key="item.id" class="hover:bg-gray-200">
            <td
                v-for="column in columns"
                :key="column.key"
                class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                <!-- Display formatted value or default -->
                {{ getDisplayValue(column.type, item[column.key]) || column.defaultValue }}
            </td>
            <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                <div class="inline-flex gap-2">
                    <a href="#" class="text-indigo-600 hover:text-indigo-900" @click.prevent="handleView(item.id)">
                        View<span class="sr-only">, {{ item.name }}</span>
                    </a>
                    <a href="#" class="text-indigo-600 hover:text-indigo-900" @click.prevent="handleEdit(item.id)">
                        Edit<span class="sr-only">, {{ item.name }}</span>
                    </a>
                    <a href="#" class="text-indigo-600 hover:text-indigo-900" @click.prevent="handleDelete(item.id)">
                        Delete<span class="sr-only">, {{ item.name }}</span>
                    </a>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</template>
