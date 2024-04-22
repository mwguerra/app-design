import BaseSelect from './BaseSelect.vue';

export default {
    title: 'Components/BaseSelect',
    component: BaseSelect,
};

const Template = (args) => ({
    components: { BaseSelect },
    setup() {
        return { args };
    },
    template: '<BaseSelect v-bind="args" />',
});

export const Default = Template.bind({});
Default.args = {
    modelValue: '',
    options: [
        { text: 'Option 1', value: '1' },
        { text: 'Option 2', value: '2' },
        { text: 'Option 3', value: '3' },
    ],
    label: 'Select Label',
};
