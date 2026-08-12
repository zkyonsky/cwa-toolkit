import { config } from '@vue/test-utils';

// Global mock for Inertia components often used in Vue pages
config.global.components = {
    Head: {
        template: '<slot />',
    },
    Link: {
        template: '<a><slot /></a>',
    },
};
