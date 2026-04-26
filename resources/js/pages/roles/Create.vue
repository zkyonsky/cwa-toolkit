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
                title: 'Role Create',
                href: '/roles/create',
            },
        ],
    },
});

defineProps({
    permissions: Array<string>
});

const form = useForm({
    name: '',
    permissions: [],
});

const submit = () => {
    form.post('/roles');
}
</script>

<template>

    <Head title="Role Create" />



    <Link href="/roles" class="m-2 w-20 px-3 py-2 bg-blue-500 text-xs text-white rounded-full">
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
                <Label for="permission">Permission</Label>
                <div class="flex flex-wrap gap-4">
                    <Label v-for="permission in permissions">
                        <input type="checkbox" v-model="form.permissions" :value="permission"
                            class="form-checkbox h-5 w-5" />
                        {{ permission }}
                    </Label>
                </div>
                <div class="text-small text-red-500" v-if="form.errors.permissions">{{ form.errors.permissions }}</div>
            </div>
            <Button type="submit" :disabled="form.processing" class="mt-4">Create</Button>
        </form>
    </div>
</template>
