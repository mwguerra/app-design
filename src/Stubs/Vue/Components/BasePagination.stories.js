import BasePagination from './BasePagination.vue';

export default {
    title: 'Components/BasePagination',
    component: BasePagination,
};

const Template = (args) => ({
    components: { BasePagination },
    setup() {
        return { args };
    },
    template: '<BasePagination v-bind="args" />',
});

export const Default = Template.bind({});
Default.args = {
    data: {
        prev_page_url: '#',
        next_page_url: '#',
        from: 1,
        to: 10,
        total: 100,
        links: [
            { url: '#', label: '1', active: false },
            { url: '#', label: '2', active: true },
            { url: '#', label: '3', active: false },
        ],
    },
};
