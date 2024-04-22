import PageNotification from './PageNotification.vue';

export default {
    title: 'Components/PageNotification',
    component: PageNotification,
};

const Template = (args) => ({
    components: { PageNotification },
    setup() {
        return { args };
    },
    template: '<PageNotification v-bind="args" />',
});

export const Default = Template.bind({});
Default.args = {
    externalMessages: [
        { message: 'Message 1', bgColor: 'rgb(229 231 235)', textColor: 'rgb(17 24 39)' },
        { message: 'Message 2', bgColor: 'rgb(134 239 172)', textColor: 'rgb(17 24 39)' },
    ],
};
