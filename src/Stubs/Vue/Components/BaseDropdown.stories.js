import BaseDropdown from './BaseDropdown.vue';

export default {
    title: 'Components/BaseDropdown',
    component: BaseDropdown,
};

const Template = (args) => ({
    components: { BaseDropdown },
    setup() {
        return { args };
    },
    template: '<BaseDropdown v-bind="args" />',
});

export const Default = Template.bind({});
Default.args = {
    buttonLabel: 'Dropdown Label',
    modelValue: '',
    options: [
        { title: 'Option 1', value: '1' },
        { title: 'Option 2', value: '2' },
        { title: 'Option 3', value: '3' },
    ],
};
