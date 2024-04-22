import BaseTable from './BaseTable.vue';

export default {
    title: 'Example/BaseTable',
    component: BaseTable,
};

const Template = (args) => ({
    components: { BaseTable },
    setup() {
        return { args };
    },
    template: '<BaseTable v-bind="args" />',
});

export const Basic = Template.bind({});
Basic.args = {
    columns: [
        { name: 'Name', key: 'name', type: 'string', defaultValue: 'N/A' },
        { name: 'Age', key: 'age', type: 'string', defaultValue: 'N/A' },
    ],
    items: [
        { id: 1, name: 'John Doe', age: 30 },
        { id: 2, name: 'Jane Smith', age: 25 },
    ],
};

export const WithDates = Template.bind({});
WithDates.args = {
    columns: [
        { name: 'Event', key: 'event', type: 'string', defaultValue: 'N/A' },
        { name: 'Date', key: 'date', type: 'date', defaultValue: 'N/A' },
    ],
    items: [
        { id: 1, event: 'Birthday', date: '1990-05-15' },
        { id: 2, event: 'Anniversary', date: '2005-08-22' },
    ],
};

export const Empty = Template.bind({});
Empty.args = {
    columns: [
        { name: 'Name', key: 'name', type: 'string', defaultValue: 'N/A' },
        { name: 'Email', key: 'email', type: 'string', defaultValue: 'N/A' },
    ],
    items: [],
};
