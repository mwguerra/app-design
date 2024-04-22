import BaseInput from './BaseInput.vue';

export default {
    title: 'Components/BaseInput',
    component: BaseInput,
};

const Template = (args) => ({
    components: { BaseInput },
    setup() {
        return { args };
    },
    template: '<BaseInput v-bind="args" />',
});

export const Text = Template.bind({});
Text.args = {
    type: 'text',
    modelValue: '',
    label: 'Text Input',
    placeholder: 'Type here...',
};

export const Textarea = Template.bind({});
Textarea.args = {
    type: 'textarea',
    modelValue: '',
    label: 'Textarea',
    placeholder: 'Type here...',
};

export const Date = Template.bind({});
Date.args = {
    type: 'date',
    modelValue: '',
    label: 'Date Input',
};

export const Select = Template.bind({});
Select.args = {
    type: 'select',
    modelValue: '',
    options: [
        { text: 'Option 1', value: '1' },
        { text: 'Option 2', value: '2' },
        { text: 'Option 3', value: '3' },
    ],
    label: 'Select Input',
};

export const Radio = (args) => ({
    components: { BaseInput },
    setup() {
        return { args };
    },
    template: `
    <div>
      <BaseInput type="radio" v-bind="args" option-value="1" label="Option 1" />
      <BaseInput type="radio" v-bind="args" option-value="2" label="Option 2" />
      <BaseInput type="radio" v-bind="args" option-value="3" label="Option 3" />
    </div>
  `,
});
Radio.args = {
    modelValue: '',
    name: 'radioGroup',
};

export const Checkbox = Template.bind({});
Checkbox.args = {
    type: 'checkbox',
    modelValue: true,
    label: 'Checkbox Input',
};
