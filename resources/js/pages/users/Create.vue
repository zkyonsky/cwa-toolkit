<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronLeft } from '@lucide/vue';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'User Create',
                href: '/users/create',
            },
        ],
    },
});

defineProps<{
    roles: Array<string>,
    govs: Array<{
        id: number;
        name: string;
        level: string;
    }>
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
    gov: '',
    assessee: true,
});

const submit = () => {
    form.post('/users');
}
</script>

<template>

    <Head title="User Create" />



    <Link href="/users" class="m-2 w-20 px-3 py-2 bg-primary text-xs text-white rounded-full">
        <div class="flex items-center gap-2">
            <ChevronLeft :size="16" /> Back
        </div>
    </Link>

    <div class="p-4">
        <form @submit.prevent="submit">
            <div class="space-y-4 mt-4">
                <Label for="name">Name</Label>
                <Input v-model="form.name" type="text" placeholder="Name" />
                <div class="text-small text-red-500" v-if="form.errors.name">{{ form.errors.name }}</div>
            </div>
            <div class="space-y-4 mt-4">
                <Label for="email">Email</Label>
                <Input v-model="form.email" type="email" placeholder="Email" />
                <div class="text-small text-red-500" v-if="form.errors.email">{{ form.errors.email }}</div>
            </div>
            <div class="space-y-4 mt-4">
                <Label for="password">Password</Label>
                <Input v-model="form.password" type="password" placeholder="Password" />
                <div class="text-small text-red-500" v-if="form.errors.password">{{ form.errors.password }}</div>
            </div>
            <div class="space-y-4 mt-4">
                <Label for="role">Role</Label>
                <select v-model="form.role"
                    class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background disabled:cursor-not-allowed disabled:opacity-50">
                    <option disabled value="">Select a role</option>
                    <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                </select>
                <div class="text-small text-red-500" v-if="form.errors.role">{{ form.errors.role }}</div>
            </div>
            <!-- Conditional fields for User role -->
            <div v-if="form.role === 'User'" class="space-y-4 mt-4">
                <Label for="assessee">Assign as Assessee</Label>
                <select v-model="form.assessee" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <option :value="true">Yes</option>
                    <option :value="false">No</option>
                </select>
                <Label for="gov">Gov</Label>
                <select v-model="form.gov" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                    <option disabled value="">Select Gov</option>
                    <option v-for="g in govs" :key="g.id" :value="g.id">{{ g.name }}</option>
                </select>
            </div>
            <Button type="submit" :disabled="form.processing" class="mt-4">Create</Button>
        </form>
    </div>
</template>
